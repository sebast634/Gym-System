<?php
session_start();

if(!isset($_SESSION['temp_id']) || !isset($_SESSION['codigo_2fa'])){
    header("Location: login.php");
    exit();
}

include("enviar_codigo.php");

$mensaje = "";

/* =========================
   INIT SEGURIDAD
========================= */

if(!isset($_SESSION['expira_2fa'])){
    $_SESSION['expira_2fa'] = time() + 60;
}

if(!isset($_SESSION['intentos_2fa'])){
    $_SESSION['intentos_2fa'] = 0;
}

/* =========================
   REENVIAR CÓDIGO
========================= */

if(isset($_POST['reenviar'])){

    $nuevoCodigo = rand(100000, 999999);

    $_SESSION['codigo_2fa'] = $nuevoCodigo;
    $_SESSION['expira_2fa'] = time() + 60;
    $_SESSION['intentos_2fa'] = 0;

    enviarCodigo($_SESSION['temp_email'], $nuevoCodigo);

    $mensaje = "Código reenviado.";

}

/* =========================
   VERIFICAR
========================= */

if(isset($_POST['verificar'])){

    $codigo = trim($_POST['codigo']);

    $valido = isset($_SESSION['codigo_2fa']);
    $no_expirado = time() <= $_SESSION['expira_2fa'];

    if($valido && $no_expirado && $codigo == $_SESSION['codigo_2fa']){

        // login final
        $_SESSION['id'] = $_SESSION['temp_id'];
        $_SESSION['nombre'] = $_SESSION['temp_nombre'];
        $_SESSION['rol'] = $_SESSION['temp_rol'];

        unset($_SESSION['temp_id']);
        unset($_SESSION['temp_nombre']);
        unset($_SESSION['temp_email']);
        unset($_SESSION['temp_rol']);
        unset($_SESSION['codigo_2fa']);
        unset($_SESSION['expira_2fa']);
        unset($_SESSION['intentos_2fa']);
        if($_SESSION['rol'] == 'admin'){
            header("Location: dashboard_admin.php");
        }else{
            header("Location: dashboard_user.php");
        }
        exit();
    }else{

        $_SESSION['intentos_2fa']++;

        if($_SESSION['intentos_2fa'] >= 3){

            unset($_SESSION['codigo_2fa']);
            unset($_SESSION['expira_2fa']);

            $mensaje = "Demasiados intentos. Vuelve a iniciar sesión.";

        }else{

            $mensaje = "Código incorrecto. Intento " . $_SESSION['intentos_2fa'] . "/3";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verificación 2FA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark">

<div class="container mt-5">
<div class="row justify-content-center">
<div class="col-md-4">

<div class="card shadow">
<div class="card-body">

<h3 class="text-center mb-3">Verificación 2FA</h3>

<div class="alert alert-info text-center">
Ingresa el código enviado a tu correo
</div>

<?php if($mensaje != ""){ ?>
<div class="alert alert-warning">
<?php echo $mensaje; ?>
</div>
<?php } ?>

<form method="POST">

<div class="mb-3">
<label>Código</label>
<input type="text" name="codigo" class="form-control" required>
</div>

<button type="submit" name="verificar" class="btn btn-primary w-100 mb-2">
Verificar
</button>

<button type="submit" name="reenviar" class="btn btn-secondary w-100">
Reenviar código
</button>

</form>

</div>
</div>

</div>
</div>
</div>

</body>
</html>