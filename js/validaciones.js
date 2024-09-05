/* ALERTAS PROVEEDORES */

document.addEventListener('DOMContentLoaded', function() {
    // Validación de nombre
    const nombreInput = document.getElementById('nombre');
    nombreInput.addEventListener('blur', function() {
        validarNombre(this);
    });

    // Validación de correo electrónico
    const correoInput = document.getElementById('correo');
    correoInput.addEventListener('blur', function() {
        validarCorreo(this);
    });

    // Validación de celular
    const celularInput = document.getElementById('celular');
    celularInput.addEventListener('blur', function() {
        validarCelular(this);
    });

    // Validación de contacto
    const contactoInput = document.getElementById('contacto');
    contactoInput.addEventListener('blur', function() {
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
    const nombreInsumoInput = document.getElementById('insumo-1');
    nombreInsumoInput.addEventListener('blur', function() {
        validarNombreInsumo(this);
    });

    // Validación de cantidad de insumo
    const cantidadInsumoInput = document.getElementById('cantidad_insumo_1');
    cantidadInsumoInput.addEventListener('blur', function() {
        validarCantidadInsumo(this);
    });
});

function validarNombreProducto(input) {
    const nombre = input.value;
    const regex = /^[a-zA-Z0-9\s]{3,30}$/;

    if (nombre.length === 0) {
        mostrarAlerta('El campo de nombre del producto no puede estar vacío.');
    } else if (nombre.length > 30) {
        mostrarAlerta('El nombre del producto no puede tener más de 30 caracteres.');
    } else if (nombre.length <= 3) {
        mostrarAlerta('El nombre del producto debe tener más de 3 caracteres.');
    } else if (!/^[a-zA-Z0-9\s]+$/.test(nombre)) {
        mostrarAlerta('El nombre del producto no puede contener caracteres especiales.');
    }
}

function validarFoto(input) {
    const file = input.files[0];
    if (!file) {
        mostrarAlerta('Debe seleccionar una foto.');
    } else {
        const validFormats = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validFormats.includes(file.type)) {
            mostrarAlerta('Formato de archivo no válido. Solo se permiten JPEG, PNG y GIF.');
        } else if (file.size > maxSize) {
            mostrarAlerta('El tamaño de la foto debe ser menor a 5MB.');
        }
    }
}

function validarDescripcionProducto(input) {
    const descripcion = input.value;
    const regexEspeciales = /^[^!@#$%^&*(),?":{}|<>]*$/;

    if (descripcion.length === 0) {
        mostrarAlerta('El campo de descripción del producto no puede estar vacío.');
    } else if (descripcion.length < 5 || descripcion.length > 300) {
        mostrarAlerta('La descripción debe tener entre 10 y 300 caracteres.');
    } else if (!regexEspeciales.test(descripcion) && descripcion.length > 0) {
        mostrarAlerta('La descripción no puede contener caracteres especiales.');
    } else if (/^\d+$/.test(descripcion)) {
        mostrarAlerta('La descripción no puede contener solo números.');
    }
}

function validarValorUnitario(input) {
    const valor = parseFloat(input.value);

    if (isNaN(valor)) {
        mostrarAlerta('El valor unitario debe ser un número.');
    } else if (valor <= 0) {
        mostrarAlerta('El valor unitario debe ser un número positivo.');
    } else if (!Number.isInteger(valor) && valor.toFixed(2).length > valor.toString().length) {
        mostrarAlerta('El valor unitario no puede tener más de dos decimales.');
    }
}

function validarNombreInsumo(input) {
    if (input.value.trim() === '') {
        mostrarAlerta('Debe seleccionar un insumo.');
    }
}

function validarCantidadInsumo(input) {
    const cantidad = parseInt(input.value, 10);

    if (isNaN(cantidad)) {
        mostrarAlerta('La cantidad de insumo debe ser un número.');
    } else if (cantidad <= 0) {
        mostrarAlerta('La cantidad de insumo debe ser mayor a 0.');
    } else if (cantidad > 15) {
        mostrarAlerta('La cantidad de insumo no puede ser mayor a 15.');
    } else if (!Number.isInteger(cantidad)) {
        mostrarAlerta('La cantidad de insumo debe ser un número entero.');
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
    // Validación de nombre del insumo
    const nombreInsumoInput = document.getElementById('nombre_insumo');
    nombreInsumoInput.addEventListener('blur', function() {
        validarNombreInsumo(this);
    });

    // Validación de proveedor
    const proveedorInput = document.getElementById('id_proveedor');
    proveedorInput.addEventListener('change', function() {
        validarProveedor(this);
    });

    // Validación de precio
    const precioInput = document.getElementById('precio');
    precioInput.addEventListener('blur', function() {
        validarPrecio(this);
    });

    // Validación de fecha de vencimiento
    const fechaVencimientoInput = document.getElementById('fecha_vencimiento');
    fechaVencimientoInput.addEventListener('change', function() {
        validarFechaVencimiento(this);
    });

    // Validación de marca
    const marcaInput = document.getElementById('marca');
    marcaInput.addEventListener('blur', function() {
        validarMarca(this);
    });

    // Validación de cantidad
    const cantidadInput = document.getElementById('cantidad');
    cantidadInput.addEventListener('blur', function() {
        validarCantidad(this);
    });

    // Validación de estado del insumo
    const estadoInsumoInput = document.getElementById('estado_insumo');
    estadoInsumoInput.addEventListener('change', function() {
        validarEstadoInsumo(this);
    });
});

function validarNombreInsumo(input) {
    const nombre = input.value;
    const regex = /^[a-zA-Z0-9\s]{3,25}$/;

    if (nombre.length < 3) {
        mostrarAlerta('El nombre del insumo debe tener al menos 5 caracteres.');
    } else if (nombre.length > 25) {
        mostrarAlerta('El nombre del insumo no puede exceder los 50 caracteres.');
    } else if (/[^a-zA-Z0-9\s]/.test(nombre)) {
        mostrarAlerta('El nombre del insumo solo puede contener caracteres alfanuméricos.');
    } else if (nombre.trim() === '') {
        mostrarAlerta('El nombre del insumo no puede estar vacío.');
    } else if (/[\W_]/.test(nombre)) {
        mostrarAlerta('El nombre del insumo no puede contener caracteres especiales.');
    }
}

function validarProveedor(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un proveedor.');
    }
}

function validarPrecio(input) {
    const precio = input.value;
    const regexPrecio = /^\d+(\.\d{1,2})?$/;

    if (precio === '') {
        mostrarAlerta('El campo de precio no puede estar vacío.');
    } else if (parseFloat(precio) === 0) {
        mostrarAlerta('El valor del precio debe ser mayor a 0.');
    } else if (parseFloat(precio) < 0) {
        mostrarAlerta('El precio no puede ser un número negativo.');
    } else if (!regexPrecio.test(precio)) {
        mostrarAlerta('El precio debe ser un número positivo con hasta dos decimales.');
    }
}

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

function validarMarca(input) {
    const marca = input.value;
    const regex = /^[a-zA-Z0-9,\.\s]{3,25}$/;

    if (marca.trim() === '') {
        mostrarAlerta('El campo de marca no puede estar vacío.');
    } else if (marca.length < 3) {
        mostrarAlerta('La marca debe tener al menos 10 caracteres.');
    } else if (marca.length > 25) {
        mostrarAlerta('La marca no puede exceder los 25 caracteres.');
    } else if (/^[\W]+$/.test(marca)) {
        mostrarAlerta('La marca no puede contener solo caracteres especiales.');
    } else if (/^\d+$/.test(marca)) {
        mostrarAlerta('La marca no puede contener solo números.');
    }
}

function validarCantidad(input) {
    const cantidad = input.value;
    const regexCantidad = /^\d+$/;

    if (cantidad === '') {
        mostrarAlerta('El campo de cantidad no puede estar vacío.');
    } else if (parseInt(cantidad) <= 0) {
        mostrarAlerta('La cantidad debe ser un número positivo mayor a 0.');
    } else if (parseInt(cantidad) > 99) {
        mostrarAlerta('La cantidad no puede ser mayor a 99.');
    } else if (!regexCantidad.test(cantidad)) {
        mostrarAlerta('La cantidad debe ser un número entero.');
    }
}

function validarEstadoInsumo(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un estado para el insumo.');
    }
}

