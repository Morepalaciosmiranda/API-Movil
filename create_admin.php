<?php
// Incluye el archivo de conexión
include '../includes/conexion.php';

// Verifica si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtén los datos del formulario
    $admin_username = $_POST['username'];
    $admin_password = $_POST['password'];
    $admin_email = $_POST['email'];

    // Validación básica
    if (empty($admin_username) || empty($admin_password) || empty($admin_email)) {
        echo "Por favor, completa todos los campos.";
    } else {
        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Crear el usuario administrador
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

            $insert_user_sql = "INSERT INTO usuarios (nombre_usuario, contrasena, correo_electronico) VALUES (?, ?, ?)";
            $insert_user_stmt = $conn->prepare($insert_user_sql);
            $insert_user_stmt->bind_param("sss", $admin_username, $hashed_password, $admin_email);

            if ($insert_user_stmt->execute()) {
                $admin_id = $conn->insert_id;
                echo "Usuario administrador creado con éxito. ID: $admin_id<br>";
                
                // Asignar rol de administrador
                $admin_role_id = 1; // Asumiendo que 1 es el ID del rol de administrador
                $update_role_sql = "UPDATE usuarios SET id_rol = ? WHERE id_usuario = ?";
                $update_role_stmt = $conn->prepare($update_role_sql);
                $update_role_stmt->bind_param("ii", $admin_role_id, $admin_id);
                $update_role_stmt->execute();
                
                echo "Rol de administrador asignado.<br>";

                // Asignar todos los permisos al administrador
                $get_permissions_sql = "SELECT id_permiso FROM permisos";
                $permissions_result = $conn->query($get_permissions_sql);
                
                if ($permissions_result->num_rows > 0) {
                    $insert_permissions_sql = "INSERT INTO rolesxpermiso (id_usuario, id_permiso) VALUES (?, ?)";
                    $insert_permissions_stmt = $conn->prepare($insert_permissions_sql);
                    
                    while ($row = $permissions_result->fetch_assoc()) {
                        $permission_id = $row['id_permiso'];
                        $insert_permissions_stmt->bind_param("ii", $admin_id, $permission_id);
                        $insert_permissions_stmt->execute();
                    }
                    
                    echo "Todos los permisos asignados al administrador.<br>";
                } else {
                    echo "No se encontraron permisos para asignar.<br>";
                }
                
                echo "Cuenta de administrador creada exitosamente.<br>";

                // Commit de la transacción
                $conn->commit();
            } else {
                throw new Exception("Error al crear el usuario administrador: " . $conn->error);
            }
        } catch (Exception $e) {
            // Rollback en caso de error
            $conn->rollback();
            echo "Error: " . $e->getMessage() . "<br>";
        }

        $conn->close();
    }
} else {
    // Si no se ha enviado el formulario, muestra el formulario (igual que antes)
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Crear Cuenta de Administrador</title>
    </head>
    <body>
        <h2>Crear Cuenta de Administrador</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Nombre de usuario:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Contraseña:</label><br>
            <input type="password" id="password" name="password" required><br>
            <label for="email">Correo electrónico:</label><br>
            <input type="email" id="email" name="email" required><br><br>
            <input type="submit" value="Crear Administrador">
        </form>
    </body>
    </html>
    <?php
}
?>