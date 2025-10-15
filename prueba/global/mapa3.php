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
    
    }
} catch (PDOException $e) {
    $errores = $e->getMessage();
}


$campoId = '';
$campoLat = '';
$campoLng = '';

foreach ($tablas as $tabla){
    $columnas = $conn->query("SHOW COLUMNS FROM `$tabla`")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($columnas as $col) {
        if (preg_match('/^ID_/', $col)) {
            $campoId = $col;
            break;
        }
        if (in_array(strtolower($col), ['latitud'])) {
            $campoLat = $col;
        } elseif (in_array(strtolower($col), ['longitud'])) {
            $campoLng = $col;
        }
    }

    // Si detectamos correctamente los campos
    if ($campoId && $campoLat && $campoLng) {
        $whereMapa = $where;
        $whereMapa[] = "`$campoLat` IS NOT NULL";
        $whereMapa[] = "`$campoLng` IS NOT NULL";

        $sqlMapa = "SELECT `$campoId` AS id, `$campoLat` AS lat, `$campoLng` AS lng FROM `$tabla`";
        if (!empty($whereMapa)) {
            $sqlMapa .= " WHERE " . implode(" AND ", $whereMapa);
        }
        $sqlMapa .= " ORDER BY id";

        $stmt = $conn->prepare($sqlMapa);
        $stmt->execute($params);
        $puntosMapa = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<pre>' . print_r($puntosMapa, true) . '</pre>';
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="stylesheet" href="estilo.css">
    <!--MAPA-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SDK de TomTom -->
    <link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps.css">
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps-web.min.js"></script>

</head>


<body>

<h2 class="titulo">Georeferenciación</h2>

     <div id="map"></div>
    <script>
      const coordenadas = <?= json_encode($puntosMapa); ?>;
    </script>
    <script src="mapa.js"></script>

</body>
</html>