<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../includes/conexion.php';

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function obtenerProductos()
{
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

function procesarProducto()
{
    global $conn;
    $respuesta = ['exito' => false, 'mensaje' => ''];

    try {
        $nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $descripcion = $_POST['descripcion'];
        $insumo_ids = $_POST['insumo_id'];
        $cantidades_insumo = $_POST['cantidad_insumo'];

        $imagen = $_FILES['imagen'];
        $imagen_tmp_name = $imagen['tmp_name'];
        $imagen_error = $imagen['error'];

        if ($imagen_error !== UPLOAD_ERR_OK) {
            throw new Exception("Error al cargar la imagen: " . $imagen_error);
        }

        // Cambiar la ruta de upload_dir para usar una ruta relativa
        $upload_dir = '../public/uploads/';

        // Verificar y crear el directorio si no existe
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("No se pudo crear el directorio de uploads.");
            }
        }
        if (!is_writable($upload_dir)) {
            throw new Exception("El directorio de uploads no tiene permisos de escritura");
        }

        $imagen_nombre = uniqid('producto_') . '_' . basename($imagen['name']);
        $imagen_destino = $upload_dir . $imagen_nombre;

        if (!move_uploaded_file($imagen_tmp_name, $imagen_destino)) {
            throw new Exception("Error al mover la imagen al directorio de destino.");
        }

        $conn->begin_transaction();

        $insert_sql = "INSERT INTO productos (nombre_producto, foto, descripcion_producto, valor_unitario) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
        }

        // Cambiar la ruta de la imagen para que sea relativa al documento raíz
        $imagen_ruta = '/uploads/' . $imagen_nombre;
        if (!$insert_stmt->bind_param("sssd", $nombre, $imagen_ruta, $descripcion, $precio)) {
            throw new Exception("Error al enlazar parámetros: " . $insert_stmt->error);
        }

        if (!$insert_stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta de inserción: " . $insert_stmt->error);
        }

        $producto_id = $conn->insert_id;

        foreach ($insumo_ids as $index => $insumo_id) {
            $cantidad_insumo = $cantidades_insumo[$index];

            $insert_producto_insumo_sql = "INSERT INTO productos_insumos (id_producto, id_insumo, cantidad) VALUES (?, ?, ?)";
            $insert_producto_insumo_stmt = $conn->prepare($insert_producto_insumo_sql);
            if (!$insert_producto_insumo_stmt) {
                throw new Exception("Error al preparar la consulta de inserción de producto_insumo: " . $conn->error);
            }

            if (!$insert_producto_insumo_stmt->bind_param("iii", $producto_id, $insumo_id, $cantidad_insumo)) {
                throw new Exception("Error al enlazar parámetros: " . $insert_producto_insumo_stmt->error);
            }

            if (!$insert_producto_insumo_stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta de inserción de producto_insumo: " . $insert_producto_insumo_stmt->error);
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

function editarProducto()
{
    global $conn;
    $respuesta = ['exito' => false, 'mensaje' => ''];

    try {
        $id_editar = $_POST['id_editar'];
        $nombre_editar = $_POST['nombre_edit'];
        $precio_editar = $_POST['precio_edit'];
        $descripcion_editar = $_POST['descripcion_edit'];

        if (isset($_FILES['imagen_edit']) && $_FILES['imagen_edit']['error'] == UPLOAD_ERR_OK) {
            $imagen = $_FILES['imagen_edit'];
            $imagen_tmp_name = $imagen['tmp_name'];
            $imagen_nombre = uniqid('producto_') . '_' . basename($imagen['name']);
            $upload_dir = __DIR__ . '/../public/uploads/';
            $imagen_destino = $upload_dir . $imagen_nombre;

            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0755, true)) {
                    throw new Exception("No se pudo crear el directorio de uploads.");
                }
            }
            if (!is_writable($upload_dir)) {
                throw new Exception("El directorio de uploads no tiene permisos de escritura");
            }

            if (!move_uploaded_file($imagen_tmp_name, $imagen_destino)) {
                throw new Exception("Error al mover la imagen al directorio de destino.");
            }

            $imagen_ruta = '/uploads/' . $imagen_nombre;
            $actualizar_sql = "UPDATE productos SET nombre_producto = ?, descripcion_producto = ?, valor_unitario = ?, foto = ? WHERE id_producto = ?";
            $actualizar_stmt = $conn->prepare($actualizar_sql);
            if (!$actualizar_stmt) {
                throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
            }

            if (!$actualizar_stmt->bind_param("ssdsi", $nombre_editar, $descripcion_editar, $precio_editar, $imagen_ruta, $id_editar)) {
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

function eliminarProducto($id_producto)
{
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

    if (isset($_POST['eliminar_id'])) {
        $id_producto = intval($_POST['eliminar_id']);
        $resultado = eliminarProducto($id_producto);
        echo json_encode($resultado);
    }
}

$conn->close();