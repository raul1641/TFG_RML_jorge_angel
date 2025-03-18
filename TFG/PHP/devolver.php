<?php
session_start();
include '../conf/conexion.php';

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

// Verificar si se recibió el ID del préstamo
if (!isset($_POST['id_prestamo'])) {
    echo json_encode(['success' => false, 'message' => 'ID de préstamo no proporcionado']);
    exit;
}

$id_prestamo = (int)$_POST['id_prestamo'];
$id_usuario = $_SESSION['user_id'];

try {
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Verificar que el préstamo pertenece al usuario y obtener el id_libro
    $stmt = $conexion->prepare("
        SELECT id_libro FROM Prestamo WHERE id_prestamo = ? AND id_usuario = ? AND estado = 'pendiente' ");
    $stmt->bind_param("ii", $id_prestamo, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Préstamo no encontrado o no autorizado');
    }
    
    $row = $result->fetch_assoc();
    $id_libro = $row['id_libro'];
    
    // Actualizar el estado del préstamo a 'devuelto'
    $stmt = $conexion->prepare("
        UPDATE Prestamo SET estado = 'devuelto', fecha_devolucion = NOW() WHERE id_prestamo = ? ");
    $stmt->bind_param("i", $id_prestamo);
    $stmt->execute();
    
    // Incrementar la cantidad disponible del libro
    $stmt = $conexion->prepare("
        UPDATE Libro SET cantidad_disponible = cantidad_disponible + 1, estado = 'disponible' WHERE id_libro = ? ");
    $stmt->bind_param("i", $id_libro);
    $stmt->execute();
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode(['success' => true, 'message' => 'Libro devuelto correctamente']);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conexion->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conexion->close();
}
?>

