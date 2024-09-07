<?php
header('Content-Type: application/json');
include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_proveedor'], $_POST['nombre_del_insumo'], $_POST['marca'], $_POST['cantidad'], $_POST['fecha_compra'], $_POST['total_compra'])) {
    try {
        $id_proveedor = $_POST['id_proveedor'];
        $nombre_insumo = $_POST['nombre_del_insumo'];
        $marca = $_POST['marca'];
        $cantidad = $_POST['cantidad'];
        $fecha_compra = $_POST['fecha_compra'];
        $total_compra = $_POST['total_compra'];

        // Imprime los valores para verificar
        echo "Valores a insertar: \n";
        echo "id_proveedor: $id_proveedor\n";
        echo "nombre_insumo: $nombre_insumo\n";
        echo "marca: $marca\n";
        echo "cantidad: $cantidad\n";
        echo "fecha_compra: $fecha_compra\n";
        echo "total_compra: $total_compra\n";

        // Validación de la fecha
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha_compra)) {
            throw new Exception('El formato de la fecha debe ser YYYY-MM-DD');
        }

        // Convertir la fecha a un objeto DateTime para asegurar que es válida
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_compra);
        if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha_compra) {
            throw new Exception('La fecha proporcionada no es válida');
        }

        // Primero, insertamos o actualizamos el insumo
        $insert_insumo_sql = "INSERT INTO insumos (nombre_del_insumo, cantidad) VALUES (?, ?) ON DUPLICATE KEY UPDATE id_insumo = LAST_INSERT_ID(id_insumo), cantidad = cantidad + VALUES(cantidad)";
        $insert_insumo_stmt = $conn->prepare($insert_insumo_sql);
        if (!$insert_insumo_stmt) {
            throw new Exception('Error al preparar la consulta de insumo: ' . $conn->error);
        }
        $insert_insumo_stmt->bind_param("si", $nombre_insumo, $cantidad);
        if (!$insert_insumo_stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta de insumo: ' . $insert_insumo_stmt->error);
        }
        $id_insumo = $insert_insumo_stmt->insert_id;

        echo "ID del insumo insertado/actualizado: $id_insumo\n";

        // Ahora insertamos la compra
        $insert_sql = "INSERT INTO compras (id_proveedor, id_insumo, nombre_del_insumo, marca, cantidad, fecha_compra, total_compra) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception('Error al preparar la consulta de inserción: ' . $conn->error);
        }
        $insert_stmt->bind_param("iissids", $id_proveedor, $id_insumo, $nombre_insumo, $marca, $cantidad, $fecha_compra, $total_compra);
        if (!$insert_stmt->execute()) {
            throw new Exception('Error al ejecutar la consulta de inserción: ' . $insert_stmt->error);
        }

        echo json_encode(['success' => true, 'message' => 'Compra agregada exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getProveedores') {
    try {
        $sql = "SELECT id_proveedor, nombre_proveedor FROM proveedores";
        $result = $conn->query($sql);

        if ($result === false) {
            throw new Exception("Error al ejecutar la consulta: " . $conn->error);
        }

        $proveedores = [];
        while ($row = $result->fetch_assoc()) {
            $proveedores[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $proveedores]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Si ninguna de las condiciones anteriores se cumple, enviar un error
echo json_encode(['success' => false, 'message' => 'Solicitud no válida o acción no reconocida']);

// Agregar un nuevo endpoint para obtener los insumos
// if (isset($_GET['action']) && $_GET['action'] == 'getInsumos') {
//     $sql = "SELECT DISTINCT id_insumo, nombre_del_insumo, cantidad FROM compras";
//     $result = $conn->query($sql);
//     $insumos = [];
//     while ($row = $result->fetch_assoc()) {
//         $insumos[] = $row;
//     }
//     echo json_encode($insumos);
//     exit;
// }

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
    $nombre_insumo = $_POST['edit_nombre_del_insumo'];
    $marca = $_POST['edit_marca'];
    $cantidad = $_POST['edit_cantidad'];
    $fecha_compra = $_POST['edit_fecha_compra'];
    $total_compra = $_POST['edit_total_compra'];

    $conn->begin_transaction();

    try {
        // Primero, actualizamos el insumo
        $update_insumo_sql = "UPDATE insumos SET nombre_del_insumo = ? WHERE id_insumo = (SELECT id_insumo FROM compras WHERE id_compra = ?)";
        $update_insumo_stmt = $conn->prepare($update_insumo_sql);
        $update_insumo_stmt->bind_param("si", $nombre_insumo, $id_compra);
        $update_insumo_stmt->execute();

        // Obtenemos la información de la compra original
        $select_compra_sql = "SELECT id_insumo, cantidad FROM compras WHERE id_compra = ?";
        $select_compra_stmt = $conn->prepare($select_compra_sql);
        $select_compra_stmt->bind_param("i", $id_compra);
        $select_compra_stmt->execute();
        $result = $select_compra_stmt->get_result();
        $compra_original = $result->fetch_assoc();

        // Actualizamos la cantidad en la tabla insumos (restamos la cantidad original y sumamos la nueva)
        $update_insumo_cantidad_sql = "UPDATE insumos SET cantidad = cantidad - ? + ? WHERE id_insumo = ?";
        $update_insumo_cantidad_stmt = $conn->prepare($update_insumo_cantidad_sql);
        $update_insumo_cantidad_stmt->bind_param("iii", $compra_original['cantidad'], $cantidad, $compra_original['id_insumo']);
        $update_insumo_cantidad_stmt->execute();

        // Actualizamos la compra
        $update_compra_sql = "UPDATE compras SET id_proveedor = ?, marca = ?, cantidad = ?, fecha_compra = ?, total_compra = ? WHERE id_compra = ?";
        $update_compra_stmt = $conn->prepare($update_compra_sql);
        $update_compra_stmt->bind_param("isidsi", $id_proveedor, $marca, $cantidad, $fecha_compra, $total_compra, $id_compra);
        $update_compra_stmt->execute();

        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Compra actualizada exitosamente.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => "Error al actualizar la compra: " . $e->getMessage()]);
    }
    exit();
}

$consulta_compras = "SELECT c.id_compra, p.nombre_proveedor, c.nombre_del_insumo, c.marca, c.cantidad, c.fecha_compra, c.total_compra 
FROM compras c
JOIN proveedores p ON c.id_proveedor = p.id_proveedor
ORDER BY c.fecha_compra DESC";
$resultado_compras = $conn->query($consulta_compras);

if ($resultado_compras === false) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $conn->error]);
    exit;
}

$compras = array();
while ($row = $resultado_compras->fetch_assoc()) {
    $compras[] = $row;
}

echo json_encode([
    'success' => true,
    'compras' => $compras,
    'num_rows' => $resultado_compras->num_rows,
    'query' => $consulta_compras
]);
exit;

$conn->close();
