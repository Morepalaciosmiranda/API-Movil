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

// Parámetro de filtrado por fecha
$fecha_filtro = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Consulta SQL para obtener las compras con filtro por fecha
$sql = "SELECT * FROM compras";
if ($fecha_filtro) {
    $sql .= " WHERE DATE(fecha_compra) = '$fecha_filtro'";
}
$sql .= " LIMIT $items_por_pagina OFFSET $offset";
$result = $conn->query($sql);

// Consulta SQL para obtener el número total de compras (con o sin filtro)
$sql_total = "SELECT COUNT(*) as total FROM compras";
if ($fecha_filtro) {
    $sql_total .= " WHERE DATE(fecha_compra) = '$fecha_filtro'";
}
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
                        <div class="form-container">
                            <form method="GET" action="compras.php">
                                <label for="fecha">Filtrar por fecha:</label>
                                <input type="date" id="fecha" name="fecha"
                                    value="<?php echo isset($_GET['fecha']) ? $_GET['fecha'] : ''; ?>">
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
                                <form id="formAgregarCompra" action="../controller/compras_controller.php"
                                    method="post">
                                    <label for="id_usuario">Usuario:</label>
                                    <select id="id_usuario" name="id_usuario" required></select><br><br>

                                    <label for="id_proveedor">Proveedor:</label>
                                    <select id="id_proveedor" name="id_proveedor" required></select><br><br>

                                    <label for="fecha_compra">Fecha de Compra:</label>
                                    <input type="date" id="fecha_compra" name="fecha_compra" required><br><br>

                                    <label for="subtotal">Subtotal:</label>
                                    <input type="number" id="subtotal" name="subtotal" step="0.01" required><br><br>

                                    <label for="total_compra">Total de Compra:</label>
                                    <input type="number" id="total_compra" name="total_compra" step="0.01"
                                        required><br><br>

                                    <!-- Nuevos campos -->
                                    <label for="cantidad">Cantidad:</label>
                                    <input type="number" id="cantidad" name="cantidad" required><br><br>

                                    <label for="valor_unitario">Valor Unitario:</label>
                                    <input type="number" id="valor_unitario" name="valor_unitario" step="0.01"
                                        required><br><br>

                                    <input type="submit" value="Agregar Compra">
                                </form>
                            </div>
                        </div>
                        <div id="modalEditarCompra" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <h2>Editar Compra</h2>
                                <form id="formEditarCompra" action="../controller/compras_controller.php" method="post">
                                    <label for="edit_id_compra">ID de Compra:</label>
                                    <input type="text" id="edit_id_compra" name="edit_id_compra" readonly><br><br>

                                    <label for="edit_id_usuario">Usuario:</label>
                                    <select id="edit_id_usuario" name="edit_id_usuario" required></select><br><br>

                                    <label for="edit_id_proveedor">Proveedor:</label>
                                    <select id="edit_id_proveedor" name="edit_id_proveedor" required></select><br><br>

                                    <label for="edit_fecha_compra">Fecha de Compra:</label>
                                    <input type="date" id="edit_fecha_compra" name="edit_fecha_compra" required><br><br>

                                    <label for="edit_subtotal">Subtotal:</label>
                                    <input type="number" id="edit_subtotal" name="edit_subtotal" step="0.01"
                                        required><br><br>

                                    <label for="edit_total_compra">Total de Compra:</label>
                                    <input type="number" id="edit_total_compra" name="edit_total_compra" step="0.01"
                                        required><br><br>

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
                                        <!-- <th>ID Compra</th> -->
                                        <th>Usuario</th>
                                        <th>Proveedor</th>
                                        <th>Fecha Compra</th>
                                        <th>Subtotal</th>
                                        <th>Total Compra</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                include '../includes/conexion.php';

                                $sql = "SELECT c.id_compra, u.nombre_usuario, p.nombre_proveedor, c.fecha_compra, c.subtotal, c.total_compra
            FROM compras c
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            JOIN proveedores p ON c.id_proveedor = p.id_proveedor
            LIMIT $items_por_pagina OFFSET $offset";
                                $resultado = $conn->query($sql);

                                if ($resultado->num_rows > 0) {
                                    while ($row = $resultado->fetch_assoc()) {
                                        echo "<tr id='compra-" . $row['id_compra'] . "'>";
                                        // echo "<td>" . $row['id_compra'] . "</td>";
                                        echo "<td>" . $row['nombre_usuario'] . "</td>";
                                        echo "<td>" . $row['nombre_proveedor'] . "</td>";
                                        echo "<td>" . $row['fecha_compra'] . "</td>";
                                        echo "<td>" . $row['subtotal'] . "</td>";
                                        echo "<td>" . $row['total_compra'] . "</td>";
                                        echo '<td class="actions">';
                                        echo '<button class="edit-btn" onclick="abrirModalEditar(' . $row['id_compra'] . ', \'' . $row['nombre_usuario'] . '\', \'' . $row['nombre_proveedor'] . '\', \'' . $row['fecha_compra'] . '\', ' . $row['subtotal'] . ', ' . $row['total_compra'] . ')"><i class="fa fa-edit"></i></button>';
                                        echo '<button class="delete-btn" onclick="eliminarCompra(' . $row['id_compra'] . ')"><i class="fa fa-trash"></i></button>';
                                        echo '<button class="details-btn" onclick="abrirModalDetalle(' . $row['id_compra'] . ')"><i class="fa fa-eye"></i></button>';
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
                                    echo "<a href='compras.php?pagina=$i&fecha=$fecha_filtro' class='active'>$i</a>";
                                } else {
                                    echo "<a href='compras.php?pagina=$i&fecha=$fecha_filtro'>$i</a>";
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
        cargarUsuarios();
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

    function abrirModalEditar(idCompra, idUsuario, idProveedor, fechaCompra, subtotal, totalCompra) {
        document.getElementById('edit_id_compra').value = idCompra;
        document.getElementById('edit_id_usuario').value = idUsuario;
        document.getElementById('edit_id_proveedor').value = idProveedor;
        document.getElementById('edit_fecha_compra').value = fechaCompra;
        document.getElementById('edit_subtotal').value = subtotal;
        document.getElementById('edit_total_compra').value = totalCompra;
        document.getElementById('modalEditarCompra').style.display = 'block';
        document.getElementById('modalEditarCompra').classList.add('show');
        cargarUsuariosEditar(idUsuario);
        cargarProveedoresEditar(idProveedor);
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
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¿Quieres agregar esta compra?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, agregar!'
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
                                'Agregado!',
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
        cargarUsuarios();
        cargarProveedores();
    });

    function cargarUsuarios() {
        fetch('../controller/usuarios_controller.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('id_usuario');
                select.innerHTML = '';
                data.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.id_usuario;
                    option.textContent = usuario.nombre_usuario;
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }

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

    function cargarUsuariosEditar(idUsuarioSeleccionado) {
        fetch('../controller/usuarios_controller.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('edit_id_usuario');
                select.innerHTML = '';
                data.forEach(usuario => {
                    const option = document.createElement('option');
                    option.value = usuario.id_usuario;
                    option.textContent = usuario.nombre_usuario;
                    if (usuario.id_usuario == idUsuarioSeleccionado) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function cargarProveedoresEditar(idProveedorSeleccionado) {
        fetch('../controller/proveedores_controller.php')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('edit_id_proveedor');
                select.innerHTML = '';
                data.forEach(proveedor => {
                    const option = document.createElement('option');
                    option.value = proveedor.id_proveedor;
                    option.textContent = proveedor.nombre_proveedor;
                    if (proveedor.id_proveedor == idProveedorSeleccionado) {
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
        document.getElementById('detalleCompraModal').style.display = 'none';
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
        if (event.target == modal) {
            closePermissionsModal();
        }
        if (event.target == rolesModal) {
            closeRolesModal();
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
</body>

</html>