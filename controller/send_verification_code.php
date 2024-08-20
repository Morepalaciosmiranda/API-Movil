<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

function sendJsonResponse($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}
include '../includes/conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $correo_electronico = mysqli_real_escape_string($conn, $_POST['correo_electronico']);
    $contraseña = mysqli_real_escape_string($conn, $_POST['contrasena']);

    // Generar código de verificación
    $codigo_verificacion = rand(100000, 999999);

    // Guardar datos temporalmente en la sesión
    session_start();
    $_SESSION['temp_register_data'] = [
        'nombre_usuario' => $nombre_usuario,
        'correo_electronico' => $correo_electronico,
        'contrasena' => $contraseña,
        'codigo_verificacion' => $codigo_verificacion
    ];

    // Enviar correo con el código de verificación
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'palaciosmirandayefersondavid@gmail.com';
        $mail->Password   = 'cjcy fcje kkoz hdfq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('palaciosmirandayefersondavid@gmail.com', 'Exterminio');
        $mail->addAddress($correo_electronico);

        $mail->isHTML(true);
        $mail->Subject = 'Código de verificación para registro';
        $mail->Body    = "Tu código de verificación es: <b>$codigo_verificacion</b>";

        $mail->send();

        echo json_encode([
            'status' => 'success',
            'message' => 'Código de verificación enviado.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => "No se pudo enviar el correo. Error: {$mail->ErrorInfo}"
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método de solicitud inválido.'
    ]);
}