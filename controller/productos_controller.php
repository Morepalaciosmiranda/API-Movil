<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/conexion.php';

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function obtenerProductos() {
    global $conn;
    $productos = [];

    $sql = "SELECT * FROM productos";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }

    return $productos;
}

function procesarProducto() {
    global $conn;
    $respuesta = ['exito' => false, 'mensaje' => ''];

    try {
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $descripcion = $_POST['descripcion'];
        $insumo_ids = $_POST['insumo_id'];
        $cantidades_insumo = $_POST['cantidad_insumo'];

        foreach ($insumo_ids as $index => $insumo_id) {
            $cantidad_insumo = $cantidades_insumo[$index];

            $consulta_cantidad_sql = "SELECT cantidad FROM insumos WHERE id_insumo = ?";
            $consulta_cantidad_stmt = $conn->prepare($consulta_cantidad_sql);
            if (!$consulta_cantidad_stmt) {
                throw new Exception("Error al preparar la consulta de cantidad: " . $conn->error);
            }
            $consulta_cantidad_stmt->bind_param("i", $insumo_id);
            $consulta_cantidad_stmt->execute();
            $consulta_cantidad_stmt->bind_result($cantidad_disponible);
            $consulta_cantidad_stmt->fetch();
            $consulta_cantidad_stmt->close();

            if ($cantidad_insumo > $cantidad_disponible) {
                throw new Exception("No hay suficientes insumos disponibles de ese tipo.");
            }
        }

        $imagen = $_FILES['imagen'];
        $imagen_contenido = file_get_contents($imagen['tmp_name']);
        $imagen_tipo = $imagen['type'];

        $conn->begin_transaction();

        $insert_sql = "INSERT INTO productos (nombre_producto, foto, foto_tipo, descripcion_producto, valor_unitario) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
        }

        if (!$insert_stmt->bind_param("ssssd", $nombre, $imagen_contenido, $imagen_tipo, $descripcion, $precio)) {
            throw new Exception("Error al enlazar parámetros: " . $insert_stmt->error);
        }

        if (!$insert_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción: " . $insert_stmt->error);
        }

        $producto_id = $conn->insert_id;

        foreach ($insumo_ids as $index => $insumo_id) {
            $cantidad_insumo = $cantidades_insumo[$index];

            $update_sql = "UPDATE insumos SET cantidad = cantidad - ? WHERE id_insumo = ?";
            $update_stmt = $conn->prepare($update_sql);
            if (!$update_stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
            }

            if (!$update_stmt->bind_param("ii", $cantidad_insumo, $insumo_id)) {
                throw new Exception("Error al enlazar parámetros: " . $update_stmt->error);
            }

            if (!$update_stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de actualización: " . $update_stmt->error);
            }
        }

        $conn->commit();
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Producto agregado correctamente";
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error al agregar producto: " . $e->getMessage());
        $respuesta['mensaje'] = "Hubo un error al agregar el producto: " . $e->getMessage();
    }

    return $respuesta;
}

function editarProducto() {
    global $conn;
    $respuesta = ['exito' => false, 'mensaje' => ''];

    try {
        $id_editar = $_POST['id_editar'];
        $nombre_editar = $_POST['nombre_edit'];
        $precio_editar = $_POST['precio_edit'];
        $descripcion_editar = $_POST['descripcion_edit'];

        if (isset($_FILES['imagen_edit']) && $_FILES['imagen_edit']['error'] == UPLOAD_ERR_OK) {
            $imagen = $_FILES['imagen_edit'];
            $imagen_contenido = file_get_contents($imagen['tmp_name']);
            $imagen_tipo = $imagen['type'];

            $actualizar_sql = "UPDATE productos SET nombre_producto = ?, descripcion_producto = ?, valor_unitario = ?, foto = ?, foto_tipo = ? WHERE id_producto = ?";
            $actualizar_stmt = $conn->prepare($actualizar_sql);
            if (!$actualizar_stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
            }

            if (!$actualizar_stmt->bind_param("ssdssi", $nombre_editar, $descripcion_editar, $precio_editar, $imagen_contenido, $imagen_tipo, $id_editar)) {
                throw new Exception("Error al enlazar parámetros: " . $actualizar_stmt->error);
            }
        } else {
            $actualizar_sql = "UPDATE productos SET nombre_producto = ?, descripcion_producto = ?, valor_unitario = ? WHERE id_producto = ?";
            $actualizar_stmt = $conn->prepare($actualizar_sql);
            if (!$actualizar_stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
            }

            if (!$actualizar_stmt->bind_param("ssdi", $nombre_editar, $descripcion_editar, $precio_editar, $id_editar)) {
                throw new Exception("Error al enlazar parámetros: " . $actualizar_stmt->error);
            }
        }

        if (!$actualizar_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de actualización: " . $actualizar_stmt->error);
        }

        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Producto editado correctamente";
    } catch (Exception $e) {
        error_log("Error al editar producto: " . $e->getMessage());
        $respuesta['mensaje'] = "Hubo un error al editar el producto: " . $e->getMessage();
    }

    return $respuesta;
}

function eliminarProducto($id_producto) {
    global $conn;
    $respuesta = ['exito' => false, 'mensaje' => ''];

    try {
        $eliminar_sql = "DELETE FROM productos WHERE id_producto = ?";
        $eliminar_stmt = $conn->prepare($eliminar_sql);
        if (!$eliminar_stmt) {
            throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
        }

        if (!$eliminar_stmt->bind_param("i", $id_producto)) {
            throw new Exception("Error al enlazar parámetros: " . $eliminar_stmt->error);
        }

        if (!$eliminar_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de eliminación: " . $eliminar_stmt->error);
        }

        $respuesta['exito'] = true;
        $respuesta['mensaje'] = "Producto eliminado correctamente";
    } catch (Exception $e) {
        error_log("Error al eliminar producto: " . $e->getMessage());
        $respuesta['mensaje'] = "Hubo un error al eliminar el producto: " . $e->getMessage();
    }

    return $respuesta;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                $resultado = procesarProducto();
                echo json_encode($resultado);
                break;
            case 'editar':
                $resultado = editarProducto();
                echo json_encode($resultado);
                break;
            default:
                echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);
                break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['eliminar'])) {
    $id_producto = intval($_GET['eliminar']);
    $resultado = eliminarProducto($id_producto);
    echo json_encode($resultado);
}

$conn->close();
?>