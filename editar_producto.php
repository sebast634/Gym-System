<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin'){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: gestionar_productos.php");
    exit();
}

$id_producto = $_GET['id'];

/* =========================
   OBTENER PRODUCTO
========================= */
$producto = mysqli_query($conn,"
    SELECT *
    FROM Producto
    WHERE id_producto='$id_producto'
");

$producto = mysqli_fetch_assoc($producto);

if(!$producto){
    header("Location: gestionar_productos.php");
    exit();
}

/* =========================
   ACTUALIZAR PRODUCTO
========================= */
if(isset($_POST['guardar'])){

    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $estado = $_POST['estado'];
    $id_categoria = $_POST['id_categoria'];

    mysqli_query($conn,"
        UPDATE Producto
        SET
        nombre='$nombre',
        marca='$marca',
        descripcion='$descripcion',
        precio='$precio',
        stock='$stock',
        estado='$estado',
        id_categoria='$id_categoria'

        WHERE id_producto='$id_producto'
    ");

    header("Location: editar_producto.php?id=".$id_producto);
    exit();
}

/* =========================
   ELIMINAR IMAGEN
========================= */
if(isset($_GET['delete_img'])){

    $id_imagen = $_GET['delete_img'];

    $img = mysqli_query($conn,"
        SELECT *
        FROM Producto_Imagen
        WHERE id_imagen='$id_imagen'
    ");

    $img = mysqli_fetch_assoc($img);

    if($img){

        $ruta = "uploads/".$img['imagen'];

        if(file_exists($ruta)){
            unlink($ruta);
        }

        mysqli_query($conn,"
            DELETE FROM Producto_Imagen
            WHERE id_imagen='$id_imagen'
        ");
    }

    header("Location: editar_producto.php?id=".$id_producto);
    exit();
}

/* =========================
   AGREGAR IMÁGENES
========================= */
if(isset($_POST['subir_imagenes'])){

    if(isset($_FILES['imagenes'])){

        $total = count($_FILES['imagenes']['name']);

        for($i=0; $i<$total; $i++){

            $tmp = $_FILES['imagenes']['tmp_name'][$i];

            if($tmp){

                $name = time()."_".$_FILES['imagenes']['name'][$i];

                move_uploaded_file($tmp,"uploads/".$name);

                mysqli_query($conn,"
                    INSERT INTO Producto_Imagen
                    (id_producto, imagen)
                    VALUES
                    ('$id_producto','$name')
                ");
            }
        }
    }

    header("Location: editar_producto.php?id=".$id_producto);
    exit();
}

/* =========================
   CATEGORÍAS
========================= */
$categorias = mysqli_query($conn,"
    SELECT *
    FROM Categoria
");

/* =========================
   IMÁGENES
========================= */
$imagenes = mysqli_query($conn,"
    SELECT *
    FROM Producto_Imagen
    WHERE id_producto='$id_producto'
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Producto</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#121212;
    color:white;
}

.card-dark{
    background:#1e1e1e;
    border:none;
    border-radius:15px;
}

.form-control,
.form-select,
textarea{
    background:#2a2a2a !important;
    border:1px solid #444 !important;
    color:white !important;
}

.form-control:focus,
.form-select:focus,
textarea:focus{
    background:#2a2a2a !important;
    color:white !important;
    border-color:#0d6efd !important;
    box-shadow:none !important;
}

.img-box{
    position:relative;
}

.img-box img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:10px;
}

.delete-btn{
    position:absolute;
    top:10px;
    right:10px;
}

</style>

</head>

<body>

<div class="container py-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h1>
✏ Editar Producto
</h1>

<a href="gestionar_productos.php"
class="btn btn-outline-light">

← Volver

</a>

</div>

<div class="card card-dark p-4 mb-4">

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">
<label>Nombre</label>
<input type="text"
       name="nombre"
       class="form-control"
       value="<?php echo $producto['nombre']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Marca</label>
<input type="text"
       name="marca"
       class="form-control"
       value="<?php echo $producto['marca']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Precio</label>
<input type="number"
       step="0.01"
       name="precio"
       class="form-control"
       value="<?php echo $producto['precio']; ?>">
</div>

<div class="col-md-6 mb-3">
<label>Stock</label>
<input type="number"
       name="stock"
       class="form-control"
       value="<?php echo $producto['stock']; ?>">
</div>

<div class="col-md-6 mb-3">

<label>Categoría</label>

<select name="id_categoria" class="form-select">

<?php while($c = mysqli_fetch_assoc($categorias)){ ?>

<option value="<?php echo $c['id_categoria']; ?>"

<?php
if($producto['id_categoria'] == $c['id_categoria']){
    echo "selected";
}
?>>

<?php echo $c['nombre_categoria']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Estado</label>

<select name="estado" class="form-select">

<option value="activo"
<?php if($producto['estado']=="activo") echo "selected"; ?>>
Activo
</option>

<option value="inactivo"
<?php if($producto['estado']=="inactivo") echo "selected"; ?>>
Inactivo
</option>

</select>

</div>

<div class="col-12 mb-3">

<label>Descripción</label>

<textarea name="descripcion"
          rows="5"
          class="form-control"><?php echo $producto['descripcion']; ?></textarea>

</div>

</div>

<button class="btn btn-success w-100"
        name="guardar">

💾 Guardar cambios

</button>

</form>

</div>

<!-- IMÁGENES -->
<div class="card card-dark p-4">

<h3 class="mb-4">
🖼 Imágenes del producto
</h3>

<div class="row">

<?php while($img = mysqli_fetch_assoc($imagenes)){ ?>

<div class="col-md-3 mb-4">

<div class="img-box">

<img src="uploads/<?php echo $img['imagen']; ?>">

<a href="?id=<?php echo $id_producto; ?>&delete_img=<?php echo $img['id_imagen']; ?>"
   class="btn btn-danger btn-sm delete-btn"
   onclick="return confirm('¿Eliminar imagen?')">

X

</a>

</div>

</div>

<?php } ?>

</div>

<hr class="text-secondary">

<form method="POST"
      enctype="multipart/form-data">

<label class="mb-2">
Agregar nuevas imágenes
</label>

<input type="file"
       name="imagenes[]"
       multiple
       class="form-control mb-3">

<button class="btn btn-primary"
        name="subir_imagenes">

📤 Subir imágenes

</button>

</form>

</div>

</div>

</body>
</html>