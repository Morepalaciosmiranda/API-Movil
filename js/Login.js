document.addEventListener('DOMContentLoaded', function () {
    // Capturamos los elementos de los formularios
    const loginForm = document.querySelector("#login-in");
    const registerForm = document.querySelector("#login-up");

    // Función para validar campos vacíos
    function validateFields(form) {
        const inputs = form.querySelectorAll("input");
        let isEmpty = false;

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                isEmpty = true;
            }
        });

        return isEmpty;
    }

    // Evento de escucha para el formulario de inicio de sesión
    // Dentro del evento de submit del formulario de inicio de sesión
    loginForm.addEventListener('submit', function (event) {
        event.preventDefault();

        console.log("Formulario de inicio de sesión enviado"); // Mensaje de depuración

        if (validateFields(loginForm)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Por favor, ingrese sus datos.',
                iconColor: '#f15d07',
                background: '#020202',
                confirmButtonColor: '#f15d07',
                customClass: {
                    popup: 'alert-text-color' // Clase CSS para aplicar estilos personalizados al texto
                }
            });
        } else {
            // Resto del código para el inicio de sesión
        }
    });

    // Dentro del evento de submit del formulario de registro
    registerForm.addEventListener('submit', function (event) {
        event.preventDefault();
        console.log("Formulario de registro enviado"); // Mensaje de depuración

        if (validateFields(registerForm)) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Por favor, ingrese sus datos.',
                iconColor: '#f15d07',
                background: '#020202',
                confirmButtonColor: '#f15d07',
                customClass: {
                    popup: 'alert-text-color' // Clase CSS para aplicar estilos personalizados al texto
                }
            });
        } else {
            Swal.fire({ // <-- Aquí movemos la alerta de registro exitoso
                icon: 'success',
                title: '¡Registro exitoso!',
                text: 'Tu cuenta ha sido creada correctamente',
                iconColor: '#4CAF50',
                background: '#020202',
                confirmButtonColor: '#f15d07',
                customClass: {
                    popup: 'alert-text-color' // Clase CSS para aplicar estilos personalizados al texto
                }
            });
            // Aquí iría el código para enviar el formulario
        }
    });

    // Eventos de cambio de formulario
    const signUp = document.querySelector("#sign-up");
    const signIn = document.querySelector("#sign-in");

    signUp.addEventListener('click', () => {
        loginForm.classList.remove('block');
        registerForm.classList.remove('none');

        loginForm.classList.add('none');
        registerForm.classList.add('block');
    });

    signIn.addEventListener('click', () => {
        loginForm.classList.remove('none');
        registerForm.classList.remove('block');

        loginForm.classList.add('block');
        registerForm.classList.add('none');
    });
});
