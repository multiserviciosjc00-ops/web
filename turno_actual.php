<?php
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

$idCaja = isset($_GET['idCaja']) ? (int)$_GET['idCaja'] : 0;
if ($idCaja <= 0) {
    echo json_encode(['turno' => null, 'pendientes' => []]);
    exit;
}

// 1. Turno en atención
$sqlAtendiendo = "SELECT * FROM turnos 
                  WHERE idCaja = $idCaja AND estado = 'atendiendo' 
                  ORDER BY id DESC LIMIT 1";
$resAtendiendo = consulta($con, $sqlAtendiendo, "Error al obtener turno atendiendo");

$turnoActual = null;
if ($resAtendiendo && mysqli_num_rows($resAtendiendo) > 0) {
    $turnoActual = mysqli_fetch_assoc($resAtendiendo);
}

// 2. Turnos pendientes según regla par/impar
$condicion = ($idCaja <= 3) ? " (turno % 2) = 0 " : " (turno % 2) = 1 ";
$sqlPendientes = "SELECT * FROM turnos 
                  WHERE estado = 'pendiente' AND $condicion
                  ORDER BY id ASC";
$resPendientes = consulta($con, $sqlPendientes, "Error al obtener turnos pendientes");

$pendientes = [];
while ($row = mysqli_fetch_assoc($resPendientes)) {
    $pendientes[] = [
        'turno' => $row['turno'],
        'servicio' => $row['servicio'],
        'tipoDocumento' => $row['tipoDocumento'],    // agregado
        'numeroDocumento' => $row['numeroDocumento']
    ];
}

echo json_encode([
    'turno' => $turnoActual['turno'] ?? null,
    'servicio' => $turnoActual['servicio'] ?? null,
    'tipoDocumento' => $turnoActual['tipoDocumento'] ?? null,  // agregado
    'numeroDocumento' => $turnoActual['numeroDocumento'] ?? null,
    'pendientes' => $pendientes
]);
