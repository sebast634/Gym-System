<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin'){
    header("Location: login.php");
    exit();
}

/* =========================
   ELIMINAR PRODUCTO
========================= */
if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    // eliminar imágenes físicas
    $imgs = mysqli_query($conn,"
        SELECT imagen
        FROM Producto_Imagen
        WHERE id_producto='$id'
    ");

    while($img = mysqli_fetch_assoc($imgs)){

        $ruta = "uploads/" . $img['imagen'];

        if(file_exists($ruta)){
            unlink($ruta);
        }
    }

    // eliminar imágenes BD
    mysqli_query($conn,"
        DELETE FROM Producto_Imagen
        WHERE id_producto='$id'
    ");

    // eliminar producto
    mysqli_query($conn,"
        DELETE FROM Producto
        WHERE id_producto='$id'
    ");

    header("Location: gestionar_productos.php");
    exit();
}

/* =========================
   AGREGAR STOCK
========================= */
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stock'])){

    $id_producto = $_POST['id_producto'];
    $stock_extra = (int)$_POST['stock_extra'];

    if($stock_extra > 0){

        mysqli_query($conn,"
            UPDATE Producto
            SET stock = stock + $stock_extra
            WHERE id_producto='$id_producto'
        ");
    }

    header("Location: gestionar_productos.php");
    exit();
}
/* =========================
   REDUCIR STOCK
========================= */
if(isset($_POST['remove_stock'])){

    $id_producto = $_POST['id_producto'];
    $stock_remove = (int)$_POST['stock_remove'];

    if($stock_remove > 0){

        mysqli_query($conn,"
            UPDATE Producto
            SET stock = GREATEST(stock - $stock_remove, 0)
            WHERE id_producto='$id_producto'
        ");
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
/* =========================
   EDITAR PRODUCTO
========================= */
if(isset($_POST['save_edit'])){

    $id_producto = $_POST['id_producto'];

    $nombre = mysqli_real_escape_string($conn,$_POST['nombre']);
    $marca = mysqli_real_escape_string($conn,$_POST['marca']);
    $descripcion = mysqli_real_escape_string($conn,$_POST['descripcion']);
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

    /* =========================
       ELIMINAR IMÁGENES
    ========================= */
    if(isset($_POST['delete_images'])){

        foreach($_POST['delete_images'] as $id_img){

            $imgQuery = mysqli_query($conn,"
                SELECT imagen
                FROM Producto_Imagen
                WHERE id_imagen='$id_img'
            ");

            $imgData = mysqli_fetch_assoc($imgQuery);

            if($imgData){

                $ruta = "uploads/" . $imgData['imagen'];

                if(file_exists($ruta)){
                    unlink($ruta);
                }

                mysqli_query($conn,"
                    DELETE FROM Producto_Imagen
                    WHERE id_imagen='$id_img'
                ");
            }
        }
    }

    /* =========================
       AGREGAR NUEVAS IMÁGENES
    ========================= */
    if(isset($_FILES['nuevas_imagenes'])){

        $total = count($_FILES['nuevas_imagenes']['name']);

        for($i = 0; $i < $total; $i++){

            if($_FILES['nuevas_imagenes']['error'][$i] == 0){

                $tmp = $_FILES['nuevas_imagenes']['tmp_name'][$i];

                $name = time() . "_" . $_FILES['nuevas_imagenes']['name'][$i];

                move_uploaded_file($tmp,"uploads/".$name);

                mysqli_query($conn,"
                    INSERT INTO Producto_Imagen(id_producto,imagen)
                    VALUES('$id_producto','$name')
                ");
            }
        }
    }

    header("Location: gestionar_productos.php");
    exit();
}

/* =========================
   PRODUCTOS
========================= */
$productos = mysqli_query($conn,"
    SELECT
        p.*,
        c.nombre_categoria
    FROM Producto p

    INNER JOIN Categoria c
    ON p.id_categoria = c.id_categoria

    ORDER BY p.id_producto DESC
");

/* =========================
   CATEGORÍAS
========================= */
$categorias = mysqli_query($conn,"
    SELECT *
    FROM Categoria
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestionar Productos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#121212;
    color:white;
}

.card-dark{
    background:#1e1e1e;
    border:none;
    color:white;
    border-radius:15px;
}

.table-dark-custom{
    background:#1e1e1e;
    color:white;
}

.table-dark-custom th{
    background:#000;
    border-color:#333;
}

.table-dark-custom td{
    border-color:#333;
    vertical-align:middle;
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

.product-img{
    width:70px;
    height:70px;
    object-fit:cover;
    border-radius:10px;
}

.gallery-img{
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:10px;
    border:2px solid #333;
}

.modal-content{
    background:#1e1e1e;
    color:white;
}

</style>

</head>

<body>

<div class="container py-5">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h1 class="fw-bold">
📦 Gestionar Productos
</h1>

<p class="text-secondary mb-0">
Administra productos, imágenes y stock
</p>

</div>

<a href="dashboard_admin.php"
class="btn btn-outline-light">

← Volver al panel

</a>

</div>

<!-- TABLA -->
<div class="card card-dark shadow-lg">

<div class="card-body">

<div class="table-responsive">

<table class="table table-dark table-hover table-dark-custom align-middle">

<tr>
<th>ID</th>
<th>Imagen</th>
<th>Producto</th>
<th>Categoría</th>
<th>Precio</th>
<th>Stock</th>
<th>Estado</th>
<th>Agregar stock</th>
<th>Reducir stock</th>
<th>Acciones</th>
</tr>

<?php while($p = mysqli_fetch_assoc($productos)){ ?>

<?php

$imgQuery = mysqli_query($conn,"
    SELECT imagen
    FROM Producto_Imagen
    WHERE id_producto='".$p['id_producto']."'
    LIMIT 1
");

$imgData = mysqli_fetch_assoc($imgQuery);

$img = "default.png";

if($imgData && $imgData['imagen']){
    $img = $imgData['imagen'];
}

?>

<tr>

<td>
#<?php echo $p['id_producto']; ?>
</td>

<td>

<img src="uploads/<?php echo $img; ?>"
class="product-img">

</td>

<td>

<div class="fw-bold">
<?php echo $p['nombre']; ?>
</div>

<small class="text-secondary">
<?php echo $p['marca']; ?>
</small>

</td>

<td>
<?php echo $p['nombre_categoria']; ?>
</td>

<td>
$<?php echo number_format($p['precio'],2); ?>
</td>

<td>

<?php if($p['stock'] > 0){ ?>

<span class="badge bg-success">
<?php echo $p['stock']; ?>
</span>

<?php } else { ?>

<span class="badge bg-danger">
Sin stock
</span>

<?php } ?>

</td>

<td>

<?php if($p['estado'] == "activo"){ ?>

<span class="badge bg-success">
Activo
</span>

<?php } else { ?>

<span class="badge bg-secondary">
Inactivo
</span>

<?php } ?>

</td>

<td>

<form method="POST" class="d-flex gap-2">

<input type="hidden"
name="id_producto"
value="<?php echo $p['id_producto']; ?>">

<input type="number"
name="stock_extra"
min="1"
class="form-control form-control-sm"
style="width:90px;"
placeholder="+ stock"
required>

<button type="submit"
        class="btn btn-primary btn-sm"
        name="add_stock"
        value="1">

➕

</button>

</form>

</td>

<td>

<form method="POST" class="d-flex gap-2">

<input type="hidden"
       name="id_producto"
       value="<?php echo $p['id_producto']; ?>">

<input type="number"
       name="stock_remove"
       min="1"
       max="<?php echo $p['stock']; ?>"
       class="form-control form-control-sm"
       style="width:90px;"
       placeholder="- stock"
       required>

<button type="submit"
        class="btn btn-warning btn-sm"
        name="remove_stock"
        value="1">

➖

</button>

</form>

</td>

<td class="d-flex gap-2">

<!-- EDITAR -->
<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#edit<?php echo $p['id_producto']; ?>">

✏ Editar

</button>

<!-- ELIMINAR -->
<a href="?delete=<?php echo $p['id_producto']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('¿Eliminar producto?')">

🗑

</a>

</td>

</tr>

<!-- MODAL EDITAR -->
<div class="modal fade"
id="edit<?php echo $p['id_producto']; ?>"
tabindex="-1">

<div class="modal-dialog modal-xl">

<div class="modal-content">

<form method="POST"
enctype="multipart/form-data">

<div class="modal-header">

<h5 class="modal-title">
Editar producto
</h5>

<button type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<input type="hidden"
name="id_producto"
value="<?php echo $p['id_producto']; ?>">

<div class="row">

<div class="col-md-6 mb-3">

<label>Nombre</label>

<input type="text"
name="nombre"
class="form-control"
value="<?php echo $p['nombre']; ?>">

</div>

<div class="col-md-6 mb-3">

<label>Marca</label>

<input type="text"
name="marca"
class="form-control"
value="<?php echo $p['marca']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Precio</label>

<input type="number"
step="0.01"
name="precio"
class="form-control"
value="<?php echo $p['precio']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Stock</label>

<input type="number"
name="stock"
class="form-control"
value="<?php echo $p['stock']; ?>">

</div>

<div class="col-md-4 mb-3">

<label>Estado</label>

<select name="estado"
class="form-select">

<option value="activo"
<?php if($p['estado']=="activo") echo "selected"; ?>>
Activo
</option>

<option value="inactivo"
<?php if($p['estado']=="inactivo") echo "selected"; ?>>
Inactivo
</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Categoría</label>

<select name="id_categoria"
class="form-select">

<?php

$cats = mysqli_query($conn,"
    SELECT *
    FROM Categoria
");

while($cat = mysqli_fetch_assoc($cats)){
?>

<option value="<?php echo $cat['id_categoria']; ?>"
<?php if($cat['id_categoria'] == $p['id_categoria']) echo "selected"; ?>>

<?php echo $cat['nombre_categoria']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-12 mb-3">

<label>Descripción</label>

<textarea name="descripcion"
class="form-control"
rows="4"><?php echo $p['descripcion']; ?></textarea>

</div>

<div class="col-12 mb-4">

<h5>Imágenes actuales</h5>

<div class="d-flex gap-3 flex-wrap mt-3">

<?php

$imagenes = mysqli_query($conn,"
    SELECT *
    FROM Producto_Imagen
    WHERE id_producto='".$p['id_producto']."'
");

while($imgP = mysqli_fetch_assoc($imagenes)){
?>

<div class="text-center">

<img src="uploads/<?php echo $imgP['imagen']; ?>"
class="gallery-img mb-2">

<div>

<label class="form-check-label">

<input type="checkbox"
name="delete_images[]"
value="<?php echo $imgP['id_imagen']; ?>"
class="form-check-input">

Eliminar

</label>

</div>

</div>

<?php } ?>

</div>

</div>

<div class="col-12">

<label>
Agregar nuevas imágenes
</label>

<input type="file"
name="nuevas_imagenes[]"
multiple
class="form-control">

</div>

</div>

</div>

<div class="modal-footer">

<button type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancelar

</button>

<button class="btn btn-success"
name="save_edit">

💾 Guardar cambios

</button>

</div>

</form>

</div>

</div>

</div>

<?php } ?>

</table>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>