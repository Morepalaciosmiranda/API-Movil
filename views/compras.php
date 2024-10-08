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

// Parámetros de paginación
$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

// Verifica si se ha enviado el filtro de fecha
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Consulta SQL base para obtener las compras y su información relacionada
$sql = "SELECT c.id_compra, u.nombre_usuario, p.nombre_proveedor, c.nombre_insumos, c.fecha_compra, c.total_compra, c.cantidad
        FROM compras c
        JOIN usuarios u ON c.id_usuario = u.id_usuario
        JOIN proveedores p ON c.id_proveedor = p.id_proveedor
        ORDER BY c.fecha_compra DESC
        LIMIT $items_por_pagina OFFSET $offset";

// Capturar el valor de la fecha desde la solicitud GET
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : null;

// Consulta SQL inicial
$sql = "SELECT * FROM compras";

// Si se ha seleccionado una fecha, agrega la condición a la consulta
if (!empty($fecha_filtro)) {
    $sql .= " WHERE DATE(compras.fecha_compra) = '$fecha_filtro'";
}

// Agregar una cláusula ORDER BY antes del LIMIT
$sql .= " ORDER BY compras.fecha_compra DESC LIMIT $items_por_pagina OFFSET $offset";

// Ejecutar la consulta
$result = mysqli_query($conn, $sql);

// Verificar si la consulta fue exitosa
if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

