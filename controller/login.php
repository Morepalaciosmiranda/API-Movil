<?php
session_start();
include '../includes/conexion.php';

header('Content-Type: application/json');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo_electronico = filter_input(INPUT_POST, 'correo_electronico', FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'];

    if (!$correo_electronico || !$contrasena) {
        $response['status'] = 'error';
        $response['message'] = 'Por favor, complete todos los campos del formulario.';
    } else {
        $sql = "SELECT usuarios.*, roles.nombre_rol FROM usuarios
                INNER JOIN roles ON usuarios.id_rol = roles.id_rol
                WHERE correo_electronico=?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $response['status'] = 'error';
            $response['message'] = 'Error en la preparación de la consulta: ' . $conn->error;
        } else {
            $stmt->bind_param("s", $correo_electronico);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                if ($row['estado_usuario'] == 'Inactivo') {
                    $inactive_message = "Su cuenta está inactiva. " . $row['mensaje_estado'];
                    $response['status'] = 'warning';
                    $response['message'] = $inactive_message;
                } elseif (password_verify($contrasena, $row['contrasena'])) {
                    session_regenerate_id(true);
                    $_SESSION['correo_electronico'] = $correo_electronico;
                    $_SESSION['id_usuario'] = $row['id_usuario'];
                    
                    if ($row['nombre_rol'] == 'Administrador') {
                        $_SESSION['rol'] = 'Administrador';
                    } else {
                        $_SESSION['rol'] = 'Usuario';
                    }

                    $response['status'] = 'success';
                    $response['role'] = $row['nombre_rol'];
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'La contraseña ingresada es incorrecta.';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'El usuario no fue encontrado.';
            }

            $stmt->close();
        }
    }

    $conn->close();
}

echo json_encode($response);
?>
