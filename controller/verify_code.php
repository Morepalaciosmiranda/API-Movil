<?php
header('Content-Type: application/json');
include '../includes/conexion.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['temp_registro']) && isset($_POST['codigo_verificacion'])) {
        $temp_registro = $_SESSION['temp_registro'];
        $codigo_ingresado = $_POST['codigo_verificacion'];

        if ($codigo_ingresado == $temp_registro['codigo_verificacion']) {
            // El código es correcto, procede con el registro
            $nombre_usuario = $temp_registro['nombre_usuario'];
            $correo_electronico = $temp_registro['correo_electronico'];
            $hashed_password = $temp_registro['contraseña'];

            // Obtener el ID del rol "Usuario"
            $sql_rol = "SELECT id_rol FROM roles WHERE nombre_rol = 'Usuario'";
            $result_rol = $conn->query($sql_rol);

            if ($result_rol->num_rows > 0) {
                $row_rol = $result_rol->fetch_assoc();
                $id_rol_usuario = $row_rol['id_rol'];

                // Insertar el nuevo usuario
                $sql = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena, id_rol, estado_usuario) VALUES (?, ?, ?, ?, 'activo')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $nombre_usuario, $correo_electronico, $hashed_password, $id_rol_usuario);

                if ($stmt->execute()) {
                    unset($_SESSION['temp_registro']);
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Registro exitoso. Puede iniciar sesión ahora.'
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
            'message' => 'Datos de verificación no disponibles.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método de solicitud inválido.'
    ]);
}

$conn->close();