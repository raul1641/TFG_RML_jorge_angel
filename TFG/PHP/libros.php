<?php
// Conexión a la base de datos
include '../conf/conexion.php'; // Incluye el archivo con la configuración de conexión a la BD

// Número de registros por página
$TP = 5; // TP = Total por Página. Define cuantos libros mostrar por página

// Obtener número total de registros
$consulta_total = "SELECT COUNT(*) as total FROM rmolinal_PROYECTO_JORGE_ANGEL.libro"; // Consulta SQL para contar todos los libros
$resultado_total = $conexion->query($consulta_total); // Ejecuta la consulta
$fila_total = $resultado_total->fetch_assoc(); // Obtiene el resultado como array asociativo
$NR = $fila_total['total']; // NR = Número de Registros totales. Almacena el total de libros

// Calcular número de páginas
$NP = ceil($NR / $TP); // NP = Número de Páginas. Calcula el total de páginas necesarias (redondea hacia arriba)

// Obtener la página actual
$PA = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // PA = Página Actual. Obtiene el número de página de la URL
if ($PA < 1) $PA = 1; // Validación para no permitir páginas menores a 1
if ($PA > $NP) $PA = $NP; // Validación para no exceder el número máximo de páginas

// Calcular registros de inicio
$RI = ($PA - 1) * $TP; // RI = Registro de Inicio. Calcula desde qué registro debe empezar en la BD

// Obtener datos de la página actual con seguridad
$consulta = "SELECT id_libro, titulo, autor, estado FROM rmolinal_PROYECTO_JORGE_ANGEL.libro LIMIT ?, ?"; // Consulta preparada
$stmt = $conexion->prepare($consulta); // Prepara la consulta SQL
$stmt->bind_param("ii", $RI, $TP); // Asigna los parámetros (enteros) para LIMIT
$stmt->execute(); // Ejecuta la consulta
$resultado = $stmt->get_result(); // Obtiene los resultados

// Mostrar resultados
if ($resultado->num_rows > 0) { // Si hay libros registrados
    while ($libro = $resultado->fetch_assoc()) { // Recorre cada libro
        echo "<tr> <!-- Crea fila en tabla HTML -->
                <td>{$libro['titulo']}</td> <!-- Muestra título -->
                <td>{$libro['autor']}</td> <!-- Muestra autor -->
                <td>" . ($libro['estado'] == 'disponible' ? 'Disponible' : 'Prestado') . "</td> <!-- Muestra estado traducido -->
                <td>";
        
        // Botones de acción según disponibilidad
        if ($libro['estado'] == 'disponible') {
            // Formulario para solicitar libro
            echo "<form method='POST' action='../PHP/solicitar.php'>
                    <input type='hidden' name='id_libro' value='{$libro['id_libro']}'>
                    <button type='submit' class='btn btn-success btn-sm'>Solicitar</button>
                  </form>";
        } else {
            // Botón desactivado si no está disponible
            echo "<button class='btn btn-secondary btn-sm' disabled>No disponible</button>";
        }

        echo "</td></tr>";
    }
} else {
    // Mensaje si no hay registros
    echo "<tr><td colspan='4' class='text-center'>No hay libros registrados</td></tr>";
}

echo "</tbody></table>"; // Cierra la tabla HTML

// Mostrar paginación debajo de la tabla
echo "<nav aria-label='Page navigation' class='mt-3'>";
echo "<ul class='pagination justify-content-center'>";

// Botones de navegación
if ($PA > 1) {
    // Enlace a primera página y página anterior
    echo "<li class='page-item'><a class='page-link' href='P_usuario.php?pagina=1'>Primera</a></li>";
    echo "<li class='page-item'><a class='page-link' href='P_usuario.php?pagina=" . ($PA - 1) . "'>Anterior</a></li>";
}

// Numeración de páginas
for ($i = 1; $i <= $NP; $i++) {
    $active = ($i == $PA) ? "active" : ""; // Resalta la página actual
    echo "<li class='page-item $active'><a class='page-link' href='P_usuario.php?pagina=$i'>$i</a></li>";
}

// Botones de navegación
if ($PA < $NP) {
    // Enlace a página siguiente y última página
    echo "<li class='page-item'><a class='page-link' href='P_usuario.php?pagina=" . ($PA + 1) . "'>Siguiente</a></li>";
    echo "<li class='page-item'><a class='page-link' href='P_usuario.php?pagina=$NP'>Última</a></li>";
}

echo "</ul></nav>";

// Cerrar conexión
$stmt->close(); // Cierra el statement preparado
$conexion->close(); // Cierra la conexión a la BD
?>
