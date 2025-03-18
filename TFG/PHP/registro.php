<?php
include('../conf/conexion.php'); // Asegúrate de que la conexión está bien configurada

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = $_POST["password"]; // Contraseña sin cifrar
    $tipo_usuario = $_POST["tipo_usuario"];

    // Cifrar la contraseña antes de guardarla
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Cifra la contraseña

    $sql = "INSERT INTO Usuario (nombre, email, password, tipo_usuario, fecha_registro) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $email, $hashed_password, $tipo_usuario); // Usa la contraseña cifrada

    if ($stmt->execute()) {
        // Redirige automáticamente al index
        header("Location: ../Index/index.html");
        exit();
    } else {
        echo "Error en el registro: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
}
?>
