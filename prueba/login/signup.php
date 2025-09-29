<?php

include 'conexiondb.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $nombre = trim($_POST["nombre"]);
    $ap_paterno=trim( $_POST["ap_paterno"]);
    $ap_materno=trim( $_POST["ap_materno"] );
    $correo = trim( $_POST["correo"]);
    $password = $_POST["password"];
    $conf_password = $_POST["conf_password"];

    // Validaciones básicas
    if (empty($nombre) || empty($ap_paterno) || empty($ap_materno) || empty($correo) || empty($password) || empty($conf_password)) {
        echo "Por favor completa todos los campos.";
        exit;
    }

    if (!filter_var(value: $correo, filter: FILTER_VALIDATE_EMAIL)) {
        echo "Correo electrónico no válido.";
        exit;
    }

    if ($password !== $conf_password) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    try {
        // Verificar si el email ya está registrado
        $stmt = $conn->prepare(query: "SELECT id_usuario FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(param: ':correo', var: $correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "Este correo ya está registrado.";
            exit;
        }

        // Hash de la contraseña
        $password_hashed = password_hash(password: $password, algo: PASSWORD_DEFAULT);

        // Insertar el nuevo usuario
        $stmt = $conn->prepare(query: "INSERT INTO usuarios (nombres, ap_paterno, ap_materno, correo, password) VALUES (:nombre, :ap_paterno, :ap_materno, :correo, :password)");
        $stmt->bindParam(param: ':nombre', var: $nombre);
        $stmt->bindParam(param: ':ap_paterno', var: $ap_paterno);
        $stmt->bindParam(param: ':ap_materno', var: $ap_materno);
        $stmt->bindParam(param: ':correo', var: $correo);
        $stmt->bindParam(param: ':password', var: $password_hashed);

        if ($stmt->execute()) {
            // Obtener el ID del nuevo usuario
            $usuario_id = $conn->lastInsertId();

            // Iniciar sesión automáticamente
            session_start();
            $_SESSION['usuario_id'] = $usuario_id;
            $_SESSION['nombre'] = $nombre; // Opciona
            
            echo "Registro exitoso.";
            echo    '<script>  
                        setTimeout(function() {
                            window.location.href = "pag_principal.php";
                        }, 3000); // 3000 milisegundos = 3 segundos
                    </script>';   
        } else {
            echo "Error en registro.";
            echo    '<script>  
                        setTimeout(function() {
                            window.location.href = "pag_signup.php";
                        }, 3000); // 3000 milisegundos = 3 segundos
                    </script>';
        }

    } catch (PDOException $e) {
        // Puedes guardar el error en un log si es producción
        echo "Error de base de datos: " . $e->getMessage();
        echo    '<script>  
                    setTimeout(function() {
                        window.location.href = "pag_signup.php";
                    }, 3000); // 3000 milisegundos = 3 segundos
                </script>';
    }

}
?>