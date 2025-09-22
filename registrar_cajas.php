<?php
$mensaje = "";
if(isset($_POST['registrar'])){
    require_once('funciones/conexion.php');
    require_once('funciones/funciones.php');

    $data = array($_POST['nombreModulo']);

    if(verificar_datos($data)){
        $modulo = limpiar($con,$_POST['nombreModulo']);
        $sql = "select id from cajas where nombre='$modulo'";
        $error = "Error al seleccionar los módulos";

        $buscar = consulta($con,$sql,$error);
        $noModulos = mysqli_num_rows($buscar);

        if($noModulos == 0){  
            $sql = "select id from cajas";
            $error = "Error al seleccionar los módulos";
            $buscar=consulta($con,$sql,$error);

            $noModulosPermitidos = 10;

            if(mysqli_num_rows($buscar) < $noModulosPermitidos){
                $fecha = date('Y-m-d H:i:s');
                $sql = "insert into cajas (nombre,fecha_de_registro) values ('$modulo', '$fecha')";
                $error = "Error al registrar el módulo";
                $registrar = consulta($con,$sql,$error);

                if($registrar == true){
                    $mensaje = "<span class='alert alert-success'>Módulo registrado</span>";
                }else{
                    $mensaje = "<span class='alert alert-danger'>Error al registrar el módulo</span>";
                }

            }else{
                $mensaje = "<span class='alert alert-warning'>No se pueden registrar más módulos</span>";
            }
        }else{
            $mensaje = '<span class="alert alert-warning">El módulo ya fue registrado</span>';
        }
    }else{
        $mensaje = "<span class='alert alert-danger'>Hay campos vacíos</span>";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Registrar Módulos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #f0f4f8;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
}

.contenedor-principal {
    max-width: 600px;
    margin: 60px auto;
    padding: 25px;
    background: linear-gradient(145deg, #74b9ff, #ffffff);
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s, box-shadow 0.3s;
}
.contenedor-principal:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

h1 {
    text-align: center;
    color: #0984e3;
    margin-bottom: 30px;
}

.form-login label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

.form-login input[type="text"] {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border: 2px solid #74b9ff;
    border-radius: 10px;
    font-size: 1rem;
    transition: border 0.3s, box-shadow 0.3s;
}
.form-login input[type="text"]:focus {
    border-color: #0984e3;
    box-shadow: 0 0 10px rgba(74,185,255,0.5);
    outline: none;
}

.form-login input[type="submit"] {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.form-login input[type="submit"]:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

.mensajes {
    display: block;
    margin-top: 15px;
    text-align: center;
    font-weight: bold;
}

.link-menu {
    display: block;
    text-align: center;
    margin-top: 25px;
    color: #0984e3;
    font-weight: bold;
    text-decoration: none;
}
.link-menu:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="contenedor-principal">

<form action="<?php $_SERVER['PHP_SELF']?>" method="post" class="form-login">
<h1>Registro de Módulos</h1>

<label>Nombre del Módulo:</label>
<input type="text" name="nombreModulo" id="nombreModulo" placeholder="Ingrese el nombre del módulo" required>
<input type="submit" name="registrar" id="registrar" value="Registrar Módulo">

<span class="mensajes"><?php echo $mensaje;?></span>
</form>

<a href="index.php" class="link-menu">Volver al Menú</a>

</div>
</body>
</html>
