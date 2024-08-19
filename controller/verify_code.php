<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_verificacion = $_POST['codigo_verificacion'];

    if ($codigo_verificacion == $_SESSION['codigo_verificacion']) {
        include '../includes/conexion.php';

        $user = $_SESSION['registro_temporal'];

        // Inserción del nuevo usuario en la base de datos
        $sql = "INSERT INTO usuarios (nombre_usuario, correo_electronico, contrasena, id_rol, estado_usuario) VALUES (?, ?, ?, ?, 'activo')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $user['nombre_usuario'], $user['correo_electronico'], $user['contrasena'], $user['id_rol_usuario']);

        if ($stmt->execute()) {
            unset($_SESSION['codigo_verificacion']);
            unset($_SESSION['registro_temporal']);

            echo json_encode([
                'status' => 'success',
                'message' => 'Tu cuenta ha sido verificada exitosamente.'
            ]);
            exit();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error en el registro: ' . $stmt->error
            ]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'El código de verificación es incorrecto.'
        ]);
    }
}
?>
