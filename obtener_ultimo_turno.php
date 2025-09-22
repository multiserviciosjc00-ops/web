<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Bogota');

// Conexión BD
$host = "localhost";
$user = "root";
$pass = "";
$db   = "turnero";
$con = new mysqli($host, $user, $pass, $db);
if ($con->connect_error) {
    echo json_encode(["error" => "Error de conexión"]);
    exit;
}

// Último turno llamado
$sql = "SELECT turno, idCaja 
        FROM turnos 
        WHERE estado='atendiendo' 
        ORDER BY horaLlamado DESC 
        LIMIT 1";
$res = $con->query($sql);

$turno = "---";
$caja = "---";

if ($res && $res->num_rows > 0) {
    $fila = $res->fetch_assoc();
    $turno = $fila['turno'];
    $caja  = $fila['idCaja'];
}

// Últimos 5 turnos
$sql = "SELECT turno, idCaja 
        FROM turnos 
        WHERE estado IN ('atendiendo','atendido') 
        ORDER BY horaLlamado DESC 
        LIMIT 5";
$res = $con->query($sql);

$ultimos = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $ultimos[] = $fila;
    }
}

echo json_encode([
    "turno"   => $turno,
    "caja"    => $caja,
    "ultimos" => $ultimos
]);
?>
