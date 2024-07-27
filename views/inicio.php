<?php
session_start();

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['rol'])) {
    header('Location: ../loginRegister.php');
    exit();
}

// Excluir específicamente el rol "Usuario"
if ($_SESSION['rol'] === 'Usuario') {
    header('Location: ../no_autorizado.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/inicio.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
</head>

<body>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="head-section">
            <div class="title-container">
                <h1>Inicio</h1>
                <div class="search-bar">
                    <input type="text" placeholder="Buscar..." />
                    <button type="button"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <div class="profile-div">
                <div class="profile-inner-container">
                    <p class="user1" onclick="toggleUserOptions()">
                        <i class="fa fa-user"></i> <?php echo isset($_SESSION['correo_electronico']) ? $_SESSION['correo_electronico'] : ''; ?>
                    </p>
                </div>
                <div id="userOptionsContainer" class="user-options-container">
                    <p><i class="fa fa-cog"></i> Configuración</p>
                    <a href="../loginRegister.php">
                        <p><i class="fa fa-power-off"></i> Cerrar sesión</p>
                    </a>
                </div>
            </div>
            <br><br>
        </div>
        <div class="image-container">
            <img id="draggableImage" src="../img/hamburguesa-cara-aterradora-otro-rasgo-monstruo-ilustracion-vectorial_648963-489-Photoroom.png" alt="Imagen Movible">
        </div>
    </div>
</div>

<script>
    function toggleUserOptions() {
        var userOptionsContainer = document.getElementById("userOptionsContainer");
        if (userOptionsContainer.style.display === "none" || userOptionsContainer.style.display === "") {
            userOptionsContainer.style.display = "block";
        } else {
            userOptionsContainer.style.display = "none";
        }
    }
</script>
</body>

</html>