// Consulta SQL para obtener el número total de compras (con o sin filtro)
$sql_total = "SELECT COUNT(*) as total FROM compras";
$result_total = $conn->query($sql_total);
$total_compras = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_compras / $items_por_pagina);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/compras14.css">
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
                    <h1>Compras</h1>
                    <form method="GET" action="compras.php">
                        <div class="search-bar">
                            <input type="text" id="searchCompras" placeholder="Buscar..." onkeyup="buscarCompra()" />
                            <button type="button" onclick="buscarCompra()">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </form>
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
                <br><br>
                <div class="content">
                    <div class="form-container">
                        <form method="GET" action="compras.php">
                            <label for="fecha">Filtrar por fecha:</label>
                            <input type="date" id="fecha" name="fecha" value="<?php echo $fecha_filtro; ?>">
                            <button type="submit">Filtrar</button>
                        </form>
                    </div>
                </div>

                <div class="content">
                    <button id="btnAgregarCompra" class="btn btn-success">Agregar Compra</button>
                    <br><br>

                    <div id="modalAgregarCompra" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Agregar Nueva Compra</h2>
                            <form id="formAgregarCompra" action="../controller/compras_controller.php" method="post">
                                <label for="id_proveedor">Proveedor:</label>
                                <select id="id_proveedor" name="id_proveedor" required></select><br><br>

                                <label for="nombre_insumos">Nombre de Insumos:</label>
                                <input type="text" id="nombre_insumos" name="nombre_insumos" required><br><br>

                                <label for="fecha_compra">Fecha de Compra:</label>
                                <input type="date" id="fecha_compra" name="fecha_compra" required><br><br>

                                <label for="total_compra">Total de Compra:</label>
                                <input type="number" id="total_compra" name="total_compra" step="0.01" required><br><br>

                                <label for="cantidad">Cantidad:</label>
                                <input type="number" id="cantidad" name="cantidad" required><br><br>

                                <input type="submit" value="Agregar Compra">
                            </form>
                        </div>
                    </div>
                    <div id="modalEditarCompra" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Editar Compra</h2>
                            <form id="formEditarCompra" action="../controller/compras_controller.php" method="post">
                                <input type="hidden" id="edit_id_compra" name="edit_id_compra">

                                <label for="edit_id_proveedor">Proveedor:</label>
                                <select id="edit_id_proveedor" name="edit_id_proveedor" required></select><br><br>

                                <label for="edit_nombre_insumos">Nombre de Insumos:</label>
                                <input type="text" id="edit_nombre_insumos" name="edit_nombre_insumos" required><br><br>

                                <label for="edit_fecha_compra">Fecha de Compra:</label>
                                <input type="date" id="edit_fecha_compra" name="edit_fecha_compra" required><br><br>

                                <label for="edit_total_compra">Total de Compra:</label>
                                <input type="number" id="edit_total_compra" name="edit_total_compra" step="0.01" required><br><br>

                                <label for="edit_cantidad">Cantidad:</label>
                                <input type="number" id="edit_cantidad" name="edit_cantidad" required><br><br>

                                <input type="submit" value="Guardar Cambios">
                            </form>
                        </div>
                    </div>

                    <div id="detalleCompraModal" class="modal">
                        <div class="modal-content">
                            <span class="close-btn" onclick="cerrarModalDetalle()">&times;</span>
                            <h2>Detalles de la Compra</h2>
                            <div id="modalContent"></div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Proveedor</th>
                                    <th>Nombre Insumo</th>
                                    <th>Fecha Compra</th>
                                    <th>Total Compra</th>
                                    <th>Cantidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include '../includes/conexion.php';

                                $sql = "SELECT c.id_compra, u.nombre_usuario, p.nombre_proveedor, c.nombre_insumos, c.fecha_compra, c.total_compra, c.cantidad
                                FROM compras c
                                JOIN usuarios u ON c.id_usuario = u.id_usuario
                                JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                                ORDER BY c.fecha_compra DESC
                                LIMIT $items_por_pagina OFFSET $offset";
                                $resultado = $conn->query($sql);

                                if ($resultado->num_rows > 0) {
                                    while ($row = $resultado->fetch_assoc()) {
                                        echo "<tr id='compra-" . $row['id_compra'] . "'>";
                                        echo "<td>" . $row['nombre_usuario'] . "</td>";
                                        echo "<td>" . $row['nombre_proveedor'] . "</td>";
                                        echo "<td>" . $row['nombre_insumos'] . "</td>";
                                        echo "<td>" . $row['fecha_compra'] . "</td>";
                                        echo "<td>" . $row['total_compra'] . "</td>";
                                        echo "<td>" . $row['cantidad'] . "</td>";
                                        echo '<td class="actions">';
                                        echo '<button class="edit-btn" onclick="abrirModalEditar(' .
                                            $row['id_compra'] . ', \'' .
                                            htmlspecialchars($row['nombre_proveedor'], ENT_QUOTES) . '\', \'' .
                                            htmlspecialchars($row['nombre_insumos'], ENT_QUOTES) . '\', \'' .
                                            $row['fecha_compra'] . '\', ' .
                                            $row['total_compra'] . ', ' .
                                            $row['cantidad'] .
                                            ')"><i class="fa fa-edit"></i></button>';
                                        echo '<button class="delete-btn" onclick="eliminarCompra(' . $row['id_compra'] . ')"><i class="fa fa-trash"></i></button>';
                                        echo '</td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>No hay compras disponibles.</td></tr>";
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
                                    echo "<a href='#' onclick='actualizarTablaCompras($i); return false;' class='active'>$i</a>";
                                } else {
                                    echo "<a href='#' onclick='actualizarTablaCompras($i); return false;'>$i</a>";
                                }
                            }
                        }
                        ?>
                    </div>

                </div>

                <!-- Script para eliminar compra -->
                <script>
                    function eliminarCompra(id_compra) {
                        if (confirm('¿Estás seguro de que quieres eliminar esta compra?')) {
                            fetch(`compras_controller.php?eliminar=${id_compra}`, {
                                    method: 'GET'
                                })
                                .then(response => response.text())
                                .then(data => {
                                    if (data.includes('Error')) {
                                        alert(data);
                                    } else {
                                        document.getElementById(`compra-${id_compra}`).remove();
                                        alert('Compra eliminada exitosamente.');
                                    }
                                })
                                .catch(error => console.error('Error al eliminar la compra:', error));
                        }
                    }
                </script>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.getElementById('btnAgregarCompra').onclick = function() {
            // cargarUsuarios();
            cargarProveedores();
            document.getElementById('modalAgregarCompra').style.display = 'block';
            document.getElementById('modalAgregarCompra').classList.add('show');
        };

        document.querySelectorAll('.close').forEach(function(el) {
            el.onclick = function() {
                this.parentElement.parentElement.style.display = 'none';
            };
        });

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        };

        function abrirModalEditar(idCompra, nombreProveedor, nombreInsumos, fechaCompra, totalCompra, cantidad) {
            document.getElementById('edit_id_compra').value = idCompra;
            document.getElementById('edit_nombre_insumos').value = nombreInsumos;
            document.getElementById('edit_fecha_compra').value = fechaCompra;
            document.getElementById('edit_total_compra').value = totalCompra;
            document.getElementById('edit_cantidad').value = cantidad;

            // Cargar y seleccionar el proveedor correcto
            cargarProveedoresEditar(nombreProveedor);

            document.getElementById('modalEditarCompra').style.display = 'block';
            document.getElementById('modalEditarCompra').classList.add('show');
        }

        function abrirModalDetalle(idCompra) {
            fetch(`../controller/obtener_detalles_compra.php?idCompra=${idCompra}`)
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            document.getElementById('modalContent').innerHTML = data.detalles;
                            document.getElementById('detalleCompraModal').style.display = 'block';
                            document.getElementById('detalleCompraModal').classList.add('show');
                            document.getElementById('detalleCompraModal').querySelector('.modal-content').classList.add(
                                'show');
                        } else {
                            alert(data.message);
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        console.error('Response text:', text);
                        alert('Error al obtener los detalles de la compra. Revisa la consola para más información.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al obtener los detalles de la compra. Revisa la consola para más información.');
                });
        }

        function eliminarCompra(idCompra) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminarlo!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`../controller/compras_controller.php?eliminar=${idCompra}`, {
                            method: 'GET'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Eliminado!',
                                    data.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message,
                                    'error'
                                );
                            }
                        });
                }
            });
        }

        document.getElementById('formAgregarCompra').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('../controller/compras_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Éxito!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                actualizarTablaCompras();
                                document.getElementById('modalAgregarCompra').style.display = 'none';
                                this.reset(); // Limpia el formulario
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Ocurrió un error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        });

        function actualizarTablaCompras(pagina = 1) {
            fetch(`../controller/obtener_compras.php?pagina=${pagina}`)
                .then(response => response.text())
                .then(html => {
                    document.querySelector('table tbody').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ocurrió un error al actualizar la tabla de compras.');
                });
        }

        document.getElementById('formEditarCompra').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¿Quieres editar esta compra?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, editar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../controller/compras_controller.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Actualizado!',
                                    data.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message,
                                    'error'
                                );
                            }
                        });
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            actualizarTablaCompras();
            cargarProveedores();
        });

        function cargarProveedores() {
            fetch('../controller/proveedores_controller.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('id_proveedor');
                    select.innerHTML = '';
                    data.forEach(proveedor => {
                        const option = document.createElement('option');
                        option.value = proveedor.id_proveedor;
                        option.textContent = proveedor.nombre_proveedor;
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }


        function cargarProveedoresEditar(nombreProveedorSeleccionado) {
            fetch('../controller/proveedores_controller.php')
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('edit_id_proveedor');
                    select.innerHTML = '';
                    data.forEach(proveedor => {
                        const option = document.createElement('option');
                        option.value = proveedor.id_proveedor;
                        option.textContent = proveedor.nombre_proveedor;
                        if (proveedor.nombre_proveedor === nombreProveedorSeleccionado) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }



        function openPermissionsModal(userId) {
            modal.style.display = "block";
        }

        function closePermissionsModal() {
            modal.style.display = "none";
        }

        function closeRolesModal() {
            rolesModal.style.display = "none";
        }

        function cerrarModalDetalle() {
            var detalleModal = document.getElementById('detalleCompraModal');
            if (detalleModal) {
                detalleModal.style.display = 'none';
            }
        }


        function toggleUserOptions() {
            var userOptionsContainer = document.getElementById("userOptionsContainer");
            if (userOptionsContainer.style.display === "none" || userOptionsContainer.style.display === "") {
                userOptionsContainer.style.display = "block";
            } else {
                userOptionsContainer.style.display = "none";
            }
        }

        window.onclick = function(event) {
            var modal = document.getElementById('modalAgregarCompra');
            var editModal = document.getElementById('modalEditarCompra');
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (event.target == editModal) {
                editModal.style.display = "none";
            }
        }

        function toggleUserOptions() {
            var userOptionsContainer = document.getElementById("userOptionsContainer");
            if (userOptionsContainer.style.display === "none" || userOptionsContainer.style.display === "") {
                userOptionsContainer.style.display = "block";
            } else {
                userOptionsContainer.style.display = "none";
            }

        }

        function buscarCompra() {
            let input = document.getElementById('searchCompras').value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                let showRow = false;
                row.querySelectorAll('td').forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(input)) {
                        showRow = true;
                    }
                });
                row.style.display = showRow ? '' : 'none';
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/validaciones.js"></script>
</body>

</html>