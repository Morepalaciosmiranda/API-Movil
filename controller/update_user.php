<?php
include '../includes/conexion.php';
session_start();

$response = array('success' => false);

if (!isset($_SESSION['correo_electronico'])) {
    echo json_encode($response);
    exit();
}

$correo_usuario = $_SESSION['correo_electronico'];

$data = json_decode(file_get_contents("php://input"), true);
$field = $data['field'];
$value = $data['value'];


$sql_check = "SELECT ultima_actualizacion FROM usuarios WHERE correo_electronico = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $correo_usuario);
$stmt_check->execute();
$stmt_check->bind_result($ultima_actualizacion);
$stmt_check->fetch();
$stmt_check->close();

if ($ultima_actualizacion) {
    $current_time = new DateTime();
    $last_update_time = new DateTime($ultima_actualizacion);
    $interval = $current_time->diff($last_update_time);
    $hours_since_last_update = $interval->h + ($interval->days * 24);

    if ($hours_since_last_update < 5) {
        $response['message'] = 'Solo puedes actualizar tu información una vez cada 5 horas.';
        echo json_encode($response);
        exit();
    }
}


if ($field && $value) {
    if ($field == 'nombre_usuario' || $field == 'correo_electronico' || $field == 'contrasena') {
        if ($field == 'nombre_usuario') {
            $sql = "UPDATE usuarios SET nombre_usuario = ?, ultima_actualizacion = NOW() WHERE correo_electronico = ?";
        } elseif ($field == 'correo_electronico') {
            $sql = "UPDATE usuarios SET correo_electronico = ?, ultima_actualizacion = NOW() WHERE correo_electronico = ?";
            $_SESSION['correo_electronico'] = $value; 
        } elseif ($field == 'contrasena') {
            $hash = password_hash($value, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET contrasena = ?, ultima_actualizacion = NOW() WHERE correo_electronico = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hash, $correo_usuario);
        }

        if ($field != 'contrasena') {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $value, $correo_usuario);
        }

        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            error_log("Error al ejecutar la consulta: " . $stmt->error);
        }
        $stmt->close();
    }
}

echo json_encode($response);
?>
