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

    // Obtener detalles del pedido y datos del cliente
    $sql = "SELECT * FROM detalle_pedido WHERE id_pedido = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idPedido);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $response = array();

    if ($row = mysqli_fetch_assoc($result)) {
        $cliente = array(
            'nombre' => htmlspecialchars($row['nombre']),
            'direccion' => htmlspecialchars($row['direccion']),
            'barrio' => htmlspecialchars($row['barrio']),
            'telefono' => htmlspecialchars($row['telefono'])
        );

        // Obtener todos los productos del pedido incluyendo el nombre del producto
        $sql_productos = "SELECT dp.*, p.nombre_producto 
                          FROM detalle_pedido dp 
                          JOIN productos p ON dp.id_producto = p.id_producto 
                          WHERE dp.id_pedido = ?";
        $stmt_productos = mysqli_prepare($conn, $sql_productos);
        mysqli_stmt_bind_param($stmt_productos, "i", $idPedido);
        mysqli_stmt_execute($stmt_productos);
        $result_productos = mysqli_stmt_get_result($stmt_productos);

        $detalles_pedido = array();
        $total_compra = 0;

        while ($row_producto = mysqli_fetch_assoc($result_productos)) {
            $detalle = array(
                'nombre_producto' => htmlspecialchars($row_producto['nombre_producto']),
                'cantidad' => htmlspecialchars($row_producto['cantidad']),
                'valor_unitario' => number_format($row_producto['valor_unitario'], 2),
                'subtotal' => number_format($row_producto['subtotal'], 2)
            );
            $detalles_pedido[] = $detalle;
            $total_compra += $row_producto['subtotal'];
        }

        $response['success'] = true;
        $response['detalles'] = $detalles_pedido;
        $response['total_compra'] = number_format($total_compra, 2);
        $response['cliente'] = $cliente;
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
    if (isset($stmt)) mysqli_stmt_close($stmt);
    if (isset($stmt_productos)) mysqli_stmt_close($stmt_productos);
    if (isset($conn)) mysqli_close($conn);
}
?>