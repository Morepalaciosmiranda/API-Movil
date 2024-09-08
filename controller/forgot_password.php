<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
include '../includes/conexion.php';

// Configurar encabezados CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Activar reporte de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

$response = array('status' => 'error', 'message' => 'Ocurrió un error desconocido');

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $correo_electronico = mysqli_real_escape_string($conn, $_POST['correo_electronico']);

        $sql = "SELECT id_usuario FROM usuarios WHERE correo_electronico='$correo_electronico'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(50));
            $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $sql = "INSERT INTO password_resets (correo_electronico, token, expira) VALUES ('$correo_electronico', '$token', '$expira')";
            if ($conn->query($sql) === TRUE) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username   = 'palaciosmirandayefersondavid@gmail.com'; 
                    $mail->Password   = 'taxlytzuvarmcodg'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('no-reply@yourdomain.com', 'Exterminio');
                    $mail->addAddress($correo_electronico);
                    $mail->isHTML(true);
                    $mail->Subject = '=?UTF-8?B?'.base64_encode('Recuperación de contraseña - Exterminio').'?=';

                    $reset_link = "https://exterminiofastfood.onrender.com/reset_password.php?token=" . $token;
                    
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
                                <img src='https://exterminiofastfood.onrender.com/img/LogoExterminio.png' alt='Logo de Exterminio' style='max-width: 200px;'>
                            </div>
                            <div class='content'>
                                <h2>Recuperación de contraseña</h2>
                                <p>Hola,</p>
                                <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente botón para crear una nueva contraseña:</p>
                                <p style='text-align: center;'>
                                    <a href='$reset_link' class='button'>Restablecer Contraseña</a>
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

                    $mail->AltBody = "Para restablecer tu contraseña, visita el siguiente enlace: $reset_link";

                    $mail->send();
                    $response['status'] = 'success';
                    $response['message'] = 'Correo de restablecimiento de contraseña enviado.';
                } catch (Exception $e) {
                    $response['status'] = 'error';
                    $response['message'] = "Error al enviar el correo: {$mail->ErrorInfo}";
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error al guardar el token en la base de datos.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'El correo electrónico no está registrado.';
        }

        $conn->close();
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Método de solicitud no permitido.';
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Ocurrió un error en el servidor.';
}

echo json_encode($response);
?>