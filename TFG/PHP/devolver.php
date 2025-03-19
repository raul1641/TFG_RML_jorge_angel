<?php
ob_start(); // Buffer para evitar errores de headers
session_start();

// Incluir conexión con rutas absolutas
include __DIR__ . '/../conf/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'Debes iniciar sesión']));
}

// Validar entrada
if (!isset($_POST['id_prestamo']) || !ctype_digit($_POST['id_prestamo'])) {
    ob_end_clean();
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'message' => 'ID de préstamo inválido']));
}

$id_prestamo = (int)$_POST['id_prestamo'];
$id_usuario = $_SESSION['user_id'];

try {
    $conexion->begin_transaction();
    
    // 1. Verificar préstamo (tablas en minúsculas)
    $stmt = $conexion->prepare("
        SELECT p.id_libro, l.cantidad_disponible 
        FROM prestamo p 
        JOIN libro l ON p.id_libro = l.id_libro 
        WHERE p.id_prestamo = ? 
        AND p.id_usuario = ? 
        AND p.estado = 'pendiente'
        FOR UPDATE
    ");
    $stmt->bind_param("ii", $id_prestamo, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Préstamo no válido o ya devuelto');
    }

    $data = $result->fetch_assoc();
    $id_libro = $data['id_libro'];
    $nueva_cantidad = $data['cantidad_disponible'] + 1;

    // 2. Actualizar préstamo
    $stmt = $conexion->prepare("
        UPDATE prestamo 
        SET estado = 'devuelto', 
            fecha_devolucion = NOW() 
        WHERE id_prestamo = ?
    ");
    $stmt->bind_param("i", $id_prestamo);
    $stmt->execute();

    // 3. Actualizar libro (con estado condicional)
    $nuevo_estado = ($nueva_cantidad > 0) ? 'disponible' : 'prestado';
    
    $stmt = $conexion->prepare("
        UPDATE libro 
        SET cantidad_disponible = ?, 
            estado = ? 
        WHERE id_libro = ?
    ");
    $stmt->bind_param("isi", $nueva_cantidad, $nuevo_estado, $id_libro);
    $stmt->execute();

    $conexion->commit();
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Devolución exitosa',
        'nuevo_estado' => ucfirst($nuevo_estado),
        'nueva_cantidad' => $nueva_cantidad
    ]);

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error devolución: " . $e->getMessage());
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error en la devolución: ' . $e->getMessage()
    ]);
    
} finally {
    $conexion->close();
}
?>
