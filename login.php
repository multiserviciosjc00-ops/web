<?php
date_default_timezone_set('America/Bogota');
$mensaje = "";

// Si ya existe cookie de usuario activo (pero única para cada usuario)
if (!empty($_COOKIE['usuario_activo'])) {
    $usuarioActivo = $_COOKIE['usuario_activo'];
    session_name("turnos_" . $usuarioActivo);
    session_start();
    if (isset($_SESSION['usuario'])) {
        header("Location: seleccionar_caja.php");
        exit;
    }
}

if (isset($_POST['login'])) {
    require_once('funciones/conexion.php');
    require_once('funciones/funciones.php');

    $usuario   = limpiar($con, $_POST['usuario']);
    $password  = limpiar($con, $_POST['password']);
    $passwordEnc = encriptarMd5($password);

    if ($usuario != "" && $password != "") {
        $sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND password='$passwordEnc'";
        $res = consulta($con, $sql, "Error al logear al usuario");

        if (mysqli_num_rows($res) > 0) {
            $usuarioData = mysqli_fetch_assoc($res);

            // Crear sesión única para este usuario
            session_name("turnos_" . $usuario);
            session_start();

            $_SESSION['idUsuario'] = $usuarioData['id'];
            $_SESSION['usuario']   = $usuarioData['usuario'];

            // Guardar cookie específica de este usuario
            setcookie("usuario_activo", $usuario, time() + 86400, "/"); // 1 día

            // Verificar cajas asignadas
            $idUsuario = $usuarioData['id'];
            $sqlCajas = "SELECT * FROM usuario_caja WHERE idUsuario='$idUsuario'";
            $resCajas = consulta($con, $sqlCajas, "Error al verificar cajas del usuario");

            if (mysqli_num_rows($resCajas) > 0) {
                header("Location: seleccionar_caja.php");
                exit;
            } else {
                $mensaje = "No tiene cajas asignadas. Contacte al administrador.";
                $_SESSION = [];
                session_destroy();
                setcookie("usuario_activo", "", time() - 3600, "/");
            }
        } else {
            $mensaje = "Usuario o password incorrectos";
        }
    } else {
        $mensaje = "Hay campos vacíos";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.contenedor-principal { max-width: 400px; margin: 80px auto; padding: 30px; }
h1 { text-align: center; color: #2c3e50; margin-bottom: 30px; }
.form-login { background: linear-gradient(180deg, #a3c4f3 0%, #ffffff 100%); padding: 25px; border-radius: 15px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
.form-login label { font-weight: bold; margin-top: 15px; color: #2c3e50; }
.form-login input { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ccc; border-radius: 10px; }
.form-login input[type="submit"] { margin-top: 25px; font-size: 1.1rem; font-weight: bold; background: linear-gradient(180deg, #a3c4f3 0%, #ffffff 100%); border: none; border-radius: 12px; color: #000; }
.mensajes { display: block; margin-top: 15px; text-align: center; color: red; font-weight: bold; }
</style>
</head>
<body>
<div class="contenedor-principal">
    <form action="" method="post" class="form-login">
        <h1>Login</h1>
        <label>Usuario</label>
        <input type="text" name="usuario" placeholder="Ingrese su usuario" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Ingrese su password" required>
        <input type="submit" name="login" value="Ingresar">
        <span class="mensajes"><?php echo $mensaje; ?></span>
    </form>
</div>
</body>
</html>
