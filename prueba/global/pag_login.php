<?php
session_start();
$error = "";
if (isset($_SESSION["login_error"])) {
    $error = $_SESSION["login_error"];
    unset($_SESSION["login_error"]); // Para que no se repita si se recarga
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="contenedor_login">
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
                <?php if (!empty($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <!--<button class="boton" onclick="window.location.href='pag_signup.php'">Registrarme.</button>-->
                <a class="olvide" href="pag_recuperar.php">¿Olvidaste tu contraseña?</a>
            </form>
        </div>
    <button class="botonpagre" onclick="window.location.href='index.php'">Volver a pagina principal</button>
    </div>
</body>
</html>|