<?php
session_start(); // ← Añadir esto para manejar sesiones
include __DIR__ . '/../conf/conexion.php'; // Ruta absoluta con __DIR__

if (!isset($_SESSION['user_id'])) {
    echo "<tr><td colspan='5'>Inicia sesión para ver tus préstamos</td></tr>";
    exit;
}

try {
    // Consulta con nombres de tablas en minúsculas (prestamo y libro)
    $stmt = $conexion->prepare("
        SELECT 
            p.id_prestamo, 
            l.titulo, 
            p.fecha_prestamo, 
            p.fecha_devolucion, 
            p.estado 
        FROM prestamo p 
        JOIN libro l ON p.id_libro = l.id_libro 
        WHERE p.id_usuario = ? 
        ORDER BY p.fecha_prestamo DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $fecha_devolucion = $row['fecha_devolucion'] ?? 'Pendiente';
            $estado = $row['estado'];
            
            // Botón condicional
            $boton_devolver = ($estado == 'pendiente') 
                ? '<button class="btn btn-outline-primary btn-sm devolver-libro" 
                     data-id="' . $row['id_prestamo'] . '">
                        <i class="fas fa-undo me-1"></i>Devolver
                    </button>'
                : '<span class="badge bg-success">Devuelto</span>';

            echo "<tr id='prestamo-{$row['id_prestamo']}'>
                    <td>{$row['titulo']}</td>
                    <td>{$row['fecha_prestamo']}</td>
                    <td>$fecha_devolucion</td>
                    <td>" . ucfirst($estado) . "</td>
                    <td>$boton_devolver</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center'>No tienes préstamos activos</td></tr>";
    }
} catch (Exception $e) {
    error_log("Error en préstamos: " . $e->getMessage());
    echo "<tr><td colspan='5' class='text-danger text-center'>Error al cargar préstamos</td></tr>";
} finally {
    $conexion->close();
}
?>
