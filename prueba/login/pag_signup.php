<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="registro">
        <form class="form_registro" action="signup.php" method="POST">
            <h1 class="textoregistro">Registro</h1>
            <label class="lab_registro">
                Nombre: <br><input class="input" type="text" name="nombre" required><br>
            </label>
            <label class="lab_registro">
                Apellido Paterno: <br><input class="input" type="text" name="ap_paterno" required><br>
            </label>
            <label class="lab_registro">
                Apellido Materno: <br><input class="input" type="text" name="ap_materno" required><br>
            </label>
            <label class="lab_registro">
                Correo: <br><input class="input" type="email" name="correo" required><br>
            </label>
            <label class="lab_registro">
                Contraseña: <br><input class="input" type="password" name="password" required><br>
            </label>
            <label class="lab_registro">
                Confirmar contraseña: <br><input class="input" type="password" name="conf_password" required><br>
            </label>
            <input class="boton" type="submit" value="Entrar">
            <button class="boton" onclick="window.location.href='pag_login.php'">Ya tengo cuenta.</button>
        </form>
    </div>
</body>
</html>