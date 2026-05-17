<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin'){
    header("Location: login.php");
    exit();
}

/* =========================
   CREAR CATEGORÍA
========================= */
if(isset($_POST['guardar'])){

    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);

    mysqli_query($conn, "
        INSERT INTO Categoria (nombre_categoria, descripcion)
        VALUES ('$nombre', '$descripcion')
    ");

    header("Location: categorias.php");
    exit();
}

/* =========================
   ELIMINAR (PROTEGIDO)
========================= */
if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    // verificar si tiene productos
    $check = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM Producto 
        WHERE id_categoria='$id'
    ");

    $count = mysqli_fetch_assoc($check)['total'];

    if($count > 0){

        echo "<script>
                alert('No puedes eliminar esta categoría porque tiene productos asociados');
                window.location.href='categorias.php';
              </script>";
        exit();
    }

    mysqli_query($conn, "
        DELETE FROM Categoria 
        WHERE id_categoria='$id'
    ");

    header("Location: categorias.php");
    exit();
}

/* =========================
   LISTAR CATEGORÍAS
========================= */
$categorias = mysqli_query($conn, "
    SELECT c.*,
    (SELECT COUNT(*) FROM Producto p WHERE p.id_categoria = c.id_categoria) AS total_productos
    FROM Categoria c
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Categorías</title>

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
    border-radius:15px;
    overflow:hidden;
}

.table-dark-custom th{
    background:#000;
    color:white;
    border-color:#333;
}

.table-dark-custom td{
    border-color:#333;
    vertical-align:middle;
}

.form-control,
textarea{
    background:#2a2a2a !important;
    border:1px solid #444 !important;
    color:white !important;
}

.form-control:focus,
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

.title-icon{
    font-size:28px;
}

</style>

</head>

<body>

<div class="container py-5">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h1 class="fw-bold">
📂 Gestión de Categorías
</h1>

<p class="text-secondary mb-0">
Administra las categorías del sistema
</p>
</div>

<a href="dashboard_admin.php" class="btn btn-outline-light">
← Volver al panel
</a>

</div>

<!-- FORM -->
<div class="card card-dark shadow-lg mb-4">

<div class="card-body p-4">

<h4 class="mb-4">
➕ Nueva categoría
</h4>

<form method="POST">

<div class="mb-3">

<label class="form-label">
Nombre de categoría
</label>

<input type="text"
       name="nombre"
       class="form-control"
       placeholder="Ej: Suplementos"
       required>

</div>

<div class="mb-3">

<label class="form-label">
Descripción
</label>

<textarea name="descripcion"
          class="form-control"
          rows="4"
          placeholder="Describe la categoría..."></textarea>

</div>

<button class="btn btn-success px-4" name="guardar">
➕ Crear categoría
</button>

</form>

</div>

</div>

<!-- TABLA -->
<div class="card card-dark shadow-lg">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">

<h4 class="mb-0">
📋 Categorías registradas
</h4>

<span class="badge bg-primary fs-6">
<?php echo mysqli_num_rows($categorias); ?> categorías
</span>

</div>

<div class="table-responsive">

<table class="table table-dark table-hover table-dark-custom align-middle">

<tr>
<th>ID</th>
<th>Nombre</th>
<th>Descripción</th>
<th>Productos</th>
<th>Acción</th>
</tr>

<?php while($c = mysqli_fetch_assoc($categorias)){ ?>

<tr>

<td>
#<?php echo $c['id_categoria']; ?>
</td>

<td class="fw-bold">
<?php echo $c['nombre_categoria']; ?>
</td>

<td>
<?php echo $c['descripcion']; ?>
</td>

<td>

<span class="badge bg-primary">
<?php echo $c['total_productos']; ?> productos
</span>

</td>

<td>

<?php if($c['total_productos'] == 0){ ?>

<a href="?delete=<?php echo $c['id_categoria']; ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('¿Eliminar categoría?')">

🗑 Eliminar

</a>

<?php } else { ?>

<button class="btn btn-secondary btn-sm" disabled>
🔒 En uso
</button>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</div>

</body>
</html>