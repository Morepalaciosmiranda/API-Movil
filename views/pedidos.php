<?php
session_start();

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ../loginRegister.php");
    exit();
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
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
                <button id="btnCrearPedido" class="btn-crear-pedido">Crear Pedido</button>
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
            <div id="detalles-pedido"></div>
            <h2>Datos del Cliente</h2>
            <ul>
                <li><strong>Nombre:</strong> <span id="cliente-nombre"></span></li>
                <li><strong>Dirección:</strong> <span id="cliente-direccion"></span></li>
                <li><strong>Barrio:</strong> <span id="cliente-barrio"></span></li>
                <li><strong>Teléfono:</strong> <span id="cliente-telefono"></span></li>
            </ul>
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


    <div id="modalCrearPedido" class="modal">
        <div class="modal-content modal-large">
            <span class="close" onclick="cerrarModalCrearPedido()">&times;</span>
            <h2>Crear Nuevo Pedido</h2>
            <form id="formCrearPedido">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_cliente">Nombre del Cliente:</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono_cliente">Teléfono:</label>
                        <input type="tel" id="telefono_cliente" name="telefono_cliente" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="calle">Calle:</label>
                        <input type="text" id="calle" name="calle" required>
                    </div>
                    <div class="form-group">
                        <label for="interior">Interior/Apartamento:</label>
                        <input type="text" id="interior" name="interior" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="barrio_cliente">Barrio:</label>
                    <input type="text" id="barrio_cliente" name="barrio_cliente" required>
                </div>
                <div class="form-group">
                    <label for="productos">Productos:</label>
                    <select id="productos" multiple>
                        <?php
                        $sql_productos = "SELECT id_producto, nombre_producto, precio FROM productos WHERE estado_producto = 'Activo'";
                        $result_productos = mysqli_query($conn, $sql_productos);
                        while ($row = mysqli_fetch_assoc($result_productos)) {
                            echo "<option value='" . $row['id_producto'] . "' data-precio='" . $row['precio'] . "'>" . $row['nombre_producto'] . " - $" . $row['precio'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div id="productosSeleccionados" class="productos-seleccionados"></div>
                <div class="total-pedido">
                    <strong>Total del Pedido: $<span id="totalPedido">0.00</span></strong>
                </div>
                <button type="submit" class="btn-guardar">Crear Pedido</button>
            </form>
        </div>
    </div>

    <script>
        function verDetallesPedido(idPedido) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                var cliente = response.cliente;
                                var detallesHtml = '<table><tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr>';
                                response.detalles.forEach(function(detalle) {
                                    detallesHtml += '<tr>' +
                                        '<td>' + detalle.nombre_producto + '</td>' +
                                        '<td>' + detalle.cantidad + '</td>' +
                                        '<td>$' + detalle.valor_unitario + '</td>' +
                                        '<td>$' + detalle.subtotal + '</td>' +
                                        '</tr>';
                                });
                                detallesHtml += '<tr><strong><td colspan="3">Total Compra:</td><td>$' + response.total_compra + '</td></strong></tr>';
                                detallesHtml += '</table>';
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
                    } else {
                        console.error("Error HTTP:", xhr.status);
                        alert("Error de conexión al obtener detalles del pedido");
                    }
                }
            };
            xhr.open("GET", "../controller/obtener_detalles_pedido.php?idPedido=" + idPedido, true);
            xhr.send();
        }

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
                if (xhr.readyState == 4 && xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alertify.success(response.message || "Pedido realizado correctamente");
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            alertify.error(response.message || "Error al procesar el pedido");
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        alertify.error("Error inesperado en el servidor");
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


        document.getElementById("btnCrearPedido").addEventListener("click", function() {
            var modal = document.getElementById("modalCrearPedido");
            modal.style.display = "block";
            setTimeout(function() {
                modal.classList.add('show');
                modal.querySelector('.modal-content').classList.add('show');
            }, 10);
        });

        function cerrarModalCrearPedido() {
            var modal = document.getElementById("modalCrearPedido");
            modal.querySelector('.modal-content').classList.remove('show');
            setTimeout(function() {
                modal.classList.remove('show');
                modal.style.display = "none";
            }, 300);
        }

        document.getElementById("productos").addEventListener("change", function() {
            var productosSeleccionados = document.getElementById("productosSeleccionados");
            productosSeleccionados.innerHTML = "";
            var total = 0;

            Array.from(this.selectedOptions).forEach(function(option) {
                var div = document.createElement("div");
                div.classList.add("producto-seleccionado");
                var precio = parseFloat(option.dataset.precio);
                total += precio;
                div.innerHTML = option.text +
                    ' <button type="button" class="btn-eliminar-producto" onclick="eliminarProducto(this, \'' + option.value + '\', ' + precio + ')">X</button>';
                productosSeleccionados.appendChild(div);
            });

            document.getElementById("totalPedido").textContent = total.toFixed(2);
        });

        function eliminarProducto(button, value, precio) {
            var select = document.getElementById("productos");
            var option = select.querySelector('option[value="' + value + '"]');
            option.selected = false;
            button.parentElement.remove();

            var totalElement = document.getElementById("totalPedido");
            var totalActual = parseFloat(totalElement.textContent);
            totalElement.textContent = (totalActual - precio).toFixed(2);
        }

        document.getElementById("formCrearPedido").addEventListener("submit", function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var productosSeleccionados = Array.from(document.getElementById("productos").selectedOptions).map(option => ({
                id: option.value,
                nombre: option.text
            }));
            formData.append("productos", JSON.stringify(productosSeleccionados));

            fetch("../controller/pedidos_controller.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alertify.success(data.message);
                        cerrarModalCrearPedido();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alertify.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertify.error("Error al procesar la solicitud");
                });
        });

        window.onclick = function(event) {
            var modalDetallesPedido = document.getElementById("modalDetallesPedido");
            var modalEstadoPedido = document.getElementById("modalEstadoPedido");
            var modalCrearPedido = document.getElementById("modalCrearPedido");

            if (event.target == modalDetallesPedido) {
                closeDetailsModal();
            }
            if (event.target == modalEstadoPedido) {
                closeEstadoModal();
            }
            if (event.target == modalCrearPedido) {
                cerrarModalCrearPedido();
            }
        }
    </script>
</body>

</html>