<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

/* =========================
   ACTUALIZAR CANTIDAD (INPUT)
========================= */
if(isset($_POST['update_qty'])){

    $id_carrito = $_POST['id_carrito'];
    $cantidad = (int)$_POST['cantidad'];

    // obtener stock del producto
    $res = mysqli_query($conn,"
        SELECT p.stock, c.id_producto
        FROM Carrito c
        INNER JOIN Producto p ON c.id_producto = p.id_producto
        WHERE c.id_carrito='$id_carrito'
    ");

    $data = mysqli_fetch_assoc($res);

    if(!$data){
        header("Location: carrito.php");
        exit();
    }

    // validar stock
    if($cantidad > $data['stock']){
        $cantidad = $data['stock'];
    }

    if($cantidad < 1){
        $cantidad = 1;
    }

    mysqli_query($conn,"
        UPDATE Carrito
        SET cantidad='$cantidad'
        WHERE id_carrito='$id_carrito'
    ");

    header("Location: carrito.php");
    exit();
}

/* =========================
   ELIMINAR
========================= */
if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    mysqli_query($conn, "
        DELETE FROM Carrito
        WHERE id_carrito='$id'
        AND id_usuario='$id_usuario'
    ");

    header("Location: carrito.php");
    exit();
}

/* =========================
   OBTENER CARRITO
========================= */
$carrito = mysqli_query($conn, "
    SELECT 
        c.id_carrito,
        p.nombre,
        p.precio,
        p.stock,
        c.cantidad,
        (p.precio * c.cantidad) AS total
    FROM Carrito c
    INNER JOIN Producto p ON c.id_producto = p.id_producto
    WHERE c.id_usuario='$id_usuario'
");

$subtotal = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carrito</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php include("navbar_user.php"); ?>

<div class="container mt-5">

<h2>🛒 Mi Carrito</h2>

<table class="table table-bordered mt-3">

<tr>
<th>Producto</th>
<th>Precio</th>
<th>Stock</th>
<th>Cantidad</th>
<th>Total</th>
<th>Acción</th>
</tr>

<?php while($c = mysqli_fetch_assoc($carrito)){

$subtotal += $c['total'];
?>

<tr>

<td><?php echo $c['nombre']; ?></td>
<td>$<?php echo $c['precio']; ?></td>
<td><?php echo $c['stock']; ?></td>

<!-- INPUT EDITABLE -->
<td>
<form method="POST" class="d-flex gap-2">

<input type="hidden" name="id_carrito" value="<?php echo $c['id_carrito']; ?>">

<input type="number"
       name="cantidad"
       value="<?php echo $c['cantidad']; ?>"
       min="1"
       max="<?php echo $c['stock']; ?>"
       class="form-control form-control-sm"
       style="width:80px;">

<button name="update_qty" class="btn btn-primary btn-sm">
✔
</button>

</form>
</td>

<td>$<?php echo $c['total']; ?></td>

<td>
<a href="?delete=<?php echo $c['id_carrito']; ?>"
class="btn btn-danger btn-sm">
X
</a>
</td>

</tr>

<?php } ?>

</table>

<h4 class="text-end">
Subtotal: $<?php echo $subtotal; ?>
</h4>

<form method="POST" action="checkout.php">
<button class="btn btn-success w-100 mt-3">
💳 Realizar compra
</button>
</form>

</div>

</body>
</html>