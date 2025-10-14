<?php

$campoId = '';
$campoLat = '';
$campoLng = '';

foreach ($columnas as $col) {
        if (preg_match('/^ID_/', $col)) {
            $campoId = $col;
            break;
        }

    // Detectar columnas de latitud y longitud
    foreach ($columnas as $col) {
        if (in_array(strtolower($col), ['latitud'])) {
            $campoLat = $col;
        } elseif (in_array(strtolower($col), ['longitud'])) {
            $campoLng = $col;
        }
    }

    $whereMapa = $where; // copiar condiciones existentes
    $whereMapa[] = "`$campoLat` IS NOT NULL";
    $whereMapa[] = "`$campoLng` IS NOT NULL";

    $sqlMapa = "SELECT `$campoId` AS id, `$campoLat` AS lat, `$campoLng` AS lng
                FROM `$tabla`";

    if (!empty($whereMapa)) {
        $sqlMapa .= " WHERE " . implode(" AND ", $whereMapa);
    }

    $sqlMapa .= " ORDER BY id";

    $stmt = $conn->prepare($sqlMapa);
    $stmt->execute($params); // usamos los mismos parámetros que antes
    $puntosMapa = $stmt->fetchAll(PDO::FETCH_ASSOC);

}
?>