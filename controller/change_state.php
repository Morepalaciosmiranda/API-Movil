<?php
session_start();
include '../includes/conexion.php';

if (isset($_POST['user_id']) && isset($_POST['new_state']) && isset($_POST['state_message'])) {
    $user_id = $_POST['user_id'];
    $new_state = $_POST['new_state'];
    $state_message = $_POST['state_message'];

  
    $update_sql = "UPDATE usuarios SET estado_usuario = ?, mensaje_estado = ? WHERE id_usuario = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $new_state, $state_message, $user_id);

    if ($update_stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Estado actualizado correctamente. Mensaje enviado: " . $state_message]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el estado: " . $conn->error]);
    }

    $update_stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
}
?>
