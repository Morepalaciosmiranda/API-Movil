<?php
include '../includes/conexion.php';

if (isset($_GET['action']) && $_GET['action'] == 'getInsumos') {
    $sql = "SELECT DISTINCT id_insumo, nombre_insumo, cantidad FROM compras";
    $result = $conn->query($sql);
    $insumos = [];
    while ($row = $result->fetch_assoc()) {
        $insumos[] = $row;
    }
    echo json_encode($insumos);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['id_insumo'], $_POST['marca'], $_POST['cantidad'], $_POST['fecha_compra'], $_POST['total_compra'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $id_insumo = $_POST['id_insumo'];
    $marca = $_POST['marca'];
    $cantidad = $_POST['cantidad'];
    $fecha_compra = $_POST['fecha_compra'];
    $total_compra = $_POST['total_compra'];

    $conn->begin_transaction();

    try {
        // Insertamos la compra
        $insert_compra_sql = "INSERT INTO compras (id_proveedor, id_insumo, marca, cantidad, fecha_compra, total_compra) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_compra_stmt = $conn->prepare($insert_compra_sql);
        $insert_compra_stmt->bind_param("iisids", $id_proveedor, $id_insumo, $marca, $cantidad, $fecha_compra, $total_compra);
        $insert_compra_stmt->execute();
        $id_compra = $conn->insert_id;

        // Actualizamos o insertamos en la tabla insumos
        $update_insumo_sql = "INSERT INTO insumos (id_insumo, cantidad, id_proveedor, id_compra) 
                              VALUES (?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              cantidad = cantidad + VALUES(cantidad), 
                              id_proveedor = VALUES(id_proveedor), 
                              id_compra = VALUES(id_compra)";
        $update_insumo_stmt = $conn->prepare($update_insumo_sql);
        $update_insumo_stmt->bind_param("iiii", $id_insumo, $cantidad, $id_proveedor, $id_compra);
        $update_insumo_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra agregada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al procesar la compra: " . $e->getMessage()]);
    }
    exit();
}

// Agregar un nuevo endpoint para obtener los insumos
if (isset($_GET['action']) && $_GET['action'] == 'getInsumos') {
    $sql = "SELECT DISTINCT id_insumo, nombre_insumo FROM compras";
    $result = $conn->query($sql);
    $insumos = [];
    while ($row = $result->fetch_assoc()) {
        $insumos[] = $row;
    }
    echo json_encode($insumos);
    exit;
}
        

if (isset($_GET['eliminar'])) {
    $id_compra = $_GET['eliminar'];

    $conn->begin_transaction();

    try {
        // Primero, obtenemos la información de la compra
        $select_compra_sql = "SELECT id_insumo, cantidad FROM compras WHERE id_compra = ?";
        $select_compra_stmt = $conn->prepare($select_compra_sql);
        $select_compra_stmt->bind_param("i", $id_compra);
        $select_compra_stmt->execute();
        $result = $select_compra_stmt->get_result();
        $compra = $result->fetch_assoc();

        // Actualizamos la cantidad en la tabla insumos
        $update_insumo_sql = "UPDATE insumos SET cantidad = cantidad - ? WHERE id_insumo = ?";
        $update_insumo_stmt = $conn->prepare($update_insumo_sql);
        $update_insumo_stmt->bind_param("ii", $compra['cantidad'], $compra['id_insumo']);
        $update_insumo_stmt->execute();

        // Eliminamos la compra
        $eliminar_sql = "DELETE FROM compras WHERE id_compra = ?";
        $eliminar_stmt = $conn->prepare($eliminar_sql);
        $eliminar_stmt->bind_param("i", $id_compra);
        $eliminar_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra eliminada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al eliminar la compra: " . $e->getMessage()]);
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id_compra'])) {
    $id_compra = $_POST['edit_id_compra'];
    $id_proveedor = $_POST['edit_id_proveedor'];
    $nombre_insumo = $_POST['edit_nombre_insumo'];
    $marca = $_POST['edit_marca'];
    $cantidad = $_POST['edit_cantidad'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $total_compra = $_POST['edit_total_compra'];

    $conn->begin_transaction();

    try {
        // Primero, actualizamos o insertamos el insumo
        $insert_insumo_sql = "INSERT INTO insumos (nombre_insumo) VALUES (?) ON DUPLICATE KEY UPDATE id_insumo = LAST_INSERT_ID(id_insumo)";
        $insert_insumo_stmt = $conn->prepare($insert_insumo_sql);
        $insert_insumo_stmt->bind_param("s", $nombre_insumo);
        $insert_insumo_stmt->execute();
        $id_insumo = $insert_insumo_stmt->insert_id;

        // Obtenemos la información de la compra original
        $select_compra_sql = "SELECT id_insumo, cantidad FROM compras WHERE id_compra = ?";
        $select_compra_stmt = $conn->prepare($select_compra_sql);
        $select_compra_stmt->bind_param("i", $id_compra);
        $select_compra_stmt->execute();
        $result = $select_compra_stmt->get_result();
        $compra_original = $result->fetch_assoc();

        // Actualizamos la cantidad en la tabla insumos (restamos la cantidad original y sumamos la nueva)
        $update_insumo_sql = "UPDATE insumos SET cantidad = cantidad - ? + ? WHERE id_insumo = ?";
        $update_insumo_stmt = $conn->prepare($update_insumo_sql);
        $update_insumo_stmt->bind_param("iii", $compra_original['cantidad'], $cantidad, $compra_original['id_insumo']);
        $update_insumo_stmt->execute();

        // Actualizamos la compra
        $update_compra_sql = "UPDATE compras SET id_proveedor = ?, id_insumo = ?, marca = ?, cantidad = ?, fecha_compra = ?, total_compra = ? WHERE id_compra = ?";
        $update_compra_stmt = $conn->prepare($update_compra_sql);
        $update_compra_stmt->bind_param("iisisdi", $id_proveedor, $id_insumo, $marca, $cantidad, $fecha_compra, $total_compra, $id_compra);
        $update_compra_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra actualizada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al actualizar la compra: " . $e->getMessage()]);
    }
    exit();
}

$consulta_compras = "SELECT c.id_compra, p.nombre_proveedor, i.nombre_insumo, c.marca, c.cantidad, c.fecha_compra, c.total_compra 
FROM compras c
JOIN proveedores p ON c.id_proveedor = p.id_proveedor
JOIN insumos i ON c.id_insumo = i.id_insumo
ORDER BY c.fecha_compra DESC";
$resultado_compras = $conn->query($consulta_compras);

if ($resultado_compras->num_rows > 0) {
$compras = array();
while ($row = $resultado_compras->fetch_assoc()) {
$compras[] = $row;
}
echo json_encode(['success' => true, 'compras' => $compras]);
} else {
echo json_encode(['success' => false, 'message' => 'No hay compras disponibles.']);
}

$conn->close();
?>


