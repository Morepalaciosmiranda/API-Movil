<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Función para enviar el correo de recuperación de contraseña
function enviarCorreoRecuperacion($correo_electronico, $token)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($correo_electronico);

        $mail->isHTML(true);
        $mail->Subject = '=?UTF-8?B?' . base64_encode('Recuperación de contraseña - Exterminio') . '?=';

        // URL de recuperación (ajusta según tu configuración)
        $url_recuperacion = $_ENV['APP_URL'] . "/reset-password.php?token=" . $token;

        $mail->Body = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playwrite+TZ:wght@100..400&display=swap');
        body { font-family: 'Playwrite TZ', Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
        .header { background-color: #000000; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; background-color: white; }
        .button { display: inline-block; padding: 10px 20px; background-color: #ec6e19; color: white; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <img src='https://exterminio-ap2w.onrender.com/img/LogoExterminio.png' alt='Logo de Exterminio' style='max-width: 200px;'>
        </div>
        <div class='content'>
            <h2>Recuperación de contraseña</h2>
            <p>Hola,</p>
            <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para crear una nueva contraseña:</p>
            <p style='text-align: center;'>
                <a href='$url_recuperacion' class='button'>Restablecer Contraseña</a>
            </p>
            <p>Si no has solicitado este cambio, por favor ignora este correo.</p>
            <p>Este enlace expirará en 1 hora por razones de seguridad.</p>
        </div>
        <div class='footer'>
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>
";

        $mail->AltBody = "Para restablecer tu contraseña, visita el siguiente enlace: $url_recuperacion";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de recuperación: " . $mail->ErrorInfo);
        return false;
    }
}

// Uso de la función (ejemplo)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo_electronico = $_POST['correo_electronico'];
    $token = bin2hex(random_bytes(32)); // Genera un token seguro

    // Aquí deberías guardar el token en tu base de datos junto con el correo y una marca de tiempo

    if (enviarCorreoRecuperacion($correo_electronico, $token)) {
        echo json_encode(['success' => true, 'message' => 'Correo de recuperación enviado.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al enviar el correo de recuperación.']);
    }
}
