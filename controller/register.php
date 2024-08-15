<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Incluir el archivo de conexión
include '../includes/conexion.php';

require '../phpmailer/PHPMailer.php';
require '../phpmailer/SMTP.php';
require '../phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Verificar la conexión
if (!$conn) {
    error_log("Error de conexión a la base de datos: " . mysqli_connect_error());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexión a la base de datos. Por favor, inténtelo más tarde.'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $correo_electronico = mysqli_real_escape_string($conn, $_POST['correo_electronico']);
    $contraseña = mysqli_real_escape_string($conn, $_POST['contrasena']);

    // Validaciones
    if (strpos($nombre_usuario, ' ') !== false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El nombre de usuario no puede contener espacios.'
        ]);
        exit();
    }

    if (strlen($nombre_usuario) < 6 || strlen($nombre_usuario) > 12) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El nombre de usuario debe tener entre 6 y 12 caracteres.'
        ]);
        exit();
    }

    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El formato del correo electrónico no es válido.'
        ]);
        exit();
    }

    if (!preg_match('/^(?=.*[A-Z]).{8,}$/', $contraseña)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'La contraseña debe tener al menos 8 caracteres e incluir una letra mayúscula.'
        ]);
        exit();
    }

    try {
        // Verificación de existencia previa de usuario o correo electrónico
        $sql_check = "SELECT * FROM usuarios WHERE nombre_usuario = ? OR correo_electronico = ?";
        $stmt_check = $conn->prepare($sql_check);
        if (!$stmt_check) {
            throw new Exception("Error preparando la consulta: " . $conn->error);
        }
        $stmt_check->bind_param("ss", $nombre_usuario, $correo_electronico);
        if (!$stmt_check->execute()) {
            throw new Exception("Error ejecutando la consulta: " . $stmt_check->error);
        }
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'El nombre de usuario o el correo electrónico ya están registrados.'
            ]);
            exit();
        }

        $stmt_check->close();

        // Generar código de verificación
        $codigo_verificacion = rand(100000, 999999);

        // Guardar datos temporalmente en la sesión
        session_start();
        $_SESSION['temp_registro'] = [
            'nombre_usuario' => $nombre_usuario,
            'correo_electronico' => $correo_electronico,
            'contraseña' => password_hash($contraseña, PASSWORD_DEFAULT),
            'codigo_verificacion' => $codigo_verificacion
        ];

        // Enviar correo de verificación
        if (enviar_correo_verificacion($correo_electronico, $codigo_verificacion)) {
            echo json_encode([
                'status' => 'verification_needed',
                'message' => 'Se ha enviado un código de verificación a su correo electrónico.'
            ]);
        } else {
            throw new Exception("Error al enviar el correo de verificación.");
        }
    } catch (Exception $e) {
        error_log("Error en el proceso de registro: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Ocurrió un error en el servidor. Por favor, inténtelo de nuevo más tarde.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método de solicitud inválido.'
    ]);
}

$conn->close();

function enviar_correo_verificacion($correo, $codigo) {
    $mail = new PHPMailer(true);

    try {
        //Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Cambia esto al servidor SMTP que uses
        $mail->SMTPAuth   = true;
        $mail->Username   = 'palaciosmirandayefersondavid@gmail.com'; // SMTP username
        $mail->Password   = 'daak kwzv olrb ygfd'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Destinatarios
        $mail->setFrom('palaciosmirandayefersondavid@gmail.com', 'Exterminio');
        $mail->addAddress($correo);

        //Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Verificación de Correo Electrónico - Exterminio';
        $mail->Body    = "Tu código de verificación es: <b>$codigo</b>";

        $mail->send();
        error_log("Correo enviado exitosamente a: " . $correo);
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo a {$correo}: " . $mail->ErrorInfo);
        return false;
    }
}
?>