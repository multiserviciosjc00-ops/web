<?php
session_start();
date_default_timezone_set('America/Bogota');

// Conexión BD
$host = "localhost";
$user = "root";
$pass = "";
$db   = "turnero";
$con = new mysqli($host, $user, $pass, $db);
if ($con->connect_error) {
    die("Error de conexión: " . $con->connect_error);
}

// 🔹 Consultar últimos 5 turnos atendiendo
$sql = "
    SELECT turno, idCaja, fecha 
    FROM (
        SELECT turno, idCaja, fecha FROM turnos_pares WHERE estado='atendiendo'
        UNION
        SELECT turno, idCaja, fecha FROM turnos_impares WHERE estado='atendiendo'
    ) AS todos
    ORDER BY fecha DESC
    LIMIT 5
";
$res = $con->query($sql);

$turnos = [];
while ($fila = $res->fetch_assoc()) {
    $turnos[] = $fila;
}

$ultimoTurno = count($turnos) > 0 ? $turnos[0] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tablero de Turnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .turno-actual {
            font-size: 6rem;
            font-weight: bold;
            color: #00ff88;
        }
        .caja-actual {
            font-size: 3rem;
            color: #00aaff;
        }
        .historial {
            margin-top: 50px;
        }
        .historial-item {
            font-size: 2rem;
            padding: 10px;
            border-bottom: 1px solid #444;
        }
    </style>
    <script>
        // Refrescar cada 5 segundos
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    </script>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">📺 Tablero de Turnos</h1>

        <?php if ($ultimoTurno): ?>
            <div class="mb-5">
                <div class="turno-actual">Turno <?php echo $ultimoTurno['turno']; ?></div>
                <div class="caja-actual">👉 Caja <?php echo $ultimoTurno['idCaja']; ?></div>
            </div>
        <?php else: ?>
            <h2 class="text-warning">⏳ Aún no hay turnos llamados</h2>
        <?php endif; ?>

        <div class="historial">
            <h3 class="mb-3">Últimos turnos llamados</h3>
            <?php if (count($turnos) > 1): ?>
                <?php foreach (array_slice($turnos, 1) as $t): ?>
                    <div class="historial-item">
                        Turno <strong><?php echo $t['turno']; ?></strong> → Caja <?php echo $t['idCaja']; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Sin historial</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
