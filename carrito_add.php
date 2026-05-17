<?php
session_start();
include("conexion.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];
$id_producto = $_POST['id_producto'];
$cantidad = (int)$_POST['cantidad'];

/* =========================
   OBTENER STOCK
========================= */
$res = mysqli_query($conn,"
    SELECT stock FROM Producto 
    WHERE id_producto='$id_producto'
");

$prod = mysqli_fetch_assoc($res);

if(!$prod || $prod['stock'] <= 0){

    header("Location: dashboard_user.php?msg=sin_stock");
    exit();
}

/* =========================
   CANTIDAD EN CARRITO
========================= */
$check = mysqli_query($conn,"
    SELECT cantidad FROM Carrito 
    WHERE id_usuario='$id_usuario' 
    AND id_producto='$id_producto'
");

$en_carrito = 0;

if(mysqli_num_rows($check) > 0){
    $en_carrito = mysqli_fetch_assoc($check)['cantidad'];
}

/* =========================
   LIMITE REAL DISPONIBLE
========================= */
$max_disponible = $prod['stock'];

/* si ya tiene algo en carrito, ajustar límite */
if(($en_carrito + $cantidad) > $max_disponible){

    // 🔥 ajustar automáticamente al máximo posible
    $cantidad = $max_disponible - $en_carrito;

    if($cantidad <= 0){
        header("Location: dashboard_user.php?msg=sin_stock");
        exit();
    }
}

/* =========================
   INSERT / UPDATE
========================= */
if($en_carrito > 0){

    mysqli_query($conn,"
        UPDATE Carrito 
        SET cantidad = cantidad + $cantidad
        WHERE id_usuario='$id_usuario' 
        AND id_producto='$id_producto'
    ");

}else{

    mysqli_query($conn,"
        INSERT INTO Carrito (id_usuario, id_producto, cantidad, fecha_creacion)
        VALUES ('$id_usuario','$id_producto','$cantidad',NOW())
    ");
}

header("Location: carrito.php");
exit();
?>