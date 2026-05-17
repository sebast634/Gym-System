<?php
session_start();

function requireLogin(){
    if(!isset($_SESSION['id'])){
        header("Location: login.php");
        exit();
    }
}

function requireAdmin(){
    if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin'){
        header("Location: dashboard.php");
        exit();
    }
}

function requireCliente(){
    if(!isset($_SESSION['id']) || $_SESSION['rol'] !== 'cliente'){
        header("Location: dashboard.php");
        exit();
    }
}
?>