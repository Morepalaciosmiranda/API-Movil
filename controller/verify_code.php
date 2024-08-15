<?php
header('Content-Type: application/json');
session_start();
include '../includes/conexion.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_ingresado = $_POST['codigo_verificacion'];
    
    if (isset($_SESSION['temp_registro']) && $_SESSION['temp_registro']['codigo_verificacion'] == $codigo_ingresado) {
        $nombre_usuario = $_SESSION['temp_registro']['nombre_usuario'];
        $correo_electronico = $_SESSION['temp_registro']['correo_electronico'];
        $contraseña = $_SESSION['temp_registro']['contraseña'];

        // Hashing de la contraseña
        $hashed_password = password_hash($contraseña, PASSWORD_DEFAULT);

        // Obtención del ID de rol "Usuario"
        $sql_rol = "SELECT id_rol FROM roles WHERE nombre_rol = 'Usuario'";
        $result_rol = $conn->query($sql_rol);

        if ($result_rol->num_rows > 0) {
            $row_rol = $result_rol->fetch_assoc();
            $id_rol_usuario = $row_rol['id_rol'];

            // Inserción del nuevo usuario en la base de datos
            $sql = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena, id_rol, estado_usuario) VALUES (?, ?, ?, ?, 'activo')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nombre_usuario, $correo_electronico, $hashed_password, $id_rol_usuario);

            if ($stmt->execute()) {
                unset($_SESSION['temp_registro']);
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Registro exitoso. Redirigiendo al inicio de sesión...'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error en el registro: ' . $stmt->error
                ]);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No se encontró el rol "Usuario" en la base de datos.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Código de verificación incorrecto.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método de solicitud inválido.'
    ]);
}

$conn->close();
?>