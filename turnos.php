<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Turnos - Pantalla</title>

    <!-- Fuente digital -->
    <link href="https://fonts.cdnfonts.com/css/digital-7" rel="stylesheet">

    <style>
        :root {
            --gris-fondo: #f0f2f5;
            --gris-tabla: #e0e5eb;
            --texto-principal: #2c3e50;
            --azul-oscuro: #0a3d62;
            --verde-degradado: linear-gradient(135deg, #00c851, #007e33);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--gris-fondo);
            color: var(--texto-principal);
            margin: 0;
        }

        /* Tablero principal */
        .contenedor-tablaTurnos {
            display: flex;
            justify-content: center;
            gap: 30px;
            background: linear-gradient(90deg, #a0c4ff, #74b9ff);
            padding: 30px 60px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            margin-top: 20px;
        }

        .columna-tablaTurnos { text-align: center; }

        .tabla-turnosArriba {
            font-size: 2rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
        }

        /* Números tipo reloj digital */
        .tabla-turnosAbajo {
            font-family: 'Digital-7', 'Courier New', monospace;
            font-size: 100px;
            font-weight: bold;
            padding: 15px 30px;
            border-radius: 15px;
            text-shadow: none;
            transition: all 1s ease;
        }

        #verTurno { 
            background-color: var(--azul-oscuro); 
            color: #fff; 
            display: flex; 
            justify-content: center; 
            gap: 10px; 
        }
        #verCaja { background: var(--verde-degradado); color: #fff; }
        #verPiso { background-color: #555; color: #fff; }

        .digito-turno { font-weight: bold; font-size: 100px; }

        /* Colores modernos y llamativos */
        .color1 { color: #ff6b6b; }
        .color2 { color: #feca57; }
        .color3 { color: #48dbfb; }

        /* Historial con colores por columna */
        .tabla-turnos td:nth-child(1) { color: #0a3d62; } /* Turno */
        .tabla-turnos td:nth-child(2) { color: #007e33; } /* Módulo */
        .tabla-turnos td:nth-child(3) { color: #c0392b; } /* Piso */
        .tabla-turnos tr.atendiendo td { font-weight: bold; }

        .contenedor-ultimos {
            width: 50%;
            margin: 30px auto;
            background: var(--gris-tabla);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .tabla-turnos {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-turnos th, .tabla-turnos td {
            text-align: center;
            padding: 15px;
            font-size: 36px;
            border-bottom: 1px solid #ccc;
            color: var(--texto-principal);
        }

        .tabla-turnos th {
            background: #0a3d62;
            color: #fff;
            font-size: 38px;
        }

        .tabla-turnos tr:last-child td { border-bottom: none; }

        /* Contenido empresa y multimedia */
        .contenido {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 30px;
        }

        .contenido-izquierda, .contenido-derecha {
            flex: 1;
            min-width: 400px;
            margin: 10px;
        }

        .logo-empresa img {
            max-width: 500px; 
            max-height: 250px; 
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }

        .nombre-empresa {
            text-align: center;
            color: var(--texto-principal);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        /* Contenedor hora y fecha al lado izquierdo */
        .contenedor-fecha-hora {
            position: absolute;
            left: 20px;
            top: 20px;
            text-align: left;
            font-size: 1.2rem;
            color: var(--texto-principal);
        }

        /* Carrusel multimedia */
        .carrusel {
            position: relative;
            width: 100%;
            max-width: 600px;
            height: 350px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .carrusel-slide { display: none; width: 100%; height: 100%; justify-content: center; align-items: center; }
        .carrusel-slide.active { display: flex; animation: fadeIn 1s ease-in-out; }
        .carrusel-slide img, .carrusel-slide video, .carrusel-slide iframe { max-width: 100%; max-height: 100%; border-radius: 10px; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

        .carrusel-buttons { position: absolute; top: 50%; width: 100%; display: flex; justify-content: space-between; transform: translateY(-50%); padding: 0 10px; pointer-events: none; }
        .carrusel-buttons button { pointer-events: all; background: rgba(0,0,0,0.4); color: #fff; border: none; padding: 8px 12px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; transition: background 0.3s; }
        .carrusel-buttons button:hover { background: rgba(0,0,0,0.7); }

        .miniaturas { display: flex; justify-content: center; margin-top: 10px; gap: 8px; }
        .miniaturas img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; cursor: pointer; border: 2px solid transparent; transition: border 0.3s; }
        .miniaturas img.active { border: 2px solid #0a3d62; }

        .upload-btn { display: block; margin: 15px auto 0; }
        .footer { display: none; }

        @media (max-width: 1024px){
            .contenedor-tablaTurnos { flex-direction: column; gap: 15px; }
            .tabla-turnosAbajo { font-size: 70px; padding: 12px 20px; }
            .tabla-turnos th, .tabla-turnos td { font-size: 28px; padding: 10px; }
            .logo-empresa img { max-width: 350px; max-height: 200px; }
        }
    </style>
</head>
<body>
<div class="contenedor-principal">

<?php
    require_once('funciones/conexion.php');
    require_once('funciones/funciones.php');
    $sql = "SELECT * FROM info_empresa LIMIT 1";
    $search = consulta($con, $sql, "Error al cargar datos de la empresa");
    $info = mysqli_fetch_assoc($search);
?>

<header>
    <div class="contenedor-tablaTurnos">
        <div class="columna-tablaTurnos">
            <div class="tabla-turnosArriba">Turno</div>
            <div class="tabla-turnosAbajo" id="verTurno"></div>
        </div>
        <div class="columna-tablaTurnos">
            <div class="tabla-turnosArriba">Módulo</div>
            <div class="tabla-turnosAbajo" id="verCaja">---</div>
        </div>
        <div class="columna-tablaTurnos">
            <div class="tabla-turnosArriba">Piso</div>
            <div class="tabla-turnosAbajo" id="verPiso">---</div>
        </div>
    </div>
</header>

<section class="contenido">
    <div class="contenido-izquierda">
        <div class="logo-empresa">
            <img src="<?php echo $info['logo'];?>" alt="Logo">
        </div>
        <h1 class="nombre-empresa"><?php echo $info['nombre'];?></h1>

        <div class="carrusel" id="carrusel"></div>
        <div class="carrusel-buttons">
            <button id="prevSlide">&#10094;</button>
            <button id="nextSlide">&#10095;</button>
        </div>
        <div class="miniaturas" id="miniaturas"></div>
        <input type="file" id="fileInput" class="upload-btn" accept="image/*,video/*,.pdf" multiple>
    </div>

    <div class="contenido-derecha">
        <div class="contenedor-ultimos">
            <table class="tabla-turnos" id="tabla-turnos">
                <tr><th>Turno</th><th>Módulo</th><th>Piso</th></tr>
            </table>
        </div>
    </div>
</section>

<div class="contenedor-fecha-hora">
    <p id="hora"></p>
    <p id="fecha"></p>
</div>

<script>
let turnoAnterior = null;

function padTurno(turno) { return turno.toString().padStart(3,"0"); }

function actualizarTurnos() {
    fetch("obtener_turno.php")
        .then(res=>res.json())
        .then(data=>{
            const turnoPrincipal = document.getElementById("verTurno");
            const cajaPrincipal = document.getElementById("verCaja");
            const pisoPrincipal = document.getElementById("verPiso");

            if(turnoAnterior!==data.turno){
                new Audio("tonos/tono.mp3").play().catch(err=>console.error(err));
                turnoAnterior = data.turno;
            }

            // Mostrar turno principal con colores modernos
            turnoPrincipal.innerHTML = "";
            padTurno(data.turno).split('').forEach((d,i)=>{
                const span = document.createElement("span");
                span.classList.add("digito-turno", `color${(i%3)+1}`);
                span.textContent=d;
                turnoPrincipal.appendChild(span);
            });

            cajaPrincipal.innerText = data.caja;
            pisoPrincipal.innerText = data.piso;

            // Últimos 5 turnos
            const tabla = document.getElementById("tabla-turnos");
            while(tabla.rows.length>1) tabla.deleteRow(1);
            data.ultimos.slice(-5).forEach(item=>{
                const tr=document.createElement('tr');
                tr.setAttribute('data-turno',item.turno);
                if(item.estado==='atendiendo') tr.classList.add('atendiendo');
                tr.innerHTML=`<td>${padTurno(item.turno)}</td><td>${item.idCaja}</td><td>${item.piso}</td>`;
                tabla.appendChild(tr);
            });
        })
        .catch(err=>console.error(err));
}

function actualizarHoraFecha(){
    const now=new Date();
    document.getElementById("hora").innerText=now.toLocaleTimeString();
    document.getElementById("fecha").innerText=new Intl.DateTimeFormat('es-CO',{ weekday:'long', year:'numeric', month:'long', day:'numeric'}).format(now);
}

setInterval(actualizarTurnos,500);
setInterval(actualizarHoraFecha,500);
actualizarHoraFecha();

// Carrusel multimedia
let slides=[], currentSlide=0;
const carrusel=document.getElementById("carrusel");
const fileInput=document.getElementById("fileInput");
const miniaturas=document.getElementById("miniaturas");
const prevBtn=document.getElementById("prevSlide");
const nextBtn=document.getElementById("nextSlide");

function renderSlides() {
    carrusel.innerHTML="";
    miniaturas.innerHTML="";
    slides.forEach((file,i)=>{
        const slide=document.createElement("div");
        slide.classList.add("carrusel-slide");
        if(i===currentSlide) slide.classList.add("active");

        let elemento;
        const url=URL.createObjectURL(file);
        if(file.type.startsWith("image/")) elemento=document.createElement("img");
        else if(file.type.startsWith("video/")) { elemento=document.createElement("video"); elemento.autoplay=true; elemento.loop=true; elemento.muted=true; }
        else if(file.type==="application/pdf") elemento=document.createElement("iframe");

        elemento.src=url;
        slide.appendChild(elemento);
        carrusel.appendChild(slide);

        if(file.type.startsWith("image/")){
            const thumb=document.createElement("img");
            thumb.src=url;
            if(i===currentSlide) thumb.classList.add("active");
            thumb.addEventListener("click",()=>{ currentSlide=i; updateSlide(); });
            miniaturas.appendChild(thumb);
        }
    });
}

function updateSlide() {
    document.querySelectorAll(".carrusel-slide").forEach((s,i)=>s.classList.toggle("active", i===currentSlide));
    document.querySelectorAll(".miniaturas img").forEach((t,i)=>t.classList.toggle("active", i===currentSlide));
}

function nextSlideFunc(){ currentSlide=(currentSlide+1)%slides.length; updateSlide(); }
function prevSlideFunc(){ currentSlide=(currentSlide-1+slides.length)%slides.length; updateSlide(); }

fileInput.addEventListener("change",e=>{ slides=Array.from(e.target.files); currentSlide=0; renderSlides(); });
nextBtn.addEventListener("click",nextSlideFunc);
prevBtn.addEventListener("click",prevSlideFunc);
setInterval(()=>{ if(slides.length>0) nextSlideFunc(); },5000);
</script>

</body>
</html>
