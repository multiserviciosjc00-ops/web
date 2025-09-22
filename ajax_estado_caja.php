<?php
session_start();
date_default_timezone_set('America/Bogota');

require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

if (!isset($_SESSION['usuario']) || !isset($_SESSION['idCaja'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idCaja = (int)$_SESSION['idCaja'];
$hoy = date('Y-m-d');

$turnoActual = null;
$cola = [];

// turno actual
$sql = "SELECT * FROM turnos WHERE estado='atendiendo' AND idCaja='$idCaja' AND DATE(fecha)='$hoy' ORDER BY id DESC LIMIT 1";
$res = consulta($con, $sql, "Error al buscar turno actual");
if ($res->num_rows > 0) {
    $turnoActual = mysqli_fetch_assoc($res);
}

// cola de pendientes
$sql = "SELECT * FROM turnos WHERE estado='pendiente' AND DATE(fecha)='$hoy' ORDER BY id ASC LIMIT 20";
$res = consulta($con, $sql, "Error al obtener cola");
while ($fila = mysqli_fetch_assoc($res)) {
    $cola[] = $fila;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'turnoActual' => $turnoActual,
    'cola' => $cola
]);
