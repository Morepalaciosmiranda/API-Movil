<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

include '../includes/conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $correo_electronico = mysqli_real_escape_string($conn, $_POST['correo_electronico']);
    $contraseña = mysqli_real_escape_string($conn, $_POST['contrasena']);

    // Validación del nombre de usuario
    if (strpos($nombre_usuario, ' ') !== false || strlen($nombre_usuario) < 6 || strlen($nombre_usuario) > 12) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El nombre de usuario debe tener entre 6 y 12 caracteres y no puede contener espacios.'
        ]);
        exit();
    }

    // Validación del correo electrónico
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
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'palaciosmirandayefersondavid@gmail.com';
        $mail->Password   = 'lnjreeskidkcfqpc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('palaciosmirandayefersondavid@gmail.com', 'ExterminioFull');
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
            'message' => "Error al enviar el correo: " . $e->getMessage()
        ]);
        error_log("Error al enviar correo: " . $e->getMessage());
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método de solicitud inválido.'
    ]);
}

$output = ob_get_clean();

if (empty($output)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se recibió respuesta del servidor'
    ]);
} else {
    if (!isJson($output)) {
        echo json_encode([
            'status' => 'error',
            'message' => $output
        ]);
    } else {
        echo $output;
    }
}

$conn->close();

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
?>