<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "base_registro");

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

// Recoger datos del formulario
$email = $_POST['email'];
$contrasena = md5($_POST['contrasena']);  // Encriptamos como lo hiciste al guardar

// Consulta
$sql = "SELECT * FROM usuarios WHERE email='$email' AND contrasena='$contrasena'";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
    echo "¡Bienvenido, " . $usuario['nombre'] . " " . $usuario['apellidop'] . "!";
} else {
    echo "Correo o contraseña incorrectos.";
}
?>