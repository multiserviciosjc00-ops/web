<?php
session_start();
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');

$mensaje = "";

// Registrar usuario
if(isset($_POST['registrar'])) {
    $usuario = limpiar($con, $_POST['usuario']);
    $password = limpiar($con, $_POST['password']);
    $cajas = isset($_POST['cajas']) ? $_POST['cajas'] : [];

    if($usuario == "" || $password == "" || empty($cajas)) {
        $mensaje = "<div class='alert alert-danger'>Hay campos vacíos</div>";
    } else {
        $sql = "SELECT * FROM usuarios WHERE usuario='$usuario'";
        $buscar = consulta($con, $sql, "Error al verificar usuario");
        if(mysqli_num_rows($buscar) > 0) {
            $mensaje = "<div class='alert alert-warning'>El usuario ya fue registrado</div>";
        } else {
            $passwordEnc = encriptarMd5($password);
            $fecha = date('Y-m-d H:i:s');
            $sql = "INSERT INTO usuarios (usuario,password,fecha_alta) VALUES ('$usuario','$passwordEnc','$fecha')";
            consulta($con, $sql, "Error al registrar usuario");

            $usuario_id = mysqli_insert_id($con);
            foreach($cajas as $caja_id) {
                $caja_id = limpiar($con, $caja_id);
                $sqlInsertCaja = "INSERT INTO usuario_caja (idUsuario, idCaja) VALUES ($usuario_id, $caja_id)";
                consulta($con, $sqlInsertCaja, "Error al asignar caja");
            }
            $mensaje = "<div class='alert alert-success'>Usuario registrado con éxito</div>";
        }
    }
}

// Modificar usuario
if(isset($_POST['modificar'])) {
    $usuario_id = (int) $_POST['usuario_id'];
    $password = limpiar($con, $_POST['password']);
    $cajas = isset($_POST['cajas']) ? $_POST['cajas'] : [];

    if($usuario_id > 0) {
        if($password != "") {
            $passwordEnc = encriptarMd5($password);
            $sql = "UPDATE usuarios SET password='$passwordEnc' WHERE id=$usuario_id";
            consulta($con, $sql, "Error al actualizar contraseña");
        }

        $sqlDel = "DELETE FROM usuario_caja WHERE idUsuario=$usuario_id";
        consulta($con, $sqlDel, "Error al eliminar cajas antiguas");

        foreach($cajas as $caja_id) {
            $caja_id = limpiar($con, $caja_id);
            $sqlInsertCaja = "INSERT INTO usuario_caja (idUsuario, idCaja) VALUES ($usuario_id, $caja_id)";
            consulta($con, $sqlInsertCaja, "Error al asignar cajas nuevas");
        }
        $mensaje = "<div class='alert alert-success'>Usuario modificado con éxito</div>";
    }
}

// Eliminar usuario
if(isset($_POST['eliminar'])) {
    $usuario_id = (int) $_POST['usuario_id'];
    if($usuario_id > 0){
        consulta($con, "DELETE FROM usuario_caja WHERE idUsuario=$usuario_id", "Error al eliminar cajas del usuario");
        consulta($con, "DELETE FROM usuarios WHERE id=$usuario_id", "Error al eliminar usuario");
        $mensaje = "<div class='alert alert-success'>Usuario eliminado con éxito</div>";
    }
}

// Obtener todas las cajas
$sqlCajas = "SELECT id, nombre FROM cajas";
$resCajas = consulta($con, $sqlCajas, "Error al cargar cajas");

// Obtener usuarios existentes
$sqlUsuarios = "SELECT * FROM usuarios ORDER BY id ASC";
$resUsuarios = consulta($con, $sqlUsuarios, "Error al cargar usuarios");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    background-color: #f0f4f8; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
    color: #2c3e50;
}

.contenedor-principal { max-width: 1000px; margin: 50px auto; }

