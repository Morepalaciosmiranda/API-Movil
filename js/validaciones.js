/* ALERTAS PROVEEDORES */

document.addEventListener('DOMContentLoaded', function() {
    // Validaciones para el formulario de creación
    const nombreInput = document.getElementById('nombre');
    nombreInput.addEventListener('blur', function() {
        validarNombre(this);
    });

    const correoInput = document.getElementById('correo');
    correoInput.addEventListener('blur', function() {
        validarCorreo(this);
    });

    const celularInput = document.getElementById('celular');
    celularInput.addEventListener('blur', function() {
        validarCelular(this);
    });

    const contactoInput = document.getElementById('contacto');
    contactoInput.addEventListener('blur', function() {
        validarContacto(this);
    });

    // Validaciones para el formulario de edición
    const editNombreInput = document.getElementById('edit-nombre');
    editNombreInput.addEventListener('blur', function() {
        validarNombre(this);
    });

    const editCorreoInput = document.getElementById('edit-correo');
    editCorreoInput.addEventListener('blur', function() {
        validarCorreo(this);
    });

    const editCelularInput = document.getElementById('edit-celular');
    editCelularInput.addEventListener('blur', function() {
        validarCelular(this);
    });

    const editContactoInput = document.getElementById('edit-contacto');
    editContactoInput.addEventListener('blur', function() {
        validarContacto(this);
    });
});

function validarNombre(input) {
    const nombre = input.value;
    const regex = /^[a-zA-Z\s]{3,25}$/;

    if (!regex.test(nombre) && nombre.length > 0) {
        mostrarAlerta('El nombre debe tener entre 3 y 25 caracteres y solo puede contener letras y espacios.');
    }
}

function validarCorreo(input) {
    const correo = input.value;
    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const regexDominio = /\.[a-z]{2,}$/;
    const regexEspacios = /\s/;
    const regexMultiplesArrobas = /@.*@/;

    if (correo.length > 0) {
        if (!correo.includes('@')) {
            mostrarAlerta('El correo electrónico debe contener un @.');
        } else if (!regexDominio.test(correo)) {
            mostrarAlerta('El correo electrónico debe contener un dominio válido.');
        } else if (!correo.endsWith('.com')) {
            mostrarAlerta('El correo electrónico debe terminar en .com.');
        } else if (regexEspacios.test(correo)) {
            mostrarAlerta('El correo electrónico no puede contener espacios.');
        } else if (regexMultiplesArrobas.test(correo)) {
            mostrarAlerta('El correo electrónico no puede contener múltiples @.');
        } else if (!regexCorreo.test(correo)) {
            mostrarAlerta('El formato del correo electrónico no es válido.');
        }
    }
}

function validarCelular(input) {
    const celular = input.value;
    const regexNumero = /^[0-9]{10}$/;

    if (celular.length > 0) {
        if (!regexNumero.test(celular)) {
            mostrarAlerta('El celular debe contener exactamente 10 dígitos y solo números.');
        }
    }
}

function validarContacto(input) {
    const contacto = input.value;
    const regex = /^[a-zA-Z0-9\s]{1,25}$/;

    if (!regex.test(contacto) && contacto.length > 0) {
        mostrarAlerta('El contacto debe tener hasta 25 caracteres y no puede contener caracteres especiales.');
    }
}

function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}


/* ALERTAS INSUMOS */

