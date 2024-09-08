<?php
header('Content-Type: application/json');
include_once('../includes/db_utils.php');

try {
    $conn = getValidConnection();

    if (!isset($_GET['nombre_insumo'])) {
        throw new Exception("Nombre de insumo no proporcionado");
    }

    $nombre_insumo = $_GET['nombre_insumo'];
    
    $query = "SELECT marca, cantidad FROM compras WHERE nombre_insumos = ? ORDER BY fecha_compra DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->bind_param("s", $nombre_insumo);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Asegúrate de que la cantidad sea un número
        $cantidad = is_numeric($row['cantidad']) ? floatval($row['cantidad']) : null;
        echo json_encode([
            "marca" => $row['marca'],
            "cantidad" => $cantidad
        ]);
    } else {
        echo json_encode(["marca" => "", "cantidad" => null]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>