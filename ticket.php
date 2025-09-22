<?php
session_start();
if (!isset($_SESSION['turnoGenerado'])) {
    header("Location: solicitar_turno.php");
    exit;
}
$tipoDocumento   = $_SESSION['tipoDocumento'];
$numeroDocumento = $_SESSION['numeroDocumento'];
$servicio        = $_SESSION['servicio'];
$fecha           = $_SESSION['fecha'];
$turnoGenerado   = $_SESSION['turnoGenerado'];

// Limpio sesión después de usar
session_unset();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket de Turno</title>
<style>
body { font-family: 'Poppins', sans-serif; text-align: center; }
.ticket { border: 1px dashed #000; padding: 15px; margin: 20px auto; width: 280px; }
</style>
<script>
window.onload = function() {
    window.print();
    setTimeout(function(){
        window.location.href = "solicitar_turno.php";
    }, 3000);
};
</script>
</head>
<body>
<div class="ticket">
    <h2>COLMEDICOS - SEDE TERMINAL</h2>
    <p><?= htmlspecialchars($tipoDocumento) ?> <?= htmlspecialchars($numeroDocumento) ?></p>
    <p><b>Servicio:</b> <?= htmlspecialchars($servicio) ?></p>
    <h1>Turno: <?= htmlspecialchars($turnoGenerado) ?></h1>
    <p><?= $fecha ?></p>
    <p>Gracias por su visita</p>
</div>
</body>
</html>
