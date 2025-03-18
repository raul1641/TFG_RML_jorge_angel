<?php
session_start(); // ← Primera línea
include '../conf/conexion.php';

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
            // Guardar datos en sesión
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_type'] = $user['tipo_usuario'];
            
            // Redirigir según tipo de usuario
            if ($_SESSION['user_type'] == 'admin') {
                header("Location: /Panel_A_U/P_admin.html");
            } else {
                header("Location: /Panel_A_U/P_usuario.php");
            }
            exit; // ← Detener ejecución después de redirigir
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "Usuario no encontrado.";
    }
}
?>
