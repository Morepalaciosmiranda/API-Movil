<?php
session_start();
include '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pedido = $_POST['id_pedido'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Verificamos el tiempo transcurrido desde que se realizó el pedido
    $sql_tiempo = "SELECT TIMESTAMPDIFF(SECOND, timestamp_pedido, NOW()) as segundos_desde_pedido, estado_pedido FROM pedidos WHERE id_pedido = ?";
    $stmt_tiempo = $conn->prepare($sql_tiempo);
    $stmt_tiempo->bind_param("i", $id_pedido);
    $stmt_tiempo->execute();
    $result_tiempo = $stmt_tiempo->get_result();
    $row_tiempo = $result_tiempo->fetch_assoc();

    if ($row_tiempo['segundos_desde_pedido'] < 600 && $row_tiempo['estado_pedido'] != 'Entregado' && $row_tiempo['estado_pedido'] != 'Cancelado') {
        // Proceder con la cancelación
        $sql = "UPDATE pedidos SET estado_pedido = ? WHERE id_pedido = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $id_pedido);

        if ($stmt->execute()) {
            if (!isset($_SESSION['cancelado_exitosamente'])) {
                $_SESSION['cancelado_exitosamente'] = [];
            }
            $_SESSION['cancelado_exitosamente'][] = $id_pedido;

            header("Location: ../configuracion.php?mensaje=pedido_cancelado");
            exit();
        } else {
            header("Location: ../configuracion.php?error=error_cancelacion");
            exit();
        }
    } else {
        if ($row_tiempo['segundos_desde_pedido'] >= 600) {
            header("Location: ../configuracion.php?error=tiempo_excedido");
        } elseif ($row_tiempo['estado_pedido'] == 'Entregado') {
            header("Location: ../configuracion.php?error=pedido_entregado");
        } elseif ($row_tiempo['estado_pedido'] == 'Cancelado') {
            header("Location: ../configuracion.php?error=pedido_ya_cancelado");
        }
        exit();
    }
} else {
    header("Location: ../configuracion.php");
    exit();
}
?>