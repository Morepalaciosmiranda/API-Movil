<?php



include_once('../includes/conexion.php');


$idPedido = $_GET['idPedido'];

$sql_detalles_pedido = "SELECT * FROM detalle_pedido WHERE id_pedido = '$idPedido'";
$result_detalles_pedido = mysqli_query($conn, $sql_detalles_pedido);


$sql_cliente = "SELECT nombre, direccion, barrio, telefono FROM detalle_pedido WHERE id_pedido = '$idPedido' LIMIT 1";
$result_cliente = mysqli_query($conn, $sql_cliente);

$response = array();

if (mysqli_num_rows($result_detalles_pedido) > 0 && mysqli_num_rows($result_cliente) > 0) {
  
    $detalles_pedido = "<ul>";
    while ($row_detalles_pedido = mysqli_fetch_assoc($result_detalles_pedido)) {
        $detalles_pedido .= "<li>ID Producto: " . $row_detalles_pedido['id_producto'] . "</li>";
        $detalles_pedido .= "<li>Nombre: " . $row_detalles_pedido['nombre'] . "</li>";
        $detalles_pedido .= "<li>Cantidad: " . $row_detalles_pedido['cantidad'] . "</li>";
        $detalles_pedido .= "<li>Valor Unitario: " . $row_detalles_pedido['valor_unitario'] . "</li>";
        $detalles_pedido .= "<li>Subtotal: " . $row_detalles_pedido['subtotal'] . "</li>";
    }
    $detalles_pedido .= "</ul>";


    $cliente = mysqli_fetch_assoc($result_cliente);

    $response['success'] = true;
    $response['detalles'] = $detalles_pedido;
    $response['cliente'] = $cliente;
} else {
 
    $response['success'] = false;
    $response['message'] = "No hay detalles disponibles para este pedido.";
}

echo json_encode($response);
?>