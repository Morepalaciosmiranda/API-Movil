<?php
include '../includes/conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../path/to/PHPMailer/src/Exception.php';
require '../path/to/PHPMailer/src/PHPMailer.php';
require '../path/to/PHPMailer/src/SMTP.php';

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

    // Validación de la contraseña (mínimo 8 caracteres, al menos una mayúscula)
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

        // Generar un código de verificación de 6 dígitos
        $codigo_verificacion = mt_rand(100000, 999999);

        // Configurar el correo para enviar el código de verificación
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP de Gmail
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'palaciosmirandayefersondavid@gmail.com'; // Tu correo
            $mail->Password = 'cjcyfcjekkozhdfq'; // Tu contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Remitente y destinatario
            $mail->setFrom('palaciosmirandayefersondavid@gmail.com', 'Exterminio');
            $mail->addAddress($correo_electronico);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Código de verificación';
            $mail->Body    = "Tu código de verificación es <b>$codigo_verificacion</b>";
            $mail->AltBody = "Tu código de verificación es $codigo_verificacion";

            $mail->send();

            // Guardar el código de verificación en la sesión
            session_start();
            $_SESSION['codigo_verificacion'] = $codigo_verificacion;
            $_SESSION['registro_temporal'] = [
                'nombre_usuario' => $nombre_usuario,
                'correo_electronico' => $correo_electronico,
                'contrasena' => $hashed_password,
                'id_rol_usuario' => $id_rol_usuario
            ];

            echo json_encode([
                'status' => 'verification',
                'message' => 'Se ha enviado un código de verificación a tu correo electrónico.'
            ]);
            exit();

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => "El mensaje no pudo ser enviado. Error de correo: {$mail->ErrorInfo}"
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
