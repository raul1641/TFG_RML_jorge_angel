<?php
$servidor = getenv('DB_HOST');
$puerto = getenv('DB_PORT');
$usuario = getenv('DB_USER');
$contraseña = getenv('DB_PASSWORD');
$shema = getenv('DB_NAME');

// Establecemos conexión al servidor de base de datos
try {
    $conexion = new mysqli($servidor, $usuario, $contraseña, $shema, $puerto);

    // Comprobamos si hay errores de conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Configurar la codificación UTF-8
    $conexion->query("SET NAMES 'utf8'");
    
    // Eliminar este mensaje o cambiarlo por un comentario
    // echo "<b>MENSAJE:</b><br> Conexión exitosa a la base de datos.";
} catch (Exception $e) {
    // Considera usar un log en lugar de mostrar el error directamente
    error_log("Error de conexión: " . $e->getMessage());
    // echo "Error: " . $e->getMessage();
}
?>