function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}


/* ALERTAS COMPRAS */

document.addEventListener('DOMContentLoaded', function() {
    // Validación de usuario
    const usuarioInput = document.getElementById('id_usuario');
    usuarioInput.addEventListener('change', function() {
        validarUsuario(this);
    });

    // Validación de proveedor
    const proveedorInput = document.getElementById('id_proveedor');
    proveedorInput.addEventListener('change', function() {
        validarProveedor(this);
    });

    // Validación de fecha de compra
    const fechaCompraInput = document.getElementById('fecha_compra');
    fechaCompraInput.addEventListener('change', function() {
        validarFechaCompra(this);
    });

    // Validación de subtotal
    const subtotalInput = document.getElementById('subtotal');
    subtotalInput.addEventListener('blur', function() {
        validarSubtotal(this);
    });

    // Validación de cantidad
    const cantidadInput = document.getElementById('cantidad');
    cantidadInput.addEventListener('blur', function() {
        validarCantidad(this);
    });

    // Validación de valor unitario
    const valorUnitarioInput = document.getElementById('valor_unitario');
    valorUnitarioInput.addEventListener('blur', function() {
        validarValorUnitario(this);
    });
});

function validarUsuario(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un usuario.');
    }
}

function validarProveedor(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un proveedor.');
    }
}

function validarFechaCompra(input) {
    const fecha = new Date(input.value);
    const hoy = new Date();
    const dosMesesDesdeHoy = new Date();
    dosMesesDesdeHoy.setMonth(hoy.getMonth() + 2);

    if (fecha < hoy) {
        mostrarAlerta('La fecha de compra debe ser posterior a 5 días.');
    } else if (fecha > dosMesesDesdeHoy) {
        mostrarAlerta('La fecha de compra no puede ser mayor a dos meses desde hoy.');
    }
}

