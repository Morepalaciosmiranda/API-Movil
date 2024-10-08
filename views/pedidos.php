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
    <link rel="stylesheet" href="./css/pedidos.css">
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
                        <div class="search-bar">
                            <input type="text" id="search" placeholder="Buscar..." onkeyup="buscarPedido()" />
                            <button type="button" onclick="buscarPedido()"><i class="fa fa-search"></i></button>
                        </div>

                    </div>

                </div>
                <div class="profile-div">
                    <div class="profile-inner-container">
                        <p class="user1" onclick="toggleUserOptions()">
                            <i class="fa fa-user"></i>
                            <?php echo isset($_SESSION['correo_electronico']) ? $_SESSION['correo_electronico'] : ''; ?>
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
                <div class="form-container">
                    <form method="GET" action="pedidos.php">
                        <label for="fecha">Filtrar por fecha:</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo $fecha_filtro; ?>">
                        <button type="submit">Filtrar</button>
                    </form>
                </div>
                <table class="content">
                    <button id="agregarPedido" class="add-pedido-btn" onclick="abrirModalNuevoPedido()">Agregar Pedido</button>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Nombre Usuario</th>
                        <th>Fecha de Pedido</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    <tbody id="pedidoTableBody">
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
                    </tbody>
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
    
    <div id="modalNuevoPedido" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeNuevoPedidoModal()">&times;</span>
            <h2>Nuevo Pedido</h2>
            <form id="formNuevoPedido">
                <div class="form-group">
                    <label for="nombreCliente">Nombre del Cliente:</label>
                    <input type="text" id="nombreCliente" name="nombreCliente" required>
                </div>
                <div class="form-group">
                    <label for="calle">Dirección:</label>
                    <input type="text" id="calle" name="calle" required>
                </div>
                <div class="form-group">
                    <label for="interior">Interior:</label>
                    <input type="text" id="interior" name="interior" required>
                </div>
                <div class="form-group">
                    <label for="barrio_cliente">Barrio:</label>
                    <input type="text" id="barrio_cliente" name="barrio_cliente" list="barrios" required>
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
                </div>
                <div class="form-group">
                    <label for="telefono_cliente">Teléfono:</label>
                    <input type="text" id="telefono_cliente" name="telefono_cliente" required>
                </div>
                <div id="productos-container">
                    <div class="producto-item">
                        <select name="producto[]" required>
                            <option value="">Selecciona un producto</option>
                            <?php
                            $sql_productos = "SELECT id_producto, nombre_producto FROM productos";
                            $result_productos = mysqli_query($conn, $sql_productos);
                            while ($row_producto = mysqli_fetch_assoc($result_productos)) {
                                echo "<option value='" . $row_producto['id_producto'] . "'>" . $row_producto['nombre_producto'] . "</option>";
                            }
                            ?>
                        </select>
                        <input type="number" name="cantidad[]" min="1" value="1" required>
                    </div>
                </div>
                <button type="button" onclick="agregarProducto()">Agregar Producto</button>
                <button type="button" onclick="quitarProducto()">Quitar Producto</button>
                <button type="submit" class="btnGuardar">Guardar</button>
            </form>
        </div>
    </div>

    <div id="modalDetallesPedido" class="modal" style="display: none;">
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
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
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
                                document.getElementById("cliente-direccion").innerText = cliente.direccion ||
                                    'No disponible';
                                document.getElementById("cliente-barrio").innerText = cliente.barrio || 'No disponible';
                                document.getElementById("cliente-telefono").innerText = cliente.telefono ||
                                    'No disponible';

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

        function abrirModalNuevoPedido() {
            var modalNuevoPedido = document.getElementById("modalNuevoPedido");
            modalNuevoPedido.style.display = "block";
            modalNuevoPedido.querySelector('.modal-content').classList.add('show');
            modalNuevoPedido.classList.add('show');
        }

        function closeNuevoPedidoModal() {
            var modalNuevoPedido = document.getElementById("modalNuevoPedido");
            modalNuevoPedido.querySelector('.modal-content').classList.remove('show');
            setTimeout(function() {
                modalNuevoPedido.style.display = "none";
            }, 300);
        }

        function agregarProducto() {
            var container = document.getElementById('productos-container');
            var nuevoProducto = document.createElement('div');
            nuevoProducto.className = 'producto-item';
            nuevoProducto.innerHTML = `
        <select name="producto[]" required>
            <option value="">Selecciona un producto</option>
            <?php
            $sql_productos = "SELECT id_producto, nombre_producto FROM productos";
            $result_productos = mysqli_query($conn, $sql_productos);
            while ($row_producto = mysqli_fetch_assoc($result_productos)) {
                echo "<option value='" . $row_producto['id_producto'] . "'>" . $row_producto['nombre_producto'] . "</option>";
            }
            ?>
        </select>
        <input type="number" name="cantidad[]" min="1" value="1" required>
    `;
            container.appendChild(nuevoProducto);
        }

        function quitarProducto() {
            var container = document.getElementById('productos-container');
            if (container.children.length > 1) {
                container.removeChild(container.lastChild);
            }
        }

        document.getElementById("formNuevoPedido").onsubmit = function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alertify.success(response.message || "Pedido creado correctamente");
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            alertify.error(response.message || "Error al crear el pedido");
                        }
                    } catch (e) {
                        console.error("Error al analizar la respuesta JSON:", e);
                        console.error("Respuesta recibida:", xhr.responseText);
                        alertify.error("Error inesperado en el servidor: " + xhr.responseText);
                    }
                }
            };

            xhr.open("POST", "../controller/pedidos_controller.php", true);
            xhr.send(formData);
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

        function buscarPedido() {
            var searchInput = document.getElementById('search').value.toLowerCase();
            var rows = document.querySelectorAll('#pedidoTableBody tr');
            rows.forEach(row => {
                var rowText = Array.from(row.getElementsByTagName('td'))
                    .map(td => td.textContent.toLowerCase())
                    .join(' ');
                if (rowText.includes(searchInput)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script src="../js/validaciones_2.js"></script>
</body>

</html>