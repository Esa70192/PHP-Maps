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
    
    $where=[];
    $params=[];

    foreach($columnas as $columna){
        if(!empty($filtros[$columna])){
            $where[]="`$columna`=:$columna";
            $params[$columna]=$filtros[$columna];
        }
    }

    $sql="SELECT * FROM `$tablaSeleccionada`";
    if(!empty($where)){
        $sql .= " WHERE " . implode(" AND ",$where);
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