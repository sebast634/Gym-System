<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

/* =========================
   ELIMINAR FAVORITO
========================= */
if(isset($_GET['delete'])){

    $id_fav = $_GET['delete'];

    mysqli_query($conn, "
        DELETE FROM Favorito
        WHERE id_favorito='$id_fav'
        AND id_usuario='$id_usuario'
    ");

    header("Location: favoritos.php");
    exit();
}

/* =========================
   COMPRAR DESDE FAVORITOS
========================= */
if(isset($_POST['buy'])){

    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    $res = mysqli_query($conn,"
        SELECT stock 
        FROM Producto 
        WHERE id_producto='$id_producto'
    ");

    $p = mysqli_fetch_assoc($res);

    if($p['stock'] < $cantidad){

        header("Location: favoritos.php?error=stock");
        exit();
    }

    /* enviar al carrito */
    mysqli_query($conn, "
        INSERT INTO Carrito (id_usuario, id_producto, cantidad)
        VALUES ('$id_usuario', '$id_producto', '$cantidad')
    ");

    header("Location: carrito.php");
    exit();
}

/* =========================
   LISTAR FAVORITOS
========================= */
$favoritos = mysqli_query($conn, "
    SELECT 
        f.id_favorito,
        p.id_producto,
        p.nombre,
        p.precio,
        p.imagen,
        p.stock
    FROM Favorito f
    INNER JOIN Producto p ON f.id_producto = p.id_producto
    WHERE f.id_usuario='$id_usuario'
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis Favoritos</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

<h2>⭐ Mis Favoritos</h2>

<!-- MENSAJE ERROR STOCK -->
<?php if(isset($_GET['error']) && $_GET['error'] == "stock"){ ?>
<div class="alert alert-danger">
    No hay suficiente stock disponible.
</div>
<?php } ?>

<table class="table table-striped mt-3">

<tr>
<th>Imagen</th>
<th>Producto</th>
<th>Precio</th>
<th>Stock</th>
<th>Cantidad</th>
<th>Acción</th>
</tr>

<?php while($f = mysqli_fetch_assoc($favoritos)){ ?>

<tr>

<td>
<img src="uploads/<?php echo $f['imagen']; ?>" width="60">
</td>

<td><?php echo $f['nombre']; ?></td>
<td>$<?php echo $f['precio']; ?></td>
<td><?php echo $f['stock']; ?></td>

<td>

<form method="POST">

<input type="hidden" name="id_producto" value="<?php echo $f['id_producto']; ?>">

<input type="number"
       name="cantidad"
       value="1"
       min="1"
       max="<?php echo $f['stock']; ?>"
       class="form-control form-control-sm">

</td>

<td>

<?php if($f['stock'] > 0){ ?>

<button name="buy" class="btn btn-success btn-sm">
🛒 Comprar
</button>

<?php } else { ?>

<button class="btn btn-secondary btn-sm" disabled>
Sin stock
</button>

<?php } ?>

<a href="?delete=<?php echo $f['id_favorito']; ?>"
class="btn btn-danger btn-sm">
Eliminar
</a>

</form>

</td>

</tr>

<?php } ?>

</table>

<a href="dashboard_user.php" class="btn btn-secondary">
⬅ Volver
</a>

</div>

</body>
</html>