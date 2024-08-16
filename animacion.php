<?php
session_start();
include_once './includes/conexion.php';
include_once './includes/functions.php';

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['id_usuario'])) {
    header('Location: ../loginRegister.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animación de Fondo con Hamburguesa</title>
    <link rel="stylesheet" href="./css/animacion2.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
</head>
<body>
    <div id="background">
        <div id="burger-container">
            <img id="burger" class="large" src="./img/hamburguesa-cara-aterradora-otro-rasgo-monstruo-ilustracion-vectorial_648963-489-Photoroom.png" alt="Hamburguesa Enfurecida">
            <div id="burger-eyes"></div> 
        </div>
        <div id="buttons-container">
            <button class="animated-button" onclick="logout()">Cerrar Sesión</button>
            <button class="animated-button" onclick="adminPanel()">Panel Administrativo</button>
        </div>
        <div class="button-description">
            <p>Cerrar Sesión</p>
            <p>Panel Administrativo</p>
        </div>
    </div>
    <script src="./js/animacion1.js"></script>
</body>
</html>