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
                <!-- En el formulario de agregar insumo -->
                <form id="formAgregarInsumo" action="../controller/insumos_controller.php" method="post">
                    <label for="id_compra">Compra:</label>
                    <select id="id_compra" name="id_compra" required onchange="llenarDatosCompra()">
                        <option value="">Seleccione una compra</option>
                        <?php
                        $consulta_compras = "SELECT c.id_compra, c.fecha_compra, c.marca, c.cantidad, c.total_compra, p.nombre_proveedor 
                             FROM compras c 
                             JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                             ORDER BY c.fecha_compra DESC";
                        $resultado_compras = $conn->query($consulta_compras);
                        if ($resultado_compras->num_rows > 0) {
                            while ($row = $resultado_compras->fetch_assoc()) {
                                echo "<option value='" . $row['id_compra'] . "' 
                             data-marca='" . htmlspecialchars($row['marca'], ENT_QUOTES) . "' 
                             data-cantidad='" . $row['cantidad'] . "' 
                             data-proveedor='" . htmlspecialchars($row['nombre_proveedor'], ENT_QUOTES) . "'
                             data-fecha-compra='" . $row['fecha_compra'] . "'
                             data-total-compra='" . $row['total_compra'] . "'>"
                                    . $row['id_compra'] . " - " . $row['fecha_compra'] . " - " . $row['marca'] . "</option>";
                            }
                        }
                        ?>
                    </select><br><br>

                    <label for="proveedor">Proveedor:</label>
                    <input type="text" id="proveedor" name="proveedor" readonly><br><br>

                    <label for="marca">Marca (Nombre del Insumo):</label>
                    <input type="text" id="marca" name="marca" readonly><br><br>

                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" readonly><br><br>

                    <label for="fecha_compra">Fecha de Compra:</label>
                    <input type="date" id="fecha_compra" name="fecha_compra" readonly><br><br>

                    <label for="total_compra">Total de Compra:</label>
                    <input type="number" id="total_compra" name="total_compra" step="0.01" readonly><br><br>

                    <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required><br><br>

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
                        <th>Insumo (Marca)</th>
                        <th>Fecha de Compra</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Cantidad</th>
                        <th>Precio Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (isset($insumos) && is_array($insumos)) {
                        foreach ($insumos as $insumo) {
                            echo "<tr>";
                            echo "<td>" . $insumo['nombre_proveedor'] . "</td>";
                            echo "<td>" . $insumo['marca'] . "</td>";
                            echo "<td>" . $insumo['fecha_compra'] . "</td>";
                            echo "<td>" . $insumo['fecha_vencimiento'] . "</td>";
                            echo "<td>" . $insumo['cantidad'] . "</td>";
                            echo "<td>" . $insumo['total_compra'] . "</td>";
                            echo "<td>" . $insumo['estado_insumo'] . "</td>";
                            echo '<td class="actions">';
                            echo '<button class="edit-btn" onclick="openEditModal(' . htmlspecialchars(json_encode($insumo), ENT_QUOTES, 'UTF-8') . ')"><i class="fa fa-edit"></i></button>';
                            echo '<button class="delete-btn" onclick="confirmarEliminacion(' . $insumo['id_insumo'] . ')"><i class="fa fa-trash"></i></button>';
                            echo '</td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No hay insumos disponibles.</td></tr>";
                    }
                    ?>
                </tbody>
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
                <label for="edit-proveedor">Proveedor:</label>
                <input type="text" id="edit-proveedor" name="proveedor_editar" readonly><br><br>
                <label for="edit-marca">Insumo (Marca):</label>
                <input type="text" id="edit-marca" name="marca_editar" readonly><br><br>
                <label for="edit-fecha-compra">Fecha de Compra:</label>
                <input type="date" id="edit-fecha-compra" name="fecha_compra_editar" readonly><br><br>
                <label for="edit-fecha">Fecha de Vencimiento:</label>
                <input type="date" id="edit-fecha" name="fecha_vencimiento_editar" required><br><br>
                <label for="edit-cantidad">Cantidad:</label>
                <input type="number" id="edit-cantidad" name="cantidad_editar" readonly><br><br>
                <label for="edit-precio">Precio Total:</label>
                <input type="number" id="edit-precio" name="precio_editar" readonly step="0.01"><br><br>
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
            document.getElementById("edit-proveedor").value = insumo.nombre_proveedor;
            document.getElementById("edit-marca").value = insumo.marca;
            document.getElementById("edit-fecha-compra").value = insumo.fecha_compra;
            document.getElementById("edit-fecha").value = insumo.fecha_vencimiento;
            document.getElementById("edit-cantidad").value = insumo.cantidad;
            document.getElementById("edit-precio").value = insumo.total_compra;
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
                    // Redirigimos a la URL del controlador
                    window.location.href = '../controller/insumos_controller.php?eliminar=' + idInsumo;
                }
            });
        }

        // Manejo de alertas basado en los parámetros de la URL
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('success')) {
                const successCode = urlParams.get('success');
                let message = '';
                if (successCode === '1') {
                    message = 'Insumo agregado exitosamente.';
                } else if (successCode === '2') {
                    message = 'Insumo actualizado exitosamente.';
                }
                if (message) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: message,
                        confirmButtonText: 'OK'
                    });
                }
            } else if (urlParams.has('error')) {
                const errorMessage = urlParams.get('error');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: decodeURIComponent(errorMessage),
                    confirmButtonText: 'OK'
                });
            }
        });


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

        function actualizarCantidad() {
            var selectCompra = document.getElementById('id_compra');
            var inputCantidad = document.getElementById('cantidad');
            var cantidadSeleccionada = selectCompra.options[selectCompra.selectedIndex].getAttribute('data-cantidad');
            inputCantidad.value = cantidadSeleccionada;
        }

        function llenarDatosCompra() {
            var select = document.getElementById('id_compra');
            var option = select.options[select.selectedIndex];

            if (option.value !== "") {
                document.getElementById('proveedor').value = option.getAttribute('data-proveedor');
                document.getElementById('marca').value = option.getAttribute('data-marca');
                document.getElementById('cantidad').value = option.getAttribute('data-cantidad');
                document.getElementById('fecha_compra').value = option.getAttribute('data-fecha-compra');
                document.getElementById('total_compra').value = option.getAttribute('data-total-compra');

                // Establecer la fecha de vencimiento a un año después de la fecha de compra
                var fechaCompra = new Date(option.getAttribute('data-fecha-compra'));
                var fechaVencimiento = new Date(fechaCompra.getFullYear() + 1, fechaCompra.getMonth(), fechaCompra.getDate());
                document.getElementById('fecha_vencimiento').value = fechaVencimiento.toISOString().split('T')[0];
            } else {
                // Limpiar los campos si no se selecciona ninguna compra
                document.getElementById('proveedor').value = '';
                document.getElementById('marca').value = '';
                document.getElementById('cantidad').value = '';
                document.getElementById('fecha_compra').value = '';
                document.getElementById('total_compra').value = '';
                document.getElementById('fecha_vencimiento').value = '';
            }
        }
    </script>
    <script src="../js/validaciones.js"></script>
</body>

</html>