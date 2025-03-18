<?php
include '../conf/conexion.php';

if (!isset($_SESSION['user_id'])) {
    echo "<tr><td colspan='5'>Inicia sesión</td></tr>";
    exit;
}

try {
    // Usar "Libro" y "Prestamo" con mayúsculas
    $stmt = $conexion->prepare("
    SELECT Prestamo.id_prestamo, Libro.titulo, Prestamo.fecha_prestamo, Prestamo.fecha_devolucion, Prestamo.estado FROM Prestamo JOIN Libro ON Prestamo.id_libro = Libro.id_libro WHERE Prestamo.id_usuario = ? ORDER BY Prestamo.fecha_prestamo DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fecha_devolucion = $row['fecha_devolucion'] ? $row['fecha_devolucion'] : 'Pendiente';
            $estado = $row['estado'];
            $boton_devolver = '';
            
            // Solo mostrar botón de devolver si el estado es pendiente
            if ($estado == 'pendiente') {
                $boton_devolver = '<button class="btn btn-outline-primary btn-sm devolver-libro" data-id="' . $row['id_prestamo'] . '">
                    <i class="fas fa-undo me-1"></i>Devolver
                </button>';
            } else {
                $boton_devolver = '<span class="badge bg-success">Devuelto</span>';
            }
            
            echo "<tr id='prestamo-" . $row['id_prestamo'] . "'>
                    <td>{$row['titulo']}</td>
                    <td>{$row['fecha_prestamo']}</td>
                    <td>{$fecha_devolucion}</td>
                    <td>" . ucfirst($estado) . "</td>
                    <td>{$boton_devolver}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No hay préstamos</td></tr>";
    }
} catch (Exception $e) {
    error_log("Error en préstamos: " . $e->getMessage());
    echo "<tr><td colspan='5'>Error al cargar préstamos</td></tr>";
}
?>

