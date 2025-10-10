<?php
require "conexiondb.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST["correo"]);

    if (empty($correo)){
        echo "Correo no ingresado.";
    }

    if (!filter_var(value: $correo, filter: FILTER_VALIDATE_EMAIL)) {
        echo "Correo electrónico no válido.";
        exit;
    }

    try{
        $stmt =$conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(param: ':correo', var: $correo);
        $stmt->execute();
        
        

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