<?php
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');
require 'vendor/autoload.php'; // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCaja = isset($_POST['idCaja']) ? intval($_POST['idCaja']) : 0;
    $tipoDocumento = isset($_POST['tipoDocumento']) ? trim($_POST['tipoDocumento']) : '';
    $numeroDocumento = isset($_POST['numeroDocumento']) ? trim($_POST['numeroDocumento']) : '';

    if ($idCaja <= 0) {
        echo json_encode(["success" => false, "mensaje" => "Caja no enviada"]);
        exit;
    }

    // Guardar en Excel
    $excelFile = 'registro_clientes.xlsx';
    if (file_exists($excelFile)) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelFile);
        $sheet = $spreadsheet->getActiveSheet();
    } else {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Fecha', 'Tipo Documento', 'Número Documento'], NULL, 'A1');
    }

    $lastRow = $sheet->getHighestRow() + 1;
    $sheet->setCellValue('A' . $lastRow, date('Y-m-d H:i:s'));
    $sheet->setCellValue('B' . $lastRow, $tipoDocumento);
    $sheet->setCellValue('C' . $lastRow, $numeroDocumento);

    $writer = new Xlsx($spreadsheet);
    $writer->save($excelFile);

    // Buscar turno pendiente
    $sql = "SELECT id, turno FROM turnos WHERE estado='pendiente' ORDER BY turno ASC LIMIT 1";
    $result = consulta($con, $sql, "Error al buscar turno");

    if (mysqli_num_rows($result) > 0) {
        $turno = mysqli_fetch_assoc($result);
        $idTurno = $turno['id'];
        $numeroTurno = $turno['turno'];

        $insert = "INSERT INTO atencion (turno, idCaja) VALUES ('$numeroTurno', '$idCaja')";
        consulta($con, $insert, "Error al guardar atención");

        $update = "UPDATE turnos SET estado='atendido' WHERE id='$idTurno'";
        consulta($con, $update, "Error al actualizar turno");

        echo json_encode([
            "success" => true,
            "turno" => $numeroTurno,
            "caja" => $idCaja,
            "mensaje" => "Turno atendido con éxito"
        ]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "No hay turnos pendientes"]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Método no permitido"]);
}
