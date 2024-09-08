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



/* ALERTAS PRODUCTOS */

document.addEventListener('DOMContentLoaded', function() {
    // Validación de nombre del producto
    const nombreProductoInput = document.getElementById('nombre');
    nombreProductoInput.addEventListener('blur', function() {
        validarNombreProducto(this);
    });

    // Validación de foto
    const fotoInput = document.getElementById('imagen');
    fotoInput.addEventListener('change', function() {
        validarFoto(this);
    });

    // Validación de descripción del producto
    const descripcionProductoInput = document.getElementById('descripcion');
    descripcionProductoInput.addEventListener('blur', function() {
        validarDescripcionProducto(this);
    });

    // Validación de valor unitario
    const valorUnitarioInput = document.getElementById('precio');
    valorUnitarioInput.addEventListener('blur', function() {
        validarValorUnitario(this);
    });

    // Validación de nombre del insumo
    const nombreInsumoInput = document.getElementById('insumo_1');
    nombreInsumoInput.addEventListener('change', function() {
        validarNombreInsumo(this);
    });

    // Validación de cantidad de insumo
    const cantidadInsumoInput = document.getElementById('cantidad_insumo_1');
    cantidadInsumoInput.addEventListener('blur', function() {
        validarCantidadInsumo(this);
    });

    // Validación al enviar el formulario
    const form = document.getElementById('formAgregarProducto');
    form.addEventListener('submit', function(event) {
        if (!validarNombreProducto(nombreProductoInput) ||
            !validarFoto(fotoInput) ||
            !validarDescripcionProducto(descripcionProductoInput) ||
            !validarValorUnitario(valorUnitarioInput) ||
            !validarNombreInsumo(nombreInsumoInput) ||
            !validarCantidadInsumo(cantidadInsumoInput)) {
            event.preventDefault(); // Detener el envío si hay errores
        }
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
    if (!file) {
        mostrarAlerta('Debe seleccionar una foto.');
        return false;
    } else {
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
        mostrarAlerta('El valor unitario debe ser un número.');
        return false;
    } else if (valor <= 0) {
        mostrarAlerta('El valor unitario debe ser un número positivo.');
        return false;
    } else if (valor.toFixed(2).length > valor.toString().length) {
        mostrarAlerta('El valor unitario no puede tener más de dos decimales.');
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
