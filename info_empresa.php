<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Información de Empresa - Azul Moderno</title>

<style>
/* Reset y fuentes */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #e0f0ff, #d0e7ff);
    color: #333;
    min-height: 100vh;
    position: relative;
    padding-bottom: 60px; /* espacio para ticker */
}

/* Contenedor principal */
.contenedor-principal {
    max-width: 1000px;
    margin: 50px auto;
    padding: 20px;
}

/* Card principal con azul degradiente */
.contenedor-info {
    background: linear-gradient(145deg, #2196f3, #6ec6ff);
    border-radius: 16px;
    padding: 30px 40px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
    color: #fff;
}

.contenedor-info:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
}

.contenedor-info h1 {
    text-align: center;
    font-size: 30px;
    font-weight: 700;
    margin-bottom: 25px;
}

/* Lista de información */
.info-empresa {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-empresa li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.3);
    font-size: 17px;
    transition: background 0.2s;
}

.info-empresa li:hover {
    background: rgba(255,255,255,0.1);
}

.strong { font-weight: 600; }
.span { font-weight: 500; }

/* Logo */
.logoImagen img {
    max-width: 140px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

/* Botones con azul degradiente */
button, input[type="submit"] {
    padding: 12px 22px;
    border-radius: 8px;
    font-size: 16px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

#editarInfo {
    background: linear-gradient(135deg, #2196f3, #6ec6ff);
    color: #fff;
    margin-top: 20px;
    display: inline-block;
}

#editarInfo:hover {
    background: linear-gradient(135deg, #6ec6ff, #2196f3);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.cerrar {
    background: #f44336;
    color: #fff;
    margin-left: 10px;
}

.cerrar:hover { background: #e53935; }

.mensajes { margin-top: 20px; font-size: 16px; }
.mensaje { color: #ffbaba; }
.correcto { color: #b4ffb4; }

/* Link menú */
.link-menu {
    display: inline-block;
    margin-top: 25px;
    text-decoration: none;
    color: #fff;
    font-weight: 600;
    font-size: 16px;
    background: rgba(0,0,0,0.1);
    padding: 8px 14px;
    border-radius: 6px;
    transition: background 0.3s;
}
.link-menu:hover { background: rgba(0,0,0,0.2); }

/* Modal de edición */
.form-editar {
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    width: 90%;
    max-width: 500px;
    background: linear-gradient(135deg, #2196f3, #6ec6ff);
    border-radius: 16px;
    padding: 30px 40px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.3);
    z-index: 1000;
    opacity: 0;
    transition: all 0.3s ease;
    color: #fff;
}

.form-editar.show {
    display: block;
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.form-editar h1 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 26px;
    font-weight: 700;
}

.form-editar .label {
    display: block;
    margin: 12px 0 5px;
    font-weight: 500;
}

.form-editar .input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    font-size: 16px;
    margin-bottom: 10px;
}

/* Overlay */
.overlay {
    position: fixed;
    top:0; left:0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    display: none;
    z-index: 900;
}

.overlay.show { display: block; }

/* Ticker de noticias */
.ticker {
    position: fixed;
    bottom: 0; left: 0;
    width: 100%;
    background: linear-gradient(135deg, #2196f3, #6ec6ff);
    color: #fff;
    font-weight: 600;
    font-size: 16px;
    padding: 12px 0;
    overflow: hidden;
    white-space: nowrap;
    box-shadow: 0 -4px 15px rgba(0,0,0,0.2);
    z-index: 1100;
}

.ticker-content {
    display: inline-block;
    padding-left: 100%;
    animation: ticker 20s linear infinite;
}

@keyframes ticker {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
</style>
</head>
<body>

<div class="contenedor-principal">        
    <div class="contenedor-info" id="info-empresa">
        <h1>Información de la empresa</h1>

        <?php
            require_once('funciones/conexion.php');
            require_once('funciones/funciones.php');

            $mensaje = "";

            if(isset($_POST['editar'])){
                $datos = array($_POST['nombre'],$_POST['direccion'],$_POST['telefono'],$_POST['correo']);
                if(verificar_datos($datos)){
                    $nombre = limpiar($con,$_POST['nombre']);
                    $direccion = limpiar($con,$_POST['direccion']);
                    $telefono = limpiar($con,$_POST['telefono']);
                    $correo = limpiar($con,$_POST['correo']);
                    $fecha = date('Y-m-d H:i:s');
                    $status = true;

                    if($_FILES['logo']['name'] != ''){
                        $name = $_FILES['logo']['name'];
                        $tmp_name = $_FILES['logo']['tmp_name'];
                        $size = $_FILES['logo']['size'];
                        $type = $_FILES['logo']['type'];
                        $mensajes = imagen($con,$name,$tmp_name,$size,$type);
                        $mensajes = json_decode($mensajes);
                        $status = $mensajes -> status;
                        $mensaje = $mensajes -> mensaje;
                        $logo = "logo='$mensajes->imagen',";
                    } else {
                        $logo = '';
                    }

                    if($status == true){
                        $sql = "update info_empresa set $logo nombre='$nombre',direccion='$direccion', telefono='$telefono',correo='$correo',fecha_actualizacion='$fecha'";
                        $error = "Error actualizar la informacion de la empresa";
                        $editar = consulta($con,$sql,$error);

                        if($editar == true){
                            $mensaje = "<div class='correcto'>Datos actualizados correctamente</div>";
                        } else {
                            $mensaje = "<div class='mensaje'>Error al actualizar la información</div>";
                        }
                    }
                } else {
                    $mensaje = "<div class='mensaje'>Hay campos vacíos</div>";
                }
            }

            $sql = "select * from info_empresa";
            $error = "Error al cargar los datos de la empresa";
            $buscar = consulta($con,$sql,$error);
            $info = mysqli_fetch_assoc($buscar);

            $idEmpresa = $info['id'];
            $logo = $info['logo'];
            $nombre = $info['nombre'];
            $direccion = $info['direccion'];
            $telefono = $info['telefono'];
            $correo = $info['correo'];
            $fecha = $info['fecha_actualizacion'];
        ?>

        <ul class="info-empresa">
            <li><strong class="strong">Logo:</strong><figure class="logoImagen"><img src="<?php echo $logo;?>" alt="Logotipo"></figure></li>
            <li><strong class="strong">Nombre:</strong><span class="span"><?php echo $nombre;?></span></li>
            <li><strong class="strong">Dirección:</strong><span class="span"><?php echo $direccion;?></span></li>
            <li><strong class="strong">Teléfono:</strong><span class="span"><?php echo $telefono;?></span></li>
            <li><strong class="strong">Correo:</strong><span class="span"><?php echo $correo;?></span></li>
            <li><strong class="strong">Fecha:</strong><span class="span"><?php echo $fecha;?></span></li>
        </ul>

        <button id="editarInfo" value="<?php echo $idEmpresa;?>">Editar Información</button>
        <div class="mensajes"><?php echo $mensaje;?></div>

        <a href="index.php" class="link-menu">Menú Principal</a>
    </div>
</div>

<!-- Overlay y Form Modal -->
<div class="overlay" id="overlay"></div>
<form action="<?php $_SERVER['PHP_SELF']?>" method="post" id="form-editar-info" class="form-editar" enctype="multipart/form-data">
    <h1>Editar Información de la Empresa</h1>
    <label class="label">Logo:</label>
    <input type="file" name="logo" id="logo" class="input">
    <label class="label">Nombre:</label>
    <input type="text" name="nombre" id="nombre" value="<?php echo $nombre;?>" class="input">
    <label class="label">Dirección:</label>
    <input type="text" name="direccion" id="direccion" value="<?php echo $direccion;?>" class="input">
    <label class="label">Teléfono:</label>
    <input type="tel" name="telefono" id="telefono" value="<?php echo $telefono;?>" class="input">
    <label class="label">Correo:</label>
    <input type="email" name="correo" id="correo" value="<?php echo $correo;?>" class="input">
    <input type="submit" name="editar" id="editar" value="Actualizar">
    <button class="cerrar" id="cerrarEditarInfo">Cerrar</button>
</form>

<!-- Noticias ticker -->
<div class="ticker">
    <div class="ticker-content">
        🚀 Bienvenidos al sistema de turnos | 📢 Recuerde reclamar su turno en la ventanilla indicada | 🎉 Promoción especial esta semana en nuestros servicios | ✅ Atención rápida y eficiente siempre a su alcance
    </div>
</div>

<!-- Scripts -->
<script>
const editarBtn = document.getElementById('editarInfo');
const formModal = document.getElementById('form-editar-info');
const cerrarBtn = document.getElementById('cerrarEditarInfo');
const overlay = document.getElementById('overlay');

editarBtn.addEventListener('click', function() {
    formModal.classList.add('show');
    overlay.classList.add('show');
});

cerrarBtn.addEventListener('click', function(e) {
    e.preventDefault();
    formModal.classList.remove('show');
    overlay.classList.remove('show');
});

overlay.addEventListener('click', function() {
    formModal.classList.remove('show');
    overlay.classList.remove('show');
});
</script>

</body>
</html>
