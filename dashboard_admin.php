<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin'){
    header("Location: login.php");
    exit();
}

/* =========================
   ELIMINAR USUARIO
========================= */
if(isset($_GET['delete_user'])){

    $id_delete = $_GET['delete_user'];

    // evitar eliminarse a sí mismo
    if($id_delete != $_SESSION['id']){

        mysqli_query($conn,"
            DELETE FROM Usuario
            WHERE id_usuario='$id_delete'
        ");
    }

    header("Location: dashboard_admin.php");
    exit();
}

/* =========================
   ESTADÍSTICAS GENERALES
========================= */

// TOTAL VENDIDO
$totalVendidoQuery = mysqli_query($conn,"
    SELECT COALESCE(SUM(total),0) AS total
    FROM Venta
");

$totalVendido = mysqli_fetch_assoc($totalVendidoQuery)['total'];

// PRODUCTOS VENDIDOS
$totalProductosVendidosQuery = mysqli_query($conn,"
    SELECT COALESCE(SUM(cantidad),0) AS total
    FROM detalle_venta
");

$totalProductosVendidos = mysqli_fetch_assoc($totalProductosVendidosQuery)['total'];

// TOTAL USUARIOS
$totalUsuariosQuery = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM Usuario
    WHERE rol='cliente'
");

$totalUsuarios = mysqli_fetch_assoc($totalUsuariosQuery)['total'];

// TOTAL PRODUCTOS
$totalProductosQuery = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM Producto
");

$totalProductos = mysqli_fetch_assoc($totalProductosQuery)['total'];

/* =========================
   PRODUCTOS MÁS VENDIDOS
========================= */
$topProductos = mysqli_query($conn,"
    SELECT 
        p.nombre,
        COALESCE(SUM(dv.cantidad),0) AS vendidos

    FROM Producto p

    LEFT JOIN detalle_venta dv
    ON p.id_producto = dv.id_producto

    GROUP BY p.id_producto

    ORDER BY vendidos DESC

    LIMIT 5
");

/* =========================
   USUARIOS
========================= */
$usuarios = mysqli_query($conn,"
    SELECT 
        u.id_usuario,
        u.nombre,
        u.correo,
        u.rol,
        u.fecha_registro,

        CASE
            WHEN u.rol = 'cliente'
            THEN COALESCE(SUM(v.total),0)
            ELSE 0
        END AS gastado

    FROM Usuario u

    LEFT JOIN Venta v
    ON u.id_usuario = v.id_usuario

    GROUP BY 
        u.id_usuario,
        u.nombre,
        u.correo,
        u.rol,
        u.fecha_registro

    ORDER BY u.id_usuario DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#121212;
}

.sidebar{
    width:260px;
    min-height:100vh;
    background:#000;
}

.sidebar a{
    text-decoration:none;
    color:white;
    display:block;
    padding:10px;
    border-radius:8px;
    transition:0.3s;
}

.sidebar a:hover{
    background:#222;
}

.card-dark{
    background:#1e1e1e;
    color:white;
    border:none;
}

.modal-content{
    background:#1e1e1e;
    color:white;
}

.stat-number{
    font-size:32px;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar p-3">

<h3 class="text-white">
ADMIN PANEL
</h3>

<hr class="text-secondary">

<a href="#">📊 Dashboard</a>

<a href="categorias.php">
📂 Crear Categoria
</a>

<a href="producto_create.php">
📦 Añadir Producto
</a>

<a href="gestionar_productos.php">
📦 Gestionar Productos
</a>
<a href="logout.php"
class="btn btn-danger mt-4 w-100">

Cerrar sesión

</a>

</div>

<!-- CONTENIDO -->
<div class="p-4 w-100 text-white">

<h2>
Bienvenido Admin:
<?php echo $_SESSION['nombre']; ?>
</h2>

<!-- ESTADÍSTICAS -->
<div class="row mt-4">

<div class="col-md-3 mb-3">

<div class="card card-dark">

<div class="card-body">

<h5>Total vendido</h5>

<div class="stat-number">
$<?php echo number_format($totalVendido,2); ?>
</div>

</div>

</div>

</div>

<div class="col-md-3 mb-3">

<div class="card card-dark">

<div class="card-body">

<h5>Productos vendidos</h5>

<div class="stat-number">
<?php echo $totalProductosVendidos; ?>
</div>

</div>

</div>

</div>

<div class="col-md-3 mb-3">

<div class="card card-dark">

<div class="card-body">

<h5>Usuarios</h5>

<div class="stat-number">
<?php echo $totalUsuarios; ?>
</div>

</div>

</div>

</div>

<div class="col-md-3 mb-3">

<div class="card card-dark">

<div class="card-body">

<h5>Productos</h5>

<div class="stat-number">
<?php echo $totalProductos; ?>
</div>

</div>

</div>

</div>

</div>

<!-- PRODUCTOS MÁS VENDIDOS -->
<div class="card card-dark mt-4">

<div class="card-body">

<h4>
🔥 Productos más vendidos
</h4>

<table class="table table-dark table-hover mt-3">

<tr>
<th>Producto</th>
<th>Cantidad vendida</th>
</tr>

<?php while($p = mysqli_fetch_assoc($topProductos)){ ?>

<tr>

<td>
<?php echo $p['nombre']; ?>
</td>

<td>
<?php echo $p['vendidos']; ?>
</td>

</tr>

<?php } ?>

</table>

</div>

</div>

<!-- USUARIOS -->
<div class="card card-dark mt-4">

<div class="card-body">

<h4>
👥 Usuarios registrados
</h4>

<div class="table-responsive">

<table class="table table-dark table-hover mt-3 align-middle">

<tr>
<th>ID</th>
<th>Nombre</th>
<th>Correo</th>
<th>Rol</th>
<th>Registro</th>
<th>Total gastado</th>
<th>Acciones</th>
</tr>

<?php
mysqli_data_seek($usuarios, 0);

while($u = mysqli_fetch_assoc($usuarios)){
?>

<tr>

<td>
<?php echo $u['id_usuario']; ?>
</td>

<td>
<?php echo $u['nombre']; ?>
</td>

<td>
<?php echo $u['correo']; ?>
</td>

<td>
<?php echo $u['rol']; ?>
</td>

<td>
<?php echo $u['fecha_registro']; ?>
</td>

<td>
$<?php echo number_format($u['gastado'],2); ?>
</td>

<td>

<button class="btn btn-warning btn-sm"
data-bs-toggle="modal"
data-bs-target="#fav<?php echo $u['id_usuario']; ?>">
Favoritos
</button>

<button class="btn btn-info btn-sm"
data-bs-toggle="modal"
data-bs-target="#compras<?php echo $u['id_usuario']; ?>">
Compras
</button>

<?php if($u['id_usuario'] == $_SESSION['id']){ ?>

<span class="badge bg-success">
Tú
</span>

<?php } else { ?>

<a href="?delete_user=<?php echo $u['id_usuario']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('¿Eliminar usuario?')">
Eliminar
</a>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

<!-- =========================
MODALES FUERA DE LA TABLA
========================= -->

<?php
mysqli_data_seek($usuarios, 0);

while($u = mysqli_fetch_assoc($usuarios)){
?>

<!-- MODAL FAVORITOS -->
<div class="modal fade"
id="fav<?php echo $u['id_usuario']; ?>"
tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>
Favoritos de <?php echo $u['nombre']; ?>
</h5>

<button type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<?php

$favs = mysqli_query($conn,"
    SELECT p.nombre
    FROM Favorito f

    INNER JOIN Producto p
    ON f.id_producto = p.id_producto

    WHERE f.id_usuario='".$u['id_usuario']."'
");

if(mysqli_num_rows($favs) > 0){

    while($f = mysqli_fetch_assoc($favs)){

        echo "<p>⭐ ".$f['nombre']."</p>";
    }

}else{

    echo "<p>No tiene favoritos</p>";
}
?>

</div>

</div>

</div>

</div>

<!-- MODAL COMPRAS -->
<div class="modal fade"
id="compras<?php echo $u['id_usuario']; ?>"
tabindex="-1">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header">

<h5>
Compras de <?php echo $u['nombre']; ?>
</h5>

<button type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<?php

$compras = mysqli_query($conn,"
    SELECT *
    FROM Venta
    WHERE id_usuario='".$u['id_usuario']."'
    ORDER BY fecha DESC
");

if(mysqli_num_rows($compras) > 0){

while($venta = mysqli_fetch_assoc($compras)){

?>

<div class="border rounded p-3 mb-3">

<h5>
Venta #<?php echo $venta['id_venta']; ?>
</h5>

<p>
Fecha:
<?php echo $venta['fecha']; ?>
</p>

<p>
Estado:
<?php echo $venta['estado_venta']; ?>
</p>

<p>
Total:
$<?php echo number_format($venta['total'],2); ?>
</p>

<hr>

<?php

$detalles = mysqli_query($conn,"
    SELECT
        dv.cantidad,
        dv.subtotal,
        p.nombre

    FROM detalle_venta dv

    INNER JOIN Producto p
    ON dv.id_producto = p.id_producto

    WHERE dv.id_venta='".$venta['id_venta']."'
");

if(mysqli_num_rows($detalles) > 0){
?>

<ul>

<?php while($d = mysqli_fetch_assoc($detalles)){ ?>

<li>
<?php echo $d['nombre']; ?>
|
Cantidad:
<?php echo $d['cantidad']; ?>
|
Subtotal:
$<?php echo number_format($d['subtotal'],2); ?>
</li>

<?php } ?>

</ul>

<?php
}else{

echo "<p>No hay detalles</p>";
}
?>

</div>

<?php
}

}else{

echo "<p>No tiene compras registradas</p>";
}
?>

</div>

</div>

</div>

</div>

<?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>