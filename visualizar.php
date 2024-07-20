<?php

include './includes/conexion.php';


if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];


 

    $sql = "SELECT * FROM productos WHERE id_producto = $id_producto";

    if ($result = $conn->query($sql)) {
        if ($row = $result->fetch_assoc()) {

            $nombre_producto = $row['nombre_producto'];
            $descripcion_producto = $row['descripcion_producto'];
            $precio_producto = $row['valor_unitario'];
            $ruta_imagen = "uploads/" . $row['foto'];
        } else {
            echo "Producto no encontrado.";
            exit();
        }

        $result->close();
    } else {
        echo "Error: La consulta falló. Revisa tu conexión a la base de datos.";
        exit();
    }

    $conn->close();
} else {
    echo "ID de producto no especificado.";
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Exterminio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/visualizar2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v6.0.0-beta2/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-xYBLw4ZuP0SmiB3KdGlLJD6EXxFR4t2GtNQbF8xM7YFjt9kyQxMkJt/KtSb7D02ErD9a5fjhLDT+8lLBkOP9Cw==" crossorigin="anonymous" />
</head>

<body>

    <header class="header">
        <img class="imagen1" src="./img/LogoExterminio.png" alt="">
        <!-- <div class="search">
        <input type="search" placeholder="Buscar...">
    </div> -->
        <nav>
            <ul class="nav-links">
                <li><a href="./index.php">Inicio</a></li>
                <li><a href="#">Productos</a></li>
                <li><a href="./configuracion.php">Mi Perfil</a></li>
            </ul>
        </nav>
        <div class="logout">
            <a href="./loginRegister.php" id="toggle-logout"><i class="fa-solid fa-right-from-bracket" style="color: #ff0000;"></i></a>
        </div>
    </header>

    <div class="card-wrapper">
        <div class="card">
            <!-- card left -->
            <div class="product-imgs">
                <div class="img-display">
                    <div class="img-showcase">
                        <img src="<?php echo $ruta_imagen; ?>" alt="Imagen del producto">
                    </div>
                </div>
                <div class="img-select">
                    <div class="img-item">
                        <a href="#" data-id="1">
                            <img src="<?php echo $ruta_imagen; ?>" alt="Imagen del producto">
                        </a>
                    </div>
                    <div class="img-item">
                        <a href="#" data-id="2">
                            <img src="<?php echo $ruta_imagen; ?>" alt="Imagen del producto">
                        </a>
                    </div>
                    <div class="img-item">
                        <a href="#" data-id="3">
                            <img src="<?php echo $ruta_imagen; ?>" alt="Imagen del producto">
                        </a>
                    </div>
                    <div class="img-item">
                        <a href="#" data-id="4">
                            <img src="<?php echo $ruta_imagen; ?>" alt="Imagen del producto">
                        </a>
                    </div>
                </div>
            </div>
            <!-- card right -->
            <div class="product-content">
                <h2 class="product-title"><?php echo $nombre_producto; ?></h2>
                <a href="#" class="product-link">Visita nuestro negocio físico</a>
                <div class="product-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star-half-alt"></i>
                    <span>4.7(21)</span>
                </div>

                <div class="product-price">
                    <p class="new-price">Precio: <span>$<?php echo $precio_producto; ?></span></p>
                </div>

                <div class="product-detail">
                    <h2>¿Qué trae?: </h2>
                    <p><?php echo $descripcion_producto; ?></p>
                </div>

                <!-- <div class="purchase-info">
                <input type="number" min="0" value="1">
                <button type="button" class="btn" onclick="syncCart(); addToCart();">
                    Añadir al carrito
                </button>
                <button type="button" class="btn" onclick="window.location.href='../Formularios/indexFormulario.html'; syncCart();">
                    Comprar Ahora
                </button>
            </div> -->

                <div class="social-links">
                    <p>Redes Sociales: </p>
                    <a href="#">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-container">
        <footer>
            <h1>EXTERMINIO</h1>
            <div class="content-footer">
                <div class="contact">
                    <h4>REDES SOCIALES</h4>
                    <a href="#"><ion-icon name="logo-whatsapp"></ion-icon></i></a>
                    <a href="#"><i class="fa-brands fa-square-instagram"></i></a>
                </div>
                <div class="group">
                    <ul>
                        <li>DESARROLLADORES:</li><br>
                        <li>CARLOS MOSQUERA</li>
                        <li>YEFFERSON PALACIOS</li>
                        <li>KEVIN HURTADO</li>
                        <li>ANDREA SECA</li>
                    </ul>
                </div>
            </div>
            <div class="copy">
                <h4>© 2023 Exterminio - Todos los derechos reservados</h4>
            </div>
        </footer>
    </div>

    <script src="./js/script.js"></script>
</body>

</html>