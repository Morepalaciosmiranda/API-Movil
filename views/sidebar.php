<?php
$user_id = $_SESSION['id_usuario'];

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
<div class="sidebar">
    <h1 class="logo">EXTERMINIO</h1>
    <div class="profile-div">
        <p class="user1" onclick="toggleUserOptions()">
            <i class="fa fa-user"></i> <?php echo isset($_SESSION['correo_electronico']) ? $_SESSION['correo_electronico'] : ''; ?>
        </p>
    </div>
    <br><br>
    <ul class="nav">
        <li><a href="#" onclick="showOverlayAndRedirect('inicio.php')"><i class="fa fa-home"></i> Inicio</a></li> 
        <?php foreach ($modules as $module) : ?>
            <?php if (in_array($module['id'], $permissions)) : ?>
                <li><a href="#" onclick="showOverlayAndRedirect('<?php echo $module['link']; ?>')"><i class="<?php echo $module['icon']; ?>"></i> <?php echo $module['name']; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>
<script>
    function showOverlayAndRedirect(link) {
        var overlay = document.getElementById('overlay');
        overlay.style.display = 'flex';
        overlay.classList.remove('hidden');
        setTimeout(function() {
            window.location.href = link;
        }, 1000); 
    }
</script>