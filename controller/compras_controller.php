<?php
include '../includes/conexion.php';

// ... (código anterior)

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['nombre_insumo'], $_POST['fecha_compra'], $_POST['total_compra'], $_POST['cantidad'], $_POST['marca'])) {
    $id_proveedor = $_POST['id_proveedor'];
    $nombre_insumo = $_POST['nombre_insumo'];
    $fecha_compra = $_POST['fecha_compra'];
    $total_compra = $_POST['total_compra'];
    $cantidad = $_POST['cantidad'];
    $marca = $_POST['marca'];

    $conn->begin_transaction();

    try {
        // Insertar la compra
        $insert_compra_sql = "INSERT INTO compras (id_proveedor, marca, fecha_compra, total_compra) VALUES (?, ?, ?, ?)";
        $stmt_compra = $conn->prepare($insert_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de inserción en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("issd", $id_proveedor, $marca, $fecha_compra, $total_compra)) {
            throw new Exception("Error al enlazar parámetros de compra: " . $stmt_compra->error);
        }

        if (!$stmt_compra->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción en compras: " . $stmt_compra->error);
        }

        $id_compra = $conn->insert_id;
        // Insertar el insumo
        $insert_insumo_sql = "INSERT INTO insumos (cantidad, fecha_vencimiento, estado_insumo, id_proveedor, id_compra) VALUES (?, DATE_ADD(?, INTERVAL 1 YEAR), 'Buen Estado', ?, ?)";
        $stmt_insumo = $conn->prepare($insert_insumo_sql);
        if (!$stmt_insumo) {
            throw new Exception("Error al preparar la consulta de inserción en insumos: " . $conn->error);
        }

        if (!$stmt_insumo->bind_param("isii", $cantidad, $fecha_compra, $id_proveedor, $id_compra)) {
            throw new Exception("Error al enlazar parámetros de insumo: " . $stmt_insumo->error);
        }

        if (!$stmt_insumo->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción en insumos: " . $stmt_insumo->error);
        }

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra e insumo agregados exitosamente.']);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al procesar la compra e insumo: " . $e->getMessage()]);
        exit();
    }
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id_compra'], $_POST['edit_id_proveedor'], $_POST['edit_id_insumo'], $_POST['edit_fecha_compra'], $_POST['edit_total_compra'], $_POST['edit_cantidad'], $_POST['edit_marca'])) {
    $id_compra = $_POST['edit_id_compra'];
    $id_proveedor = $_POST['edit_id_proveedor'];
    $id_insumo = $_POST['edit_id_insumo'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $total_compra = $_POST['edit_total_compra'];
    $cantidad = $_POST['edit_cantidad'];
    $marca = $_POST['edit_marca'];

    $conn->begin_transaction();

    try {
        $update_compra_sql = "UPDATE compras SET id_proveedor = ?, id_insumo = ?, fecha_compra = ?, total_compra = ?, cantidad = ?, marca = ? WHERE id_compra = ?";
        $stmt_compra = $conn->prepare($update_compra_sql);
        if (!$stmt_compra) {
            throw new Exception("Error al preparar la consulta de actualización en compras: " . $conn->error);
        }

        if (!$stmt_compra->bind_param("iisddsi", $id_proveedor, $id_insumo, $fecha_compra, $total_compra, $cantidad, $marca, $id_compra)) {
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


function obtenerCompras() {
    global $conn;
    $compras = array();

    $consulta = "SELECT c.*, p.nombre_proveedor, i.nombre_insumo 
                 FROM compras c 
                 JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                 JOIN insumos i ON c.id_insumo = i.id_insumo
                 ORDER BY c.fecha_compra DESC";
    $resultado = $conn->query($consulta);

    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $compras[] = $row;
        }
    }

    return $compras;
}

function buscarComprasPorFecha($fecha) {
    global $conn;
    $compras = array();

    $consulta = "SELECT c.*, p.nombre_proveedor, i.nombre_insumo 
                 FROM compras c 
                 JOIN proveedores p ON c.id_proveedor = p.id_proveedor
                 JOIN insumos i ON c.id_insumo = i.id_insumo
                 WHERE DATE(c.fecha_compra) = ?
                 ORDER BY c.fecha_compra DESC";
    
    $stmt = $conn->prepare($consulta);
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $compras[] = $row;
        }
    }

    return $compras;
}

$conn->close();
?>
