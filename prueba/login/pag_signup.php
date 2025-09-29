<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <h1>Registro</h1>
    <form action="signup.php" method="POST">
        Nombre: <input type="text" name="nombre" required><br><br>
        Apellido Paterno: <input type="text" name="ap_paterno" required><br><br>
        Apellido Materno: <input type="text" name="ap_materno" required><br><br>
        Correo: <input type="email" name="correo" required><br><br>
        Contraseña: <input type="password" name="password" required><br><br>
        Confirmar contraseña: <input type="password" name="conf_password" required><br><br>
        <input type="submit" value="Entrar">
    </form>
    <button onclick="window.location.href='pag_login.php'">Ya tengo cuenta.</button>
</body>
</html>