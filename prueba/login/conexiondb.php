<?php
$host = "localhost";
$dbname = "capas";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SHOW TABLES");
    $tablas = $stmt->fetchALL(PDO::FETCH_COLUMN);

    $tabla_excluir='usuarios';
    
    $tablas = array_filter($tablas, function ($tabla) use ($tabla_excluir) {
        return $tabla !== $tabla_excluir;
    });

    $conexionExitosa = true;
} catch (PDOException $e) {
    $conexionExitosa = false;
    $errorMensaje = $e->getMessage();
}
?>