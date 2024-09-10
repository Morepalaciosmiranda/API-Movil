/* ALERTAS PEDIDOS */
document.addEventListener('DOMContentLoaded', function() {
    // Validación de nombre del cliente
    const nombreClienteInput = document.getElementById('nombreCliente');
    nombreClienteInput.addEventListener('blur', function() {
        validarNombreCliente(this);
    });

    // Validación de producto (select)
    const productoInput = document.getElementById('producto');
    productoInput.addEventListener('change', function() {
        validarProducto(this);
    });

    // Validación de cantidad
    const cantidadInput = document.getElementById('cantidad');
    cantidadInput.addEventListener('blur', function() {
        validarCantidad(this);
    });

    // Validación de dirección
    const direccionInput = document.getElementById('calle');
    direccionInput.addEventListener('blur', function() {
        validarDireccion(this);
    });

    // Validación de interior
    const interiorInput = document.getElementById('interior');
    interiorInput.addEventListener('blur', function() {
        validarInterior(this);
    });

    // Validación de barrio
    const barrioInput = document.getElementById('barrio_cliente');
    barrioInput.addEventListener('blur', function() {
        validarBarrio(this);
    });

    // Validación de teléfono
    const telefonoInput = document.getElementById('telefono_cliente');
    telefonoInput.addEventListener('blur', function() {
        validarTelefono(this);
    });

    // Validación al enviar el formulario
    const form = document.getElementById('formNuevoPedido');
    form.addEventListener('submit', function(event) {
        if (!validarNombreCliente(nombreClienteInput) ||
            !validarProducto(productoInput) ||
            !validarCantidad(cantidadInput) ||
            !validarDireccion(direccionInput) ||
            !validarInterior(interiorInput) ||
            !validarBarrio(barrioInput) ||
            !validarTelefono(telefonoInput)) {
            event.preventDefault(); // Detener el envío si hay errores
        }
    });
});

// Validar nombre del cliente
function validarNombreCliente(input) {
    const valor = input.value.trim();
    if (valor === '') {
        mostrarAlerta('El nombre del cliente no puede estar vacío.');
        return false;
    } else if (valor.length < 2 || valor.length > 12) {
        mostrarAlerta('El nombre del cliente debe tener entre 2 y 12 caracteres.');
        return false;
    } else if (/^[0-9]+$/.test(valor)) {
        mostrarAlerta('El nombre del cliente no puede contener solo números.');
        return false;
    }
    return true;
}

// Validar producto (select)
function validarProducto(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un producto.');
        return false;
    }
    return true;
}

// Validar cantidad
function validarCantidad(input) {
    const cantidad = parseInt(input.value, 10);
    if (isNaN(cantidad)) {
        mostrarAlerta('La cantidad debe ser un número entero.');
        return false;
    } else if (cantidad <= 0) {
        mostrarAlerta('La cantidad debe ser mayor a 0.');
        return false;
    } else if (cantidad > 100) {
        mostrarAlerta('La cantidad no puede ser mayor a 100.');
        return false;
    }
    return true;
}

// Validar dirección
function validarDireccion(input) {
    const valor = input.value.trim();
    if (valor === '') {
        mostrarAlerta('La dirección no puede estar vacía.');
        return false;
    } else if (valor.length < 5 || valor.length > 50) {
        mostrarAlerta('La dirección debe tener entre 5 y 50 caracteres.');
        return false;
    }
    return true;
}

// Validar interior
function validarInterior(input) {
    const valor = input.value.trim();
    const numero = parseInt(valor, 10);
    if (valor === '') {
        mostrarAlerta('El interior no puede estar vacío.');
        return false;
    } else if (isNaN(numero)) {
        mostrarAlerta('El interior debe ser un número entero.');
        return false;
    } else if (numero <= 0) {
        mostrarAlerta('El interior debe ser mayor a 0.');
        return false;
    } else if (numero > 100) {
        mostrarAlerta('El interior no puede ser mayor a 100.');
        return false;
    }
    return true;
}

// Validar barrio
function validarBarrio(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un barrio.');
        return false;
    }
    return true;
}

// Validar teléfono
function validarTelefono(input) {
    const valor = input.value.trim();
    if (valor === '') {
        mostrarAlerta('El teléfono no puede estar vacío.');
        return false;
    } else if (!/^\d{10}$/.test(valor)) {
        mostrarAlerta('El teléfono debe tener 10 dígitos y solo contener números.');
        return false;
    }
    return true;
}

// Función para mostrar alertas
function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}


/* ALERTAS PRODUCTOS */

