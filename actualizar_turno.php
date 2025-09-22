<?php
session_start();
if (!isset($_SESSION['idCaja'])) exit;
$idCaja = $_SESSION['idCaja'];
$tablaTurnos = ($idCaja % 2 === 0) ? "turnos_pares" : "turnos_impares";
$con = new mysqli("localhost","root","","turnero");
if ($con->connect_error) die("Error de conexión: ".$con->connect_error);

function consulta($con,$sql){ $res=mysqli_query($con,$sql); if(!$res) die(mysqli_error($con)); return $res; }

// Turno actual
$sql="SELECT * FROM $tablaTurnos WHERE estado='atendiendo' AND idCaja='$idCaja' ORDER BY id ASC LIMIT 1";
$res=consulta($con,$sql);
$turnoActual = $res->num_rows>0 ? $res->fetch_assoc() : null;

// Cola
$cola=[];
$sql="SELECT turno, numeroDocumento, fecha FROM $tablaTurnos WHERE estado='pendiente' ORDER BY id ASC";
$res=consulta($con,$sql);
while($fila=$res->fetch_assoc()) $cola[]=$fila;

header('Content-Type: application/json');
echo json_encode(['turnoActual'=>$turnoActual,'cola'=>$cola]);
