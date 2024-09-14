let cart = JSON.parse(localStorage.getItem('shoppingCart')) || [];
let totalPrice = 0;

document.getElementById('toggle-car').addEventListener('click', function (event) {
    event.preventDefault();
    toggleSidebar();
});

function toggleSidebar() {
    var sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
    document.body.classList.toggle('sidebar-active');
    document.querySelector('.dashboard').classList.toggle('sidebar-active');
}

const addToCartButtons = document.querySelectorAll('.addToCartButton');
addToCartButtons.forEach((button) => {
    button.addEventListener('click', addToCart);
});

function addToCart(event) {
    const button = event.target;
    const productCard = button.closest('.dashboard-card');
    const productId = productCard.dataset.productId;
    const productName = productCard.querySelector('h4').innerText.trim();
    const productPriceStr = productCard.querySelector('.span').innerText;
    const productPrice = parseFloat(productPriceStr.replace('$', '').replace(',', ''));
    const productImageSrc = productCard.querySelector('img').src;

    const existingProductIndex = cart.findIndex(item => item.id === productId);
    if (existingProductIndex !== -1) {
        cart[existingProductIndex].quantity += 1;
    } else {
        const product = {
            id: productId,
            name: productName,
            price: productPrice,
            imageSrc: productImageSrc,
            quantity: 1
        };
        cart.push(product);
    }
    updateCart();
    localStorage.setItem('shoppingCart', JSON.stringify(cart));

    Swal.fire({
        position: 'bottom-end',
        icon: 'success',
        iconColor: '#4CAF50',
        background: '#020202',
        confirmButtonColor: '#f15d07',
        title: '¡Agregado al carrito!',
        text: `El producto ${productName} ha sido agregado al carrito de compras.`,
        showConfirmButton: false,
        customClass: {
            popup: 'alert-text-color'
        },
        timer: 1500
    });
}

function updateCart() {
    const cartWrapper = document.querySelector('.order-wrapper');
    cartWrapper.innerHTML = '';
    totalPrice = 0;

    cart.forEach((product, index) => {
        const cartCard = document.createElement('div');
        cartCard.classList.add('order-card');
        cartCard.setAttribute('data-product-id', product.id);

        const productImage = document.createElement('img');
        productImage.classList.add('order-image');
        productImage.src = product.imageSrc;

        const orderDetails = document.createElement('div');
        orderDetails.classList.add('order-details');

        const productName = document.createElement('p');
        productName.classList.add('order-name');
        productName.textContent = product.name;

        const productPrice = document.createElement('p');
        productPrice.classList.add('order-price');
        productPrice.textContent = `$${Number(product.price * product.quantity).toLocaleString('es-CO')}`;

        orderDetails.appendChild(productName);
        orderDetails.appendChild(productPrice);

        cartCard.appendChild(productImage);
        cartCard.appendChild(orderDetails);

        const quantityControls = document.createElement('div');
        quantityControls.classList.add('quantity-controls');

        const minusButton = document.createElement('button');
        minusButton.classList.add('quantity-btn', 'minus');
        minusButton.textContent = '-';
        minusButton.addEventListener('click', () => updateQuantity(index, -1));

        const cartCounter = document.createElement('span');
        cartCounter.classList.add('cart-counter');
        cartCounter.textContent = product.quantity;

        const plusButton = document.createElement('button');
        plusButton.classList.add('quantity-btn', 'plus');
        plusButton.textContent = '+';
        plusButton.addEventListener('click', () => updateQuantity(index, 1));

        quantityControls.appendChild(minusButton);
        quantityControls.appendChild(cartCounter);
        quantityControls.appendChild(plusButton);

        cartCard.appendChild(quantityControls);

        const removeButton = document.createElement('span');
        removeButton.classList.add('order-remove');
        removeButton.innerHTML = '&times;';
        removeButton.addEventListener('click', () => removeFromCart(index));
        cartCard.appendChild(removeButton);

        cartWrapper.appendChild(cartCard);

        totalPrice += product.price * product.quantity;
    });

    updateTotalPrice();
    updateCartIcon();
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity < 1) {
        removeFromCart(index);
    } else {
        updateCart();
        localStorage.setItem('shoppingCart', JSON.stringify(cart));
    }
}

function updateTotalPrice() {
    const roundedTotal = Math.round(totalPrice * 100) / 100;
    const totalPriceFormatted = roundedTotal.toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
    const totalPriceElement = document.createElement('h3');
    totalPriceElement.innerText = `Total: ${totalPriceFormatted}`;
    const totalContainer = document.querySelector('#total-container');
    totalContainer.innerHTML = '';
    totalContainer.appendChild(totalPriceElement);
}

document.querySelector('.order-wrapper').addEventListener('click', function(event) {
    if (event.target.classList.contains('quantity-btn')) {
        const productCard = event.target.closest('.order-card');
        const productId = productCard.getAttribute('data-product-id');
        const change = event.target.classList.contains('plus') ? 1 : -1;
        const index = cart.findIndex(item => item.id === productId);
        if (index !== -1) {
            updateQuantity(index, change);
        }
    }
});

updateCart();

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCart();
    localStorage.setItem('shoppingCart', JSON.stringify(cart));
}

function updateCartIcon() {
    const cartBadge = document.querySelector('.cart-badge');
    cartBadge.innerText = cart.length;

    // También actualizamos los contadores individuales
    const orderCards = document.querySelectorAll('.order-card');
    orderCards.forEach((card, index) => {
        const counter = card.querySelector('.cart-counter');
        if (counter) {
            counter.innerText = cart[index].quantity;
        }
    });
}

const payButton = document.querySelector('.checkout');
const modalContainer = document.getElementById('modalContainer');

payButton.addEventListener('click', function () {
    modalContainer.classList.add('modal');
    modalContainer.style.display = 'block';
    pagarAhora();
});

modalContainer.addEventListener('click', function (event) {
    if (event.target === modalContainer) {
        modalContainer.style.display = 'none';
    }
});

document.getElementById('exit').addEventListener('click', function () {
    modalContainer.classList.add('fade-out');
    setTimeout(() => {
        modalContainer.style.display = 'none';
        modalContainer.classList.remove('fade-out');
    }, 500);
});

function pagarAhora() {
    const carrito = cart.map(producto => ({
        id_producto: producto.id,
        nombre_producto: producto.name,
        precio_producto: Number(producto.price).toLocaleString('es-CO', { minimumFractionDigits: 0 }), // Asegúrate de que el precio no tenga decimales
        cantidad_producto: producto.quantity,
        nombre: document.getElementById('nombre').value,
        direccion: document.getElementById('direccion').value,
        barrio: document.getElementById('barrio').value,
        telefono: document.getElementById('telefono').value
    }));

    document.getElementById('productos').value = JSON.stringify(carrito);

    $.ajax({
        type: "POST",
        url: "./controller/pedidos_controller.php",
        data: $('#pedidoFormulario').serialize(),
        success: function (response) {
            console.log("Pedido realizado con éxito:", response);
            cart = [];
            updateCart();
            localStorage.removeItem('shoppingCart');
            Swal.fire({
                position: 'bottom-end',
                icon: 'success',
                iconColor: '#4CAF50',
                background: '#020202',
                confirmButtonColor: '#f15d07',
                title: 'Pedido realizado con éxito',
                text: '¡Gracias por tu compra!',
                showConfirmButton: false,
                customClass: {
                    popup: 'alert-text-color'
                },
                timer: 1500
            });
        },
        error: function (xhr, status, error) {
            console.error("Error al realizar el pedido:", error);
        }
    });
}

function obtenerCarrito() {
    return cart;
}

updateCart();

