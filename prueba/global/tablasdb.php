<?php

//TABLA
$tablaSeleccionada = $_POST['tabla'] ?? null;
$filtros = $_POST['filtros'] ?? [];
$datos = [];
$columnas=[];

if ($tablaSeleccionada && in_array($tablaSeleccionada, $tablas)) {
    
    $stmt = $conn->prepare("DESCRIBE `$tablaSeleccionada` ");
    $stmt->execute();
    $columnas = $stmt->fetchALL(PDO::FETCH_COLUMN);
    
    
    $campoId=NULL;

    foreach ($columnas as $col) {
        if (preg_match('/^ID_/', $col)) {
            $campoId = $col;
            break;
        }
    }
    
    $columnas_muestra=[];

    if($campoId!==NULL){
        array_unshift($columnas_muestra, $campoId);
    }

    $columna_opcional=['CALLE', 'NUMERO','COLONIA','DESCRIPCION','ACTIVIDAD','TIPO_ACTI','ESPECIE'];

    $columna_opcional_valida = array_intersect($columna_opcional,$columnas);
    $columnas_muestra = array_merge($columnas_muestra, $columna_opcional_valida);
    
    if (empty($columnas_muestra)) {
        exit('No hay columnas válidas para mostrar en la tabla seleccionada.');
    }

    $columnas_sql = implode(', ', array_map(fn($col) => "`$col`", $columnas));

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
}

?>