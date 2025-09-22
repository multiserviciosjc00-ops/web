<!doctype html>
<html lang="es">
<?php
require_once('funciones/conexion.php');
require_once('funciones/funciones.php');
?>
<head>
    <meta charset="utf-8">
    <title>Controlador de Turnos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --azul-claro: #74b9ff;
            --azul-oscuro: #0984e3;
            --gris-fondo: #f0f4f8;
            --texto-principal: #2c3e50;
            --blanco: #ffffff;
            --rojo-error: red;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gris-fondo);
            color: var(--texto-principal);
            margin: 0;
        }

        .contenedor-principal {
            max-width: 1400px;
            margin: 50px auto;
            padding: 0 15px;
            text-align: center;
        }

        h1.titulo-seccion {
            margin-bottom: 50px;
            font-size: 3.5rem;
            color: var(--azul-oscuro);
        }

        .contenedor-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        .menu-card {
            width: 260px;
            height: 260px;
            background: linear-gradient(145deg, var(--azul-claro), var(--blanco));
            color: #000;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
        }

        .menu-card i {
            font-size: 4.5rem;
            margin-bottom: 15px;
            color: var(--azul-oscuro);
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px){
            .menu-card { width: 220px; height: 220px; }
            .menu-card i { font-size: 3.5rem; }
        }
    </style>
</head>
<body>
    <div class="contenedor-principal">
        <h1 class="titulo-seccion">Controlador de Turnos</h1>

        <div class="contenedor-menu">
            <!-- Visualizador de turnos -->
            <div class="menu-card" onclick="location.href='turnos.php'">
                <i class="fas fa-tv"></i>
                Visualizador de turnos
            </div>

            <!-- Solicitar turno ahora va a la pantalla de inicio -->
            <div class="menu-card" onclick="location.href='inicio.php'">
                <i class="fas fa-ticket-alt"></i>
                Solicitar turno
            </div>

            <!-- Ingreso a Módulo -->
            <div class="menu-card" onclick="location.href='login.php'">
                <i class="fas fa-door-open"></i>
                Ingreso a Módulo
            </div>

            <!-- Registrar usuario -->
            <div class="menu-card" onclick="location.href='registrar_usuarios.php'">
                <i class="fas fa-user-plus"></i>
                Registrar usuario
            </div>

            <!-- Registrar Módulo -->
            <div class="menu-card" onclick="location.href='registrar_cajas.php'">
                <i class="fas fa-cash-register"></i>
                Registrar Módulo
            </div>

            <!-- Información de la empresa -->
            <div class="menu-card" onclick="location.href='info_empresa.php'">
                <i class="fas fa-building"></i>
                Información de la empresa
            </div>

            <!-- Resetear turnos -->
            <div class="menu-card" id="reset">
                <i class="fas fa-redo"></i>
                Resetear turnos
            </div>
        </div>
    </div>

    <script src="js/funcionesGenerales.js"></script>
    <script>
        agregarEvento(window, 'load', iniciarReset, false);
        function iniciarReset(){
            var resetear = document.getElementById('reset');
            if(!resetear) return;

            agregarEvento(resetear, 'click', function(e){
                if(e) e.preventDefault();
                var seguro = confirm("⚠️ ¿Seguro que quieres resetear todos los turnos? Esta acción no se puede deshacer.");
                if(!seguro) return;

                var datos = "registrar=reset-turnos";
                funcion = procesarReseteo;
                fichero = "consultas/registrar.php";
                conectarViaPost(funcion,fichero,datos);
            }, false);

            function procesarReseteo(){
                if(conexion.readyState == 4){
                    try{
                        var data = JSON.parse(conexion.responseText);
                        if(data.status == "correcto"){
                            alert(data.mensaje);
                        } else{
                            console.log(data.mensaje);
                        }
                    } catch(err){
                        console.log('Error al procesar respuesta:', conexion.responseText);
                    }
                } else{
                    console.log('cargando');
                }
            }
        }
    </script>
</body>
</html>
