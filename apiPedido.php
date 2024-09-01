<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

file_put_contents('debug.log', 'API called: ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents('debug.log', 'GET: ' . print_r($_GET, true) . "\n", FILE_APPEND);
file_put_contents('debug.log', 'POST: ' . print_r($_POST, true) . "\n", FILE_APPEND);
file_put_contents('debug.log', 'Raw input: ' . file_get_contents('php://input') . "\n", FILE_APPEND);

header("Content-Type: application/json");
require_once('./includes/conexion.php');

if ($conn->connect_error) {
    file_put_contents('debug.log', 'Connection failed: ' . $conn->connect_error . "\n", FILE_APPEND);
    die("Connection failed: " . $conn->connect_error);
}

// Función para enviar respuesta JSON
function send_json_response($success, $message = '', $data = null)
{
    $response = [
        'success' => $success,
        'message' => $message,
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    file_put_contents('debug.log', 'Response: ' . json_encode($response) . "\n", FILE_APPEND);
    exit;
}

// Manejar diferentes tipos de solicitudes
$method = $_SERVER['REQUEST_METHOD'];
file_put_contents('debug.log', 'Method: ' . $method . "\n", FILE_APPEND);

switch ($method) {
    case 'GET':
        // Obtener pedidos
        if (isset($_GET['action']) && $_GET['action'] == 'obtener_pedidos') {
            $sql = "SELECT p.id_pedido, p.fecha_pedido, p.estado_pedido, p.precio_domicilio, 
                    MAX(dp.nombre) as nombre, MAX(dp.direccion) as direccion, MAX(dp.barrio) as barrio, MAX(dp.telefono) as telefono, 
                    SUM(dp.subtotal) as total_pedido,
                    GROUP_CONCAT(CONCAT(dp.id_producto, ':', dp.cantidad, ':', dp.valor_unitario) SEPARATOR '|') as productos
             FROM pedidos p
             JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
             GROUP BY p.id_pedido
             ORDER BY p.fecha_pedido DESC";
            file_put_contents('debug.log', 'SQL query: ' . $sql . "\n", FILE_APPEND);

            $result = $conn->query($sql);

            if ($result) {
                $pedidos = [];
                while ($row = $result->fetch_assoc()) {
                    $productos = [];
                    if (!is_null($row['productos'])) {
                        $productosRaw = explode('|', $row['productos']);
                        foreach ($productosRaw as $productoRaw) {
                            $productoData = explode(':', $productoRaw);
                            if (count($productoData) >= 3) {
                                $productos[] = [
                                    'id' => $productoData[0],
                                    'cantidad' => $productoData[1],
                                    'precio' => $productoData[2]
                                ];
                            }
                        }
                    }
                    $pedidos[] = [
                        'id' => $row['id_pedido'],
                        'fechapedido' => $row['fecha_pedido'],
                        'estadopedido' => $row['estado_pedido'],
                        'precio_domicilio' => $row['precio_domicilio'],
                        'nombre_cliente' => $row['nombre'],
                        'direccion' => $row['direccion'],
                        'barrio' => $row['barrio'],
                        'telefono' => $row['telefono'],
                        'total_pedido' => $row['total_pedido'],
                        'productos' => $productos
                    ];
                }
                file_put_contents('debug.log', 'Pedidos: ' . print_r($pedidos, true) . "\n", FILE_APPEND);
                send_json_response(true, "Pedidos obtenidos con éxito", $pedidos);
            } else {
                file_put_contents('debug.log', 'Error: ' . $conn->error . "\n", FILE_APPEND);
                send_json_response(false, "Error al obtener los pedidos: " . $conn->error);
            }
        }
        break;

    case 'POST':
        // Crear nuevo pedido
        $data = json_decode(file_get_contents("php://input"), true);
        file_put_contents('debug.log', 'POST data: ' . print_r($data, true) . "\n", FILE_APPEND);

        // Aquí va la lógica para crear el pedido en la base de datos
        $nombre_cliente = $data['nombre_cliente'] ?? '';
        $direccion = $data['direccion'] ?? '';
        $barrio = $data['barrio'] ?? '';
        $telefono = $data['telefono'] ?? '';
        $precio_domicilio = $data['precio_domicilio'] ?? 0;
        $productos = $data['productos'] ?? [];

        // Iniciar transacción
        $conn->begin_transaction();

        try {
            // Insertar el pedido
            $sql_pedido = "INSERT INTO pedidos (fecha_pedido, estado_pedido, precio_domicilio, id_usuario) VALUES (NOW(), 'En Proceso', ?, ?)";
            $stmt_pedido = $conn->prepare($sql_pedido);
            $stmt_pedido->bind_param("di", $precio_domicilio, $data['id_usuario']);
            $stmt_pedido->execute();
            $pedido_id = $conn->insert_id;

            // Insertar detalles del pedido
            $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, nombre, direccion, barrio, telefono, cantidad, valor_unitario, subtotal) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);

            $total_compra = 0;
            foreach ($productos as $producto) {
                $subtotal = $producto['cantidad'] * $producto['precio'];
                $total_compra += $subtotal;
                $stmt_detalle->bind_param("iissssidd", $pedido_id, $producto['id'], $nombre_cliente, $direccion, $barrio, $telefono, $producto['cantidad'], $producto['precio'], $subtotal);
                $stmt_detalle->execute();
            }

            // Actualizar el total_compra en la tabla detalle_pedido
            $sql_update_total = "UPDATE detalle_pedido SET total_compra = ? WHERE id_pedido = ?";
            $stmt_update_total = $conn->prepare($sql_update_total);
            $stmt_update_total->bind_param("di", $total_compra, $pedido_id);
            $stmt_update_total->execute();

            // Confirmar transacción
            $conn->commit();

            // Preparar datos para la app Flutter
            $pedidoFlutter = [
                'id' => $pedido_id,
                'fechapedido' => date('Y-m-d'),
                'estadopedido' => 'En Proceso',
                'precio_domicilio' => $precio_domicilio,
                'nombre_cliente' => $nombre_cliente,
                'direccion' => $direccion,
                'barrio' => $barrio,
                'telefono' => $telefono,
                'total_pedido' => $total_compra,
                'productos' => $productos
            ];

            send_json_response(true, "Pedido creado exitosamente", $pedidoFlutter);
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $conn->rollback();
            file_put_contents('debug.log', 'Error: ' . $e->getMessage() . "\n", FILE_APPEND);
            send_json_response(false, "Error al crear el pedido: " . $e->getMessage());
        }
        break;

    case 'PUT':
        // Actualizar estado del pedido
        $data = json_decode(file_get_contents("php://input"), true);
        $pedido_id = isset($_GET['id']) ? $_GET['id'] : null;
        $nuevo_estado = $data['estadopedido'] ?? null;

        file_put_contents('debug.log', 'PUT data: ' . print_r($data, true) . "\n", FILE_APPEND);
        file_put_contents('debug.log', 'Pedido ID: ' . $pedido_id . "\n", FILE_APPEND);
        file_put_contents('debug.log', 'Nuevo estado: ' . $nuevo_estado . "\n", FILE_APPEND);

        if (!$pedido_id || !$nuevo_estado) {
            send_json_response(false, "Datos incompletos para actualizar el pedido");
        }

        $sql = "UPDATE pedidos SET estado_pedido = ? WHERE id_pedido = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nuevo_estado, $pedido_id);

        if ($stmt->execute()) {
            send_json_response(true, "Estado del pedido actualizado exitosamente");
        } else {
            file_put_contents('debug.log', 'Error: ' . $stmt->error . "\n", FILE_APPEND);
            send_json_response(false, "Error al actualizar el estado del pedido: " . $stmt->error);
        }
        break;

    default:
        send_json_response(false, "Método no permitido");
        break;
}

$conn->close();