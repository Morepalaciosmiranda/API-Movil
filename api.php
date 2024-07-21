<?php
header("Content-Type: application/json");
require_once('./includes/conexion.php'); // Archivo de configuración de la base de datos

// Manejar la solicitud POST para iniciar sesión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    // Obtener datos del formulario
    $correo_electronico = $data->correo_electronico;
    $contrasena = $data->contrasena;

    // Consulta SQL para verificar credenciales
    $query = "SELECT usuarios.*, roles.nombre_rol FROM usuarios
              INNER JOIN roles ON usuarios.id_rol = roles.id_rol
              WHERE correo_electronico=?";

    $stmt = $conn->prepare($query);

    if (!$stmt) {
        $response = array(
            "status" => "error",
            "message" => "Error en la preparación de la consulta: " . $conn->error
        );
    } else {
        $stmt->bind_param("s", $correo_electronico);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($contrasena, $row['contrasena'])) {
                // Credenciales válidas
                if ($row['estado_usuario'] == 'Inactivo') {
                    $response = array(
                        "status" => "warning",
                        "message" => "Su cuenta está inactiva. " . $row['mensaje_estado']
                    );
                } else {
                    $response = array(
                        "status" => "success",
                        "role" => $row['nombre_rol'],
                        "message" => "Inicio de sesión exitoso"
                    );
                }
            } else {
                // Contraseña incorrecta
                $response = array(
                    "status" => "error",
                    "message" => "Contraseña incorrecta"
                );
            }
        } else {
            // Usuario no encontrado
            $response = array(
                "status" => "error",
                "message" => "Usuario no encontrado"
            );
        }

        $stmt->close();
    }

    echo json_encode($response);
}

mysqli_close($conn); // Cerrar conexión
?>