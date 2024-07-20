<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo_electronico = $_POST['correo_electronico'];
    $codigo = $_POST['codigo'];

    if ($codigo == $_SESSION['verification_code']) {
        $_SESSION['verified'] = true;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
