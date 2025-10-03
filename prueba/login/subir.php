<?php

require 'conexiondb.php';

// Verificar si se subió un archivo
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
    $archivoTmp = $_FILES['archivo']['tmp_name'];

    // Abrir el archivo CSV
        if (($handle = fopen($archivoTmp, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ','); // Leer la primera línea (cabecera)

            if ($headers) {
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

                /*************************************************** */
                $tablaSeleccionada = $_POST['tabla'] ?? null;
$filtros = $_POST['filtros'] ?? [];
$datos = [];
$columnas=[];
                $campoId=NULL;
                foreach ($columnas as $col) {
                    if (preg_match('/^ID_/', $col)) {
                    $campoId = $col;
            break;
        }
    }
    
    $columnas_muestra=['CALLE', 'NUMERO','COLONIA'];

    if($campoId!==NULL){
        array_unshift($columnas_muestra, $campoId);
    }

    $columna_opcional=['DESCRIPCION','ACTIVIDAD','TIPO_ACTI','ESPECIE'];

    $columna_opcional_valida = array_intersect($columna_opcional,$columnas);
    $columnas_muestra = array_merge($columnas_muestra, $columna_opcional_valida);
    
    if (empty($columnas_muestra)) {
        exit('No hay columnas válidas para mostrar en la tabla seleccionada.');
        
    }

    $columnas_sql = implode(', ', array_map(fn($col) => "`$col`", $columnas_muestra));

    $where=[];
    $params=[];

    foreach($columnas as $columna){
        if(!empty($filtros[$columna])){
            $where[]="`$columna`=:$columna";
            $params[$columna]=$filtros[$columna];
        }
    }
    
    $sql = "SELECT $columnas_sql FROM `$tablaSeleccionada`";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $stmt=$conn->prepare($sql);
    $stmt->execute($params);
    $datos=$stmt->fetchALL(PDO::FETCH_ASSOC);

    $valoresUnicos=[];
    foreach($columnas as $columna){
        $stmt=$conn->prepare("SELECT DISTINCT `$columna` FROM `$tablaSeleccionada` ORDER BY `$columna`");
        $stmt->execute();
        $valoresUnicos[$columna]=$stmt->fetchALL(PDO::FETCH_COLUMN);
    }

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
