<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

$ventas = mysqli_query($conn, "
    SELECT * FROM Venta 
    WHERE id_usuario='$id_usuario'
    ORDER BY fecha DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="container mt-5">

<h2>📜 Mis Compras</h2>

<table class="table table-dark mt-3">

<tr>
<th>ID</th>
<th>Fecha</th>
<th>Total</th>
</tr>

<?php while($v = mysqli_fetch_assoc($ventas)){ ?>

<tr>
<td><?php echo $v['id_venta']; ?></td>
<td><?php echo $v['fecha']; ?></td>
<td>$<?php echo $v['total']; ?></td>
</tr>

<?php } ?>

</table>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>