document.addEventListener('DOMContentLoaded', function() {
    // Validaciones en creación
    const nombreInsumoInput = document.getElementById('nombre_insumo');
    if (nombreInsumoInput) {
        nombreInsumoInput.addEventListener('change', function() {
            validarNombreInsumo(this);
        });
    }

    const marcaInput = document.getElementById('marca');
    if (marcaInput) {
        marcaInput.addEventListener('blur', function() {
            validarMarca(this);
        });
    }

    const fechaVencimientoInput = document.getElementById('fecha_vencimiento');
    if (fechaVencimientoInput) {
        fechaVencimientoInput.addEventListener('change', function() {
            validarFechaVencimiento(this);
        });
    }

    const estadoInsumoInput = document.getElementById('estado_insumo');
    if (estadoInsumoInput) {
        estadoInsumoInput.addEventListener('change', function() {
            validarEstadoInsumo(this);
        });
    }

    // Validaciones en edición
    const editNombreInsumoInput = document.getElementById('edit-nombre_insumo');
    if (editNombreInsumoInput) {
        editNombreInsumoInput.addEventListener('change', function() {
            validarNombreInsumo(this);
        });
    }

    const editMarcaInput = document.getElementById('edit-marca');
    if (editMarcaInput) {
        editMarcaInput.addEventListener('blur', function() {
            validarMarca(this);
        });
    }

    const editFechaVencimientoInput = document.getElementById('edit-fecha_vencimiento');
    if (editFechaVencimientoInput) {
        editFechaVencimientoInput.addEventListener('change', function() {
            validarFechaVencimiento(this);
        });
    }

    const editEstadoInsumoInput = document.getElementById('edit-estado_insumo');
    if (editEstadoInsumoInput) {
        editEstadoInsumoInput.addEventListener('change', function() {
            validarEstadoInsumo(this);
        });
    }
});

// Validar nombre del insumo (select)
function validarNombreInsumo(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un insumo.');
    }
}

// Validar marca
function validarMarca(input) {
    const marca = input.value;
    const regex = /^[a-zA-Z0-9,\.\s]{3,25}$/;

    if (marca.trim() === '') {
        mostrarAlerta('El campo de marca no puede estar vacío.');
    } else if (marca.length < 3) {
        mostrarAlerta('La marca debe tener al menos 3 caracteres.');
    } else if (marca.length > 25) {
        mostrarAlerta('La marca no puede exceder los 25 caracteres.');
    } else if (/^[\W]+$/.test(marca)) {
        mostrarAlerta('La marca no puede contener solo caracteres especiales.');
    } else if (/^\d+$/.test(marca)) {
        mostrarAlerta('La marca no puede contener solo números.');
    }
}

// Validar fecha de vencimiento
function validarFechaVencimiento(input) {
    const fecha = new Date(input.value);
    const hoy = new Date();
    const dosMesesDesdeHoy = new Date();
    dosMesesDesdeHoy.setMonth(hoy.getMonth() + 2);

    if (fecha < hoy) {
        mostrarAlerta('La fecha de vencimiento debe ser posterior al día actual.');
    } else if (fecha > dosMesesDesdeHoy) {
        mostrarAlerta('La fecha de vencimiento no puede ser mayor a dos meses desde hoy.');
    }
}

// Validar estado del insumo (select)
function validarEstadoInsumo(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un estado para el insumo.');
    }
}

// Función genérica para mostrar alertas
function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}


