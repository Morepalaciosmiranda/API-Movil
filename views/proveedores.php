<?php

include_once "../includes/conexion.php";


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

$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$sql = "SELECT * FROM proveedores";
$result = mysqli_query($conn, $sql);
$total_proveedores = mysqli_num_rows($result);
$total_paginas = ceil($total_proveedores / $items_por_pagina);

if ($offset >= $total_proveedores) {
    header("Location: proveedores.php?pagina=1");
    exit();
}

$sql = "SELECT * FROM proveedores LIMIT $items_por_pagina OFFSET $offset";
$result = mysqli_query($conn, $sql);
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
    <link rel="stylesheet" href="./css/proveedores15.css">
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <div class="head-section">
                <div class="title-container">
                    <h1>Proveedores</h1>
                    <div class="search-bar">
                        <input type="text" id="search" placeholder="Buscar..." onkeyup="buscarProveedor()" />
                        <button type="button" onclick="buscarProveedor()"><i class="fa fa-search"></i></button>
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
            <div class="main">
                <br><br>
                <div class="content">
                    <button id="btnAgregarProveedor" class="btn btn-success">Agregar Proveedor</button>
                    <br><br>

                    <div id="modalAgregarProveedor" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <h2>Agregar Nuevo Proveedor</h2>
                            <form id="formAgregarProveedor" action="../controller/proveedores_controller.php" method="post">
                                <label for="nombre">Nombre:</label>
                                <input type="text" id="nombre" name="nombre" required><br><br>
                                <label for="correo">Correo Electrónico:</label>
                                <input type="email" id="correo" name="correo" required><br><br>
                                <label for="celular">Celular:</label>
                                <input type="tel" id="celular" name="celular" required><br><br>
                                <label for="contacto">Contacto:</label>
                                <input type="text" id="contacto" name="contacto" required><br><br>
                                <label for="estado">Estado:</label>
                                <select id="estado" name="estado" required>
                                    <option value="Habilitado">Habilitado</option>
                                    <option value="Inhabilitado">Inhabilitado</option>
                                </select><br><br>
                                <input type="submit" value="Agregar Proveedor">
                            </form>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>

                                    <th>Nombre Proveedor</th>
                                    <th>Correo Electronico</th>
                                    <th>Celular</th>
                                    <th>Estado proveedor</th>
                                    <th>Contacto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="proveedoresTableBody">
                            </tbody>
                        </table>

                        <div id="modalEditarProveedor" class="modal">
                            <div class="modal-content">
                                <span class="close">&times;</span>
                                <h2>Editar Proveedor</h2>
                                <form id="formEditarProveedor" action="../controller/proveedores_controller.php" method="post">
                                    <input type="hidden" id="edit-id" name="id_editar">
                                    <label for="edit-nombre">Nombre:</label>
                                    <input type="text" id="edit-nombre" name="nombre_edit"><br><br>
                                    <label for="edit-correo">Correo Electrónico:</label>
                                    <input type="email" id="edit-correo" name="correo_edit"><br><br>
                                    <label for="edit-celular">Celular:</label>
                                    <input type="tel" id="edit-celular" name="celular_edit"><br><br>
                                    <label for="edit-contacto">Contacto:</label>
                                    <input type="text" id="edit-contacto" name="contacto_edit"><br><br>
                                    <label for="edit-estado">Estado:</label>
                                    <select id="edit-estado" name="estado_edit">
                                        <option value="Habilitado">Habilitado</option>
                                        <option value="Inhabilitado">Inhabilitado</option>
                                    </select><br><br>
                                    <input type="submit" value="Guardar Cambios">
                                </form>
                            </div>
                        </div>
                        <div class="pagination">
                            <?php
                            for ($i = 1; $i <= $total_paginas; $i++) {
                                if ($i == $pagina_actual) {
                                    echo "<a href='proveedores.php?pagina=$i' class='active'>$i</a>";
                                } else {
                                    echo "<a href='proveedores.php?pagina=$i'>$i</a>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>



            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    cargarProveedores();
                });

                function cargarProveedores() {
                    fetch('../controller/proveedores_controller.php')
                        .then(response => response.json())
                        .then(data => {
                            const tableBody = document.getElementById('proveedoresTableBody');
                            tableBody.innerHTML = '';
                            data.forEach(proveedor => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                <td>${proveedor.nombre_proveedor}</td>
                                <td>${proveedor.correo_electronico}</td>
                                <td>${proveedor.celular}</td>
                                <td>${proveedor.estado_proveedor}</td>
                                <td>${proveedor.contacto}</td>
                                <td class="actions">
                                    <button class="edit-btn" onclick="abrirModalEditar('${proveedor.id_proveedor}', '${proveedor.nombre_proveedor}', '${proveedor.correo_electronico}', '${proveedor.celular}', '${proveedor.estado_proveedor}', '${proveedor.contacto}')"><i class="fa fa-edit"></i></button>
                                    <button class="delete-btn" onclick="confirmarEliminacion('${proveedor.id_proveedor}')"><i class="fa fa-trash"></i></button>
                                </td>
                            `;
                                tableBody.appendChild(row);
                            });
                        })
                        .catch(error => console.error('Error al obtener los proveedores:', error));
                }


                var modalAgregar = document.getElementById("modalAgregarProveedor");
                var btnAgregar = document.getElementById("btnAgregarProveedor");
                var spanCerrarAgregar = document.querySelector("#modalAgregarProveedor .close");

                btnAgregar.onclick = function() {
                    modalAgregar.style.display = "block";
                }

                spanCerrarAgregar.onclick = function() {
                    modalAgregar.style.display = "none";
                }

                window.onclick = function(event) {
                    if (event.target == modalAgregar) {
                        modalAgregar.style.display = "none";
                    }
                }

                var modalEditar = document.getElementById("modalEditarProveedor");
                var spanCerrarEditar = document.querySelector("#modalEditarProveedor .close");

                function abrirModalEditar(id, nombre, correo, celular, estado, contacto) {
                    document.getElementById("edit-id").value = id;
                    document.getElementById("edit-nombre").value = nombre;
                    document.getElementById("edit-correo").value = correo;
                    document.getElementById("edit-celular").value = celular;
                    document.getElementById("edit-contacto").value = contacto;
                    document.getElementById("edit-estado").value = estado;
                    modalEditar.style.display = "block";
                }

                spanCerrarEditar.onclick = function() {
                    modalEditar.style.display = "none";
                }

                window.onclick = function(event) {
                    if (event.target == modalEditar) {
                        modalEditar.style.display = "none";
                    }
                }

                document.getElementById('formAgregarProveedor').addEventListener('submit', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¿Deseas agregar este proveedor?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, agregarlo!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Proveedor agregado',
                                text: 'El proveedor se ha agregado exitosamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                event.target.submit();
                            });
                        }
                    });
                });

                document.getElementById('formEditarProveedor').addEventListener('submit', function(event) {
                    event.preventDefault();
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
                                title: 'Proveedor editado',
                                text: 'El proveedor se ha editado exitosamente.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                event.target.submit();
                            });
                        }
                    });
                });

                function confirmarEliminacion(id) {

                    fetch(`../controller/proveedores_controller.php?eliminar=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {

                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error al intentar eliminar el proveedor.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            } else if (data.asociado) {

                                Swal.fire({
                                    title: 'Error',
                                    text: 'No puedes eliminar este proveedor porque está asociado a compras.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            } else {

                                Swal.fire({
                                    title: '¿Estás seguro?',
                                    text: '¿Deseas eliminar este proveedor?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Sí, eliminarlo'
                                }).then((result) => {
                                    if (result.isConfirmed) {

                                        fetch(`../controller/proveedores_controller.php?eliminar=${id}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.success) {

                                                    Swal.fire({
                                                        title: 'Eliminado!',
                                                        text: 'El proveedor ha sido eliminado correctamente.',
                                                        icon: 'success',
                                                        confirmButtonText: 'OK'
                                                    }).then(() => {
                                                        window.location.href = '../views/proveedores.php';
                                                    });
                                                } else {

                                                    Swal.fire({
                                                        title: 'Error',
                                                        text: 'Error al intentar eliminar el proveedor.',
                                                        icon: 'error',
                                                        confirmButtonText: 'OK'
                                                    });
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error al intentar eliminar el proveedor:', error);
                                                Swal.fire({
                                                    title: 'Error',
                                                    text: 'Ocurrió un error inesperado.',
                                                    icon: 'error',
                                                    confirmButtonText: 'OK'
                                                });
                                            });
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al verificar la asociación de compras del proveedor:', error);
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al intentar eliminar el proveedor.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }


                function buscarProveedor() {
                    const input = document.getElementById('search').value.toLowerCase();
                    const tableRows = document.querySelectorAll('#proveedoresTableBody tr');

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
        </div>
</body>

</html>