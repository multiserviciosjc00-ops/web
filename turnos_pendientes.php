<?php
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

$idCaja = isset($_GET['idCaja']) ? (int)$_GET['idCaja'] : 0;
$piso   = isset($_GET['piso']) ? (int)$_GET['piso'] : 0;
header('Content-Type: application/json');

if ($idCaja <= 0 || $piso <= 0) { echo json_encode([]); exit; }

$condicion = in_array($idCaja, [1,2,3]) ? " (turno % 2) = 0 " : " (turno % 2) = 1 ";

$sql = "SELECT turno, servicio 
        FROM turnos 
        WHERE estado='pendiente' AND piso=$piso AND $condicion
        ORDER BY id ASC";
$res = consulta($con, $sql, "Error al obtener turnos pendientes");

$pendientes = [];
while ($row = mysqli_fetch_assoc($res)) {
    $pendientes[] = ['turno' => $row['turno'], 'servicio' => $row['servicio']];
}

echo json_encode($pendientes);
