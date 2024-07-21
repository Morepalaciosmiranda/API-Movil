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

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
echo "Conexión exitosa a la base de datos.<br>";

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

echo "SQL Query: " . $sql . "<br>"; // Depuración: Imprimir la consulta SQL

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}
echo "Número de filas devueltas: " . mysqli_num_rows($result) . "<br>";

// Depuración: Imprimir los resultados
echo "Resultados de la consulta:<br>";
while ($row = mysqli_fetch_assoc($result)) {
    print_r($row);
    echo "<br>";
}
mysqli_data_seek($result, 0); // Resetear el puntero del resultado

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
                    if (mysqli_num_rows($result) > 0) {
                        mysqli_data_seek($result, 0); // Asegurarse de que el puntero del resultado esté al inicio
                        while ($row = mysqli_fetch_assoc($result)) {
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

    <script>
        function verDetallesPedido(idPedido) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Verifica que los datos del cliente estén presentes en la respuesta
                        var cliente = response.cliente;
                        if (cliente) {
                            document.getElementById("detalles-pedido").innerHTML = response.detalles;
                            document.getElementById("cliente-nombre").innerText = cliente.nombre || 'Nombre no disponible';
                            document.getElementById("cliente-direccion").innerText = cliente.direccion || 'Dirección no disponible';
                            document.getElementById("cliente-barrio").innerText = cliente.barrio || 'Barrio no disponible';
                            document.getElementById("cliente-telefono").innerText = cliente.telefono || 'Teléfono no disponible';
                        } else {
                            document.getElementById("detalles-pedido").innerText = 'Datos del cliente no disponibles';
                        }

                        var modalDetallesPedido = document.getElementById("modalDetallesPedido");
                        modalDetallesPedido.style.display = "block";
                        modalDetallesPedido.classList.add('show');
                        modalDetallesPedido.querySelector('.modal-content').classList.add('show');
                    } else {
                        document.getElementById("detalles-pedido").innerText = response.message || 'Error al obtener detalles';
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
    </script>
    </div>
    </div>
</body>

</html>