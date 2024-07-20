<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Restringido</title>
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
        </div>
        <div class="button-description">
        </div>
    </div>
    <script src="./js/animacion1.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Acceso No Autorizado',
                text: 'No tienes permiso para acceder a esta página.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'loginRegister.php';
                }
            });
        });
    </script>
</body>
</html>
