<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin'){
    header("Location: login.php");
    exit();
}

/* =========================
   CREAR PRODUCTO
========================= */
if(isset($_POST['guardar'])){

    $nombre = $_POST['nombre'];
    $marca = $_POST['marca'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $id_categoria = $_POST['id_categoria'];
    $estado = $_POST['estado'];

    mysqli_query($conn, "
        INSERT INTO Producto
        (id_categoria, nombre, marca, descripcion, precio, stock, estado)
        VALUES
        ('$id_categoria','$nombre','$marca','$descripcion','$precio','$stock','$estado')
    ");

    $id_producto = mysqli_insert_id($conn);

    /* =========================
       SUBIR MÚLTIPLES IMÁGENES
    ========================= */
    if(isset($_FILES['imagenes'])){

        $total = count($_FILES['imagenes']['name']);

        for($i = 0; $i < $total; $i++){

            $tmp = $_FILES['imagenes']['tmp_name'][$i];
            $name = time() . "_" . $_FILES['imagenes']['name'][$i];

            move_uploaded_file($tmp, "uploads/".$name);

            mysqli_query($conn, "
                INSERT INTO Producto_Imagen (id_producto, imagen)
                VALUES ('$id_producto','$name')
            ");
        }
    }

    header("Location: dashboard_admin.php");
    exit();
}

/* =========================
   CATEGORÍAS
========================= */
$categorias = mysqli_query($conn, "SELECT * FROM Categoria");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Producto</title>

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

.form-control::placeholder,
textarea::placeholder{
    color:#aaa !important;
}

.form-select option{
    background:#2a2a2a;
    color:white;
}

.preview-box{
    border:2px dashed #444;
    border-radius:15px;
    padding:30px;
    text-align:center;
    background:#181818;
}

</style>

</head>

<body>

<div class="container py-5">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h1 class="fw-bold">
➕ Crear Producto
</h1>

<p class="text-secondary mb-0">
Agrega nuevos productos al catálogo
</p>

</div>

<a href="dashboard_admin.php" class="btn btn-outline-light">
← Volver al panel
</a>

</div>

<!-- CARD -->
<div class="card card-dark shadow-lg">

<div class="card-body p-4">

<form method="POST" enctype="multipart/form-data">

<div class="row">

<!-- NOMBRE -->
<div class="col-md-6 mb-4">

<label class="form-label">
Nombre del producto
</label>

<input type="text"
       name="nombre"
       class="form-control"
       placeholder="Ej: Whey Protein"
       required>

</div>

<!-- MARCA -->
<div class="col-md-6 mb-4">

<label class="form-label">
Marca
</label>

<input type="text"
       name="marca"
       class="form-control"
       placeholder="Ej: Optimum Nutrition">

</div>

<!-- PRECIO -->
<div class="col-md-6 mb-4">

<label class="form-label">
Precio
</label>

<input type="number"
       step="0.01"
       name="precio"
       class="form-control"
       placeholder="0.00"
       required>

</div>

<!-- STOCK -->
<div class="col-md-6 mb-4">

<label class="form-label">
Stock
</label>

<input type="number"
       name="stock"
       class="form-control"
       placeholder="0"
       required>

</div>

<!-- CATEGORÍA -->
<div class="col-md-6 mb-4">

<label class="form-label">
Categoría
</label>

<select name="id_categoria" class="form-select">

<?php while($c = mysqli_fetch_assoc($categorias)){ ?>

<option value="<?php echo $c['id_categoria']; ?>">

<?php echo $c['nombre_categoria']; ?>

</option>

<?php } ?>

</select>

</div>

<!-- ESTADO -->
<div class="col-md-6 mb-4">

<label class="form-label">
Estado
</label>

<select name="estado" class="form-select">

<option value="activo">
Activo
</option>

<option value="inactivo">
Inactivo
</option>

</select>

</div>

<!-- DESCRIPCIÓN -->
<div class="col-12 mb-4">

<label class="form-label">
Descripción
</label>

<textarea name="descripcion"
          class="form-control"
          rows="5"
          placeholder="Describe el producto..."></textarea>

</div>

<!-- IMÁGENES -->
<div class="col-12 mb-4">

<label class="form-label">
Imágenes del producto
</label>

<div class="preview-box">

<p class="mb-3 text-secondary">
Puedes seleccionar múltiples imágenes
</p>

<input type="file"
       name="imagenes[]"
       multiple
       class="form-control">

</div>

</div>

</div>

<button class="btn btn-success w-100 py-2 fw-bold"
        name="guardar">

💾 Guardar Producto

</button>

</form>

</div>

</div>

</div>

</body>
</html>