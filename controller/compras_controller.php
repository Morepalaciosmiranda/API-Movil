<?php
include '../includes/conexion.php';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['nombre_insumos'], $_POST['fecha_compra'], $_POST['total_compra'], $_POST['cantidad'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $nombre_insumos = $_POST['nombre_insumos'];
    $fecha_compra = $_POST['fecha_compra'];
    $total_compra = $_POST['total_compra'];
    $cantidad = $_POST['cantidad'];

    $conn->begin_transaction();

    try {
        $insert_compra_sql = "INSERT INTO compras (id_proveedor, nombre_insumos, fecha_compra, total_compra, cantidad) VALUES (?, ?, ?, ?, ?)";
        $stmt_compra = $conn->prepare($insert_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de inserción en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("issdi", $id_proveedor, $nombre_insumos, $fecha_compra, $total_compra, $cantidad)) {
            throw new Exception("Error al enlazar parámetros: " . $stmt_compra->error);
        }

        if (!$stmt_compra->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción en compras: " . $stmt_compra->error);
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra agregada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al procesar la compra: " . $e->getMessage()]);
    }
    exit();
}

if (isset($_GET['eliminar'])) {
    $id_compra = $_GET['eliminar'];

    $conn->begin_transaction();

    try {
        $eliminar_sql = "DELETE FROM compras WHERE id_compra = ?";
        $eliminar_stmt = $conn->prepare($eliminar_sql);
        if (!$eliminar_stmt) {
            throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
        }
        if (!$eliminar_stmt->bind_param("i", $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $eliminar_stmt->error);
        }
        if (!$eliminar_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de eliminación: " . $eliminar_stmt->error);
        }
        $eliminar_stmt->close();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra eliminada exitosamente.']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al eliminar la compra: " . $e->getMessage()]);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id_compra'], $_POST['edit_id_proveedor'], $_POST['edit_nombre_insumos'], $_POST['edit_fecha_compra'], $_POST['edit_total_compra'], $_POST['edit_cantidad'])) {
    $id_compra = $_POST['edit_id_compra'];
    $id_proveedor = $_POST['edit_id_proveedor'];
    $nombre_insumos = $_POST['edit_nombre_insumos'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $total_compra = $_POST['edit_total_compra'];
    $cantidad = $_POST['edit_cantidad'];

    $conn->begin_transaction();

    try {
        $update_compra_sql = "UPDATE compras SET id_proveedor = ?, nombre_insumos = ?, fecha_compra = ?, total_compra = ?, cantidad = ? WHERE id_compra = ?";
        $stmt_compra = $conn->prepare($update_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de actualización en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("issdii", $id_proveedor, $nombre_insumos, $fecha_compra, $total_compra, $cantidad, $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $stmt_compra->error);
        }

        if (!$stmt_compra->execute()) {
            throw new Exception("Error al ejecutar la consulta de actualización en compras: " . $stmt_compra->error);
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra actualizada exitosamente.']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al actualizar la compra: " . $e->getMessage()]);
        exit();
    }
}

$consulta_compras = "SELECT * FROM compras";
$resultado_compras = $conn->query($consulta_compras);

if ($resultado_compras->num_rows > 0) {
    $compras = array();
    while ($row = $resultado_compras->fetch_assoc()) {
        $compras[] = $row;
    }
} else {
    $compras = array();
}


$conn->close();
?>
