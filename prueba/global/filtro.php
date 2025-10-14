<?php

$filtros = ['COLONIA', 'CALLE', 'NUMERO'];
$datosTablas = [];
$errores = '';
$columnasPorTabla = [];

try {
    foreach ($tablas as $tabla) {
        // Verificamos las columnas de esta tabla
        $columnas = $conn->query("SHOW COLUMNS FROM `$tabla`")->fetchAll(PDO::FETCH_COLUMN);
        $columnasPorTabla[$tabla] = $columnas;

        // Armamos la cláusula WHERE en base a filtros disponibles
        $where = [];
        $params = [];

        foreach ($filtros as $filtro) {
            if (in_array($filtro, $columnas) && !empty($_GET[$filtro])) {
                $where[] = "`$filtro` = :$filtro";
                $params[$filtro] = $_GET[$filtro];
            }
        }

        $sql = "SELECT * FROM `$tabla`";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $datosTablas[$tabla] = $registros;
    
    
        //PARAMETROS DEL MAPA
        $stmt = $conn->prepare("DESCRIBE `$tabla` ");
        $stmt->execute();
        $columnas = $stmt->fetchALL(PDO::FETCH_COLUMN);
        
        
    
    
    
    
    }
} catch (PDOException $e) {
    $errores = $e->getMessage();
}


?>
