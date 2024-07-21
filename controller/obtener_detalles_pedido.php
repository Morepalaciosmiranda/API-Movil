<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    include_once('../includes/conexion.php');

    if (!isset($_GET['idPedido'])) {
        throw new Exception("ID de pedido no proporcionado");
    }

    $idPedido = intval($_GET['idPedido']);

    // Obtener detalles del pedido
    $sql_detalles_pedido = "SELECT * FROM detalle_pedido WHERE id_pedido = ?";
    $stmt_detalles = mysqli_prepare($conn, $sql_detalles_pedido);
    mysqli_stmt_bind_param($stmt_detalles, "i", $idPedido);
    mysqli_stmt_execute($stmt_detalles);
    $result_detalles_pedido = mysqli_stmt_get_result($stmt_detalles);

    // Obtener datos del cliente
    $sql_cliente = "SELECT p.nombre_cliente, p.direccion, p.barrio, p.telefono 
                    FROM pedidos p 
                    WHERE p.id_pedido = ?";
    $stmt_cliente = mysqli_prepare($conn, $sql_cliente);
    mysqli_stmt_bind_param($stmt_cliente, "i", $idPedido);
    mysqli_stmt_execute($stmt_cliente);
    $result_cliente = mysqli_stmt_get_result($stmt_cliente);

    $response = array();

    if (mysqli_num_rows($result_detalles_pedido) > 0 && mysqli_num_rows($result_cliente) > 0) {
        $detalles_pedido = "<ul>";
        while ($row_detalles_pedido = mysqli_fetch_assoc($result_detalles_pedido)) {
            $detalles_pedido .= "<li>ID Producto: " . htmlspecialchars($row_detalles_pedido['id_producto']) . "</li>";
            $detalles_pedido .= "<li>Nombre: " . htmlspecialchars($row_detalles_pedido['nombre']) . "</li>";
            $detalles_pedido .= "<li>Cantidad: " . htmlspecialchars($row_detalles_pedido['cantidad']) . "</li>";
            $detalles_pedido .= "<li>Valor Unitario: " . htmlspecialchars($row_detalles_pedido['valor_unitario']) . "</li>";
            $detalles_pedido .= "<li>Subtotal: " . htmlspecialchars($row_detalles_pedido['subtotal']) . "</li>";
        }
        $detalles_pedido .= "</ul>";

        $cliente = mysqli_fetch_assoc($result_cliente);
        
        $response['success'] = true;
        $response['detalles'] = $detalles_pedido;
        $response['cliente'] = array(
            'nombre' => htmlspecialchars($cliente['nombre_cliente']),
            'direccion' => htmlspecialchars($cliente['direccion']),
            'barrio' => htmlspecialchars($cliente['barrio']),
            'telefono' => htmlspecialchars($cliente['telefono'])
        );
    } else {
        throw new Exception("No hay detalles disponibles para este pedido.");
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt_detalles)) mysqli_stmt_close($stmt_detalles);
    if (isset($stmt_cliente)) mysqli_stmt_close($stmt_cliente);
    if (isset($conn)) mysqli_close($conn);
}
?>