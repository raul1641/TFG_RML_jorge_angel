// Espera a que la página cargue completamente
document.addEventListener("DOMContentLoaded", () => {

    // Captura los clics en la sección de préstamos
    document.querySelector("#prestamos").addEventListener("click", (e) => {
        // Verifica si el clic fue en un botón de "Devolver"
        let boton = e.target.closest(".devolver-libro");
        
        if (boton) {
            let id = boton.dataset.id; // Obtiene el ID del préstamo desde el botón

            // Confirma si el usuario realmente quiere devolver el libro
            if (confirm("¿Devolver este libro?")) {
                devolverLibro(id, boton);
            }
        }
    });
});

// Función para enviar la solicitud de devolución al servidor
function devolverLibro(id, boton) {
    boton.textContent = "Procesando..."; // Muestra un mensaje en el botón
    boton.disabled = true; // Desactiva el botón para evitar clics múltiples

    // Enviar la petición AJAX con fetch
    fetch("../PHP/devolver.php", {
        method: "POST",
        body: new URLSearchParams({ id_prestamo: id })
    })
    .then(response => response.json()) // Convertir la respuesta a JSON
    .then(data => {
        if (data.success) {
            // Si el servidor confirma la devolución, actualizamos la fila en la tabla
            let fila = document.getElementById(`prestamo-${id}`);
            if (fila) {
                fila.cells[2].textContent = new Date().toLocaleString(); // Fecha de devolución actual
                fila.cells[3].textContent = "Devuelto"; // Cambia estado a "Devuelto"
                fila.cells[4].innerHTML = '<span class="badge bg-success">Devuelto</span>'; // Cambia el botón por un texto
            }
            alert("Libro devuelto con éxito"); // Muestra un mensaje de éxito
        } else {
            throw new Error(data.message); // Lanza un error si algo falla
        }
    })
    .catch(error => {
        // Si hay un error, vuelve a habilitar el botón y muestra un mensaje de error
        boton.textContent = "Devolver";
        boton.disabled = false;
        alert("Error: " + (error.message || "No se pudo completar la devolución"));
    });
}
