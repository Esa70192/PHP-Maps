<?php
require "conexiondb.php";
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
    
        $datosFiltrados = [];

foreach ($datosTablas as $tabla => $registros) {
    foreach ($registros as $registro) {
        // Extraemos solo las columnas deseadas
        $filaFiltrada = [];

        foreach ($registro as $columna => $valor) {
            if (
                str_starts_with($columna, 'ID_') ||
                $columna === 'LATITUD' ||
                $columna === 'LONGITUD'
            ) {
                $filaFiltrada[$columna] = $valor;
            }
        }

        // Solo agregamos si encontramos al menos una de las columnas esperadas
        if (!empty($filaFiltrada)) {
            $datosFiltrados[$tabla][] = $filaFiltrada;
        }
    }
echo "<pre>\n";
echo "Filtrados:\n";
print_r($datosFiltrados);
echo "</pre>\n";
}


    }
} catch (PDOException $e) {
    $errores = $e->getMessage();
}


?>
