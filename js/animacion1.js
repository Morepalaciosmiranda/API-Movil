window.onload = function() {
    document.getElementById('background').style.animation = 'none';
    void document.getElementById('background').offsetWidth; // Reflow para reiniciar la animación
    document.getElementById('background').style.animation = 'slideDown 1s ease-out';
    
    
    const burgerEyes = document.getElementById('burger-eyes');
    setTimeout(() => {
        burgerEyes.style.display = 'block';
    }, 1000); 
};

function logout() {
    Swal.fire({
        title: 'Cerrando sesión',
        html: 'Espere un momento...',
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });
    setTimeout(() => {
        Swal.close(); 
        window.location.href = '../loginRegister.php';
    }, 2000);
}

function adminPanel() {
    Swal.fire({
        title: 'Entrando al panel administrativo',
        html: 'Espere un momento...',
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });

   
    setTimeout(() => {
        Swal.close(); 
        window.location.href = '../views/inicio.php';
    }, 3000);
}
