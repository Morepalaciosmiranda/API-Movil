<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['correo_electronico']) || !isset($_SESSION['user_id'])) {
    header("Location: ../loginRegister.php");
    exit();
}

$user_id = $_SESSION['user_id'];

include '../includes/conexion.php';

$permissions = array();

$sql = "SELECT id_permiso FROM rolesxpermiso WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $permissions[] = $row['id_permiso'];
}

$modules = array(
    1 => array('id' => '1', 'name' => 'Pedidos', 'icon' => 'fas fa-truck', 'link' => './pedidos.php'),
    2 => array('id' => '2', 'name' => 'Ventas', 'icon' => 'fas fa-chart-line', 'link' => './ventas.php'),
    3 => array('id' => '3', 'name' => 'Compras', 'icon' => 'fas fa-shopping-cart', 'link' => './compras.php'),
    4 => array('id' => '4', 'name' => 'Usuarios', 'icon' => 'fas fa-users', 'link' => './Usuarios.php'),
    5 => array('id' => '5', 'name' => 'Insumos', 'icon' => 'fas fa-box', 'link' => './insumos.php'),
    6 => array('id' => '6', 'name' => 'Proveedores', 'icon' => 'fas fa-truck-moving', 'link' => './proveedores.php'),
    7 => array('id' => '7', 'name' => 'Clientes', 'icon' => 'fas fa-user-friends', 'link' => './clientes.php'),
    8 => array('id' => '8', 'name' => 'Permisos', 'icon' => 'fas fa-key', 'link' => './permisos.php'),
    9 => array('id' => '9', 'name' => 'Productos', 'icon' => 'fas fa-box-open', 'link' => './productos.php'),
);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato&display=swap" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/index3.css">
</head>
<div class="sidebar">
    <h1 class="logo">EXTERMINIO</h1>
    <hr>
    <div class="profile-div">
        <p class="user1" onclick="toggleUserOptions()">
            <i class="fa fa-user"></i> <?php echo isset($_SESSION['correo_electronico']) ? $_SESSION['correo_electronico'] : ''; ?>
        </p>
        <div id="userOptionsContainer" class="user-options-container">
            <p><i class="fa fa-cog"></i> Configuración</p>
            <a href="../loginRegister.php">
                <p><i class="fa fa-power-off"></i> Cerrar sesión</p>
            </a>
        </div>
    </div>
    <br><br>
    <ul class="nav">
        <?php foreach ($modules as $module) : ?>
            <?php if (in_array($module['id'], $permissions)) : ?>
                <li><a href="<?php echo $module['link']; ?>"><i class="<?php echo $module['icon']; ?>"></i> <?php echo $module['name']; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <script>
        function toggleUserOptions() {
            var userOptionsContainer = document.getElementById("userOptionsContainer");
            if (userOptionsContainer.style.display === "none" || userOptionsContainer.style.display === "") {
                userOptionsContainer.style.display = "block";
            } else {
                userOptionsContainer.style.display = "none";
            }
        }
    </script>
</div>