/* ALERTAS COMPRAS */
document.addEventListener('DOMContentLoaded', function() {
    // Validación de proveedor en creación
    const proveedorInput = document.getElementById('id_proveedor');
    if (proveedorInput) {
        proveedorInput.addEventListener('change', function() {
            validarProveedor(this);
        });
    }

    // Validación de nombre de insumo en creación
    const nombreInsumoInput = document.getElementById('nombre_insumos');
    if (nombreInsumoInput) {
        nombreInsumoInput.addEventListener('blur', function() {
            validarNombreInsumo(this);
        });
    }

    // Validación de fecha de compra en creación
    const fechaCompraInput = document.getElementById('fecha_compra');
    if (fechaCompraInput) {
        fechaCompraInput.addEventListener('change', function() {
            validarFechaCompra(this);
        });
    }

    // Validación de total de compra en creación
    const totalCompraInput = document.getElementById('total_compra');
    if (totalCompraInput) {
        totalCompraInput.addEventListener('blur', function() {
            validarTotalCompra(this);
        });
    }

    // Validación de cantidad en creación
    const cantidadInput = document.getElementById('cantidad');
    if (cantidadInput) {
        cantidadInput.addEventListener('blur', function() {
            validarCantidad(this);
        });
    }

    // Validaciones en edición
    const editProveedorInput = document.getElementById('edit_id_proveedor');
    if (editProveedorInput) {
        editProveedorInput.addEventListener('change', function() {
            validarProveedor(this);
        });
    }

    const editNombreInsumoInput = document.getElementById('edit_nombre_insumos');
    if (editNombreInsumoInput) {
        editNombreInsumoInput.addEventListener('blur', function() {
            validarNombreInsumo(this);
        });
    }

    const editFechaCompraInput = document.getElementById('edit_fecha_compra');
    if (editFechaCompraInput) {
        editFechaCompraInput.addEventListener('change', function() {
            validarFechaCompra(this);
        });
    }

    const editTotalCompraInput = document.getElementById('edit_total_compra');
    if (editTotalCompraInput) {
        editTotalCompraInput.addEventListener('blur', function() {
            validarTotalCompra(this);
        });
    }

    const editCantidadInput = document.getElementById('edit_cantidad');
    if (editCantidadInput) {
        editCantidadInput.addEventListener('blur', function() {
            validarCantidad(this);
        });
    }
});

// Validación del proveedor
function validarProveedor(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un proveedor.');
    }
}

// Validación del nombre de insumo
function validarNombreInsumo(input) {
    const nombre = input.value;
    const regex = /^[a-zA-Z0-9\s]{3,25}$/;

    if (nombre.length < 3) {
        mostrarAlerta('El nombre del insumo debe tener al menos 3 caracteres.');
    } else if (nombre.length > 25) {
        mostrarAlerta('El nombre del insumo no puede exceder los 25 caracteres.');
    } else if (regex.test(nombre) === false) {
        mostrarAlerta('El nombre del insumo solo puede contener caracteres alfanuméricos y espacios.');
    } else if (nombre.trim() === '') {
        mostrarAlerta('El nombre del insumo no puede estar vacío.');
    }
}

// Validación de la fecha de compra
function validarFechaCompra(input) {
    const fecha = new Date(input.value);
    const hoy = new Date();
    const dosMesesDesdeHoy = new Date();
    dosMesesDesdeHoy.setMonth(hoy.getMonth() + 2);
    
    const cincoDiasAntes = new Date();
    cincoDiasAntes.setDate(hoy.getDate() - 5);

    if (fecha < cincoDiasAntes) {
        mostrarAlerta('La fecha de compra no puede ser anterior a 5 días.');
    } else if (fecha > dosMesesDesdeHoy) {
        mostrarAlerta('La fecha de compra no puede ser mayor a dos meses desde hoy.');
    }
}

// Validación del total de compra
function validarTotalCompra(input) {
    const totalCompra = input.value;
    if (totalCompra === '') {
        mostrarAlerta('El campo de total de compra no puede estar vacío.');
    } else if (parseFloat(totalCompra) < 0) {
        mostrarAlerta('El total de compra no puede ser un número negativo.');
    } else if (/[^0-9.]/.test(totalCompra)) {
        mostrarAlerta('El total de compra solo puede contener caracteres numéricos.');
    } else if (totalCompra.length > 25) {
        mostrarAlerta('El total de compra no puede tener más de 25 caracteres.');
    }
}

// Validación de cantidad
function validarCantidad(input) {
    const cantidad = input.value;
    if (cantidad === '') {
        mostrarAlerta('El campo de cantidad no puede estar vacío.');
    } else if (parseInt(cantidad) <= 0) {
        mostrarAlerta('La cantidad debe ser un número positivo mayor a 0.');
    } else if (!/^\d+$/.test(cantidad)) {
        mostrarAlerta('La cantidad debe ser un número entero.');
    } else if (parseInt(cantidad) > 100) {
        mostrarAlerta('La cantidad no puede ser mayor a 100.');
    }
}

// Función para mostrar alertas con SweetAlert
function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}



