// Get all the edit buttons and modal elements
const editButtons = document.querySelectorAll('.edit-button');
const modals = document.querySelectorAll('.modal');

// Add click event listeners to the edit buttons
editButtons.forEach((button, index) => {
    button.addEventListener('click', () => {
        // Hide all modals except the one for the clicked button
        modals.forEach((modal, i) => {
            if (i !== index) {
                modal.style.display = 'none';
            } else {
                modal.style.display = 'block';
            }
        });
    });
});

// Add click event listener to the close button in each modal
const closeButtons = document.querySelectorAll('.modal .close');
closeButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Hide the modal
        button.closest('.modal').style.display = 'none';
    });
});

// Add submit event listener to the form in each modal
const modalsForms = document.querySelectorAll('.modal form');
modalsForms.forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        // Handle form submission here
    });
});

function mostrarAjustes() {
    console.log('Mostrando ajustes');
    document.getElementById('ajustes-container').style.display = 'block';
    document.getElementById('pedidos-container').style.display = 'none';
}

function mostrarPedidos() {
    console.log('Mostrando pedidos');
    document.getElementById('ajustes-container').style.display = 'none';
    document.getElementById('pedidos-container').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', (event) => {
    const modals = document.querySelectorAll('.modal');
    const editButtons = document.querySelectorAll('.edit-button');
    const closeButtons = document.querySelectorAll('.close');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetModal = document.getElementById(button.getAttribute('data-target'));
            targetModal.style.display = 'block';
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            button.closest('.modal').style.display = 'none';
        });
    });

    window.onclick = (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };

    document.getElementById('name-form').addEventListener('submit', (event) => {
        event.preventDefault();
        console.log('Enviando solicitud de actualización de nombre');
        updateUserData('nombre_usuario', document.getElementById('name').value);
    });

    document.getElementById('email-form').addEventListener('submit', (event) => {
        event.preventDefault();
        console.log('Enviando solicitud de actualización de correo electrónico');
        updateUserData('correo_electronico', document.getElementById('email').value);
    });

    document.getElementById('password-form').addEventListener('submit', (event) => {
        event.preventDefault();
        console.log('Enviando solicitud de actualización de contraseña');
        updateUserData('contrasena', document.getElementById('password').value);
    });

    function updateUserData(field, value) {
        console.log('Se está enviando la solicitud de actualización de datos');
        fetch('/exterminio1/controller/update_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ field: field, value: value })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Datos actualizados correctamente');
                    location.reload();
                } else if (data.message) {
                    alert(data.message);  // Mostrar mensaje de error al usuario
                } else {
                    alert('Error al actualizar datos');
                }
            })
            .catch(error => console.error('Error:', error));
    }
});

function confirmCancel(idPedido, segundosDesdePedido) {
    if (segundosDesdePedido >= 600) {
        Swal.fire({
            title: 'No se puede cancelar',
            text: "Han pasado más de 10 minutos desde que se realizó el pedido.",
            icon: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    Swal.fire({
        title: '¿Está seguro de cancelar el pedido?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancelarForm_' + idPedido).submit();
        }
    });
}

// Función para actualizar el estado de los botones de cancelar
function actualizarBotonesCancelar() {
    const pedidoItems = document.querySelectorAll('.pedido-item');
    pedidoItems.forEach(item => {
        const estadoPedido = item.querySelector('.pedido-info span:nth-child(5)').textContent.split(': ')[1];
        const minutosDesdePedidoSpan = item.querySelector('.pedido-info span:nth-child(7)');
        let minutosDesdePedido = 0;
        if (minutosDesdePedidoSpan) {
            minutosDesdePedido = parseInt(minutosDesdePedidoSpan.textContent.split(': ')[1]);
        }
        const cancelarButton = item.querySelector('.cancelar-button');
        const noCancelarMensaje = item.querySelector('.pedido-actions p');

        if (cancelarButton && noCancelarMensaje) {
            if (estadoPedido === 'Entregado' || estadoPedido === 'Cancelado' || minutosDesdePedido > 10) {
                cancelarButton.style.display = 'none';
                noCancelarMensaje.style.display = 'block';
            } else {
                cancelarButton.style.display = 'inline-block';
                noCancelarMensaje.style.display = 'none';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', actualizarBotonesCancelar);
setInterval(actualizarBotonesCancelar, 60000); // Actualizar cada minuto


function actualizarTiempoRestante() {
    const pedidoItems = document.querySelectorAll('.pedido-item');
    pedidoItems.forEach(item => {
        const tiempoTranscurridoSpan = item.querySelector('span:nth-child(7)');
        const tiempoRestanteSpan = item.querySelector('span:nth-child(9)');
        const cancelarButton = item.querySelector('.cancelar-button');

        if (tiempoTranscurridoSpan && tiempoRestanteSpan) {
            let segundosTranscurridos = parseInt(tiempoTranscurridoSpan.textContent.split(':')[1].trim().split(' ')[0]) * 60 + 
                                        parseInt(tiempoTranscurridoSpan.textContent.split('y')[1].trim().split(' ')[0]);
            segundosTranscurridos++;

            let tiempoRestante = Math.max(0, 600 - segundosTranscurridos);
            let minutosRestantes = Math.floor(tiempoRestante / 60);
            let segundosRestantes = tiempoRestante % 60;

            tiempoTranscurridoSpan.textContent = `Tiempo transcurrido: ${Math.floor(segundosTranscurridos / 60)} minutos y ${segundosTranscurridos % 60} segundos`;
            tiempoRestanteSpan.textContent = `Tiempo restante para cancelar: ${minutosRestantes} minutos y ${segundosRestantes} segundos`;

            if (segundosTranscurridos >= 600 && cancelarButton) {
                cancelarButton.style.display = 'none';
                item.querySelector('.pedido-actions p').style.display = 'block';
            }
        }
    });
}

// Actualizar el tiempo cada segundo
setInterval(actualizarTiempoRestante, 1000);


function mostrarDetallesPedido(idPedido) {
    fetch(`obtener_detalles_pedido.php?idPedido=${idPedido}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const detallesContainer = document.getElementById('detalles-pedido');
                detallesContainer.innerHTML = `
                    <h3>Detalles del Pedido ${idPedido}</h3>
                    <div>
                        ${data.detalles}
                    </div>
                    <h3>Información del Cliente</h3>
                    <div>
                        <p>Nombre: ${data.cliente.nombre}</p>
                        <p>Dirección: ${data.cliente.direccion}</p>
                        <p>Barrio: ${data.cliente.barrio}</p>
                        <p>Teléfono: ${data.cliente.telefono}</p>
                    </div>
                `;
                detallesContainer.style.display = 'block';
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Función para mostrar mensajes de éxito o error
function mostrarMensaje(tipo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: tipo === 'success' ? 'Éxito' : 'Error',
        text: mensaje,
        timer: 3000,
        showConfirmButton: false
    });
}

// Verificar si hay mensajes de éxito o error en la URL
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    const error = urlParams.get('error');

    if (mensaje) {
        mostrarMensaje('success', mensaje);
    } else if (error) {
        mostrarMensaje('error', error);
    }
});