<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include '../includes/conexion.php';


header('Content-Type: application/json');

$response = array();

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

                    // Configuración del correo
                    $mail->setFrom('no-reply@yourdomain.com', 'YourAppName');
                    $mail->addAddress($correo_electronico);
                    $mail->isHTML(true);
                    $mail->Subject = 'Restablecer Contraseña';
       
                    $reset_link = "http://localhost/exterminio1/reset_password.php?token=" . $token;
                    
                    $mail->Body = "Haz clic en el siguiente enlace para restablecer tu contraseña: <a href='$reset_link'>$reset_link</a>";

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