/* Tarjetas modernas */
.card-custom {
    background: linear-gradient(145deg, #74b9ff, #ffffff);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    color: #2c3e50;
    transition: transform 0.3s, box-shadow 0.3s;
}
.card-custom:hover {
    transform: scale(1.03);
    box-shadow: 0 15px 30px rgba(0,0,0,0.25);
}

/* Botones gradientes */
.btn-custom {
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: #fff;
    font-weight: bold;
    border-radius: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.btn-custom:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

/* Botón eliminar */
.btn-danger {
    background: linear-gradient(135deg, #e17055, #fab1a0);
    color: #fff;
    border: none;
    font-weight: bold;
}
.btn-danger:hover {
    background: linear-gradient(135deg, #fab1a0, #e17055);
}

/* Checkboxes */
.cajas-checkboxes label { display: block; margin-bottom: 5px; color: #2c3e50; }

/* Tabla */
.table-users th, .table-users td { vertical-align: middle; }

/* Inline form */
.form-inline { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.form-inline .form-check { margin-right: 10px; }
.form-inline button { min-width: 100px; }

/* Tabs */
.nav-tabs .nav-link { font-weight: bold; color: #0984e3; }
.nav-tabs .nav-link.active { background-color: #74b9ff; color: #fff; border-radius: 10px 10px 0 0; }
.tab-content { margin-top: 20px; }

</style>
</head>
<body>
<div class="contenedor-principal">

    <!-- Mensaje general -->
    <?php echo $mensaje; ?>

    <!-- Pestañas -->
    <ul class="nav nav-tabs" id="tabsUsuarios" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-registrar" data-bs-toggle="tab" data-bs-target="#registrar" type="button">Registrar Usuario</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-existentes" data-bs-toggle="tab" data-bs-target="#existentes" type="button">Usuarios Existentes</button>
      </li>
    </ul>

    <div class="tab-content">
        <!-- Registrar Usuario -->
        <div class="tab-pane fade show active" id="registrar">
            <div class="card card-custom shadow mt-3">
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ingrese su usuario" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cajas</label>
                            <div class="cajas-checkboxes">
                                <?php
                                mysqli_data_seek($resCajas,0);
                                while($caja = mysqli_fetch_assoc($resCajas)){
                                    echo "<div class='form-check'><input class='form-check-input' type='checkbox' name='cajas[]' value='{$caja['id']}' id='caja{$caja['id']}'>";
                                    echo "<label class='form-check-label' for='caja{$caja['id']}'>{$caja['nombre']}</label></div>";
                                }
                                ?>
                            </div>
                        </div>
                        <button type="submit" name="registrar" class="btn btn-custom">Registrar</button>
                        <a href="index.php" class="btn btn-outline-secondary ms-2">Volver al Menú</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Usuarios Existentes -->
        <div class="tab-pane fade" id="existentes">
            <div class="card card-custom shadow mt-3">
                <div class="card-body">
                    <table class="table table-striped table-bordered table-users">
                        <thead class="table-dark">
                            <tr>
                                <th>Usuario</th>
                                <th>Cajas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        mysqli_data_seek($resUsuarios,0);
                        while($usuario = mysqli_fetch_assoc($resUsuarios)){
                            $idUsuario = $usuario['id'];
                            $nombreUsuario = $usuario['usuario'];

                            $sqlC = "SELECT c.id, c.nombre FROM usuario_caja uc JOIN cajas c ON uc.idCaja=c.id WHERE uc.idUsuario=$idUsuario";
                            $resC = consulta($con, $sqlC, "Error al obtener cajas asignadas");
                            $cajasAsignadas = [];
                            while($r = mysqli_fetch_assoc($resC)){
                                $cajasAsignadas[$r['id']] = $r['nombre'];
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($nombreUsuario); ?></td>
                                <td><?php echo implode(", ", $cajasAsignadas); ?></td>
                                <td>
                                    <form method="post" class="form-inline p-2 border rounded">
                                        <input type="hidden" name="usuario_id" value="<?php echo $idUsuario; ?>">
                                        <input type="password" name="password" class="form-control" placeholder="Nueva contraseña">
                                        <?php
                                        mysqli_data_seek($resCajas,0);
                                        while($caja = mysqli_fetch_assoc($resCajas)){
                                            $checked = isset($cajasAsignadas[$caja['id']]) ? 'checked' : '';
                                            echo "<div class='form-check'><input class='form-check-input' type='checkbox' name='cajas[]' value='{$caja['id']}' id='modCaja{$idUsuario}_{$caja['id']}' $checked>";
                                            echo "<label class='form-check-label' for='modCaja{$idUsuario}_{$caja['id']}'>{$caja['nombre']}</label></div>";
                                        }
                                        ?>
                                        <button type="submit" name="modificar" class="btn btn-custom btn-sm">Modificar</button>
                                        <button type="submit" name="eliminar" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario <?php echo $nombreUsuario; ?>?');">Eliminar</button>
                                        <a href="index.php" class="btn btn-outline-secondary btn-sm ms-2">Volver al Menú</a>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
