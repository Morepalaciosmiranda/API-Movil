<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pedido = $_POST['id_pedido'];
    $nuevo_estado = $_POST['nuevo_estado'];

    $sql = "UPDATE pedidos SET estado_pedido = ? WHERE id_pedido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);

    if ($stmt->execute()) {
  
        if (!isset($_SESSION['cancelado_exitosamente'])) {
            $_SESSION['cancelado_exitosamente'] = [];
        }
        $_SESSION['cancelado_exitosamente'][] = $id_pedido;

        header("Location: ../configuracion.php");
        exit();
    } else {
        echo "Error al cancelar el pedido.";
    }
}
?>
