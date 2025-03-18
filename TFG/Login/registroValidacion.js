document.getElementById("registrationForm").addEventListener("submit", function(event) {
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let userType = document.getElementById("userType").value;

    if (password !== confirmPassword) {
        alert("Las contraseñas no coinciden.");
        event.preventDefault();
        return;
    }

    if (userType === "") {
        alert("Por favor, selecciona un tipo de usuario.");
        event.preventDefault();
        return;
    }
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("loginForm").addEventListener("submit", function (event) {
        let email = document.getElementById("email").value.trim();
        let password = document.getElementById("password").value.trim();

        // Verificación de campos vacíos
        if (email === "") {
            alert("Por favor, ingresa tu correo electrónico.");
            event.preventDefault(); // Evita el envío del formulario
            return;
        }

        if (password === "") {
            alert("Por favor, ingresa tu contraseña.");
            event.preventDefault(); // Evita el envío del formulario
            return;
        }

        // Validación de formato de email (opcional)
        let emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        if (!emailRegex.test(email)) {
            alert("Por favor, ingresa un correo electrónico válido.");
            event.preventDefault(); // Evita el envío del formulario
            return;
        }
    });
});

//************************************************************************************* */
