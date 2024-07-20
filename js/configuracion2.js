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

