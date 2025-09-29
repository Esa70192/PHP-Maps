<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Iniciar Sesión</h1>
    <form action="login.php" method="post">
        Correo: <input type="email" name="correo" required><br><br>
        Contraseña: <input type="password" name="password" required><br><br>
        <input type="submit" value="Iniciar sesión">
    </form>
    <button onclick="window.location.href='pag_signup.php'">Registrarme.</button>
</body>
</html>