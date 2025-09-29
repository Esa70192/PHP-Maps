<?php

include 'conexiondb.php';

$correo = $_POST['correo'];
$password = $_POST['password'];

// 3. Buscar al usuario en la base de datos
$sql = "SELECT password FROM usuarios WHERE correo = :correo";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':correo', $correo);
$stmt->execute();

// 4. Verificar si existe y validar la contraseña
if ($stmt->rowCount() === 1) {
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);
    $hash_en_bd = $fila['password'];

    // Verificar la contraseña
    if (password_verify($password, $hash_en_bd)) {
        echo "✅ Inicio de sesión exitoso. Bienvenido, $correo.";
        // session_start(); $_SESSION['usuario'] = $usuario;
    } else {
        echo "❌ Contraseña incorrecta.";
    }
} else {
    echo "❌ Usuario no encontrado.";
}
?>