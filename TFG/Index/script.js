document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const userType = document.getElementById('userType').value;
    
    // Simulación de inicio de sesión
    alert(`Iniciando sesión como ${userType}`);
    // Aquí irían las llamadas a tu backend
});

document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Registro exitoso');
    // Aquí irían las llamadas a tu backend
});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

