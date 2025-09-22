<?php
if (isset($_COOKIE['usuario_activo'])) {
    $usuarioActivo = $_COOKIE['usuario_activo'];
    session_name("turnos_" . $usuarioActivo);
    session_start();

    // Vaciar y cerrar sesión de este usuario
    $_SESSION = [];
    session_destroy();

    // Eliminar cookie de este usuario
    setcookie("usuario_activo", "", time() - 3600, "/");
}

// Redirigir al login
header("Location: login.php");
exit;
?>
