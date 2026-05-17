<?php
session_start();

include("conexion.php");

$mensaje = "";

if(isset($_POST['login'])){

    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if(empty($correo) || empty($password)){

        $mensaje = "Completa todos los campos.";

    }else{

        // 🔐 QUERY SEGURA (evita SQL injection)
        $stmt = $conn->prepare("SELECT * FROM Usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if($resultado->num_rows > 0){

            $usuario = $resultado->fetch_assoc();

            // 🔐 verificar contraseña
            if(password_verify($password, $usuario['contraseña'])){

                include("enviar_codigo.php");

                // 🎲 generar código 2FA
                $codigo = rand(100000, 999999);

                // 🔐 sesiones temporales (ANTES del 2FA)
                $_SESSION['codigo_2fa'] = $codigo;
                $_SESSION['expira_2fa'] = time() + 60;

                $_SESSION['temp_id'] = $usuario['id_usuario'];
                $_SESSION['temp_nombre'] = $usuario['nombre'];
                $_SESSION['temp_email'] = $usuario['correo'];
                $_SESSION['temp_rol'] = $usuario['rol'];

                // 📩 enviar correo
                enviarCodigo($usuario['correo'], $codigo);

                // 🔁 ir a verificación
                header("Location: verificar_codigo.php");
                exit();

            }else{

                $mensaje = "Contraseña incorrecta.";

            }

        }else{

            $mensaje = "Usuario no encontrado.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-dark">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-body">

<h2 class="text-center mb-4">
Iniciar Sesión
</h2>

<?php if($mensaje != ""){ ?>

<div class="alert alert-danger">
<?php echo $mensaje; ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>Correo</label>

<input type="email"
name="correo"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Contraseña</label>

<input type="password"
name="password"
class="form-control"
required>

</div>

<button type="submit"
name="login"
class="btn btn-success w-100">

Ingresar

</button>

</form>

<div class="text-center mt-3">

<a href="registro.php">
¿No tienes cuenta? Crear cuenta
</a>

</div>

</div>
</div>
</div>
</div>
</div>

</body>
</html>