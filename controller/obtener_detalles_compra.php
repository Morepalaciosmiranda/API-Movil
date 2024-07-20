<?php
header('Content-Type: application/json');

if (!isset($_GET['idCompra'])) {
    echo json_encode(['success' => false, 'message' => 'ID de compra no proporcionado.']);
    exit;
}

$idCompra = $_GET['idCompra'];

require_once '../includes/conexion.php'; 


$sql_detalles_compra = "SELECT id_detalle_compra, id_compra, cantidad, valor_unitario 
                        FROM detalle_compras WHERE id_compra = ?";
$stmt = $conn->prepare($sql_detalles_compra);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
    exit;
}

$stmt->bind_param("i", $idCompra);
$stmt->execute();
$result = $stmt->get_result();

$response = array();

if ($result->num_rows > 0) {
    $detalles_compra = "<ul>";
    while ($row_detalles_compra = $result->fetch_assoc()) {
        $detalles_compra .= "<li>ID Detalle Compra: " . $row_detalles_compra['id_detalle_compra'] . "</li>";
        $detalles_compra .= "<li>ID Compra: " . $row_detalles_compra['id_compra'] . "</li>";
        $detalles_compra .= "<li>Cantidad: " . $row_detalles_compra['cantidad'] . "</li>";
        $detalles_compra .= "<li>Valor Unitario: " . $row_detalles_compra['valor_unitario'] . "</li>";
    }
    $detalles_compra .= "</ul>";

    $response['success'] = true;
    $response['detalles'] = $detalles_compra;
} else {
    $response['success'] = false;
    $response['message'] = "No hay detalles disponibles para esta compra.";
}

echo json_encode($response);

$stmt->close();
$conn->close();
?>
