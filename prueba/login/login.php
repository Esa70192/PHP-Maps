<?php
session_start(); // Necesario para usar $_SESSION

require 'conexiondb.php'; // Asegúrate de tener tu conexión PDO aquí

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);
    $password = $_POST["password"];

    try {
        $stmt = $conn->prepare("SELECT id_usuario, nombres, password FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario["password"])) {
            // Login exitoso
            $_SESSION["usuario_id"] = $usuario["id_usuario"];
            $_SESSION["nombre"] = $usuario["nombres"];

            // Redirigir a página principal
            header("Location: pag_principal.php");
            exit();
        } else {
            // Login fallido
            echo "Correo o contraseña incorrectos.";
            echo "Recargando pagina en 3 segundos.";
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "pag_login.php";
                    }, 3000);
                  </script>';
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
        echo '<script>
                setTimeout(function() {
                    window.location.href = "pag_login.php";
                }, 3000);
              </script>';
    }
} else {
    // Si alguien entra directo sin enviar formulario, redirige
    header("Location: pag_login.php");
    exit();
}
