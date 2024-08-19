<?php
session_start();
include '../includes/conexion.php';

if (isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = $_POST['user_id'];
    $new_role_or_permissions = $_POST['new_role'];

    if (is_numeric($new_role_or_permissions)) {
        $update_sql = "UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_role_or_permissions, $user_id);
        if ($update_stmt->execute()) {
            echo "Rol actualizado correctamente.<br>";
        } else {
            echo "Error al actualizar el rol: " . $conn->error . "<br>";
        }
    } else {
        if (isset($_POST['permissions'])) {
            $permissions = $_POST['permissions'];

            // Eliminar permisos anteriores
            $delete_sql = "DELETE FROM rolesxpermiso WHERE id_usuario = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $user_id);
            if ($delete_stmt->execute()) {
                echo "Permisos anteriores eliminados correctamente.<br>";
            } else {
                echo "Error al eliminar permisos anteriores: " . $conn->error . "<br>";
            }


            $insert_sql = "INSERT INTO rolesxpermiso (id_usuario, id_permiso) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            foreach ($permissions as $permission) {
                $insert_stmt->bind_param("ii", $user_id, $permission);
                if ($insert_stmt->execute()) {
                    echo "Permiso $permission asignado correctamente.<br>";
                } else {
                    echo "Error al asignar permiso $permission: " . $conn->error . "<br>";
                }
            }
        } else {
            echo "Debe seleccionar al menos un permiso.<br>";
        }
    }
}

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['rol'])) {
    // Redirigir a la página de inicio de sesión si no hay sesión
    header('Location: ../loginRegister.php');
    exit();
}

if ($_SESSION['rol'] !== 'Administrador') {
    // Redirigir a una página de acceso no autorizado o hacer otra acción
    header('Location: ../no_autorizado.php');
    exit();
}

$results_per_page = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

