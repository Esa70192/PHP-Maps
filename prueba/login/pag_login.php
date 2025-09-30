<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="registrolog">   
        <form class="form_registro" action="login.php" method="post">
            <h1 class="textoregistro">Iniciar Sesión</h1> 
            <label class="lab_registro">
                Correo: <br><input class="input" type="email" name="correo" required><br><br>
            </label>
            <label class="lab_registro">
                Contraseña: <br><input class="input" type="password" name="password" required><br><br>
            </label>
            <input class="boton" type="submit" value="Iniciar sesión">
            <button class="boton" onclick="window.location.href='pag_signup.php'">Registrarme.</button>
        </form>
    </div>
</body>
</html>