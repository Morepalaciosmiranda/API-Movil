<?php
include './includes/conexion.php';

$response = ['success' => false];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);

    $sql = "SELECT correo_electronico, expira FROM password_resets WHERE token='$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (strtotime($row['expira']) > time()) {
            $correo_electronico = $row['correo_electronico'];

            echo '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="./css/reset_password.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
                <title>Restablecer Contraseña</title>
                <style>
                    .eye-icon {
                        position: absolute;
                        right: 10px;
                        top: 50%;
                        transform: translateY(-50%);
                        cursor: pointer;
                        font-size: 1.5em; /* Aumenta el tamaño del icono del ojo */
                    }

                    .inp-box {
                        position: relative;
                    }
                </style>
            </head>
            <body>
                <div id="reset-password-modal" class="modal" style="display: flex;">
                    <div class="modal-content">
                        <h2 class="modal-title">Restablecer Contraseña</h2>
                        <p class="modal-subtitle">Ingrese su nueva contraseña:</p>
                        <div class="inp-box">
                            <input type="password" id="new-password" placeholder="Nueva Contraseña">
                            <i class=\'bx bx-show eye-icon\' id="toggle-new-password"></i>
                        </div>
                        <input type="hidden" id="reset-token" value="' . htmlspecialchars($_GET['token']) . '">
                        <button type="button" class="modal-button" onclick="resetPassword()">
                            Restablecer Contraseña
                            <span class="spinner" id="loading-spinner" style="display:none;"></span>
                        </button>
                    </div>
                </div>

                <script>
                    function resetPassword() {
                        const newPassword = document.getElementById(\'new-password\').value;
                        const token = document.getElementById(\'reset-token\').value;

                        // Validación de contraseña
                        if (!validatePassword(newPassword)) {
                            return;
                        }

                        const loadingSpinner = document.getElementById(\'loading-spinner\');
                        loadingSpinner.style.display = \'inline-block\';

                        fetch(\'reset_password.php?token=\' + token, {
                            method: \'POST\',
                            headers: { \'Content-Type\': \'application/x-www-form-urlencoded\' },
                            body: \'new_password=\' + encodeURIComponent(newPassword)
                        })
                        .then(response => response.json())
                        .then(data => {
                            loadingSpinner.style.display = \'none\';
                            if (data.success) {
                                Swal.fire({
                                    icon: \'success\',
                                    title: \'¡Contraseña restablecida!\',
                                    text: \'Su contraseña ha sido restablecida exitosamente.\',
                                    willClose: function() { // Cambiado de onClose a willClose
                                        window.location.href = \'./loginRegister.php\'; // Redirigir al loginRegister.php
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: \'error\',
                                    title: \'Error\',
                                    text: \'No se pudo restablecer la contraseña. Inténtelo nuevamente.\'
                                });
                            }
                        })
                        .catch(error => {
                            console.error(\'Error:\', error);
                            Swal.fire({
                                icon: \'error\',
                                title: \'Error\',
                                text: \'Hubo un error al procesar su solicitud. Por favor, inténtelo nuevamente.\'
                            });
                        });
                    }

                    function validatePassword(password) {
                        if (password.length < 8) {
                            Swal.fire({
                                icon: \'error\',
                                title: \'Error\',
                                text: \'La contraseña debe tener al menos 8 caracteres.\'
                            });
                            return false;
                        }

                        if (!/[A-Z]/.test(password)) {
                            Swal.fire({
                                icon: \'error\',
                                title: \'Error\',
                                text: \'La contraseña debe contener al menos una letra mayúscula.\'
                            });
                            return false;
                        }

                        return true;
                    }

                    const toggleNewPassword = document.getElementById(\'toggle-new-password\');
                    const newPassword = document.getElementById(\'new-password\');
                    toggleNewPassword.addEventListener(\'click\', function() {
                        if (newPassword.type === \'password\') {
                            newPassword.type = \'text\';
                            toggleNewPassword.classList.replace(\'bx-show\', \'bx-hide\');
                        } else {
                            newPassword.type = \'password\';
                            toggleNewPassword.classList.replace(\'bx-hide\', \'bx-show\');
                        }
                    });

                </script>

                <!-- SweetAlert -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </body>
            </html>';
        } else {
            echo "El enlace de restablecimiento de contraseña ha expirado.";
        }
    } else {
        echo "Token inválido.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    $sql = "SELECT correo_electronico FROM password_resets WHERE token='$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $correo_electronico = $row['correo_electronico'];

        $sql_update = "UPDATE usuarios SET contrasena='$hashed_password' WHERE correo_electronico='$correo_electronico'";
        if ($conn->query($sql_update) === TRUE) {
            $response['success'] = true;

            $sql_delete = "DELETE FROM password_resets WHERE token='$token'";
            $conn->query($sql_delete);
        } else {
            $response['message'] = "Error al restablecer la contraseña.";
        }
    } else {
        $response['message'] = "Token inválido.";
    }

    echo json_encode($response);
} else {
    echo "No se proporcionó un token válido.";
}

$conn->close();
?>
