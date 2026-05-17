<?php
include("conexion.php");

$mensaje = "";

if(isset($_POST['registrar'])){

    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);
    $confirmar = trim($_POST['confirmar']);

    // VALIDACIONES

    if(empty($nombre) || empty($correo) || empty($password) || empty($confirmar)){

        $mensaje = "Todos los campos son obligatorios.";

    }elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)){

        $mensaje = "Correo inválido.";

    }elseif(strlen($password) < 6){

        $mensaje = "La contraseña debe tener al menos 6 caracteres.";

    }elseif($password != $confirmar){

        $mensaje = "Las contraseñas no coinciden.";

    }else{

        // VERIFICAR CORREO

        $sql = "SELECT * FROM Usuario WHERE correo='$correo'";
        $resultado = mysqli_query($conn, $sql);

        if(mysqli_num_rows($resultado) > 0){

            $mensaje = "El correo ya existe.";

        }else{

            // ENCRIPTAR CONTRASEÑA

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $insertar = "INSERT INTO Usuario
            (nombre, correo, contraseña, rol)
            VALUES
            ('$nombre', '$correo', '$passwordHash', 'cliente')";

            if(mysqli_query($conn, $insertar)){

                $mensaje = "Usuario registrado correctamente.";

            }else{

                $mensaje = "Error al registrar.";

            }

        }

    }

}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Registro</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-dark">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-body">

<h2 class="text-center mb-4">
Crear Cuenta
</h2>

<?php if($mensaje != ""){ ?>

<div class="alert alert-info">
<?php echo $mensaje; ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">
<label>Nombre</label>
<input type="text"
name="nombre"
class="form-control">
</div>

<div class="mb-3">
<label>Correo</label>
<input type="email"
name="correo"
class="form-control">
</div>

<div class="mb-3">
<label>Contraseña</label>
<input type="password"
name="password"
class="form-control"
required
pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[_\W]).{8,}$"
title="Debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un símbolo especial">
</div>

<div class="mb-3">
<label>Confirmar contraseña</label>
<input type="password"
name="confirmar"
class="form-control">
</div>

<button type="submit"
name="registrar"
class="btn btn-primary w-100">

Registrarse

</button>

</form>

<div class="text-center mt-3">

<a href="login.php">
¿Ya tienes cuenta? Inicia sesión
</a>

</div>

</div>
</div>
</div>
</div>
</div>

</body>
</html>