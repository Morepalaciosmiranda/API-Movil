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

include_once('../includes/conexion.php');


$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : '';

$sql = "SELECT pedidos.id_pedido, usuarios.nombre_usuario, pedidos.fecha_pedido, pedidos.estado_pedido 
        FROM pedidos
        JOIN usuarios ON pedidos.id_usuario = usuarios.id_usuario";

if ($fecha_filtro) {
    $sql .= " WHERE DATE(pedidos.fecha_pedido) = '$fecha_filtro'";
}
$sql .= " LIMIT $items_por_pagina OFFSET $offset";


$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

// Guarda los resultados en un array
$pedidos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pedidos[] = $row;
}


$sql_total = "SELECT COUNT(*) as total FROM pedidos";
if ($fecha_filtro) {
    $sql_total .= " WHERE DATE(fecha_pedido) = '$fecha_filtro'";
}
$result_total = mysqli_query($conn, $sql_total);
$total_pedidos = mysqli_fetch_assoc($result_total)['total'];
$total_paginas = ceil($total_pedidos / $items_por_pagina);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/pedidos12.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="content">
            <div class="head-section">
                <div class="title-container">
                    <h1>Pedidos</h1>
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
            </div>
            <div class="content">
                <div class="add-order-button">
                    <button onclick="abrirModalAgregarPedido()" class="btn-agregar">
                        <i class="fa fa-plus"></i> Agregar Pedido
                    </button>
                </div>
                <div class="form-container">
                    <form method="GET" action="pedidos.php">
                        <label for="fecha">Filtrar por fecha:</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo $fecha_filtro; ?>">
                        <button type="submit">Filtrar</button>
                    </form>
                </div>
                <table class="content">
                    <tr>
                        <th>ID Pedido</th>
                        <th>Nombre Usuario</th>
                        <th>Fecha de Pedido</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    <?php
                    if (count($pedidos) > 0) {
                        foreach ($pedidos as $row) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id_pedido']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombre_usuario']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['fecha_pedido']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['estado_pedido']) . "</td>";
                            echo '<td class="actions">';
                            echo '<button class="details-btn" onclick="verDetallesPedido(' . $row['id_pedido'] . ')"><i class="fa fa-info-circle"></i></button>';
                            echo '<button class="edit-btn" onclick="abrirModalEstado(' . $row['id_pedido'] . ', \'' . htmlspecialchars($row['estado_pedido'], ENT_QUOTES) . '\')"><i class="fa fa-edit"></i></button>';
                            echo '</td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No hay pedidos disponibles.</td></tr>";
                    }
                    ?>
                </table>
                <div class="pagination">
                    <?php
                    if ($total_paginas > 0) {
                        for ($i = 1; $i <= $total_paginas; $i++) {
                            if ($i == $pagina_actual) {
                                echo "<a href='pedidos.php?pagina=$i&fecha=$fecha_filtro' class='active'>$i</a>";
                            } else {
                                echo "<a href='pedidos.php?pagina=$i&fecha=$fecha_filtro'>$i</a>";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div id="modalDetallesPedido" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetailsModal()">&times;</span>
            <h2>Detalles del Pedido</h2>

            <div class="cliente-info">
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong> <span id="cliente-nombre"></span></p>
                <p><strong>Dirección:</strong> <span id="cliente-direccion"></span></p>
                <p><strong>Barrio:</strong> <span id="cliente-barrio"></span></p>
                <p><strong>Teléfono:</strong> <span id="cliente-telefono"></span></p>
            </div>

            <div class="productos-lista">
                <h3>Productos</h3>
                <div id="detalles-pedido"></div>
            </div>
        </div>
    </div>

    <div id="modalAgregarPedido" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalAgregarPedido()">&times;</span>
            <h2>Agregar Nuevo Pedido</h2>
            <form id="formAgregarPedido">
                <div class="form-group">
                    <label for="nombre_cliente">Nombre del Cliente:</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" required>
                </div>
                <div class="form-group">
                    <label for="calle">Calle:</label>
                    <input type="text" id="calle" name="calle" required>
                </div>
                <div class="form-group">
                    <label for="interior">Interior:</label>
                    <input type="text" id="interior" name="interior" required>
                </div>
                <div class="form-group">
                    <label for="barrio_cliente">Barrio:</label>
                    <input type="text" id="barrio_cliente" name="barrio_cliente" required>
                </div>
                <div class="form-group">
                    <label for="telefono_cliente">Teléfono:</label>
                    <input type="tel" id="telefono_cliente" name="telefono_cliente" required>
                </div>
                <div class="form-group">
                    <label for="producto">Producto:</label>
                    <select id="producto" name="producto" required>
                        <option value="">Seleccione un producto</option>
                        <?php
                        // Consulta para obtener todos los productos
                        $sql_productos = "SELECT id_producto, nombre_producto FROM productos";
                        $result_productos = mysqli_query($conn, $sql_productos);

                        while ($row_producto = mysqli_fetch_assoc($result_productos)) {
                            echo "<option value='" . $row_producto['id_producto'] . "'>" . htmlspecialchars($row_producto['nombre_producto']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" required>
                </div>
                <button type="submit" class="btn-guardar">Guardar Pedido</button>
            </form>
        </div>
    </div>

    <div id="modalEstadoPedido" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEstadoModal()">&times;</span>
            <h2>Cambiar Estado del Pedido</h2>
            <form id="formEstadoPedido">
                <input type="hidden" id="estadoPedidoId" name="pedido_id">
                <label for="estado_pedido">Estado:</label>
                <select id="estado_pedido" name="nuevo_estado">
                    <option value="en proceso">En Proceso</option>
                    <option value="en camino">En Camino</option>
                    <option value="entregado">Entregado</option>
                </select>
                <button type="submit" class="btnGuardad">Guardar</button>
            </form>
        </div>
    </div>

    <script>
        function verDetallesPedido(idPedido) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            var cliente = response.cliente;
                            var detallesHtml = '';

                            response.detalles.forEach(function(detalle) {
                                detallesHtml += `
                                <div class="producto-item">
                                    <span class="producto-nombre">${detalle.nombre_producto}</span>
                                    <div class="producto-detalles">
                                        <span>Cantidad: ${detalle.cantidad}</span>
                                        <span>Precio: $${detalle.valor_unitario}</span>
                                        <span>Total: $${detalle.subtotal}</span>
                                    </div>
                                </div>
                            `;
                            });

                            detallesHtml += `
                            <div class="producto-item total-compra">
                                <span class="producto-nombre">Total Compra:</span>
                                <div class="producto-detalles">
                                    <span></span>
                                    <span></span>
                                    <span>$${response.total_compra}</span>
                                </div>
                            </div>
                        `;

                            document.getElementById("detalles-pedido").innerHTML = detallesHtml;
                            document.getElementById("cliente-nombre").innerText = cliente.nombre || 'No disponible';
                            document.getElementById("cliente-direccion").innerText = cliente.direccion || 'No disponible';
                            document.getElementById("cliente-barrio").innerText = cliente.barrio || 'No disponible';
                            document.getElementById("cliente-telefono").innerText = cliente.telefono || 'No disponible';

                            var modalDetallesPedido = document.getElementById("modalDetallesPedido");
                            modalDetallesPedido.style.display = "block";
                            modalDetallesPedido.classList.add('show');
                            modalDetallesPedido.querySelector('.modal-content').classList.add('show');
                        } else {
                            console.error("Error del servidor:", response.message);
                            alert("Error al obtener detalles del pedido: " + response.message);
                        }
                    } catch (e) {
                        console.error("Error al parsear JSON:", xhr.responseText);
                        alert("Error inesperado al obtener detalles del pedido");
                    }
                }
            };
            xhr.open("GET", "../controller/obtener_detalles_pedido.php?idPedido=" + idPedido, true);
            xhr.send();
        }

        function abrirModalAgregarPedido() {
            document.getElementById("modalAgregarPedido").style.display = "block";
        }

        function cerrarModalAgregarPedido() {
            document.getElementById("modalAgregarPedido").style.display = "none";
        }

        document.getElementById("formAgregarPedido").onsubmit = function(event) {
            event.preventDefault();
            var formData = new FormData(this);

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alertify.success(response.message);
                                cerrarModalAgregarPedido();
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                alertify.error(response.message);
                            }
                        } catch (e) {
                            console.error("Error al analizar la respuesta JSON:", e);
                            alertify.error("Error inesperado al procesar la respuesta del servidor");
                        }
                    } else {
                        console.error("Error HTTP:", xhr.status);
                        alertify.error("Error de conexión al crear el pedido");
                    }
                }
            };

            xhr.open("POST", "../controller/pedidos_controller.php", true);
            xhr.send(formData);
        };

        function closeDetailsModal() {
            var modalDetallesPedido = document.getElementById("modalDetallesPedido");
            modalDetallesPedido.querySelector('.modal-content').classList.remove('show');
            setTimeout(function() {
                modalDetallesPedido.style.display = "none";
            }, 300);
        }

        function abrirModalEstado(idPedido, estadoPedido) {
            document.getElementById("estadoPedidoId").value = idPedido;
            document.getElementById("estado_pedido").value = estadoPedido;
            var modalEstadoPedido = document.getElementById("modalEstadoPedido");
            modalEstadoPedido.style.display = "block";
            modalEstadoPedido.querySelector('.modal-content').classList.add('show');
            modalEstadoPedido.classList.add('show');
        }

        function closeEstadoModal() {
            var modalEstadoPedido = document.getElementById("modalEstadoPedido");
            modalEstadoPedido.querySelector('.modal-content').classList.remove('show');
            setTimeout(function() {
                modalEstadoPedido.style.display = "none";
            }, 300);
        }

        document.getElementById("formEstadoPedido").onsubmit = function(event) {
            event.preventDefault();
            var idPedido = document.getElementById("estadoPedidoId").value;
            var estadoPedido = document.getElementById("estado_pedido").value;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    console.log("Respuesta del servidor:", xhr.responseText); // Agregar este log
                    if (xhr.status == 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alertify.success(response.message || "Pedido actualizado correctamente");
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                alertify.error(response.message || "Error al procesar el pedido");
                            }
                        } catch (e) {
                            console.error("Error al analizar la respuesta JSON:", e);
                            console.error("Respuesta recibida:", xhr.responseText);
                            alertify.error("Error inesperado en el servidor");
                        }
                    } else {
                        console.error("Error HTTP:", xhr.status);
                        alertify.error("Error de conexión al actualizar el pedido");
                    }
                }
            };

            xhr.open("POST", "../controller/pedidos_controller.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("pedido_id=" + encodeURIComponent(idPedido) + "&nuevo_estado=" + encodeURIComponent(estadoPedido));
        };

        function toggleUserOptions() {
            var userOptionsContainer = document.getElementById("userOptionsContainer");
            if (userOptionsContainer.style.display === "none" || userOptionsContainer.style.display === "") {
                userOptionsContainer.style.display = "block";
            } else {
                userOptionsContainer.style.display = "none";
            }
        }

        window.onclick = function(event) {
            var modalDetallesPedido = document.getElementById("modalDetallesPedido");
            if (event.target == modalDetallesPedido) {
                closeDetailsModal();
            }
            var modalEstadoPedido = document.getElementById("modalEstadoPedido");
            if (event.target == modalEstadoPedido) {
                closeEstadoModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var modalDetallesPedido = document.getElementById("modalDetallesPedido");
            if (modalDetallesPedido) {
                modalDetallesPedido.style.display = "none";
            }
        });
    </script>
</body>

</html>