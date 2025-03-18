<?php
// Aumentar el tiempo máximo de ejecución
ini_set('max_execution_time', 300);

// Declaraciones 'use' correctas
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir archivos de PHPMailer
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

session_start();
include '../conf/conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["success" => false, "message" => "Debes iniciar sesión"]));
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["success" => false, "message" => "Método no permitido"]));
}

// Obtener ID del libro
$id_libro = isset($_POST['id_libro']) ? (int)$_POST['id_libro'] : 0;

try {
    $conexion->begin_transaction();

    // 1. Verificar disponibilidad del libro y extraer título
    $stmt = $conexion->prepare("
        SELECT titulo, cantidad_disponible FROM Libro WHERE id_libro = ? FOR UPDATE ");
    $stmt->bind_param("i", $id_libro);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die(json_encode(["success" => false, "message" => "Libro no existe"]));
    }

    $libro = $result->fetch_assoc();
    $cantidad = $libro['cantidad_disponible'];
    $titulo   = $libro['titulo'];  // Para usarlo en el PDF y en el email

    if ($cantidad <= 0) {
        die(json_encode(["success" => false, "message" => "No hay ejemplares disponibles"]));
    }

    // 2. Actualizar cantidad y estado en Libro
    $nueva_cantidad = $cantidad - 1;
    $estado = ($nueva_cantidad > 0) ? "disponible" : "prestado";

    $stmt = $conexion->prepare("
        UPDATE Libro SET cantidad_disponible = ?, estado = ? WHERE id_libro = ? ");
    $stmt->bind_param("isi", $nueva_cantidad, $estado, $id_libro);
    $stmt->execute();

    // 3. Insertar préstamo en Prestamo
    $stmt = $conexion->prepare("
        INSERT INTO Prestamo (id_usuario, id_libro, fecha_prestamo, estado) VALUES (?, ?, NOW(), 'pendiente') ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $id_libro);
    $stmt->execute();
    
    // Obtener el ID del préstamo insertado
    $id_prestamo = $conexion->insert_id;

    // 4. Generar el PDF
    require_once 'C:/xampp/htdocs/TFG_RML_jorge_angel/TFG/fpdf/fpdf.php';

    $fecha_solicitud = date('Y-m-d H:i:s');
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Comprobante de Solicitud', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Libro solicitado: ' . $titulo, 0, 1);
    $pdf->Cell(0, 10, 'Fecha y hora: ' . $fecha_solicitud, 0, 1);
    
    // Generar nombre único para el PDF
    $pdfName = 'comprobante_' . time() . '.pdf';
    
    // Guardar el PDF en una variable
    $pdfContent = $pdf->Output('S'); // 'S' retorna el PDF como string
    
    // Guardar el PDF en una variable de sesión para descarga posterior
    $_SESSION['pdf_content'] = base64_encode($pdfContent);
    $_SESSION['pdf_filename'] = $pdfName;

    // 5. Obtener datos del usuario
    $user_id = $_SESSION['user_id'];
    $stmtUser = $conexion->prepare("SELECT email, nombre FROM Usuario WHERE id_usuario = ?");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();

    if ($resUser->num_rows > 0) {
    $usuario = $resUser->fetch_assoc();

    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gradomediosmrv@gmail.com';
        $mail->Password = 'wayi jvfe sdec drdf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Configuración de remitente y destinatarios
        $mail->setFrom('gradomediosmrv@gmail.com', 'Biblioteca BookLineRML');
        $mail->addAddress('smtp.code.oficial33@gmail.com', 'Profesor');
        $mail->addCC($usuario['email'], $usuario['nombre']);

        // Configurar contenido del correo
        $mail->isHTML(true);
        $mail->Subject = "Confirmación de préstamo: $titulo";

        // Cargar plantilla y reemplazar datos
        $mail->Body = str_replace(
            ['{NOMBRE}', '{TITULO}', '{FECHA}', '{ID_PRESTAMO}'],
            [$usuario['nombre'], $titulo, $fecha_solicitud, $id_prestamo],
            file_get_contents('../PHP/plantillas/email_prestamo.html')
        );

        $mail->AltBody = "Hola {$usuario['nombre']}, has solicitado el libro '{$titulo}' el {$fecha_solicitud}. ID de préstamo: {$id_prestamo}";

        $mail->send();
        $_SESSION['email_enviado'] = true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        $_SESSION['email_enviado'] = false;
    }
} else {
    error_log("No se encontró el email del usuario.");
}

// Confirmar transacción y redirigir
$conexion->commit();
header("Location: ../Panel_A_U/P_usuario.php?mensaje=Libro solicitado con éxito" . 
       ($_SESSION['email_enviado'] ? " - Correo enviado" : ""));
exit;


} catch (Exception $e) {
    // En caso de error, hacer rollback
    $conexion->rollback();
    error_log("Error en la transacción: " . $e->getMessage());

    // Redirigir con mensaje de error
    header("Location: ../Panel_A_U/P_usuario.php?mensaje=Error al solicitar el libro");
    exit;
} finally {
    $conexion->close();
}
?>