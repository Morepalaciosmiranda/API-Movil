<?php
session_start();

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['rol'])) {

    header('Location: loginRegister.php');
    exit();
}

if ($_SESSION['rol'] !== 'Usuario') {

    header('Location: ./no_autorizado.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exterminio</title>
    <link rel="stylesheet" href="./css/index17.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v6.0.0-beta2/css/all.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-xYBLw4ZuP0SmiB3KdGlLJD6EXxFR4t2GtNQbF8xM7YFjt9kyQxMkJt/KtSb7D02ErD9a5fjhLDT+8lLBkOP9Cw==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
</head>

<body>
    <header class="header">
        <img src="./img/LogoExterminio.png" alt="">
        <nav>
            <ul class="nav-links">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#productos">Productos</a></li>
                <li><a href="./configuracion.php">Mi Perfil</a></li>
            </ul>
        </nav>
        <div class="car-shop">
            <a href="#" id="toggle-car" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge">0</span>
            </a>
        </div>
        <div class="logout">
            <a href="./loginRegister.php" id="toggle-logout"><i class="fa-solid fa-right-from-bracket" style="color: #ff0000;"></i></a>
        </div>
    </header>

    <div class="dashboard">
        <div class="banner">
            <video autoplay loop muted>
                <source src="./videos/video.mp4" type="video/mp4">
            </video>
        </div>
        <div class="prod-title">
            <h2>TODAS NUESTROS PRODUCTOS</h2>
        </div>

        <div class="dashboard-menu">
            <a href="#" data-category="Todo">Todo</a>
            <a href="#" data-category="Hamburguesas">Hamburguesas</a>
            <a href="#" data-category="Perros">Perros</a>
            <a href="#" data-category="Picadas">Picadas</a>
            <a href="#" data-category="Salchipapas">Salchipapas</a>
            <a href="#" data-category="Alitas BBQ">Alitas BBQ</a>
        </div>

        <div class="dashboard-content">
            <?php
            include './includes/conexion.php';

            $sql = "SELECT * FROM productos";

            if ($result = $conn->query($sql)) {
                $tarjetasPorFila = 3;
                $conteoTarjetas = 0;

                while ($row = $result->fetch_assoc()) {
                    if ($conteoTarjetas > 0 && $conteoTarjetas % $tarjetasPorFila === 0) {
                        echo '</div><div class="tarjetas-contenedor">';
                    }
                    $ruta_imagen = "uploads/" . $row['foto'];
                    $categoria = isset($row['categoria']) ? strtolower($row['categoria']) : 'sin-categoria';
                    $categoria_data = isset($row['categoria']) ? $row['categoria'] : 'sin-categoria'; // Valor predeterminado para data-category
                    $precio_formateado = number_format($row['valor_unitario'], 0, '.', ',');

                    echo '
        <div class="dashboard-card ' . $categoria . '" data-product-id="' . $row['id_producto'] . '" data-category="' . $categoria_data . '">
            <a href="./visualizar.php?id=' . $row['id_producto'] . '"><img class="card-image" src="' . $ruta_imagen . '"></a>
            <div class="card-detail">
                <h4>' . $row['nombre_producto'] . '</h4>
                <p>Descripción: ' . $row['descripcion_producto'] . '</p>
                <div class="info">
                    <p class="card-time"><span class="fas fa-clock"></span> Precio: <span class="span">$' . $precio_formateado . '</span></p>
                </div>
                <div class="btn-add">
                    <button class="addToCartButton">AÑADIR AL CARRITO</button>
                </div>
            </div>
        </div>
        ';

                    $conteoTarjetas++;
                }

                if ($conteoTarjetas % $tarjetasPorFila > 0) {
                    echo '</div>';
                }

                $result->close();
            } else {
                echo "Error al ejecutar la consulta: " . $conn->error;
            }

            $conn->close();
            ?>
        </div>



        <div class="sidebar">
            <h2>EXTERMINIO - MI CARRITO</h2>
            <div class="order-address">
                <p>Crea Tu Pedido</p>
            </div>
            <div class="order-wrapper">
                <?php
                session_start(); // Asegúrate de iniciar la sesión

                // Suponiendo que los productos ya se han añadido al carrito en $_SESSION['carritos'][$_SESSION['id_usuario']]
                $productos = isset($_SESSION['carritos'][$_SESSION['id_usuario']]) ? $_SESSION['carritos'][$_SESSION['id_usuario']] : array();

                foreach ($productos as $producto) {
                    // Formatear el precio con dos decimales para mostrarlo correctamente
                    $precio_formateado = number_format($producto['precio'], 2, '.', ',');
                    echo '<div class="order-card">';
                    echo '<img class="order-image" src="' . htmlspecialchars($producto['imagen']) . '" />';
                    echo '<div class="order-details">';
                    echo '<p class="order-name">' . htmlspecialchars($producto['nombre']) . '</p>';
                    echo '<p class="order-price">$' . htmlspecialchars($precio_formateado) . '</p>';
                    echo '</div>';
                    echo '<div class="cart-counter">' . htmlspecialchars($producto['cantidad']) . '</div>';
                    echo '<span class="order-remove">&times;</span>';
                    echo '</div>';
                }
                ?>
            </div>
            <button id="payButton" class="checkout">
                Pedir Ahora
            </button>
            <div id="total-container"></div>
        </div>



        <div class="footer-container">
            <footer>
                <h1>EXTERMINIO</h1>
                <div class="content-footer">
                    <div class="contact">
                        <h4>REDES SOCIALES</h4>
                        <a href="#"><ion-icon name="logo-whatsapp"></ion-icon></i></a>
                        <!-- <a href="#"><i class="fa-brands fa-square-instagram"></i></a> -->
                    </div>
                    <div class="group">
                        <ul>
                            <li>DESARROLLADORES:</li><br>
                            <li>CARLOS MOSQUERA</li>
                            <li>YEFFERSON PALACIOS</li>
                            <li>KEVIN HURTADO</li>
                        </ul>
                    </div>
                </div>
                <div class="copy">
                    <h4>© 2023 Exterminio - Todos los derechos reservados</h4>
                </div>
            </footer>
        </div>

        <div id="modalContainer" style="display: none;">
            <div class="main">



                <div class="container">
                    <svg id="exit" width="90" height="90" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3.35288 8.95043C4.00437 6.17301 6.17301 4.00437 8.95043 3.35288C10.9563 2.88237 13.0437 2.88237 15.0496 3.35288C17.827 4.00437 19.9956 6.17301 20.6471 8.95044C21.1176 10.9563 21.1176 13.0437 20.6471 15.0496C19.9956 17.827 17.827 19.9956 15.0496 20.6471C13.0437 21.1176 10.9563 21.1176 8.95044 20.6471C6.17301 19.9956 4.00437 17.827 3.35288 15.0496C2.88237 13.0437 2.8823717.25 10.9563 3.35288Z" stroke="#1B1B1B" stroke-width="1.5" />
                        <path d="M13.7678 10.2322L10.2322 13.7678M13.7678 13.7678L10.2322 10.2322" stroke="#1B1B1B" stroke-width="1.5" stroke-linecap="round" />
                    </svg>

                    <div class="heading">
                        <path d="M13.3986 7.64605C13.495 7.37724 13.88 7.37724 13.9764 7.64605L14.2401 8.38111C14.271 8.46715 14.3395 8.53484 14.4266 8.56533L15.1709 8.82579C15.443 8.92103 15.443 9.30119 15.1709 9.39644L14.4266 9.65689C14.3395 9.68738 14.271 9.75507 14.2401 9.84112L13.9764 10.5762C13.88 10.845 13.495 10.845 13.3986 10.5762L13.1349 9.84112C13.104 9.75507 13.0355 9.68738 12.9484 9.65689L12.2041 9.39644C11.932 9.30119 11.932 8.92103 12.2041 8.82579L12.9484 8.56533C13.0355 8.53484 13.104 8.46715 13.1349 8.38111L13.3986 7.64605Z" fill="#1B1B1B" />
                        <path d="M16.3074 10.9122C16.3717 10.733 16.6283 10.733 16.6926 10.9122L16.8684 11.4022C16.889 11.4596 16.9347 11.5047 16.9928 11.525L17.4889 11.6987C17.6704 11.7622 17.6704 12.0156 17.4889 12.0791L16.9928 12.2527C16.9347 12.2731 16.889 12.3182 16.8684 12.3756L16.6926 12.8656C16.6283 13.0448 16.3717 13.0448 16.3074 12.8656L16.1316 12.3756C16.111 12.3182 16.0653 12.2731 16.0072 12.2527L15.5111 12.0791C15.3296 12.0156 15.3296 11.7622 15.5111 11.6987L16.0072 11.525C16.0653 11.5047 16.111 11.4596 16.1316 11.4022L16.3074 10.9122Z" fill="#1B1B1B" />
                        <path d="M17.7693 3.29184C17.9089 2.90272 18.4661 2.90272 18.6057 3.29184L19.0842 4.62551C19.1288 4.75006 19.2281 4.84805 19.3542 4.89219L20.7045 5.36475C21.0985 5.50263 21.0985 6.05293 20.7045 6.19081L19.3542 6.66337C19.2281 6.7075 19.1288 6.80549 19.0842 6.93005L18.6057 8.26372C18.4661 8.65284 17.9089 8.65284 17.7693 8.26372L17.2908 6.93005C17.2462 6.80549 17.1469 6.7075 17.0208 6.66337L15.6705 6.19081C15.2765 6.05293 15.2765 5.50263 15.6705 5.36475L17.0208 4.89219C17.1469 4.84805 17.2462 4.75006 17.2908 4.62551L17.7693 3.29184Z" fill="#1B1B1B" />
                        <path d="M3 13.4597C3 17.6241 6.4742 21 10.7598 21C14.0591 21 16.8774 18.9993 18 16.1783C17.1109 16.5841 16.1181 16.8109 15.0709 16.8109C11.2614 16.8109 8.17323 13.8101 8.17323 10.1084C8.17323 8.56025 8.71338 7.13471 9.62054 6C5.87502 6.5355 3 9.67132 3 13.4597Z" stroke="#1B1B1B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <h1>Datos Para Confirmar Su Compra</h1>
                        <br>
                    </div>
                    <form id="pedidoFormulario" action="./controller/pedidos_controller.php" method="post">
                        <label for="nombre_cliente">Nombre Completo</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" placeholder="Nombre Completo" required />
                        <br>

                        <label for="calle">Dirección</label>
                        <input type="text" id="calle" name="calle" placeholder="Dirección" required />
                        <br>

                        <label for="interior">Interior</label>
                        <input type="text" id="interior" name="interior" placeholder="Interior" required />
                        <br>

                        <label for="barrio_cliente">Barrio</label>
                        <input type="text" id="barrio_cliente" name="barrio_cliente" list="barrios" placeholder="Barrio" required />
                        <datalist id="barrios">
                            <option value="Amazonas">
                            <option value="Araucarias">
                            <option value="Bello Horizonte">
                            <option value="Central">
                            <option value="Niquía">
                            <option value="Pachelly">
                            <option value="París">
                            <option value="Trapiche">
                        </datalist>
                        <br>

                        <label for="telefono_cliente">Teléfono</label>
                        <input type="text" id="telefono_cliente" name="telefono_cliente" placeholder="Teléfono" pattern="\d{10}" required title="El número de teléfono debe tener 10 dígitos y solo contener números" />
                        <br>

                        <input type="hidden" id="productos" name="productos" value="" />
                        <div class="btn">
                            <button id="btn_comprar" type="button" onclick="submitForm()">
                                <i class="fas fa-shopping-cart"></i> Comprar
                            </button>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const addToCartButtons = document.querySelectorAll('.addToCartButton');

                            addToCartButtons.forEach(button => {
                                button.addEventListener('click', function() {
                                    const card = this.closest('.dashboard-card');
                                    const productId = card.getAttribute('data-product-id');
                                    const productName = card.querySelector('h4').textContent;
                                    const productPrice = parseFloat(card.querySelector('.span').textContent.replace('$', '').replace(',', ''));
                                    const productQuantity = 1;

                                    fetch('index.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded'
                                            },
                                            body: `agregar_al_carrito=1&id_producto=${productId}&nombre_producto=${productName}&precio_producto=${productPrice}&cantidad_producto=${productQuantity}`
                                        })
                                        .then(response => {
                                            if (!response.ok) {
                                                throw new Error('Error al agregar el producto al carrito.');
                                            }
                                            return response.text();
                                        })
                                        .then(data => {
                                            // Actualizar la insignia del carrito o mostrar mensaje de éxito si es necesario
                                        })
                                        .catch(error => {
                                            Swal.fire('Error', 'Error: ' + error.message, 'error');
                                        });
                                });
                            });
                        });

                        document.getElementById('btn_comprar').addEventListener('click', function() {
                            const cartProducts = JSON.parse(localStorage.getItem('shoppingCart')) || [];
                            if (cartProducts.length === 0) {
                                Swal.fire('Error', 'No hay productos en el carrito. Agrega productos antes de enviar el pedido.', 'error');
                            } else {
                                document.getElementById('modalContainer').style.display = 'block';
                            }
                        });

                        function submitForm() {
                            const btnComprar = document.getElementById('btn_comprar');
                            const telefonoCliente = document.getElementById('telefono_cliente').value;
                            const barrioCliente = document.getElementById('barrio_cliente').value;
                            const barrioList = document.querySelectorAll('#barrios option');
                            let barrioValido = false;

                            barrioList.forEach(option => {
                                if (option.value === barrioCliente) {
                                    barrioValido = true;
                                }
                            });

                            if (!barrioValido) {
                                Swal.fire('Error', 'Por favor seleccione un barrio válido de la lista.', 'error');
                                return;
                            }

                            if (!/^\d{10}$/.test(telefonoCliente)) {
                                Swal.fire('Error', 'El número de teléfono debe tener 10 dígitos y solo contener números.', 'error');
                                return;
                            }

                            var productos = JSON.parse(localStorage.getItem('shoppingCart')) || [];

                            if (productos.length > 0) {
                                document.getElementById('productos').value = JSON.stringify(productos);

                                var form = document.getElementById('pedidoFormulario');
                                if (form.checkValidity()) {
                                    Swal.fire({
                                        title: '¿Estás seguro?',
                                        text: '¿Deseas realizar el pedido?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Sí, realizar pedido',
                                        cancelButtonText: 'Cancelar'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            var formData = new FormData(form);

                                            // Deshabilitar el botón mientras se procesa la solicitud
                                            btnComprar.setAttribute('disabled', true);

                                            fetch('./controller/pedidos_controller.php', {
                                                    method: 'POST',
                                                    body: formData
                                                })
                                                .then(response => response.text())
                                                .then(data => {
                                                    Swal.fire('Éxito', 'Pedido realizado correctamente.', 'success');
                                                    document.getElementById('modalContainer').style.display = 'none';
                                                    localStorage.removeItem('shoppingCart'); // Limpiar el carrito después de la compra
                                                    updateCartBadge(); // Actualizar la insignia del carrito si es necesario
                                                })
                                                .catch(error => {
                                                    Swal.fire('Error', 'Error: ' + error.message, 'error');
                                                })
                                                .finally(() => {
                                                    btnComprar.removeAttribute('disabled'); // Habilitar el botón después de completar la solicitud
                                                });
                                        }
                                    });
                                } else {
                                    Swal.fire('Error', 'Por favor complete todos los campos obligatorios.', 'error');
                                }
                            } else {
                                Swal.fire('Error', 'No hay productos en el carrito. Agrega productos antes de enviar el pedido.', 'error');
                            }
                        }

                        function updateCartBadge() {
                            const cartProducts = JSON.parse(localStorage.getItem('shoppingCart')) || [];
                            document.querySelector('.cart-badge').textContent = cartProducts.length;
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            const menuLinks = document.querySelectorAll('.dashboard-menu a');
                            const productCards = document.querySelectorAll('.dashboard-card');

                            menuLinks.forEach(link => {
                                link.addEventListener('click', function(event) {
                                    event.preventDefault();
                                    const category = this.getAttribute('data-category').toLowerCase();

                                    productCards.forEach(card => {
                                        const productCategory = card.getAttribute('data-category').toLowerCase();
                                        const productName = card.querySelector('.card-detail h4').textContent.toLowerCase();

                                        if (category === 'todo') {
                                            card.style.display = 'block';
                                        } else {
                                            if (productCategory.includes(category) || productName.includes(category)) {
                                                card.style.display = 'block';
                                            } else {
                                                card.style.display = 'none';
                                            }
                                        }
                                    });
                                });
                            });
                        });

                        function closeForm() {
                            document.getElementById('pedidoFormulario').style.display = 'none';
                        }
                    </script>




</html>
<script src="./js/index12.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-canvas/2.0.2/dom-canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.0/dist/sweetalert2.all.min.js"></script>
</body>


</html>