$sql = "SELECT * FROM usuarios LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/usuarios11.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <div class="head-section">
                <div class="title-container">
                    <h1>Usuarios</h1>
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
                <div class="content">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo Electrónico</th>
                                <th>ID Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM usuarios";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row["id_usuario"] . "</td>";
                                    echo "<td>" . $row["nombre_usuario"] . "</td>";
                                    echo "<td>" . $row["correo_electronico"] . "</td>";
                                    echo "<td>" . $row["id_rol"] . "</td>";
                                    echo "<td>" . $row["estado_usuario"] . "</td>";
                                    echo "<td>";
                                    echo "<div class='actions'>";
                                    echo "<button class='btn state-button action-btn' data-user-id='" . $row["id_usuario"] . "' data-current-state='" . $row["estado_usuario"] . "' onclick='openStateModal(" . $row["id_usuario"] . ", \"" . $row["estado_usuario"] . "\")'><i class='fa fa-edit'></i></button>";
                                    
                                    // Mostrar el botón de asignar rol para todos los usuarios
                                    echo "<form class='assign-role-form' method='post' action='../controller/assign_role.php'>";
                                    echo "<input type='hidden' name='user_id' value='" . $row["id_usuario"] . "'>";
                                    echo "<button class='btn assign-role-button action-btn' data-user-id='" . $row["id_usuario"] . "'><i class='fa fa-user-plus'></i></button>";
                                    echo "</form>";
                                    
                                    // Mostrar el botón de permisos solo para usuarios con id_rol 1
                                    if ($row["id_rol"] == 1) {
                                        echo "<button class='btn permission-button action-btn' data-user-id='" . $row["id_usuario"] . "' onclick='openPermissionsModal(" . $row["id_usuario"] . ")'><i class='fa fa-lock'></i></button>";
                                    }
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>No hay usuarios registrados.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <?php
                    $sql = "SELECT COUNT(id_usuario) AS total FROM usuarios";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();
                    $total_pages = ceil($row["total"] / $results_per_page);

                    echo "<div class='pagination'>";
                    for ($i = 1; $i <= $total_pages; $i++) {
                        echo "<a href='?page=" . $i . "'>" . $i . "</a>";
                    }
                    echo "</div>";
                    ?>
                </div>
            </div>
        </div>

        <div id="permissionsModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closePermissionsModal()">&times;</span>
                <h2>Permisos existentes</h2>
                <ul id="permissionsList">
                    <?php
                    $sql = "SELECT * FROM permisos";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<li class='checkbox-container'>";
                            echo "<input type='checkbox' id='permiso_" . $row["id_permiso"] . "' class='permission-checkbox'>";
                            echo "<label for='permiso_" . $row["id_permiso"] . "' class='checkbox-label'>" . $row["nombre_permiso"] . "</label>";
                            echo "</li>";
                        }
                    } else {
                        echo "<li>No se encontraron permisos.</li>";
                    }
                    ?>
                </ul>
                <button id="confirmPermissionsBtn" class='btn btn-primary'><i class='fa fa-check'></i> Confirmar Permisos</button>
            </div>
        </div>

        <div id="rolesModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeRolesModal()">&times;</span>
                <h2>Roles existentes</h2>
                <ul id="rolesList">
                    <?php
                    $sql = "SELECT * FROM roles";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<li class='checkbox-container'>";
                            echo "<input type='radio' name='rol' id='rol_" . $row["id_rol"] . "' value='" . $row["id_rol"] . "' class='role-radio'>";
                            echo "<label for='rol_" . $row["id_rol"] . "' class='checkbox-label'>" . $row["nombre_rol"] . "</label>";
                            echo "</li>";
                        }
                    } else {
                        echo "<li>No se encontraron roles.</li>";
                    }
                    ?>
                </ul>
                <button id="confirmRoleBtn" class='btn btn-primary'><i class='fa fa-check'></i> Confirmar Rol</button>
            </div>
        </div>

        <div id="stateModal" class="modalstate">
            <div class="modal-state">
                <span class="close" onclick="closeStateModal()">&times;</span>
                <h2>Cambiar Estado del Usuario</h2>
                <form id="stateForm" onsubmit="return submitStateForm();">
                    <input type="hidden" name="user_id" id="stateUserId">
                    <label for="new_state">Nuevo Estado:</label>
                    <select name="new_state" id="new_state">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                    <label for="state_message">Mensaje:</label>
                    <textarea name="state_message" id="state_message" rows="4" placeholder="Ingrese el mensaje para el usuario..."></textarea>
                    <button id="confirmStateBtn" type="submit" class='btn btn-primary'><i class='fa fa-check'></i> Confirmar</button>
                </form>
            </div>
        </div>

        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
            var modal = document.getElementById('permissionsModal');
            var rolesModal = document.getElementById('rolesModal');
            var assignRoleButtons = document.querySelectorAll(".assign-role-button");
            var permissionButtons = document.querySelectorAll(".permission-button");
            var spans = document.getElementsByClassName("close");

            assignRoleButtons.forEach(function(button) {
                button.onclick = function(event) {
                    event.preventDefault();
                    var userId = button.getAttribute('data-user-id');
                    rolesModal.setAttribute('data-user-id', userId);
                    rolesModal.style.display = "block";
                };
            });

            permissionButtons.forEach(function(button) {
                button.onclick = function(event) {
                    event.preventDefault();
                    var userId = button.getAttribute('data-user-id');
                    modal.setAttribute('data-user-id', userId);
                    modal.style.display = "block";
                };
            });

            Array.from(spans).forEach(function(span) {
                span.onclick = function() {
                    closePermissionsModal();
                    closeRolesModal();
                };
            });

            window.onclick = function(event) {
                if (event.target == modal) {
                    closePermissionsModal();
                }
                if (event.target == rolesModal) {
                    closeRolesModal();
                }
            };

            var confirmRoleBtn = document.getElementById('confirmRoleBtn');
            confirmRoleBtn.onclick = function() {
                var userId = rolesModal.getAttribute('data-user-id');
                var selectedRole = document.querySelector('input[name="rol"]:checked');
                if (selectedRole) {
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: '¿Quieres asignar este rol al usuario?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, asignar rol',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var roleId = selectedRole.value;
                            var xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState == 4) {
                                    try {
                                        var response = JSON.parse(xhr.responseText);
                                        if (response.status === "success") {
                                            Swal.fire({
                                                title: 'Éxito',
                                                text: response.message,
                                                icon: 'success',
                                                confirmButtonText: 'OK'
                                            }).then(() => {
                                                location.reload();
                                            });
                                        } else {
                                            let errorMessage = response.message;
                                            if (response.details) {
                                                errorMessage += "\n\nDetalles: " + JSON.stringify(response.details);
                                            }
                                            Swal.fire({
                                                title: 'Error',
                                                text: errorMessage,
                                                icon: 'error',
                                                confirmButtonText: 'OK'
                                            });
                                            console.error("Error details:", response);
                                        }
                                    } catch (e) {
                                        console.error("Error parsing JSON:", xhr.responseText);
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Ocurrió un error inesperado. Por favor, intenta de nuevo.',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                    closeRolesModal();
                                }
                            };
                            xhr.open("POST", "../controller/assign_role.php", true);
                            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xhr.send("user_id=" + userId + "&new_role=" + roleId);
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Advertencia',
                        text: 'Por favor, selecciona un rol.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            };

            var confirmPermissionsBtn = document.getElementById('confirmPermissionsBtn');
            confirmPermissionsBtn.onclick = function() {
                var userId = modal.getAttribute('data-user-id');
                var selectedPermissions = document.querySelectorAll('.permission-checkbox:checked');
                if (selectedPermissions.length > 0) {
                    var permissions = Array.from(selectedPermissions).map(function(checkbox) {
                        return checkbox.id.replace('permiso_', '');
                    });

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: '¿Quieres asignar estos permisos al usuario?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, asignar permisos',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState == 4 && xhr.status == 200) {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.status == 'success') {
                                        Swal.fire({
                                            title: 'Éxito',
                                            text: response.message,
                                            icon: 'success',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: response.message,
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                    closePermissionsModal();
                                }
                            };
                            xhr.open("POST", "../controller/assign_permission.php", true);
                            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            xhr.send("user_id=" + userId + "&permissions=" + JSON.stringify(permissions));
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Advertencia',
                        text: 'Por favor, selecciona al menos un permiso.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                }
            };

            function toggleUserOptions() {
                var userOptionsContainer = document.getElementById("userOptionsContainer");
                if (userOptionsContainer.style.display === "none" || userOptionsContainer.style.display === "") {
                    userOptionsContainer.style.display = "block";
                } else {
                    userOptionsContainer.style.display = "none";
                }
            }

            function closePermissionsModal() {
                modal.style.display = "none";
            }

            function closeRolesModal() {
                rolesModal.style.display = "none";
            }

            document.addEventListener('DOMContentLoaded', (event) => {
                var stateModal = document.getElementById('stateModal');
                var closeButtons = document.querySelectorAll('.close');

                closeButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        closeStateModal();
                    });
                });

                window.addEventListener('click', function(event) {
                    if (event.target == stateModal) {
                        closeStateModal();
                    }
                });
            });

            function closeStateModal() {
                var stateModal = document.getElementById('stateModal');
                stateModal.style.display = 'none';
            }

            function openStateModal(userId, currentState) {
                document.getElementById('stateUserId').value = userId;
                document.getElementById('new_state').value = currentState;
                document.getElementById('state_message').value = ''; // Clear previous message
                document.getElementById('stateModal').style.display = 'block';
            }

            function submitStateForm() {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Quieres cambiar el estado de este usuario?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar estado',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var userId = document.getElementById('stateUserId').value;
                        var newState = document.getElementById('new_state').value;
                        var stateMessage = document.getElementById('state_message').value;

                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "../controller/change_state.php", true);
                        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState == 4 && xhr.status == 200) {
                                var response = JSON.parse(xhr.responseText);
                                if (response.status === "success") {
                                    Swal.fire({
                                        title: 'Éxito',
                                        text: 'Estado actualizado correctamente. ' + response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                                closeStateModal();
                            }
                        };
                        xhr.send("user_id=" + userId + "&new_state=" + newState + "&state_message=" + encodeURIComponent(stateMessage));
                    }
                });
                return false;
            }

            var createRoleBtn = document.getElementById('createRoleBtn');
            var createRoleModal = document.getElementById('createRoleModal');

            createRoleBtn.onclick = function() {
                createRoleModal.style.display = "block";
            }

            function closeCreateRoleModal() {
                createRoleModal.style.display = "none";
            }

            document.getElementById('createRoleForm').onsubmit = function(e) {
                e.preventDefault();
                var roleName = document.getElementById('roleName').value;
                var permissions = Array.from(document.querySelectorAll('input[name="permissions[]"]:checked')).map(el => el.value);

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: '¿Quieres crear este nuevo rol con los permisos seleccionados?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, crear rol',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var xhr = new XMLHttpRequest();
                        xhr.open("POST", "../controller/create_role.php", true);
                        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState == 4 && xhr.status == 200) {
                                var response = JSON.parse(xhr.responseText);
                                if (response.status === "success") {
                                    Swal.fire({
                                        title: 'Éxito',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'OK'
                                    });
                                }
                                closeCreateRoleModal();
                            }
                        };
                        xhr.send("roleName=" + encodeURIComponent(roleName) + "&permissions=" + JSON.stringify(permissions));
                    }
                });
            }

            // Cerrar la modal si se hace clic fuera de ella
            window.onclick = function(event) {
                if (event.target == createRoleModal) {
                    closeCreateRoleModal();
                }
            }
        </script>
    </div>
</body>
</html>