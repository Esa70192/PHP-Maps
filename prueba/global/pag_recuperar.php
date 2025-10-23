<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Olvide mi contraseña</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="contenedor_login">
        <div class="registrorecuperar">   
            <form class="form_registro" action="recuperar.php" method="post">
                <h1 class="textoregistro">Recuperar contraseña</h1> 
                <label class="lab_registro">
                    Introduce tu correo electrónico: <br><input class="input" type="email" name="correo" required><br><br>
                </label>
                <input class="boton" type="submit" value="Enviar">
                <p class="texto">Si la dirección de correo electrónico esta registrada, se enviará un enlace para continuar con el proceso de recuperación de contraseña.</p>
            </form>
        </div>
        <button class="botonpagre" onclick="window.location.href='pag_login.php'">Volver a login</button>
    </div>
</body>
</html>