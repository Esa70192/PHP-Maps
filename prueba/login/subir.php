<?php

require_once 'conexiondb.php';
require_once 'tablasdb.php';

// Verificar si se subió un archivo
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
    $archivoTmp = $_FILES['archivo']['tmp_name'];

    // Abrir el archivo CSV
        if (($handle = fopen($archivoTmp, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ','); // Leer la primera línea (cabecera)

            if ($headers) {
                $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
                // Limpiar y preparar nombres de columnas
                $columnas = array_map(function($col) {
                    return preg_replace('/[^a-zA-Z0-9_]/', '_', strtoupper(trim($col)));
                }, $headers);

                // Crear una tabla con nombre único (puedes cambiar esto)
                $nombreTabla = 'tabla_csv_' . time();

                // Crear SQL para la tabla
                $sqlCreate = "CREATE TABLE `$nombreTabla` (";
                foreach ($columnas as $col) {
                    $sqlCreate .= "`$col` TEXT, ";
                }
                $sqlCreate = rtrim($sqlCreate, ', ') . ")";
                $conn->exec($sqlCreate);

                // Preparar SQL de inserción
                $placeholders = rtrim(str_repeat('?,', count($columnas)), ',');
                $sqlInsert = "INSERT INTO `$nombreTabla` (`" . implode("`,`", $columnas) . "`) VALUES ($placeholders)";
                $stmt = $conn->prepare($sqlInsert);

                // Insertar las filas del CSV
                while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
                    $stmt->execute($fila);
                }
                fclose($handle);

                require 'conexiondb.php';
                require 'tablasdb.php';
                require 'mapa.php';

                header("Location: pag_principal.php");

            } else {
                echo "El archivo CSV está vacío o no tiene cabecera.";
            }
        } else {
            echo "No se pudo abrir el archivo.";
        }      
} else {
    echo "No se ha subido ningún archivo válido.";
}
?>
