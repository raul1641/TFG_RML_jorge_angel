<?php
ob_start(); // Evita problemas con header()
session_start();
include '../conf/conexion.php'; // Ruta corregida

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Usuario WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_type'] = $user['tipo_usuario'];

            // Redirigir según tipo de usuario
            header("Location: /Panel_A_U/" . ($_SESSION['user_type'] == 'admin' ? "P_admin.html" : "P_usuario.php"));
            exit;
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "Usuario no encontrado.";
    }
}
?>
