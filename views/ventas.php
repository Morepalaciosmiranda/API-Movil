<?php
include_once "../includes/conexion.php";

session_start();

if (!isset($_SESSION['correo_electronico'])) {
    header("Location: ../loginRegister.php");
    exit();
}

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['rol'])) {
    header('Location: ../loginRegister.php');
    exit();
}

if ($_SESSION['rol'] !== 'Administrador') {
    header('Location: ../no_autorizado.php');
    exit();
}

$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : '';

$sql = "SELECT ventas.*, usuarios.nombre_usuario AS nombre_usuario, productos.nombre_producto AS nombre_producto
        FROM ventas
        JOIN usuarios ON ventas.id_usuario = usuarios.id_usuario
        JOIN detalle_venta ON ventas.id_venta = detalle_venta.id_venta
        JOIN productos ON detalle_venta.id_producto = productos.id_producto";

if ($fecha_filtro) {
    $sql .= " WHERE DATE(ventas.fecha_venta) = ?";
}

$sql .= " ORDER BY ventas.fecha_venta DESC"; 

$sql .= " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if ($fecha_filtro) {
    $stmt->bind_param('sii', $fecha_filtro, $items_por_pagina, $offset);
} else {
    $stmt->bind_param('ii', $items_por_pagina, $offset);
}

$stmt->execute();
$resultado = $stmt->get_result();

// Consulta para contar el total de ventas
$sql_total = "SELECT COUNT(*) as total FROM ventas";
if ($fecha_filtro) {
    $sql_total .= " WHERE DATE(fecha_venta) = ?";
}
$stmt_total = $conn->prepare($sql_total);

if ($fecha_filtro) {
    $stmt_total->bind_param('s', $fecha_filtro);
}

$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_ventas = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_ventas / $items_por_pagina);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="./css/ventas11.css">
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <div class="head-section">
                <div class="title-container">
                    <h1>Ventas</h1>
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
            <br><br>
            <div class="content">
                <div class="form-container">
                    <form method="GET" action="ventas.php">
                        <label for="fecha">Filtrar por fecha:</label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo $fecha_filtro; ?>">
                        <button type="submit">Filtrar</button>
                    </form>
                </div>
                        <br>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre Usuario</th>
                                        <th>Nombre Producto</th>
                                        <th># De Venta</th>
                                        <th>Fecha Venta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="ventasTableBody">
                                    <?php
                                    if ($resultado && $resultado->num_rows > 0) {
                                        while ($fila = $resultado->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($fila['nombre_usuario']) . "</td>";
                                            echo "<td>" . htmlspecialchars($fila['nombre_producto']) . "</td>";
                                            echo "<td>" . htmlspecialchars($fila['id_pedido']) . "</td>";
                                            echo "<td>" . htmlspecialchars($fila['fecha_venta']) . "</td>";
                                            echo "<td class='actions'>";
                                            echo "<button class='details-btn' onclick='verDetallesVenta(" . htmlspecialchars($fila['id_venta']) . ")'><i class='fa fa-info-circle'></i></button>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5'>No hay ventas registradas</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination">
                            <?php
                            if ($total_paginas > 0) {
                                for ($i = 1; $i <= $total_paginas; $i++) {
                                    if ($i == $pagina_actual) {
                                        echo "<a href='ventas.php?pagina=$i&fecha=$fecha_filtro' class='active'>$i</a>";
                                    } else {
                                        echo "<a href='ventas.php?pagina=$i&fecha=$fecha_filtro'>$i</a>";
                                    }
                                }
                            }
                            ?>
                        </div>

                        <div id="modalDetallesVenta" class="modal">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2></h2>
                                    <button class="close-btn" onclick="cerrarModalDetallesVenta()">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div id="detalles-venta"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verDetallesVenta(idVenta) {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        var detallesHtml = '<h2>Datos del Cliente</h2>';
                        detallesHtml += '<div><strong>Nombre Usuario:</strong> ' + response.usuario.nombre_usuario + '<br>';
                        detallesHtml += '<strong>Correo Electrónico:</strong> ' + response.usuario.correo_electronico + '<br><br></div>';

                        detallesHtml += '<h2>Detalles de la Venta</h2>';
                        response.detalles.forEach(function(detalle) {
                            detallesHtml += '<div class="detail-item">';
                            detallesHtml += '<strong>ID Detalle Venta:</strong> ' + detalle.id_detalle_venta + '<br>';
                            detallesHtml += '<strong>Nombre Producto:</strong> ' + detalle.nombre_producto + '<br>';
                            detallesHtml += '<strong>Cantidad:</strong> ' + detalle.cantidad + '<br>';
                            detallesHtml += '<strong>Valor Unitario:</strong> ' + detalle.valor_unitario + '<br>';
                            detallesHtml += '<strong>Total Venta:</strong> ' + detalle.total_venta + '<br>';
                            detallesHtml += '</div>';
                        });
                        document.getElementById("detalles-venta").innerHTML = detallesHtml;
                        var modalDetallesVenta = document.getElementById("modalDetallesVenta");
                        modalDetallesVenta.style.display = "block";
                        modalDetallesVenta.classList.add('show');
                        modalDetallesVenta.querySelector('.modal-content').classList.add('show');
                    } else {
                        document.getElementById("detalles-venta").innerText = response.message;
                    }
                }
            };
            xhr.open("GET", "../controller/obtener_detalles_venta.php?id_venta=" + idVenta, true);
            xhr.send();
        }

        function cerrarModalDetallesVenta() {
            var modalDetallesVenta = document.getElementById("modalDetallesVenta");
            modalDetallesVenta.style.display = "none";
        }

        window.onclick = function(event) {
            var modalDetallesVenta = document.getElementById("modalDetallesVenta");
            if (event.target == modalDetallesVenta) {
                cerrarModalDetallesVenta();
            }
        }
    </script>
</body>

</html>