function validarSubtotal(input) {
    const subtotal = input.value;
    if (subtotal === '') {
        mostrarAlerta('El campo de subtotal no puede estar vacío.');
    } else if (parseFloat(subtotal) < 0) {
        mostrarAlerta('El subtotal no puede ser un número negativo.');
    } else if (/[^0-9.]/.test(subtotal)) {
        mostrarAlerta('El subtotal solo puede contener caracteres numéricos.');
    } else if (subtotal.length > 15) {
        mostrarAlerta('El subtotal no puede tener más de 15 caracteres.');
    } 
}

function validarCantidad(input) {
    const cantidad = input.value;
    if (cantidad === '') {
        mostrarAlerta('El campo de cantidad no puede estar vacío.');
    } else if (parseInt(cantidad) <= 0) {
        mostrarAlerta('La cantidad debe ser un número positivo mayor a 0.');
    } else if (!/^\d+$/.test(cantidad)) {
        mostrarAlerta('La cantidad debe ser un número entero.');
    }
}

function validarValorUnitario(input) {
    const valor = parseFloat(input.value);

    if (isNaN(valor)) {
        mostrarAlerta('El valor unitario debe ser un número.');
    } else if (valor <= 0) {
        mostrarAlerta('El valor unitario debe ser un número positivo.');
    } else if (!Number.isInteger(valor) && valor.toFixed(2).length > valor.toString().length) {
        mostrarAlerta('El valor unitario no puede tener más de dos decimales.');
    }
}

function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}


/* ALERTAS PEDIDOS */

document.addEventListener('DOMContentLoaded', function() {
    // Validación del nombre del cliente
    const nombreClienteInput = document.getElementById('nombreCliente');
    nombreClienteInput.addEventListener('blur', function() {
        validarNombreCliente(this);
    });

    // Validación del producto
    const productoInput = document.getElementById('producto');
    productoInput.addEventListener('change', function() {
        validarProducto(this);
    });

    // Validación de cantidad
    const cantidadInput = document.getElementById('cantidad');
    cantidadInput.addEventListener('blur', function() {
        validarCantidad(this);
    });

    // Validación de la dirección
    const direccionInput = document.getElementById('calle');
    direccionInput.addEventListener('blur', function() {
        validarDireccion(this);
    });

    // Validación del interior
    const interiorInput = document.getElementById('interior');
    interiorInput.addEventListener('blur', function() {
        validarInterior(this);
    });

    // Validación del barrio
    const barrioInput = document.getElementById('barrio_cliente');
    barrioInput.addEventListener('change', function() {
        validarBarrio(this);
    });

    // Validación del teléfono
    const telefonoInput = document.getElementById('telefono_cliente');
    telefonoInput.addEventListener('blur', function() {
        validarTelefono(this);
    });
});

function validarNombreCliente(input) {
    const valor = input.value.trim();
    if (valor === '') {
        mostrarAlerta('El nombre del cliente no puede estar vacío.');
    } else if (valor.length < 2 || valor.length > 12) {
        mostrarAlerta('El nombre del cliente debe tener entre 2 y 12 caracteres.');
    } else if (/^[0-9]+$/.test(valor)) {
        mostrarAlerta('El nombre del cliente no puede contener solo números.');
    }
}

function validarProducto(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un producto.');
    }
}

function validarCantidad(input) {
    const cantidad = parseInt(input.value, 10);
    if (isNaN(cantidad)) {
        mostrarAlerta('La cantidad debe ser un número entero.');
    } else if (cantidad <= 0) {
        mostrarAlerta('La cantidad debe ser mayor a 0.');
    } else if (cantidad > 100) {
        mostrarAlerta('La cantidad no puede ser mayor a 100.');
    }
}

function validarDireccion(input) {
    const valor = input.value.trim();
    if (valor === '') {
        mostrarAlerta('La dirección no puede estar vacía.');
    } else if (valor.length < 5 || valor.length > 50) {
        mostrarAlerta('La dirección debe tener entre 5 y 50 caracteres.');
    }
}

function validarInterior(input) {
    const valor = input.value.trim();
    const numero = parseInt(valor, 10);
    if (valor === '') {
        mostrarAlerta('El interior no puede estar vacío.');
    } else if (isNaN(numero)) {
        mostrarAlerta('El interior debe ser un número entero.');
    } else if (numero <= 0) {
        mostrarAlerta('El interior debe ser mayor a 0.');
    } else if (numero > 100) {
        mostrarAlerta('El interior no puede ser mayor a 100.');
    }
}

function validarBarrio(input) {
    if (input.value === '') {
        mostrarAlerta('Debe seleccionar un barrio.');
    }
}

function validarTelefono(input) {
    const valor = input.value.trim();
    if (valor === '') {
        mostrarAlerta('El teléfono no puede estar vacío.');
    } else if (!/^\d{10}$/.test(valor)) {
        mostrarAlerta('El teléfono debe tener 10 dígitos y solo contener números.');
    }
}

// Función para mostrar alertas
function mostrarAlerta(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de validación',
        text: mensaje,
    });
}
