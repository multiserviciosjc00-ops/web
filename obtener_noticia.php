<?php
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

$sql = "SELECT texto FROM noticias ORDER BY fecha DESC LIMIT 10";
$res = consulta($con, $sql, "Error al obtener noticias");

$noticias = [];
while($row = mysqli_fetch_assoc($res)){
    $noticias[] = $row['texto'];
}

echo json_encode($noticias);
?>
