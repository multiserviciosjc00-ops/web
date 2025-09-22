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

// 1️⃣ Turno actualmente en atención (más reciente)
$sql = "SELECT turno, idCaja 
        FROM turnos 
        WHERE estado='atendiendo' 
        ORDER BY horaLlamado DESC 
        LIMIT 1";
$res = $con->query($sql);

$turno = "---";
$caja  = "---";
$piso  = "---";

if ($res && $res->num_rows > 0) {
    $fila = $res->fetch_assoc();
    $turno = $fila['turno'];
    $caja  = $fila['idCaja'];
    $piso  = ($turno % 2 === 0) ? "2" : "4";
}

// 2️⃣ Últimos 5 turnos (incluye atendiendo y atendidos)
$sql = "SELECT turno, idCaja, estado 
        FROM turnos 
        WHERE estado IN ('atendiendo','atendido') 
        ORDER BY horaLlamado DESC 
        LIMIT 5";
$res = $con->query($sql);

$ultimos = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $fila['piso'] = ($fila['turno'] % 2 === 0) ? "2" : "4";
        // Mantener estado para resaltar en azul degradado si está 'atendiendo'
        $fila['estado'] = $fila['estado']; 
        $ultimos[] = $fila;
    }
}

// 3️⃣ Siempre mostrar 5 filas
while(count($ultimos) < 5){
    $ultimos[] = ['turno'=>'---','idCaja'=>'---','piso'=>'---','estado'=>''];
}

// 4️⃣ Devolver JSON
echo json_encode([
    "turno"   => $turno,
    "caja"    => $caja,
    "piso"    => $piso,
    "ultimos" => $ultimos
]);
