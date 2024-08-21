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

include_once('../controller/insumos_controller.php');

$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

if (isset($_POST['buscar_nombre'])) {
    $nombre = $_POST['nombre_insumo_buscar'];
    $insumos = buscarInsumosPorNombre($nombre);
} else {
    $insumos = obtenerInsumos();
}

$total_insumos = count($insumos);
$total_pag = ceil($total_insumos / $items_por_pagina);

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/insumos15.css">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <div class="head-section">
                <div class="title-container">
                    <h1>Insumos</h1>
                    <div class="search-bar">
                        <input type="text" id="searchInsumos" placeholder="Buscar..." onkeyup="buscarInsumo()" />
                        <button type="button" onclick="buscarInsumo()"><i class="fa fa-search"></i></button>
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
                <!-- <form method="post" action="" class="search-form">
                    <input type="text" name="nombre_insumo_buscar" placeholder="Buscar por nombre">
                    <button type="submit" name="buscar_nombre"><i class="fa fa-search"></i></button>
                </form> -->
                <br>

                <button id="btnAgregarInsumo" class="btn btn-success">Agregar Insumo</button>
                <br><br>

                <div id="modalAgregarInsumo" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Agregar Nuevo Insumo</h2>
                        <form id="formAgregarInsumo" action="../controller/insumos_controller.php" method="post">
                            <label for="nombre_insumo">Nombre del Insumo:</label>
                            <input type="text" id="nombre_insumo" name="nombre_insumo" required><br><br>
                            <label for="id_proveedor">Proveedor:</label>
                            <select id="id_proveedor" name="id_proveedor" required>
                                <?php
                                include_once('../includes/conexion.php');
                                $consulta_proveedores = "SELECT * FROM proveedores";
                                $resultado_proveedores = $conn->query($consulta_proveedores);
                                if ($resultado_proveedores->num_rows > 0) {
                                    while ($row = $resultado_proveedores->fetch_assoc()) {
                                        echo "<option value='" . $row['id_proveedor'] . "'>" . $row['nombre_proveedor'] . "</option>";
                                    }
                                }
                                ?>
                            </select><br><br>
                            <label for="precio">Precio:</label>
                            <input type="number" id="precio" name="precio" required><br><br>
                            <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
                            <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required min="<?php echo date('Y-m-d'); ?>"><br><br>
                            <label for="marca">Marca:</label>
                            <input type="text" id="marca" name="marca" required><br><br>
                            <label for="cantidad">Cantidad:</label>
                            <input type="number" id="cantidad" name="cantidad" required><br><br>
                            <label for="estado_insumo">Estado del Insumo:</label>
                            <select id="estado_insumo" name="estado_insumo" required>
                                <option value="Buen Estado">Buen Estado</option>
                                <option value="Mal Estado">Mal Estado</option>
                            </select><br><br>
                            <input type="submit" value="Agregar Insumo">
                        </form>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Fecha de Vencimiento</th>
                                <th>Marca</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($insumos) && is_array($insumos)) {
                                foreach ($insumos as $insumo) {
                                    echo "<tr>";
                                    echo "<td>" . $insumo['nombre_proveedor'] . "</td>"; // Cambiado de id_proveedor a nombre_proveedor
                                    echo "<td>" . $insumo['nombre_insumo'] . "</td>";
                                    echo "<td>" . $insumo['precio'] . "</td>";
                                    echo "<td>" . $insumo['fecha_vencimiento'] . "</td>";
                                    echo "<td>" . $insumo['marca'] . "</td>";
                                    echo "<td>" . $insumo['cantidad'] . "</td>";
                                    echo "<td>" . $insumo['estado_insumo'] . "</td>";
                                    echo '<td class="actions">';
                                    echo '<button class="edit-btn" onclick="openEditModal(' . htmlspecialchars(json_encode($insumo), ENT_QUOTES, 'UTF-8') . ')"><i class="fa fa-edit"></i></button>';
                                    echo '<button class="delete-btn" onclick="confirmarEliminacion(' . $insumo['id_insumo'] . ')"><i class="fa fa-trash"></i></button>';
                                    echo '</td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9'>No hay insumos disponibles.</td></tr>";
                            }
                            ?>
                    </table>
                    <div class="pagination">
                        <?php
                        if ($total_pag > 0) {
                            for ($i = 1; $i <= $total_pag; $i++) {
                                if ($i == $pagina_actual) {
                                    echo "<a href='insumos.php?pagina=$i' class='active'>$i</a>";
                                } else {
                                    echo "<a href='insumos.php?pagina=$i'>$i</a>";
                                }
                            }
                        }
                        ?>
                    </div>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="modalEditarInsumo" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Insumo</h2>
            <form id="formEditarInsumo" action="../controller/insumos_controller.php" method="post">
                <input type="hidden" id="edit-id" name="id_editar">
                <label for="edit-nombre">Nombre del Insumo:</label>
                <input type="text" id="edit-nombre" name="nombre_editar" required><br><br>
                <label for="edit-proveedor">Proveedor:</label>
                <select id="edit-proveedor" name="id_proveedor_editar" required>
                    <?php
                    $consulta_proveedores = "SELECT * FROM proveedores";
                    $resultado_proveedores = $conn->query($consulta_proveedores);
                    if ($resultado_proveedores->num_rows > 0) {
                        while ($row = $resultado_proveedores->fetch_assoc()) {
                            echo "<option value='" . $row['id_proveedor'] . "'>" . $row['nombre_proveedor'] . "</option>";
                        }
                    }
                    ?>
                </select><br><br>
                <label for="edit-precio">Precio:</label>
                <input type="number" id="edit-precio" name="precio_editar" required><br><br>
                <label for="edit-fecha">Fecha de Vencimiento:</label>
                <input type="date" id="edit-fecha" name="fecha_vencimiento_editar" required><br><br>
                <label for="edit-marca">Marca:</label>
                <input type="text" id="edit-marca" name="marca_editar" required><br><br>
                <label for="edit-cantidad">Cantidad:</label>
                <input type="number" id="edit-cantidad" name="cantidad_editar" required><br><br>
                <label for="edit-estado">Estado del Insumo:</label>
                <select id="edit-estado" name="estado_insumo_editar" required>
                    <option value="Buen Estado">Buen Estado</option>
                    <option value="Mal Estado">Mal Estado</option>
                </select><br><br>
                <input type="submit" value="Guardar Cambios">
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalAgregarInsumo = document.getElementById('modalAgregarInsumo');
            var modalEditarInsumo = document.getElementById('modalEditarInsumo');
            var btnAgregarInsumo = document.getElementById('btnAgregarInsumo');
            var spanAgregar = modalAgregarInsumo.getElementsByClassName('close')[0];
            var spanEditar = modalEditarInsumo.getElementsByClassName('close')[0];

            btnAgregarInsumo.onclick = function() {
                modalAgregarInsumo.style.display = 'block';
            }

            spanAgregar.onclick = function() {
                modalAgregarInsumo.style.display = 'none';
            }

            spanEditar.onclick = function() {
                modalEditarInsumo.style.display = 'none';
            }

            window.onclick = function(event) {
                if (event.target == modalAgregarInsumo) {
                    modalAgregarInsumo.style.display = 'none';
                } else if (event.target == modalEditarInsumo) {
                    modalEditarInsumo.style.display = 'none';
                }
            }

            document.getElementById('formAgregarInsumo').addEventListener('submit', function(event) {
                event.preventDefault();

                var nombreInsumo = document.getElementById('nombre_insumo').value;
                var precio = document.getElementById('precio').value;
                var fechaVencimiento = document.getElementById('fecha_vencimiento').value;
                var marca = document.getElementById('marca').value;
                var cantidad = document.getElementById('cantidad').value;
                var estadoInsumo = document.getElementById('estado_insumo').value;

                if (nombreInsumo && precio && fechaVencimiento && marca && cantidad && estadoInsumo) {
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¿Deseas agregar este insumo?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, agregarlo!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Insumo agregado',
                                text: 'El insumo se ha agregado exitosamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                event.target.submit();
                            });
                        }
                    });
                }
            });

            document.getElementById('formEditarInsumo').addEventListener('submit', function(event) {
                event.preventDefault();

                var nombreInsumo = document.getElementById('edit-nombre').value;
                var precio = document.getElementById('edit-precio').value;
                var fechaVencimiento = document.getElementById('edit-fecha').value;
                var marca = document.getElementById('edit-marca').value;
                var cantidad = document.getElementById('edit-cantidad').value;
                var estadoInsumo = document.getElementById('edit-estado').value;

                if (nombreInsumo && precio && fechaVencimiento && marca && cantidad && estadoInsumo) {
                    Swal.fire({
                        title: '¿Confirmar edición?',
                        text: "¿Estás seguro de que deseas guardar los cambios?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, guardar cambios',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Insumo editado',
                                text: 'El insumo se ha editado exitosamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                event.target.submit();
                            });
                        }
                    });
                }
            });
        });

        function openEditModal(insumo) {
            var modalEditar = document.getElementById("modalEditarInsumo");
            document.getElementById("edit-id").value = insumo.id_insumo;
            document.getElementById("edit-nombre").value = insumo.nombre_insumo;
            document.getElementById("edit-proveedor").value = insumo.id_proveedor;
            document.getElementById("edit-precio").value = insumo.precio;
            document.getElementById("edit-fecha").value = insumo.fecha_vencimiento;
            document.getElementById("edit-marca").value = insumo.marca;
            document.getElementById("edit-cantidad").value = insumo.cantidad;
            document.getElementById("edit-estado").value = insumo.estado_insumo;

            modalEditar.style.display = "block";
        }

        function confirmarEliminacion(idInsumo) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminado!',
                        text: 'El insumo ha sido eliminado.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '../controller/insumos_controller.php?eliminar=' + idInsumo;
                    });
                }
            });
        }

        function buscarInsumo() {
            const input = document.getElementById('searchInsumos').value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr'); // Asegúrate de que 'tbody tr' selecciona las filas correctas

            tableRows.forEach(row => {
                // Concatenar todo el texto de la fila para buscar en todos los campos
                const rowText = Array.from(row.getElementsByTagName('td'))
                    .map(td => td.textContent.toLowerCase())
                    .join(' ');

                // Verificar si el texto de búsqueda está en alguna parte de la fila
                if (rowText.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script src="../js/validaciones.js"></script>
</body>

</html>