document.addEventListener('DOMContentLoaded', function() {
    // Validaciones para el formulario de creación
    const nombreProductoInput = document.getElementById('nombre');
    nombreProductoInput.addEventListener('blur', function() {
        validarNombreProducto(this);
    });

    const descripcionProductoInput = document.getElementById('descripcion');
    descripcionProductoInput.addEventListener('blur', function() {
        validarDescripcionProducto(this);
    });

    const valorUnitarioInput = document.getElementById('precio');
    valorUnitarioInput.addEventListener('blur', function() {
        validarValorUnitario(this);
    });

    const fotoInput = document.getElementById('imagen');
    fotoInput.addEventListener('change', function() {
        validarFoto(this);
    });

    const nombreInsumoInput = document.getElementById('insumo_1');
    nombreInsumoInput.addEventListener('change', function() {
        validarNombreInsumo(this);
    });

    const cantidadInsumoInput = document.getElementById('cantidad_insumo_1');
    cantidadInsumoInput.addEventListener('blur', function() {
        validarCantidadInsumo(this);
    });

    // Validaciones para el formulario de edición
    const editNombreProductoInput = document.getElementById('edit-nombre');
    editNombreProductoInput.addEventListener('blur', function() {
        validarNombreProducto(this);
    });

    const editDescripcionProductoInput = document.getElementById('edit-descripcion');
    editDescripcionProductoInput.addEventListener('blur', function() {
        validarDescripcionProducto(this);
    });

    const editValorUnitarioInput = document.getElementById('edit-precio');
    editValorUnitarioInput.addEventListener('blur', function() {
        validarValorUnitario(this);
    });

    const editFotoInput = document.getElementById('edit-imagen');
    editFotoInput.addEventListener('change', function() {
        validarFoto(this);
    });

});

function validarNombreProducto(input) {
    const nombre = input.value;
    const regex = /^[a-zA-Z0-9\s]{3,30}$/;

    if (nombre.length === 0) {
        mostrarAlerta('El campo de nombre del producto no puede estar vacío.');
        return false;
    } else if (nombre.length > 30) {
        mostrarAlerta('El nombre del producto no puede tener más de 30 caracteres.');
        return false;
    } else if (nombre.length < 3) {
        mostrarAlerta('El nombre del producto debe tener al menos 3 caracteres.');
        return false;
    } else if (!regex.test(nombre)) {
        mostrarAlerta('El nombre del producto no puede contener caracteres especiales.');
        return false;
    }
    return true;
}

function validarFoto(input) {
    const file = input.files[0];
    if (file) {
        const validFormats = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validFormats.includes(file.type)) {
            mostrarAlerta('Formato de archivo no válido. Solo se permiten JPEG, PNG y GIF.');
            return false;
        } else if (file.size > maxSize) {
            mostrarAlerta('El tamaño de la foto debe ser menor a 5MB.');
            return false;
        }
    }
    return true;
}

function validarDescripcionProducto(input) {
    const descripcion = input.value;
    const regexEspeciales = /^[^!@#$%^&*(),?":{}|<>]*$/;

    if (descripcion.length === 0) {
        mostrarAlerta('El campo de descripción del producto no puede estar vacío.');
        return false;
    } else if (descripcion.length < 5 || descripcion.length > 300) {
        mostrarAlerta('La descripción debe tener entre 5 y 300 caracteres.');
        return false;
    } else if (!regexEspeciales.test(descripcion)) {
        mostrarAlerta('La descripción no puede contener caracteres especiales.');
        return false;
    } else if (/^\d+$/.test(descripcion)) {
        mostrarAlerta('La descripción no puede contener solo números.');
        return false;
    }
    return true;
}

function validarValorUnitario(input) {
    const valor = parseFloat(input.value);

    if (isNaN(valor)) {
        mostrarAlerta('El precio debe ser un número.');
        return false;
    } else if (valor <= 0) {
        mostrarAlerta('El precio debe ser un número positivo.');
        return false;
    }
    return true;
}

function validarNombreInsumo(input) {
    if (input.value.trim() === '') {
        mostrarAlerta('Debe seleccionar un insumo.');
        return false;
    }
    return true;
}

function validarCantidadInsumo(input) {
    const cantidad = parseInt(input.value, 10);

    if (isNaN(cantidad)) {
        mostrarAlerta('La cantidad de insumo debe ser un número.');
        return false;
    } else if (cantidad <= 0) {
        mostrarAlerta('La cantidad de insumo debe ser mayor a 0.');
        return false;
    } else if (cantidad > 15) {
        mostrarAlerta('La cantidad de insumo no puede ser mayor a 15.');
        return false;
    } else if (!Number.isInteger(cantidad)) {
        mostrarAlerta('La cantidad de insumo debe ser un número entero.');
        return false;
    }
    return true;
}

function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}
