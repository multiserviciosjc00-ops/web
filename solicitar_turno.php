<?php
session_start();
date_default_timezone_set('America/Bogota');

// Verificar aceptación
if (!isset($_SESSION['aceptacion']) || $_SESSION['aceptacion'] !== true) {
    header("Location: inicio.php");
    exit;
}

// Conexión DB
$host = "localhost";
$user = "root";
$pass = "";
$db   = "turnero";
$con = new mysqli($host, $user, $pass, $db);
if ($con->connect_error) die("Error de conexión: " . $con->connect_error);

$mensaje = "";
$datosGuardados = false;
$forzarReinicio = false;

$fecha = date('Y-m-d H:i:s');

// Logo
$logoBase64 = '';
if (file_exists(__DIR__ . '/logo.png')) {
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents(__DIR__ . '/logo.png'));
}

$SERVICIOS = [
    ['value' => 'Ingreso a Examenes',  'label' => '🧾 Ingreso a Exámenes', 'class' => 'ingreso'],
    ['value' => 'Reclamar Resultados', 'label' => '📑 Reclamar Resultados', 'class' => 'resultados'],
    ['value' => 'Solicitar Cita',      'label' => '📅 Solicitar Cita', 'class' => 'cita'],
];

// Paso 1: Documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tipoDocumento'], $_POST['numeroDocumento']) && !isset($_POST['servicio'])) {
    $tipoDocumento   = trim($_POST['tipoDocumento']);
    $numeroDocumento = str_replace(".", "", trim($_POST['numeroDocumento']));
    if ($tipoDocumento === "" || $numeroDocumento === "") {
        $mensaje = "Debe llenar todos los campos.";
    } else {
        $_SESSION['tipoDocumento']   = $tipoDocumento;
        $_SESSION['numeroDocumento'] = $numeroDocumento;
    }
}

// Paso 2: Servicio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['servicio'])) {
    $servicio = trim($_POST['servicio']);
    if ($servicio !== "") {
        $tipoDocumento   = $_SESSION['tipoDocumento'] ?? '';
        $numeroDocumento = $_SESSION['numeroDocumento'] ?? '';

        // Validar último turno en 10 min
        $sqlCheck = "SELECT fecha FROM turnos WHERE numeroDocumento=? ORDER BY fecha DESC LIMIT 1";
        $stmtCheck = $con->prepare($sqlCheck);
        $stmtCheck->bind_param("s", $numeroDocumento);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($resCheck->num_rows > 0) {
            $filaCheck = $resCheck->fetch_assoc();
            $ultimaFecha = strtotime($filaCheck['fecha']);
            $ahora = strtotime($fecha);
            if (($ahora - $ultimaFecha) < 600) {
                $mensaje = "⚠ Ya solicitó un turno hace menos de 10 minutos (" . $filaCheck['fecha'] . ").";
                $forzarReinicio = true;
            }
        }

        if (!$forzarReinicio) {
            // Obtener último turno del día
            $hoy = date("Y-m-d");
            $sqlUltimo = "SELECT MAX(turno) as ultimo FROM turnos WHERE DATE(fecha) = '$hoy'";
            $resUltimo = $con->query($sqlUltimo);
            $fila = $resUltimo->fetch_assoc();
            $ultimoTurno = $fila ? intval($fila['ultimo']) : 0;

            $nuevoTurnoNum = $ultimoTurno + 1;

            // --- Definir piso según par/impar solo para mostrar ---
            $piso = ($nuevoTurnoNum % 2 === 0) ? "2" : "4";

            // Insertar en tabla principal turnos
            $stmt = $con->prepare("INSERT INTO turnos (tipoDocumento, numeroDocumento, servicio, fecha, turno, estado, piso) 
                                   VALUES (?, ?, ?, ?, ?, 'pendiente', ?)");
            $stmt->bind_param("ssssis", $tipoDocumento, $numeroDocumento, $servicio, $fecha, $nuevoTurnoNum, $piso);

            if ($stmt->execute()) {
                $datosGuardados = true;
                $_SESSION['ultimo_turno']    = $nuevoTurnoNum;
                $_SESSION['ultimo_servicio'] = $servicio;
                $_SESSION['ultimo_tipoDoc']  = $tipoDocumento;
                $_SESSION['ultimo_numdoc']   = $numeroDocumento;
            } else $mensaje = "No se pudo guardar el turno.";
        }
    } else $mensaje = "Debe seleccionar un servicio.";
}

// Limpiar sesión de turno y regresar a inicio
if (isset($_GET['clearSession']) && $_GET['clearSession'] == 1) {
    unset($_SESSION['tipoDocumento']);
    unset($_SESSION['numeroDocumento']);
    unset($_SESSION['ultimo_turno']);
    unset($_SESSION['ultimo_servicio']);
    unset($_SESSION['ultimo_tipoDoc']);
    unset($_SESSION['ultimo_numdoc']);

    header("Location: inicio.php?reinicio=1"); 
    exit;
}

