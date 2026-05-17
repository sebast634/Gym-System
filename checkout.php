<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

/* =========================
   OBTENER CARRITO CON PRECIO
========================= */
$carrito = mysqli_query($conn,"
    SELECT 
        c.id_producto,
        c.cantidad,
        p.stock,
        p.precio
    FROM Carrito c
    INNER JOIN Producto p ON c.id_producto = p.id_producto
    WHERE c.id_usuario='$id_usuario'
");

if(mysqli_num_rows($carrito) == 0){
    die("Carrito vacío");
}

/* =========================
   VALIDAR STOCK
========================= */
$items = [];

while($item = mysqli_fetch_assoc($carrito)){

    $id_producto = $item['id_producto'];
    $cantidad = $item['cantidad'];
    $stock = $item['stock'];

    if($cantidad > $stock){
        die("Stock insuficiente en un producto del carrito");
    }

    $items[] = $item;
}

/* =========================
   CREAR VENTA
========================= */
mysqli_query($conn,"
    INSERT INTO Venta (id_usuario, fecha, total, estado_venta)
    VALUES ('$id_usuario', NOW(), 0, 'completada')
");

$id_venta = mysqli_insert_id($conn);

$total = 0;

/* =========================
   PROCESAR ITEMS
========================= */
foreach($items as $item){

    $id_producto = $item['id_producto'];
    $cantidad = $item['cantidad'];
    $precio = $item['precio'];

    $subtotal = $cantidad * $precio;
    $total += $subtotal;

    // detalle venta (IMPORTANTE)
    mysqli_query($conn,"
        INSERT INTO Detalle_Venta
        (id_venta, id_producto, cantidad, precio_unitario, subtotal)
        VALUES
        ('$id_venta', '$id_producto', '$cantidad', '$precio', '$subtotal')
    ");

    // descontar stock
    mysqli_query($conn,"
        UPDATE Producto 
        SET stock = stock - $cantidad
        WHERE id_producto='$id_producto'
    ");
}

/* =========================
   ACTUALIZAR TOTAL REAL
========================= */
mysqli_query($conn,"
    UPDATE Venta 
    SET total='$total'
    WHERE id_venta='$id_venta'
");

/* =========================
   LIMPIAR CARRITO
========================= */
mysqli_query($conn,"
    DELETE FROM Carrito 
    WHERE id_usuario='$id_usuario'
");

header("Location: historial_compras.php");
exit();
?>