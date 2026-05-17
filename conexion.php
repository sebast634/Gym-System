<?php

$host = "localhost";
$usuario = "root";
$password = "";
$bd = "gimnasio_store";

$conn = mysqli_connect($host, $usuario, $password, $bd);

if(!$conn){
    die("Error de conexión: " . mysqli_connect_error());
}

?>