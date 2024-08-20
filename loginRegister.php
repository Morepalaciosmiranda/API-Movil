<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/login16.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Iniciar Sesión | Exterminio</title>
</head>

<body>
    <section class="form-box">
        <div class="form-col-content">
            <div class="form-top-box">
                <p>Bienvenido a</p>
                <div class="title">
                    <h2>EXTERMINIO</h2>
                </div>
            </div>
            <div class="login-forms">
                <form action="./controller/login.php" method="POST" class="login-register" id="login-in">
                    <h3 class="form-title">Iniciar Sesión</h3>
                    <div class="inp-box">
                        <i class='bx bx-user login-icon'></i>
                        <input type="text" name="correo_electronico" placeholder="Correo" id="login-email">
                    </div>
                    <div class="inp-box">
                        <i class='bx bx-lock login-icon'></i>
                        <input type="password" name="contrasena" placeholder="Contraseña" id="login-password">
                        <i class='bx bx-show eye-icon' id="toggle-login-password"></i>
                    </div>
                    <a href="#" class="login-forget" id="forgot-password-link">Olvidaste la Contraseña?</a>
                    <button type="submit" class="form-button">Iniciar Sesión</button>
                    <div class="form-desc">
                        <span class="login-account">Aún no tienes cuenta?</span>
                        <a href="#login-up" class="login-signup" id="sign-up">Registrarse</a>
                    </div>
                </form>
                <form action="./controller/register.php" method="POST" class="login-create none" id="login-up">
                    <h3 class="form-title">Crear Cuenta</h3>
                    <div class="inp-box">
                        <i class='bx bx-user login-icon'></i>
                        <input type="text" name="nombre_usuario" placeholder="Nombre Usuario" id="register-username">
                    </div>
                    <div class="inp-box">
                        <i class='bx bx-at login-icon'></i>
                        <input type="text" name="correo_electronico" placeholder="Correo" id="register-email">
                    </div>
                    <div class="inp-box">
                        <i class='bx bx-lock login-icon'></i>
                        <input type="password" name="contrasena" placeholder="Contraseña" id="register-password">
                        <i class='bx bx-show eye-icon' id="toggle-register-password"></i>
                    </div>
                    <button type="submit" class="form-button">Registrarse</button>
                    <div class="form-desc">
                        <span class="login-account">Ya tienes una cuenta?</span>
                        <a href="#login-in" class="login-signup" id="sign-in">Iniciar Sesión</a>
                    </div>
                </form>
            </div>
            <div class="social-box">
                <span class="social-title">Nuestras Redes Sociales</span>
                <div class="social-icons-box">
                    <div class="social-item">
                        <i class='bx bxl-facebook facebook'></i>
                    </div>
                    <div class="social-item">
                        <i class='bx bxl-whatsapp whatsapp'></i>
                    </div>
                    <div class="social-item">
                        <i class='bx bxl-linkedin linkedin'></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-col-image">
            <img src="./img/LogoExterminio.png" alt="">
            <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Cupiditate nostrum architecto quas inventore, eligendi dolorem omnis repellendus quaerat aperiam dolorum vero eum officiis atque sequi necessitatibus error beatae at iste?</p>
        </div>
    </section>

    <div id="forgot-password-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-title">Restablecer Contraseña</h2>
            <p class="modal-subtitle">Ingrese su correo electrónico para recibir un enlace de restablecimiento de contraseña:</p>
            <input type="email" id="reset-email" placeholder="Correo Electrónico">
            <button type="button" class="modal-button" onclick="sendResetLink()">
                Enviar
                <span class="spinner" id="loading-spinner" style="display:none;"></span>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const signupLinks = document.querySelectorAll('.login-signup');
            signupLinks.forEach(function(link) {
                link.addEventListener('click', function(event) {
                    const targetId = this.getAttribute('href');
                    const targetForm = document.querySelector(targetId);
                    const forms = document.querySelectorAll('.login-forms form');
                    forms.forEach(function(form) {
                        form.classList.add('none');
                    });
                    targetForm.classList.remove('none');
                    event.preventDefault();
                });
            });

            const forgotPasswordLink = document.getElementById('forgot-password-link');
            const forgotPasswordModal = document.getElementById('forgot-password-modal');
            const closeModal = document.querySelector('.modal .close');

            forgotPasswordLink.addEventListener('click', function(event) {
                event.preventDefault();
                forgotPasswordModal.style.display = 'flex';
            });

            closeModal.addEventListener('click', function() {
                forgotPasswordModal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == forgotPasswordModal) {
                    forgotPasswordModal.style.display = 'none';
                }
            });

            const loginForm = document.getElementById('login-in');
            loginForm.addEventListener('submit', function(event) {
                event.preventDefault();
                validateLoginForm();
            });

            const registerForm = document.getElementById('login-up');
            registerForm.addEventListener('submit', function(event) {
                event.preventDefault();
                validateRegisterForm();
            });

            const toggleLoginPassword = document.getElementById('toggle-login-password');
            const loginPassword = document.getElementById('login-password');
            toggleLoginPassword.addEventListener('click', function() {
                if (loginPassword.type === 'password') {
                    loginPassword.type = 'text';
                    toggleLoginPassword.classList.replace('bx-show', 'bx-hide');
                } else {
                    loginPassword.type = 'password';
                    toggleLoginPassword.classList.replace('bx-hide', 'bx-show');
                }
            });

            const toggleRegisterPassword = document.getElementById('toggle-register-password');
            const registerPassword = document.getElementById('register-password');
            toggleRegisterPassword.addEventListener('click', function() {
                if (registerPassword.type === 'password') {
                    registerPassword.type = 'text';
                    toggleRegisterPassword.classList.replace('bx-show', 'bx-hide');
                } else {
                    registerPassword.type = 'password';
                    toggleRegisterPassword.classList.replace('bx-hide', 'bx-show');
                }
            });

            function validateLoginForm() {
                const email = document.getElementById('login-email').value;
                const password = document.getElementById('login-password').value;

                if (!email || !password) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor, complete todos los campos del formulario.',
                    });
                } else {
                    fetch('./controller/login.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'correo_electronico': email,
                                'contrasena': password
                            })
                        })
                        .then(response => response.text())
                        .then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (error) {
                                console.error('Error parsing JSON:', text);
                                throw new Error('Invalid JSON response');
                            }
                        })
                        .then(data => {
                            if (data.status === 'error') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message,
                                });
                            } else if (data.status === 'warning') {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Cuenta Inactiva',
                                    text: data.message,
                                });
                            } else if (data.status === 'success') {
                                let redirectUrl = '';
                                switch (data.role) {
                                    case 'Administrador':
                                        redirectUrl = 'https://api-movil-tj84.onrender.com/animacion.php';
                                        break;
                                    case 'Usuario':
                                        redirectUrl = 'https://api-movil-tj84.onrender.com/index.php';
                                        break;
                                    default:
                                        redirectUrl = 'https://api-movil-tj84.onrender.com/animacion.php';
                                        break;
                                }
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Inicio de sesión exitoso',
                                    text: 'Bienvenido',
                                }).then(() => {
                                    window.location.href = redirectUrl;
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error en la solicitud:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error en el servidor. Por favor, inténtelo de nuevo más tarde.',
                            });
                        });
                }
            }

            function validateRegisterForm() {
                const username = document.getElementById('register-username').value;
                const email = document.getElementById('register-email').value;
                const password = document.getElementById('register-password').value;

                if (!username || !email || !password) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor, complete todos los campos del formulario.',
                    });
                } else {
                    fetch('./controller/send_verification_code.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'nombre_usuario': username,
                                'correo_electronico': email,
                                'contrasena': password
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                showVerificationModal();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message,
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error en la solicitud:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Ocurrió un error en el servidor. Por favor, inténtelo de nuevo más tarde.',
                            });
                        });
                }
            }

            function showVerificationModal() {
                Swal.fire({
                    title: 'Verificación de correo',
                    input: 'text',
                    inputLabel: 'Ingrese el código de verificación enviado a su correo',
                    inputPlaceholder: 'Código de verificación',
                    showCancelButton: true,
                    confirmButtonText: 'Verificar',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: (code) => {
                        return fetch('./controller/register.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: new URLSearchParams({
                                    'nombre_usuario': document.getElementById('register-username').value,
                                    'correo_electronico': document.getElementById('register-email').value,
                                    'contrasena': document.getElementById('register-password').value,
                                    'codigo_verificacion': code
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'error') {
                                    throw new Error(data.message)
                                }
                                return data
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Error: ${error.message}`
                                )
                            })
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registro exitoso',
                            text: 'Su cuenta ha sido verificada y creada con éxito.',
                        }).then(() => {
                            document.getElementById('sign-in').click();
                        });
                    }
                })
            }
        });

        function sendResetLink() {
            const email = document.getElementById('reset-email').value;
            const spinner = document.getElementById('loading-spinner');
            const sendButton = document.querySelector('.modal-button');

            if (!email) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor, ingrese su correo electrónico.',
                });
            } else {
                sendButton.disabled = true;
                sendButton.innerHTML = '<span class="spinner" id="loading-spinner"></span>';
                spinner.style.display = 'inline-block';

                fetch('../exterminio1/controller/forgot_password.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            'correo_electronico': email
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        sendButton.disabled = false;
                        sendButton.innerHTML = 'Enviar';
                        spinner.style.display = 'none';

                        if (data.status === 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message,
                            });
                        } else if (data.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Enlace Enviado',
                                text: data.message,
                            }).then(() => {
                                document.getElementById('forgot-password-modal').style.display = 'none';
                            });
                        }
                    })
                    .catch(error => {
                        sendButton.disabled = false;
                        sendButton.innerHTML = 'Enviar';
                        spinner.style.display = 'none';

                        console.error('Error en la solicitud:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error en el servidor. Por favor, inténtelo de nuevo más tarde.',
                        });
                    });
            }
        }
    </script>
</body>

</html>