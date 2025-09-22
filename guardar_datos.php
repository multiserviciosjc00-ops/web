<?php
require 'vendor/autoload.php';
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

$datosGuardados = false;
$turnoGenerado  = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoDocumento   = $_POST['tipoDocumento'] ?? '';
    $numeroDocumento = $_POST['numeroDocumento'] ?? '';

    if (empty($tipoDocumento) || empty($numeroDocumento)) {
        die("Error: Debe completar todos los campos.");
    }

    // 🚀 Generar turno nuevo
    $sqlUltimo = "SELECT turno FROM turnos ORDER BY id DESC LIMIT 1";
    $buscar    = consulta($con, $sqlUltimo, "Error al buscar último turno");
    $resultado = mysqli_fetch_assoc($buscar);

    if ($resultado) {
        $ultimoTurno   = intval(substr($resultado['turno'], 1));
        $turnoGenerado = "T" . str_pad($ultimoTurno + 1, 3, "0", STR_PAD_LEFT);
    } else {
        $turnoGenerado = "T001";
    }

    // 🚀 Guardar en tabla turnos
    $sqlTurno = "INSERT INTO turnos (turno, fecha_registro) VALUES ('$turnoGenerado', NOW())";
    consulta($con, $sqlTurno, "Error al guardar turno");

    // 🚀 Guardar en Excel
    $archivoExcel = __DIR__ . "/clientes.xlsx";

    if (file_exists($archivoExcel)) {
        $spreadsheet = IOFactory::load($archivoExcel);
        $hoja        = $spreadsheet->getActiveSheet();
    } else {
        $spreadsheet = new Spreadsheet();
        $hoja        = $spreadsheet->getActiveSheet();
        $hoja->setCellValue("A1", "Tipo Documento")
             ->setCellValue("B1", "Número Documento")
             ->setCellValue("C1", "Fecha Registro")
             ->setCellValue("D1", "Turno");
    }

    // Última fila con datos
    $fila = $hoja->getHighestRow() + 1;
    $hoja->setCellValue("A$fila", $tipoDocumento)
         ->setCellValue("B$fila", $numeroDocumento)
         ->setCellValue("C$fila", date("Y-m-d H:i:s"))
         ->setCellValue("D$fila", $turnoGenerado);

    $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
    $writer->save($archivoExcel);

    $datosGuardados = true;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Solicitar Turno</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; text-align: center; padding: 40px; background: #f4f6f9; }
        h1 { color: #2c3e50; }
        form { background: #fff; padding: 20px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, select, button { margin: 10px; padding: 10px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #3498db; color: #fff; cursor: pointer; border: none; font-weight: bold; }
        button:hover { background: #2980b9; }
        .numero-turno { font-size: 3rem; color: #e74c3c; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Solicitar Turno</h1>

    <?php if ($datosGuardados): ?>
        <span class="datos-turno">Su turno asignado es:</span>
        <div class="numero-turno"><?= htmlspecialchars($turnoGenerado) ?></div>

        <script>
            // 🖨️ Crear ticket para imprimir
            const turno = "<?= $turnoGenerado ?>";
            const ticket = `
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; }
                        .ticket {
                            width: 200px;
                            padding: 10px;
                            border: 1px dashed #000;
                            margin: 0 auto;
                        }
                        h2 { margin: 5px 0; }
                    </style>
                </head>
                <body>
                    <div class="ticket">
                        <h2>Bienvenido</h2>
                        <h3>Su turno es:</h3>
                        <h1>${turno}</h1>
                        <p><?= date("Y-m-d H:i:s") ?></p>
                        <p>Gracias por su espera</p>
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                            window.onafterprint = function() {
                                window.close();
                            };
                        }
                    <\/script>
                </body>
                </html>
            `;
            const ventana = window.open("", "PRINT", "width=300,height=400");
            ventana.document.write(ticket);
            ventana.document.close();

            // ⏳ Volver a la pantalla inicial después de 5 segundos
            setTimeout(() => {
                window.location.href = "<?= $_SERVER['PHP_SELF'] ?>";
            }, 5000);
        </script>
    <?php else: ?>
        <!-- Formulario -->
        <form method="post" action="">
            <label>Tipo de Documento:</label>
            <select name="tipoDocumento" required>
                <option value="">Seleccione</option>
                <option value="CC">Cédula</option>
                <option value="TI">Tarjeta de Identidad</option>
                <option value="CE">Cédula de Extranjería</option>
            </select><br>

            <label>Número de Documento:</label>
            <input type="text" name="numeroDocumento" required><br>

            <button type="submit">Solicitar Turno</button>
        </form>
    <?php endif; ?>
</body>
</html>