// Tiempo de inactividad (ms)
$tiempoInactividadMs = 10 * 1000;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Turnero COLMEDICOS</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
<style>
/* --- Estilos --- */
body { font-family:'Poppins',sans-serif; background:#f0f2f5; margin:0; padding:20px; }
.contenedor { background:#fff; padding:40px; border-radius:30px; max-width:700px; margin:50px auto; text-align:center; box-shadow:0 6px 98px rgba(0,0,0,.1); }
.teclado { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:12px; }
.teclado button { font-size:2em; padding:20px; background:#1976d2; color:#fff; border:none; border-radius:10px; }
.teclado button:active { background:#2e7d32; }
.grid { display:grid; grid-template-columns:1fr; gap:16px; margin-top:20px; }
.svc { padding:22px; border:none; border-radius:12px; color:#fff; font-size:1.5em; width:100%; transition: background 0.2s, transform 0.1s; }
.ingreso { background:#1976d2; }
.resultados { background:#388e3c; }
.cita { background:#f57c00; }
.svc.pressed { background:#2e7d32 !important; transform:scale(0.97); }
.doc-btn { padding:20px; font-size:1.3em; border:none; border-radius:10px; background:#1976d2; color:#fff; transition: background 0.2s; width:100%; height:80px; display:flex; align-items:center; justify-content:center; text-align:center; }
.doc-btn:active { background:#2e7d32; }
#numeroDocumento { font-size:2em; padding:12px; text-align:center; width:35%; margin:20px auto; display:block; }
.msg { color:#c62828; margin-top:10px; font-weight:600; }
.turno { font-size:2em; font-weight:bold; color:#1976d2; margin-top:12px; }
.bienvenida { font-size:1.5em; font-weight:bold; color:#1976d2; margin-bottom:15px; }
#contenedorHomeFecha { display:flex; flex-direction:column; align-items:center; gap:10px; margin-top:20px; }
#btnHome { padding:12px 25px; border:none; border-radius:12px; background:#1976d2; color:#fff; cursor:pointer; font-size:1.2em; transition:0.2s; }
#btnHome:hover { background:#2e7d32; transform:scale(1.05); }
#reloj { font-size:1.1em; font-weight:bold; color:#555; text-align:center; }
@media (max-width:600px){ #btnHome { font-size:1em; padding:10px; } #reloj { font-size:0.9em; } }
</style>
<script>
// Temporizador inactividad
const TIEMPO_MAXIMO = <?= (int)$tiempoInactividadMs ?>;
let _temporizador;
function _iniciarTemporizador() { _temporizador = setTimeout(() => { window.location.href = "inicio.php"; }, TIEMPO_MAXIMO); }
function _reiniciarTemporizador() { clearTimeout(_temporizador); _iniciarTemporizador(); }
window.addEventListener('load', _iniciarTemporizador);
['keydown','click','input','touchstart','mousemove'].forEach(evt => { document.addEventListener(evt, _reiniciarTemporizador, {passive:true}); });

// Impresión ticket
<?php if ($datosGuardados): ?>
window.addEventListener('load', function() {
    const datos = {
        turno: "<?= $_SESSION['ultimo_turno'] ?>",
        servicio: "<?= htmlspecialchars($_SESSION['ultimo_servicio']) ?>",
        tipoDoc: "<?= htmlspecialchars($_SESSION['ultimo_tipoDoc']) ?>",
        numdoc: "<?= htmlspecialchars($_SESSION['ultimo_numdoc']) ?>",
        fecha: "<?= $fecha ?>",
        logo: "<?= $logoBase64 ?>",
    };
    const piso = (parseInt(datos.turno,10) % 2 === 0) ? "PISO 2" : "PISO 4";
    const v = window.open('', '_blank', 'width=400,height=280');
    v.document.write(`
        <div style="width:380px; font-family:Poppins,sans-serif; text-align:center;">
            ${datos.logo ? `<img src="${datos.logo}" style="height:60px; margin-bottom:8px;">` : ""}
            <p style="margin:5px 0; font-size:0.9em; font-weight:bold;">COLMEDICOS RIONEGRO</p>
            <p style="margin:8px 0; font-size:1.6em; font-weight:bold; color:#c62828;">FAVOR DIRIGIRSE AL ${piso}</p>
            <p style="margin:5px 0; font-size:2em; font-weight:bold;">Turno: ${datos.turno.toString().padStart(3,"0")}</p>
            <p style="margin:2px 0; font-size:1.1em; font-weight:bold;">${datos.tipoDoc} - ${formatearDocumento(datos.numdoc)}</p>
            <p style="margin:3px 0;">${datos.servicio}</p>
            <p style="margin:3px 0; font-size:0.8em;">${datos.fecha}</p>
            <hr>
            <p style="margin:5px 0;">Gracias por su visita</p>
        </div>
    `);
    v.document.close();
    v.print();
    setTimeout(() => { window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?clearSession=1'; }, 2000);
});
<?php endif; ?>

function formatearDocumento(valor) { return valor.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }
function agregarNumero(num) { const campo = document.getElementById('numeroDocumento'); campo.value = campo.value.replace(/\./g, '') + num; campo.value = formatearDocumento(campo.value); }
function borrarNumero() { const campo = document.getElementById('numeroDocumento'); campo.value = campo.value.replace(/\./g, ''); campo.value = campo.value.slice(0, -1); campo.value = formatearDocumento(campo.value); }
function actualizarHora(){ document.getElementById("reloj").innerText = new Date().toLocaleString("es-CO"); }
setInterval(actualizarHora,1000);
actualizarHora();
</script>
</head>
<body>
<div class="contenedor">
    <?php if ($logoBase64): ?>
        <div style="margin-bottom:15px;">
            <img src="<?= $logoBase64 ?>" alt="Logo" style="height:120px;">
        </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['tipoDocumento']) && !$datosGuardados): ?>
        <div class="bienvenida">Por favor regístrese para obtener su turno.</div>
        <form method="POST" id="formDocumento">
            <div class="grid" style="grid-template-columns:repeat(2,1fr); gap:8px; margin-bottom:15px;">
                <button type="button" class="doc-btn" data-value="CC">Cédula de Ciudadanía</button>
                <button type="button" class="doc-btn" data-value="TI">Tarjeta de Identidad</button>
                <button type="button" class="doc-btn" data-value="CE">Cédula de Extranjería</button>
                <button type="button" class="doc-btn" data-value="PAS">Pasaporte</button>
            </div>
            <input type="hidden" name="tipoDocumento" id="tipoDocumento" required>
            <input type="text" id="numeroDocumento" name="numeroDocumento" readonly required>
            <div class="teclado">
                <?php for($i=1;$i<=9;$i++): ?>
                <button type="button" onclick="agregarNumero('<?= $i ?>')"><?= $i ?></button>
                <?php endfor; ?>
                <button type="button" onclick="borrarNumero()">Borrar</button>
                <button type="button" onclick="agregarNumero('0')">0</button>
                <button type="submit">Continuar</button>
            </div>
        </form>
        <script>
        document.querySelectorAll('.doc-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('tipoDocumento').value = this.dataset.value;
                document.querySelectorAll('.doc-btn').forEach(b => b.style.background = '#1976d2');
                this.style.background = '#2e7d32';
            });
        });
        </script>

    <?php elseif (isset($_SESSION['tipoDocumento']) && !$datosGuardados): ?>
        <h3>Seleccione el servicio</h3>
        <form method="POST">
            <div class="grid">
                <?php foreach ($SERVICIOS as $s): ?>
                    <button class="svc <?= $s['class'] ?>" type="submit" name="servicio" value="<?= $s['value'] ?>">
                        <?= $s['label'] ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </form>

    <?php elseif ($datosGuardados): ?>
        <h3>✅ Turno Generado</h3>
        <?php 
            $turnoPantalla = str_pad($_SESSION['ultimo_turno'],3,'0',STR_PAD_LEFT);
            $pisoPantalla  = ($_SESSION['ultimo_turno'] % 2 === 0) ? "2" : "4";
        ?>
        <?php if ($logoBase64): ?>
            <div style="margin-bottom:10px;">
                <img src="<?= $logoBase64 ?>" alt="Logo" style="height:80px;">
            </div>
        <?php endif; ?>
        <p style="margin:5px 0; font-size:0.9em; font-weight:bold;">COLMEDICOS RIONEGRO</p>
        <p style="margin:10px 0; font-size:1.8em; font-weight:bold; color:#c62828;">
            FAVOR DIRIGIRSE AL PISO <?= $pisoPantalla ?>
        </p>
        <p class="turno">Turno: <?= $turnoPantalla ?></p>
        <p style="margin:2px 0; font-size:1.1em; font-weight:bold;">
            <?= htmlspecialchars($_SESSION['ultimo_tipoDoc']) ?> - <?= number_format($_SESSION['ultimo_numdoc'], 0, ",", ".") ?>
        </p>
        <p style="margin:3px 0;"><?= htmlspecialchars($_SESSION['ultimo_servicio']) ?></p>
        <p style="margin:3px 0; font-size:0.9em;"><?= $fecha ?></p>
        <hr>
        <p style="margin:5px 0;">Gracias por su visita</p>
        <p style="margin-top:10px; font-size:0.9em; color:#555;">
            La pantalla se reiniciará automáticamente para el siguiente usuario.
        </p>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <div class="msg"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if ($forzarReinicio): ?>
        <script>
        setTimeout(() => { window.location.href = "<?= $_SERVER['PHP_SELF'] ?>?clearSession=1"; }, 3000);
        </script>
    <?php endif; ?>

    <div id="contenedorHomeFecha">
        <button id="btnHome" onclick="window.location.href='<?= $_SERVER['PHP_SELF'] ?>?clearSession=1'">
            🏠 Volver
        </button>
        <strong id="reloj"></strong>
    </div>
</div>
</body>
</html>
