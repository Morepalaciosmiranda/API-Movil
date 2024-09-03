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

include_once('../controller/productos_controller.php');
include_once('../controller/insumos_controller.php');

$productos = obtenerProductos();
$items_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;
$total_productos = count($productos);
$total_paginas = ceil($total_productos / $items_por_pagina);

if ($offset >= $total_productos) {
    header("Location: productos.php?pagina=1");
    exit();
}

$productos_paginados = array_slice($productos, $offset, $items_por_pagina);
$insumos = obtenerInsumos();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/productos12.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <div class="head-section">
                <div class="title-container">
                    <h1>Productos</h1>
                    <div class="search-bar">
                        <input type="text" id="search" placeholder="Buscar..." onkeyup="buscarProducto()" />
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
                    <button onclick="abrirModalAgregar()" id="btnAgregarProducto" class="btn btn-primary">Agregar Producto</button>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productosTableBody">
                                <?php
                                if (count($productos_paginados) > 0) {
                                    foreach ($productos_paginados as $producto) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($producto['id_producto']) . "</td>";
                                        echo "<td>" . htmlspecialchars($producto['nombre_producto']) . "</td>";
                                        echo "<td>" . htmlspecialchars($producto['descripcion_producto']) . "</td>";
                                        echo "<td>" . htmlspecialchars($producto['valor_unitario']) . "</td>";
                                        echo '<td class="actions">';
                                        echo '<button class="edit-btn" onclick="abrirModalEditar(\'' . htmlspecialchars($producto['id_producto']) . '\', \'' . htmlspecialchars($producto['nombre_producto']) . '\', \'' . htmlspecialchars($producto['descripcion_producto']) . '\', \'' . htmlspecialchars($producto['valor_unitario']) . '\')"><i class="fa fa-edit"></i></button>';
                                        echo '<button class="delete-btn" onclick="confirmarEliminacion(' . htmlspecialchars($producto['id_producto']) . ')"><i class="fa fa-trash"></i></button>';
                                        echo '</td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5'>No se encontraron productos</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination">
                        <?php
                        for ($i = 1; $i <= $total_paginas; $i++) {
                            if ($i == $pagina_actual) {
                                echo "<a href='productos.php?pagina=$i' class='active'>$i</a>";
                            } else {
                                echo "<a href='productos.php?pagina=$i'>$i</a>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="modalAgregarProducto" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModalAgregar()">&times;</span>
                <h2>Agregar Nuevo Producto</h2>
                <form id="formAgregarProducto" action="../controller/productos_controller.php" method="post" enctype="multipart/form-data">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required><br><br>

                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required></textarea><br><br>

                    <label for="precio">Precio:</label>
                    <input type="number" id="precio" name="precio" step="0.01" required><br><br>

                    <div id="insumos-container">
                        <div id="insumo-1" class="insumo-item">
                            <label for="insumo">Insumo:</label>
                            <select id="insumo_1" name="insumo_id[]" required>
                                <?php foreach ($insumos as $insumo) { ?>
                                    <option value="<?php echo $insumo['id_insumo']; ?>"><?php echo $insumo['nombre_insumo']; ?></option>
                                <?php } ?>
                            </select>

                            <label for="cantidad_insumo">Cantidad de Insumo:</label>
                            <input type="number" id="cantidad_insumo_1" name="cantidad_insumo[]" required><br><br>

                            <button type="button" onclick="eliminarInsumo('insumo-1')">Eliminar</button>
                        </div>
                    </div>

                    <button type="button" onclick="agregarInsumo()">Agregar Insumo</button><br><br>

                    <label class="custom-file-upload">
                        <input type="file" id="imagen" name="imagen" accept="image/*" required>
                        Seleccionar Imagen
                    </label>

                    <input type="submit" value="Agregar Producto">
                </form>
            </div>
        </div>

        <div id="modalEditarProducto" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModalEditar()">&times;</span>
                <h2>Editar Producto</h2>
                <form id="formEditarProducto" action="../controller/productos_controller.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="edit-id" name="id_editar">
                    <label for="edit-nombre">Nombre:</label>
                    <input type="text" id="edit-nombre" name="nombre_edit" required><br><br>
                    <label for="edit-descripcion">Descripción:</label>
                    <textarea id="edit-descripcion" name="descripcion_edit" required></textarea>
                    <label for="edit-precio">Precio:</label>
                    <input type="number" id="edit-precio" name="precio_edit" step="0.01" required><br><br>
                    <label for="edit-imagen">Imagen:</label>
                    <input type="file" id="edit-imagen" name="imagen_edit" accept="image/*"><br><br>
                    <input type="submit" value="Guardar Cambios">
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var modalAgregar = document.getElementById("modalAgregarProducto");
                var modalEditar = document.getElementById("modalEditarProducto");
                var insumoCount = 1;

                window.abrirModalAgregar = function() {
                    modalAgregar.style.display = "block";
                }

                window.cerrarModalAgregar = function() {
                    modalAgregar.style.display = "none";
                }

                window.abrirModalEditar = function(id, nombre, descripcion, precio) {
                    document.getElementById("edit-id").value = id;
                    document.getElementById("edit-nombre").value = nombre;
                    document.getElementById("edit-descripcion").value = descripcion;
                    document.getElementById("edit-precio").value = precio;
                    document.getElementById("edit-imagen").value = "";
                    modalEditar.style.display = "block";
                }

                window.cerrarModalEditar = function() {
                    modalEditar.style.display = "none";
                }

                window.onclick = function(event) {
                    if (event.target == modalAgregar) {
                        cerrarModalAgregar();
                    }
                    if (event.target == modalEditar) {
                        cerrarModalEditar();
                    }
                }

                window.agregarInsumo = function() {
                    insumoCount++;
                    var newInsumoDiv = document.createElement("div");
                    newInsumoDiv.id = "insumo-" + insumoCount;
                    newInsumoDiv.className = "insumo-item";

                    newInsumoDiv.innerHTML = `
                    <label for="insumo">Insumo:</label>
                    <select id="insumo_${insumoCount}" name="insumo_id[]" required>
                        <?php foreach ($insumos as $insumo) { ?>
                            <option value="<?php echo $insumo['id_insumo']; ?>"><?php echo $insumo['nombre_insumo']; ?></option>
                        <?php } ?>
                    </select>

                    <label for="cantidad_insumo">Cantidad de Insumo:</label>
                    <input type="number" id="cantidad_insumo_${insumoCount}" name="cantidad_insumo[]" required><br><br>

                    <button type="button" onclick="eliminarInsumo('insumo-${insumoCount}')">Eliminar</button>
                `;

                    document.getElementById("insumos-container").appendChild(newInsumoDiv);
                }

                window.eliminarInsumo = function(idInsumo) {
                    var insumoDiv = document.getElementById(idInsumo);
                    if (insumoDiv) {
                        insumoDiv.parentNode.removeChild(insumoDiv);
                    }
                }

                window.confirmarEliminacion = function(id) {
                    Swal.fire({
                        title: '¿Estás seguro de eliminar este producto?',
                        text: "No podrás revertir esto!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminarlo!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`../controller/productos_controller.php?eliminar=${id}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    if (data.exito) {
                                        Swal.fire({
                                            title: 'Eliminado!',
                                            text: data.mensaje,
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: data.mensaje,
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Hubo un error al procesar la solicitud.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                });
                        }
                    });
                }

document.getElementById('formAgregarProducto').addEventListener('submit', function(event) {
    event.preventDefault();

    Swal.fire({
        title: '¿Estás seguro de agregar este producto?',
        text: "Una vez agregado, no podrás revertir esta acción.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, agregar producto',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = new FormData(event.target);
            formData.append('accion', 'agregar');

            fetch('../controller/productos_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        Swal.fire({
                            title: 'Éxito',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.mensaje,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un error al procesar la solicitud.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    });
});

document.getElementById('formEditarProducto').addEventListener('submit', function(event) {
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
            var formData = new FormData(event.target);
            formData.append('accion', 'editar');

            fetch('../controller/productos_controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exito) {
                        Swal.fire({
                            title: 'Éxito',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.mensaje,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                                        title: 'Error',
                                        text: 'Hubo un error al procesar la solicitud.',
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                });
                        }
                    });
                });

                window.buscarProducto = function() {
                    var input, filter, table, tr, i, j, td, txtValue;
                    input = document.getElementById("search");
                    filter = input.value.toUpperCase();
                    table = document.getElementById("productosTableBody");
                    tr = table.getElementsByTagName("tr");

                    for (i = 0; i < tr.length; i++) {
                        var match = false;
                        td = tr[i].getElementsByTagName("td");
                        for (j = 0; j < td.length; j++) {
                            if (td[j]) {
                                txtValue = td[j].textContent || td[j].innerText;
                                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                    match = true;
                                    break;
                                }
                            }
                        }
                        tr[i].style.display = match ? "" : "none";
                    }
                }
            });
        </script>
    </div>
</body>

</html>