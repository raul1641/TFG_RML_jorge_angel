<?php
ob_start(); // Buffer para evitar errores de headers
ini_set('max_execution_time', 300);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

session_start();
include __DIR__ . '/../conf/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["success" => false, "message" => "Debes iniciar sesión"]));
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["success" => false, "message" => "Método no permitido"]));
}

$id_libro = (int)($_POST['id_libro'] ?? 0);

try {
    $conexion->begin_transaction();

    // 1. Verificar disponibilidad del libro
    $stmt = $conexion->prepare("
        SELECT titulo, cantidad_disponible 
        FROM libro 
        WHERE id_libro = ? 
        FOR UPDATE
    ");
    $stmt->bind_param("i", $id_libro);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die(json_encode(["success" => false, "message" => "Libro no existe"]));
    }

    $libro = $result->fetch_assoc();
    $cantidad = $libro['cantidad_disponible'];
    $titulo = $libro['titulo'];

    if ($cantidad <= 0) {
        die(json_encode(["success" => false, "message" => "No hay ejemplares disponibles"]));
    }

    // 2. Actualizar libro
    $nueva_cantidad = $cantidad - 1;
    $estado = ($nueva_cantidad > 0) ? "disponible" : "prestado";

    $stmt = $conexion->prepare("
        UPDATE libro 
        SET cantidad_disponible = ?, estado = ? 
        WHERE id_libro = ?
    ");
    $stmt->bind_param("isi", $nueva_cantidad, $estado, $id_libro);
    $stmt->execute();

    // 3. Registrar préstamo
    $stmt = $conexion->prepare("
        INSERT INTO prestamo 
        (id_usuario, id_libro, fecha_prestamo, estado) 
        VALUES (?, ?, NOW(), 'pendiente')
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $id_libro);
    $stmt->execute();
    $id_prestamo = $conexion->insert_id;

    // 4. Generar PDF
    require_once __DIR__ . '/../fpdf/fpdf.php';

    $fecha_solicitud = date('Y-m-d H:i:s');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Comprobante de Solicitud', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Libro solicitado: ' . $titulo, 0, 1);
    $pdf->Cell(0, 10, 'Fecha y hora: ' . $fecha_solicitud, 0, 1);

    $pdfName = 'comprobante_' . time() . '.pdf';
    $pdfContent = $pdf->Output('S');
    
    $_SESSION['pdf_content'] = base64_encode($pdfContent);
    $_SESSION['pdf_filename'] = $pdfName;

    // 5. Enviar email
    $stmtUser = $conexion->prepare("
        SELECT email, nombre 
        FROM usuario 
        WHERE id_usuario = ?
    ");
    $stmtUser->bind_param("i", $_SESSION['user_id']);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();

    // 5. Enviar email
if ($resUser->num_rows > 0) {
    $usuario = $resUser->fetch_assoc();
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gradomediosmrv@gmail.com';
        $mail->Password = 'wayi jvfe sdec drdf'; // ← Usar contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 2; // ← Activar depuración (0 para producción)

        $mail->setFrom('gradomediosmrv@gmail.com', 'Biblioteca BookLineRML');
        $mail->addAddress('smtp.code.oficial33@gmail.com', 'Profesor');
        $mail->addCC($usuario['email'], $usuario['nombre']);

        // Cargar plantilla
        $ruta_plantilla = __DIR__ . '../plantillas/email_prestamo.html';
        if (!file_exists($ruta_plantilla)) {
            throw new Exception("Plantilla no encontrada en: $ruta_plantilla");
        }
        $plantilla = file_get_contents($ruta_plantilla);

        // Personalizar y enviar
        $mail->isHTML(true);
        $mail->Subject = "Préstamo confirmado: $titulo";
        $mail->Body = str_replace(
            ['{NOMBRE}', '{TITULO}', '{FECHA}', '{ID_PRESTAMO}'],
            [$usuario['nombre'], $titulo, $fecha_solicitud, $id_prestamo],
            $plantilla
        );
        
        $mail->send();
        $_SESSION['email_enviado'] = true;

    } catch (Exception $e) {
        error_log("Error crítico en PHPMailer: " . $e->getMessage());
        $_SESSION['email_enviado'] = false;
    }
}

    // Confirmar transacción y redirigir
    $conexion->commit();
    
    ob_end_clean(); // Limpiar buffer antes de headers
    header("Location: ../Panel_A_U/P_usuario.php?mensaje=Libro solicitado con éxito" . 
          ($_SESSION['email_enviado'] ?? false ? " - Correo enviado" : ""));
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error en la transacción: " . $e->getMessage());
    
    ob_end_clean();
    header("Location: ../Panel_A_U/P_usuario.php?mensaje=Error al solicitar el libro");
    exit;
} finally {
    $conexion->close();
}
?>
