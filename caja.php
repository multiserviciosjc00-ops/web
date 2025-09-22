<?php
if (isset($_COOKIE['usuario_activo'])) {
    $usuarioActivo = $_COOKIE['usuario_activo'];
    session_name("turnos_" . $usuarioActivo);
}
session_start();
date_default_timezone_set('America/Bogota');

if (!isset($_SESSION['usuario']) || !isset($_SESSION['idCaja']) || !isset($_SESSION['idUsuario'])) {
    header('Location: seleccionar_caja.php');
    exit;
}

require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

$idCaja    = (int)$_SESSION['idCaja'];
$idUsuario = (int)$_SESSION['idUsuario'];
$usuario   = $_SESSION['usuario'];

// Nombre de la caja
$sqlCaja = "SELECT nombre FROM cajas WHERE id = $idCaja";
$resCaja = consulta($con, $sqlCaja, "Error al obtener la caja");
$cajaData = mysqli_fetch_assoc($resCaja);
$nombreCaja = $cajaData ? $cajaData['nombre'] : "Modulo 3 P2";

// Atender siguiente turno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_turno'])) {

    // Verificar si ya hay un turno en atención en esta caja
    $sqlCheck = "SELECT * FROM turnos WHERE idCaja = $idCaja AND estado = 'atendiendo' LIMIT 1";
    $resCheck = consulta($con, $sqlCheck, "Error al verificar turno en atención");

    if (mysqli_num_rows($resCheck) > 0) {
        // Solo actualizar horaLlamado para llamar el mismo turno nuevamente
        $turnoActual = mysqli_fetch_assoc($resCheck);
        $idTurno = (int)$turnoActual['id'];
        $sqlUpd = "UPDATE turnos 
                   SET horaLlamado = NOW() 
                   WHERE id = $idTurno";
        consulta($con, $sqlUpd, "Error al actualizar hora de llamado del turno actual");
        header("Location: caja.php");
        exit;
    }

    // No hay turno en atención, buscar el siguiente pendiente
    $condicion = ($idCaja <= 3) ? " (turno % 2) = 0 " : " (turno % 2) = 1 ";
    $sqlSig = "SELECT * FROM turnos WHERE estado = 'pendiente' AND $condicion ORDER BY id ASC LIMIT 1";
    $resSig = consulta($con, $sqlSig, "Error al obtener siguiente turno");

    if (mysqli_num_rows($resSig) > 0) {
        $sigData = mysqli_fetch_assoc($resSig);
        $idTurno = (int)$sigData['id'];

        $sqlUpd = "UPDATE turnos 
                   SET estado = 'atendiendo',
                       idCaja = $idCaja,
                       idUsuario = $idUsuario,
                       usuario = '".mysqli_real_escape_string($con, $usuario)."',
                       horaLlamado = NOW()
                   WHERE id = $idTurno";
        consulta($con, $sqlUpd, "Error al actualizar turno");
        header("Location: caja.php");
        exit;
    }
}

// Finalizar turno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_turno'])) {
    $sqlFin = "SELECT * FROM turnos WHERE idCaja = $idCaja AND estado = 'atendiendo' ORDER BY id DESC LIMIT 1";
    $resFin = consulta($con, $sqlFin, "Error al obtener turno actual");

    if (mysqli_num_rows($resFin) > 0) {
        $finData = mysqli_fetch_assoc($resFin);
        $idTurno = (int)$finData['id'];
        $sqlUpd = "UPDATE turnos SET estado = 'finalizado', horaAtencion = NOW() WHERE id = $idTurno";
        consulta($con, $sqlUpd, "Error al finalizar turno");
        header("Location: caja.php");
        exit;
    }
}

// Historial últimos 5 turnos finalizados desde la DB
$sqlHist = "SELECT turno, usuario, horaLlamado, horaAtencion, servicio, tipoDocumento, numeroDocumento 
            FROM turnos 
            WHERE idCaja = $idCaja AND estado = 'finalizado' 
            ORDER BY id DESC LIMIT 5";
