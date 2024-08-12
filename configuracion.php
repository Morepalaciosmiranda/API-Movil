<?php
include './includes/conexion.php';
session_start();

date_default_timezone_set('America/Bogota'); // Ajusta esto a tu zona horaria correcta

if (!isset($_SESSION['correo_electronico'])) {
    header("Location: loginRegister.php");
    exit();
}

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['rol'])) {
    header('Location: loginRegister.php');
    exit();
}

if ($_SESSION['rol'] !== 'Usuario') {
    header('Location: ./no_autorizado.php');
    exit();
}

$correo_usuario = $_SESSION['correo_electronico'];

$sql = "SELECT nombre_usuario, correo_electronico, contrasena, id_usuario FROM usuarios WHERE correo_electronico = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nombre_usuario = $row['nombre_usuario'];
    $correo_electronico = $row['correo_electronico'];
    $id_usuario = $row['id_usuario'];
    $contraseña_mascara = '*******';
} else {
    echo "Error: Usuario no encontrado.";
    exit();
}

$sql_pedidos = "SELECT pedidos.id_pedido, pedidos.fecha_pedido, pedidos.precio_domicilio, pedidos.estado_pedido, usuarios.nombre_usuario, SUM(detalle_pedido.subtotal) as subtotal_cliente, 
                NOW() as fecha_actual
                FROM pedidos 
                JOIN usuarios ON pedidos.id_usuario = usuarios.id_usuario 
                JOIN detalle_pedido ON pedidos.id_pedido = detalle_pedido.id_pedido
                WHERE pedidos.id_usuario = ?
                GROUP BY pedidos.id_pedido
                ORDER BY pedidos.fecha_pedido DESC";

$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $id_usuario);
$stmt_pedidos->execute();
$result_pedidos = $stmt_pedidos->get_result();

$pedidos = [];
while ($row_pedido = $result_pedidos->fetch_assoc()) {
    $pedidos[] = $row_pedido;
}

foreach ($pedidos as &$pedido) {
    $fecha_pedido = new DateTime($pedido['fecha_pedido']);
    $fecha_actual = new DateTime($pedido['fecha_actual']);
    $intervalo = $fecha_pedido->diff($fecha_actual);
    $pedido['minutos_desde_pedido'] = $intervalo->days * 24 * 60 + $intervalo->h * 60 + $intervalo->i;
}

$cancelled_orders = isset($_SESSION['cancelado_exitosamente']) ? $_SESSION['cancelado_exitosamente'] : [];

// Manejo de mensajes y errores
if (isset($_GET['mensaje'])) {
    $mensaje = '';
    switch ($_GET['mensaje']) {
        case 'pedido_cancelado':
            $mensaje = "El pedido ha sido cancelado exitosamente.";
            break;
    }
}

