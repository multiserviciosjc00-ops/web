<?php
session_start();
date_default_timezone_set('America/Bogota');

// Ruta del logo
$logoPath = __DIR__ . '/logo.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
}

// Si ya aceptó y no viene de "reinicio", enviar a solicitar_turno.php
if (isset($_SESSION['aceptacion']) && $_SESSION['aceptacion'] === true && !isset($_GET['reinicio'])) {
    header("Location: solicitar_turno.php");
    exit;
}

// Si presiona aceptar, marcar sesión y redirigir
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['aceptacion'] = true;
    header("Location: solicitar_turno.php");
    exit;
}

// Si viene con reinicio, NO borrar aceptación, solo dejar que redirija a solicitar_turno
if (isset($_GET['reinicio'])) {
    // Mantiene $_SESSION['aceptacion'] intacto
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Bienvenida - Colmedicos Rionegro</title>
<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 0; 
    height: 100vh;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
}
.contenedor { 
    background: #fff; 
    width: 80%; 
    height: 80%; 
    display: flex; 
    flex-direction: column; 
    justify-content: center; 
    align-items: center; 
    text-align: center;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.2);
}
.logo { 
    max-height: 220px; 
    margin-bottom: 30px; 
}
h2 {
    font-size: 2.3em; 
    margin-bottom: 20px;
    color: #1976d2;
}
p {
    font-size: 1.1em; 
    line-height: 1.6em;
    max-width: 900px;
    text-align: justify;
    margin: 0 auto 20px auto;
}
button { 
    margin-top: 20px; 
    padding: 18px 50px; 
    font-size: 1.3em; 
    border: none; 
    border-radius: 12px; 
    background: #1976d2; 
    color: #fff; 
    cursor: pointer; 
    transition: 0.2s; 
}
button:hover { 
    background: #1565c0; 
}

/* Fecha y hora centrada debajo del botón */
#fechaHora {
    margin-top: 20px;
    font-size: 1.2em;
    color: #555;
    font-weight: bold;
    text-align: center;
}
</style>
</head>
<body>
<div class="contenedor">
    <?php if ($logoBase64): ?>
        <img src="<?= $logoBase64 ?>" alt="Logo COLMÉDICOS" class="logo">
    <?php endif; ?>

    <h2>Bienvenido(a) a Colmedicos Rionegro</h2>

    <p>
        En cumplimiento de la <strong>Ley 1581 de 2012</strong> y normas relacionadas, 
        informamos que sus datos personales serán tratados por 
        <strong>COLMÉDICOS</strong> únicamente para fines médicos, administrativos 
        y de atención al usuario.
    </p>

    <p>
        Como titular, usted puede ejercer sus derechos de <strong>conocer, actualizar, 
        rectificar</strong> o <strong>solicitar la supresión</strong> de sus datos, así 
        como <strong>revocar la autorización</strong> otorgada, a través de los canales 
        de contacto oficiales de la institución.
    </p>

    <p>
        Al presionar <strong>“Aceptar y Continuar”</strong>, usted autoriza el tratamiento 
        de sus datos conforme a lo aquí señalado.
    </p>

    <form method="POST">
        <button type="submit">Aceptar y Continuar</button>
    </form>

    <!-- Fecha y hora debajo del botón -->
    <div id="fechaHora"></div>
</div>

<script>
// Función para actualizar fecha y hora
function actualizarFechaHora() {
    const fechaHora = new Date().toLocaleString("es-CO");
    document.getElementById("fechaHora").innerText = fechaHora;
}
setInterval(actualizarFechaHora, 1000);
actualizarFechaHora();
</script>
</body>
</html>
