const imgs = document.querySelectorAll('.img-select a');
const imgBtns = [...imgs];
let imgId = 1;

imgBtns.forEach((imgItem) => {
    imgItem.addEventListener('click', (event) => {
        event.preventDefault();
        imgId = imgItem.dataset.id;
        slideImage();
    });
});

document.getElementById('toggle-car').addEventListener('click', function (event) {
    // Evita el comportamiento predeterminado del enlace
    event.preventDefault();
    toggleSidebar();
});

function toggleSidebar() {
    var sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
    document.body.classList.toggle('sidebar-active');
    document.querySelector('.dashboard').classList.toggle('sidebar-active');
}


function getCart() {
  return cart;
}

function syncCart() {
    const productsCart = getCart();
    cart = [...cart, ...productsCart];
    updateCart();
    localStorage.setItem('shoppingCart', JSON.stringify(cart));
  }




