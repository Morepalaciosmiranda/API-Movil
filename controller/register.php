<?php
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $correo_electronico = mysqli_real_escape_string($conn, $_POST['correo_electronico']);
    $contraseña = mysqli_real_escape_string($conn, $_POST['contrasena']);

    // Validación del nombre de usuario para que no contenga espacios
    if (strpos($nombre_usuario, ' ') !== false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El nombre de usuario no puede contener espacios.'
        ]);
        exit();
    }

    // Validación de longitud del nombre de usuario (entre 6 y 12 caracteres)
    if (strlen($nombre_usuario) < 6 || strlen($nombre_usuario) > 12) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El nombre de usuario debe tener entre 6 y 12 caracteres.'
        ]);
        exit();
    }

    // Validación del formato del correo electrónico
    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El formato del correo electrónico no es válido.'
        ]);
        exit();
    }

    // Validación de la contraseña
    if (!preg_match('/^(?=.*[A-Z]).{8,}$/', $contraseña)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'La contraseña debe tener al menos 8 caracteres e incluir una letra mayúscula.'
        ]);
        exit();
    }

    // Verificación de existencia previa de usuario o correo electrónico
    $sql_check = "SELECT * FROM usuarios WHERE nombre_usuario = ? OR correo_electronico = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $nombre_usuario, $correo_electronico);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El nombre de usuario o el correo electrónico ya están registrados.'
        ]);
        exit();
    }

    $stmt_check->close();

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
            session_start();
            $_SESSION['correo_electronico'] = $correo_electronico;
            echo json_encode([
                'status' => 'success',
                'message' => 'Registro exitoso. Redirigiendo al inicio de sesión...'
            ]);
            exit();
        } else {
            error_log("Error en el registro: " . $stmt->error);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error en el registro: ' . $stmt->error
            ]);
        }
    } else {
        error_log('No se encontró el rol "Usuario" en la base de datos.');
        echo json_encode([
            'status' => 'error',
            'message' => 'No se encontró el rol "Usuario" en la base de datos.'
        ]);
    }

    $stmt->close();
}

$conn->close();
?>