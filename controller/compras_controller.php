<?php
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_usuario'], $_POST['id_proveedor'], $_POST['fecha_compra'], $_POST['subtotal'], $_POST['total_compra'], $_POST['cantidad'], $_POST['valor_unitario'])) {
    $id_usuario = $_POST['id_usuario'];
    $id_proveedor = $_POST['id_proveedor'];
    $fecha_compra = $_POST['fecha_compra'];
    $subtotal = $_POST['subtotal'];
    $total_compra = $_POST['total_compra'];

    $conn->begin_transaction();

    try {
        $insert_compra_sql = "INSERT INTO compras (id_usuario, id_proveedor, fecha_compra, subtotal, total_compra) VALUES (?, ?, ?, ?, ?)";
        $stmt_compra = $conn->prepare($insert_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de inserción en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("iissd", $id_usuario, $id_proveedor, $fecha_compra, $subtotal, $total_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $stmt_compra->error);
        }

        if (!$stmt_compra->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción en compras: " . $stmt_compra->error);
        }

        $id_compra = $conn->insert_id;

        $cantidad = $_POST['cantidad'];
        $valor_unitario = $_POST['valor_unitario'];

        $insert_detalle_sql = "INSERT INTO detalle_compras (id_compra, cantidad, valor_unitario) VALUES (?, ?, ?)";
        $stmt_detalle = $conn->prepare($insert_detalle_sql);
        if (!$stmt_detalle) {
            throw new Exception("Error al preparar la consulta de inserción en detalle_compras: " . $conn->error);
        }

        if (!$stmt_detalle->bind_param("iid", $id_compra, $cantidad, $valor_unitario)) {
            throw new Exception("Error al enlazar parámetros: " . $stmt_detalle->error);
        }

        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción en detalle_compras: " . $stmt_detalle->error);
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra agregada exitosamente.']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al procesar la compra: " . $e->getMessage()]);
        exit();
    }
}

if (isset($_GET['eliminar'])) {
    $id_compra = $_GET['eliminar'];

    $conn->begin_transaction();

    try {
        $eliminar_detalles_sql = "DELETE FROM detalle_compras WHERE id_compra = ?";
        $eliminar_detalles_stmt = $conn->prepare($eliminar_detalles_sql);
        if (!$eliminar_detalles_stmt) {
            throw new Exception("Error al preparar la consulta de eliminación de detalles: " . $conn->error);
        }
        if (!$eliminar_detalles_stmt->bind_param("i", $id_compra)) {
            throw new Exception("Error al enlazar parámetros: " . $eliminar_detalles_stmt->error);
        }
        if (!$eliminar_detalles_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de eliminación de detalles: " . $eliminar_detalles_stmt->error);
        }
        $eliminar_detalles_stmt->close();

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id_compra'], $_POST['edit_id_usuario'], $_POST['edit_id_proveedor'], $_POST['edit_fecha_compra'], $_POST['edit_subtotal'], $_POST['edit_total_compra'])) {
    $id_compra = $_POST['edit_id_compra'];
    $id_usuario = $_POST['edit_id_usuario'];
    $id_proveedor = $_POST['edit_id_proveedor'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $subtotal = $_POST['edit_subtotal'];
    $total_compra = $_POST['edit_total_compra'];

    $conn->begin_transaction();

    try {
        $update_compra_sql = "UPDATE compras SET id_usuario = ?, id_proveedor = ?, fecha_compra = ?, subtotal = ?, total_compra = ? WHERE id_compra = ?";
        $stmt_compra = $conn->prepare($update_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de actualización en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("iissdi", $id_usuario, $id_proveedor, $fecha_compra, $subtotal, $total_compra, $id_compra)) {
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