$resHist = consulta($con, $sqlHist, "Error al obtener historial");
$historialDB = [];
while($row = mysqli_fetch_assoc($resHist)){
    $historialDB[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?php echo htmlspecialchars($nombreCaja); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    background: linear-gradient(to right, #d0e7ff, #a0d4ff); 
    font-family: 'Inter', sans-serif; 
}
.contenedor { 
    max-width: 1000px; 
    margin: 30px auto; 
    padding: 30px; 
    background: #fff; 
    border-radius: 15px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
    position: relative;
}
.modulo-nombre {
    position: absolute;
    left: 30px;
    top: 30px;
    font-size: 24px;
    font-weight: 700;
    color: #0d3b66;
}
.turno-numero {
    font-size: 50px; 
    font-weight: 700;
    color: #1e6091;
    text-align: center;
    display: block;
}
.turno-detalle {
    font-size: 20px;
    color: #0d3b66;
    text-align: center;
    display: block;
    margin-top: 5px;
    font-weight: 600;
}
.btn-turno { 
    font-size: 1.2rem; 
    padding: 12px 20px; 
    margin: 10px 0; 
    border-radius: 12px; 
    transition: all 0.3s ease;
}
.btn-turno:hover { 
    transform: scale(1.05); 
    background: #1e6091; 
    color: #fff; 
}
.datos-usuario { 
    text-align: right; font-size: 0.95rem; color: #0d3b66; margin-bottom: 15px; 
}
.table td, .table th { font-size:12px; }
.table thead { background-color: #1e6091; color: #fff; }
.table-striped>tbody>tr:nth-of-type(odd) { background-color: #e1f0ff; }
.btn-copy.copied {
    background-color: #1e6091;
    color: #fff;
    border-color: #1e6091;
}

/* Estilo del toast flotante */
.toast-copy {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #1e6091;
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.4s ease;
}
.toast-copy.show {
    opacity: 1;
    pointer-events: auto;
}
</style>
</head>
<body>
<div class="contenedor">
    <div class="modulo-nombre"><?php echo htmlspecialchars($nombreCaja); ?></div>

    <div class="datos-usuario">
        <strong>Usuario:</strong> <?php echo htmlspecialchars($usuario); ?> |
        <a href="logout.php" class="text-danger">Cerrar sesión</a>
    </div>

    <div id="turno-actual">
        <span class="turno-numero">no hay turnos pendientes</span>
    </div>

    <form method="post">
        <button type="submit" name="nuevo_turno" class="btn btn-success w-100 btn-turno" id="btn-nuevo">Atender siguiente turno</button>
        <button type="submit" name="finalizar_turno" class="btn btn-danger w-100 btn-turno mt-2">Finalizar turno actual</button>
    </form>

    <div class="tabla-pendientes mt-4">
        <h2 class="text-center">Turnos pendientes</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Turno</th>
                    <th>Servicio</th>
                </tr>
            </thead>
            <tbody id="pendientes-body">
                <tr><td colspan="2" class="text-center">no hay turnos pendientes</td></tr>
            </tbody>
        </table>
    </div>

    <div class="tabla-historial mt-4">
        <h2 class="text-center">5 Ultimos turnos atendidos</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Turno</th>
                    <th>Usuario</th>
                    <th>Hora Llamado</th>
                    <th>Hora Atención</th>
                    <th>Servicio</th>
                    <th>Documento</th>
                </tr>
            </thead>
            <tbody id="historial-body">
                <?php if(!empty($historialDB)): ?>
                    <?php foreach($historialDB as $row): ?>
                        <tr>
                            <td><?php echo "T " . str_pad($row['turno'],3,"0",STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($row['horaLlamado']); ?></td>
                            <td><?php echo htmlspecialchars($row['horaAtencion']); ?></td>
                            <td><?php echo htmlspecialchars($row['servicio']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipoDocumento'] . ' ' . $row['numeroDocumento']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No hay historial disponible</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Toast flotante -->
<div id="toastCopy" class="toast-copy">✔️ Documento copiado</div>

<script>
let turnoAnterior = null;
let sonidoReproducido = false;

// Inicializar historial con datos desde la DB
let ultimosTurnos = <?php echo json_encode($historialDB); ?>;

function actualizarTurnos() {
    fetch("turno_actual.php?idCaja=<?php echo $idCaja; ?>")
    .then(res => res.json())
    .then(data => {
        const turno = data.turno ?? null;
        const tipoDocumento = data.tipoDocumento ?? "";
        const numeroDocumento = data.numeroDocumento ?? "";
        const servicio = data.servicio ?? "";

        const turnoFormateado = turno ? "T " + String(turno).padStart(3,"0") : "no hay turnos pendientes";

        if(turno){
            document.getElementById("turno-actual").innerHTML = `
                <span class="turno-numero">${turnoFormateado}</span>
                <span class="turno-detalle">${tipoDocumento} ${numeroDocumento} - ${servicio}</span>
                <button onclick="copiarDocumento('${numeroDocumento}')" class="btn btn-sm btn-primary btn-copy mt-2">Copiar</button>
            `;

            if(!sonidoReproducido || turno !== turnoAnterior){
                reproducirSonido();
                sonidoReproducido = true;
                turnoAnterior = turno;

                ultimosTurnos.unshift({
                    turno: turno,
                    usuario: "<?php echo $usuario; ?>",
                    horaLlamado: new Date().toLocaleTimeString(),
                    horaAtencion: "-",
                    servicio: servicio,
                    tipoDocumento: tipoDocumento,
                    numeroDocumento: numeroDocumento
                });

                if(ultimosTurnos.length > 5) ultimosTurnos.pop();
            }

        } else {
            document.getElementById("turno-actual").innerHTML = `<span class="turno-numero"></span>`;
            sonidoReproducido = false;
            turnoAnterior = null;
        }

        const tbody = document.getElementById("pendientes-body");
        tbody.innerHTML = "";
        if(data.pendientes && data.pendientes.length>0){
            data.pendientes.forEach(t=>{
                tbody.innerHTML += `<tr><td>T ${String(t.turno).padStart(3,"0")}</td><td>${t.servicio}</td></tr>`;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="2" class="text-center">No hay turnos pendientes</td></tr>';
        }

        const tablaHist = document.getElementById("historial-body");
        tablaHist.innerHTML = "";
        if(ultimosTurnos.length > 0){
            ultimosTurnos.forEach(row => {
                tablaHist.innerHTML += `
                    <tr>
                        <td>T ${String(row.turno).padStart(3,"0")}</td>
                        <td>${row.usuario}</td>
                        <td>${row.horaLlamado}</td>
                        <td>${row.horaAtencion}</td>
                        <td>${row.servicio}</td>
                        <td>${row.tipoDocumento} ${row.numeroDocumento}</td>
                    </tr>
                `;
            });
        } else {
            tablaHist.innerHTML = '<tr><td colspan="6" class="text-center">No hay historial disponible</td></tr>';
        }

    })
    .catch(err=>console.error("Error al actualizar turnos:",err));
}

function reproducirSonido(){
    const audio = new Audio("sounds/beep.mp3");
    audio.play().catch(()=>{});
}

function copiarDocumento(valor){
    navigator.clipboard.writeText(valor);
    const toast = document.getElementById('toastCopy');
    toast.classList.add('show');
    setTimeout(()=>toast.classList.remove('show'),1500);
}

actualizarTurnos();
setInterval(actualizarTurnos, 5000);
</script>
</body>
</html>
