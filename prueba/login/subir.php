<?php
session_start();

require_once 'conexiondb.php';
require_once 'tablasdb.php';

if (isset($_POST['nombre_tabla'])) {
    $nombre_ingresado = trim($_POST['nombre_tabla']);
    $nombre_ingresado = substr($nombre_ingresado, 0, 63);

    // Validar que el nombre sea seguro (solo letras, números y guiones bajos)
    if (preg_match('/^[a-zA-Z0-9_]+$/', $nombre_ingresado)) {
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = :db AND table_name = :tabla";
        $stmt = $conn->prepare($sql);
        $stmt -> execute([
            'db' => $dbname,
            'tabla' => $nombre_ingresado
        ]);

        $existe = $stmt->fetchColumn();
        if($existe > 0) {
            $_SESSION['error_nombre'] = "El nombre de tabla ya existe";
            header("Location: pag_principal.php");
            exit();
        }else{
            $nombreTabla = $nombre_ingresado;
        }
    }else{
        $_SESSION['error_nombre'] = "❌ Nombre de tabla inválido. Solo se permiten letras, números y guión bajo.";
        header("Location: pag_principal.php");
        exit();
    }
}else{
    $_SESSION['error_nombre'] = "No se recibió ningún nombre de tabla.";
    header("Location: pag_principal.php");
    exit();
}

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
                    $col = trim($col);
                    $col = strtoupper($col);
                    $col = preg_replace('/[^A-Z0-9_]/', '_', $col);
                    $col = substr($col, 0, 64);

                    $base = $col;
                    $i = 1;
                    while (isset($nombresUsados[$col])) {
                        $col = substr("{$base}_{$i}", 0, 64); // evita pasarse de 64 chars
                        $i++;
                    }
                    $nombresUsados[$col] = true;

                    return $col;
                }, $headers);

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
