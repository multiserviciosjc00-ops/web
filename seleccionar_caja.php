<?php
date_default_timezone_set('America/Bogota');

// Reabrir sesión con el usuario activo
if (isset($_COOKIE['usuario_activo'])) {
    $usuarioActivo = $_COOKIE['usuario_activo'];
    session_name("turnos_" . $usuarioActivo);
    session_start();
} else {
    header("Location: login.php");
    exit;
}

// Validar sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit;
}

require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

$idUsuario = (int)$_SESSION['idUsuario'];

// Obtener todas las cajas asignadas
$sql = "SELECT c.id, c.nombre 
        FROM cajas c
        INNER JOIN usuario_caja uc ON c.id = uc.idCaja
        WHERE uc.idUsuario = $idUsuario";
$res = consulta($con, $sql, "Error al obtener cajas");

if (isset($_POST['caja'])) {
    $_SESSION['idCaja'] = (int)$_POST['caja'];
    header("Location: caja.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Seleccionar Módulo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.contenedor-principal { max-width: 750px; margin: 50px auto; padding: 20px; }
h1 { text-align: center; color: #2c3e50; margin-bottom: 40px; }
.link-menu { display: block; text-align: center; margin-top: 25px; color: #2c3e50; font-size: 1.4rem; font-weight: bold; }
.link-menu:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="contenedor-principal">
    <h1>Selecciona tu módulo</h1>
    <form id="formSeleccion" action="" method="post">
        <div class="row g-4">
            <?php while($caja = mysqli_fetch_assoc($res)): ?>
                <div class="col-md-6 col-lg-4">
                    <button type="submit" name="caja" value="<?php echo $caja['id']; ?>" class="btn btn-primary w-100 p-3">
                        <?php echo htmlspecialchars($caja['nombre']); ?>
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </form>
    <a href="logout.php" class="link-menu">Cerrar sesión</a>
</div>
</body>
</html>