if (isset($_GET['error'])) {
    $error = '';
    switch ($_GET['error']) {
        case 'error_cancelacion':
            $error = "Hubo un error al intentar cancelar el pedido.";
            break;
        case 'tiempo_excedido':
            $error = "No se puede cancelar el pedido después de 10 minutos de realizado.";
            break;
        case 'pedido_entregado':
            $error = "No se puede cancelar un pedido que ya ha sido entregado.";
            break;
        case 'pedido_ya_cancelado':
            $error = "Este pedido ya ha sido cancelado anteriormente.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link rel="stylesheet" href="./css/configuracion12.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <nav class="navbar">
        <div class="logo">
            <img src="./img/LogoExterminio.png" alt="Logo">
        </div>
    </nav>

    <?php if (isset($mensaje)): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="container" id="ajustes-container">
        <div class="title">
            <h2>Información de la cuenta</h2>
        </div>
        <div class="inputs-container">
            <div class="inputs-column">
                <div class="input-container">
                    <input placeholder="Nombre" class="input-field" type="text" value="<?php echo $nombre_usuario; ?>" id="nombre_usuario" readonly>
                    <label for="nombre_usuario" class="input-label">Nombre</label>
                    <span class="input-highlight"></span>
                    <button class="edit-button" data-target="name-modal"><i class="fas fa-pencil-alt"></i></button>
                </div>
                <div class="input-container">
                    <input placeholder="Correo electrónico" class="input-field" type="text" value="<?php echo $correo_electronico; ?>" id="correo_electronico" readonly>
                    <label for="correo_electronico" class="input-label">Correo electrónico</label>
                    <span class="input-highlight"></span>
                    <button class="edit-button" data-target="email-modal"><i class="fas fa-pencil-alt"></i></button>
                </div>
            </div>
            <div class="inputs-column">
                <div class="input-container">
                    <input placeholder="Contraseña" class="input-field" type="password" value="<?php echo $contraseña_mascara; ?>" id="contrasena" readonly>
                    <label for="contrasena" class="input-label">Contraseña</label>
                    <span class="input-highlight"></span>
                    <button class="edit-button" data-target="password-modal"><i class="fas fa-pencil-alt"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div id="name-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar nombre</h2>
            <form id="name-form">
                <input type="text" name="name" id="name" value="<?php echo $nombre_usuario; ?>">
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <div id="email-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar correo electrónico</h2>
            <form id="email-form">
                <input type="text" name="email" id="email" value="<?php echo $correo_electronico; ?>">
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <div id="password-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar contraseña</h2>
            <form id="password-form">
                <input type="password" name="password" id="password">
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <div class="containerPedidos" id="pedidos-container" style="display: none;">
        <div class="titlePedido">
            <h2>¡Mis Pedidos!</h2>
        </div>
        <div class="subtitle">
            <h3>En este apartado aparecen todos los pedidos que hagas a través de nuestra página</h3>
        </div>
        <div class="pedidos-lista">
            <?php foreach ($pedidos as $pedido) : ?>
                <div class="pedido-item">
                    <div class="pedido-info">
                        <span><strong>Número:</strong> <?php echo $pedido['id_pedido']; ?></span>
                        <span><strong>Fecha:</strong> <?php echo $pedido['fecha_pedido']; ?></span>
                        <span><strong>Nombre:</strong> <?php echo $pedido['nombre_usuario']; ?></span>
                        <span><strong>Domicilio:</strong> <?php echo $pedido['precio_domicilio']; ?></span>
                        <span><strong>Estado:</strong> <?php echo $pedido['estado_pedido']; ?></span>
                        <span><strong>Total:</strong> <?php echo isset($pedido['subtotal_cliente']) ? $pedido['subtotal_cliente'] : 'No disponible'; ?></span>
                        <span><strong>Minutos desde pedido:</strong> <?php echo $pedido['minutos_desde_pedido']; ?></span>
                        <span><strong>Fecha actual:</strong> <?php echo $pedido['fecha_actual']; ?></span>
                    </div>
                    <?php 
                    $puedeSerCancelado = $pedido['estado_pedido'] != 'entregado' && 
                                         $pedido['estado_pedido'] != 'cancelado' && 
                                         $pedido['minutos_desde_pedido'] <= 10;
                    
                    if ($puedeSerCancelado) : 
                    ?>
                        <div class="pedido-actions">
                            <form method="POST" action="./controller/cambiar_estado_pedido.php" id="cancelarForm_<?php echo $pedido['id_pedido']; ?>">
                                <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                                <input type="hidden" name="nuevo_estado" value="cancelado">
                                <button type="button" class="cancelar-button" id="cancelarButton_<?php echo $pedido['id_pedido']; ?>" onclick="confirmCancel('<?php echo $pedido['id_pedido']; ?>', <?php echo $pedido['minutos_desde_pedido']; ?>)">Cancelar Pedido</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="pedido-actions">
                            <p>No se puede cancelar este pedido</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="container2">
        <div class="navigation">
            <h3 onclick="mostrarAjustes()"><i class="fas fa-cog fa-sm"></i> Ajustes de cuenta</h3>
            <h3 onclick="mostrarPedidos()"><i class="fas fa-shopping-cart fa-sm"></i> Mis pedidos</h3>
        </div>
    </div>
    <script src="./js/configuracion.js"></script>
</body>

</html>