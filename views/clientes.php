<?php
include_once "../includes/conexion.php";
include_once "../includes/functions.php";

session_start();

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ../loginRegister.php");
    exit();
}

// Verificar si el usuario tiene el permiso para acceder a esta página
if (!tienePermiso($_SESSION['id_usuario'], 'ver_ventas')) {
    header('Location: ../no_autorizado.php');
    exit();
}

$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

include_once('../controller/clientes_controller.php');

$clientes = obtenerClientes($offset, $items_por_pagina);
$total_clientes = obtenerTotalClientes();
$total_paginas = ceil($total_clientes / $items_por_pagina);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/clientes3.css">
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
                <h1>Clientes</h1>
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
            <div class="content">
                <br><br>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo Electrónico</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($clientes) && is_array($clientes)) {
                                foreach ($clientes as $cliente) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($cliente['nombre_cliente']) . "</td>";
                                    echo "<td>" . htmlspecialchars($cliente['correo_electronico']) . "</td>";
                                    echo "<td>" . htmlspecialchars($cliente['estado_cliente']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No hay clientes disponibles.</td></tr>";
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
                                echo "<a href='clientes.php?pagina=$i' class='active'>$i</a>";
                            } else {
                                echo "<a href='clientes.php?pagina=$i'>$i</a>";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
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
