<?php
// Conexión a la base de datos (PDO)
$host = "localhost";
$dbname = "capas";
$user = "root";
$pass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SHOW TABLES");
    $tablas = $stmt->fetchALL(PDO::FETCH_COLUMN);

    $conexionExitosa = true;
} catch (PDOException $e) {
    $conexionExitosa = false;
    $errorMensaje = $e->getMessage();
}

//TABLA
$tablaSeleccionada = $_POST['tabla'] ?? null;
$datos = [];

if($tablaSeleccionada && in_array($tablaSeleccionada,$tablas)){
    $stmt = $conn->prepare("SELECT * FROM `$tablaSeleccionada` ");
    $stmt->execute();
    $datos = $stmt->fetchALL(PDO::FETCH_ASSOC);
}

//MAPA
$tablasConCoords = [];
$coordenadas = [];
$campoId = '';
$campoLat = '';
$campoLng = '';

if ($tablaSeleccionada && in_array($tablaSeleccionada, $tablas)) {
    // Obtener nombres de columnas
    $stmt = $conn->query("DESCRIBE `$tablaSeleccionada`");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Detectar nombre de columna ID (la primera que empiece con id_)
    foreach ($columnas as $col) {
        if (preg_match('/^ID_/', $col)) {
            $campoId = $col;
            break;
        }
    }

    // Detectar columnas de latitud y longitud
    foreach ($columnas as $col) {
        if (in_array(strtolower($col), ['latitud'])) {
            $campoLat = $col;
        } elseif (in_array(strtolower($col), ['longitud'])) {
            $campoLng = $col;
        }
    }

    // Solo ejecutar la consulta si se encontraron todos los campos
    if ($campoId && $campoLat && $campoLng) {
        $stmt = $conn->prepare("
            SELECT `$campoId` AS id, `$campoLat` AS lat, `$campoLng` AS lng
            FROM `$tablaSeleccionada`
            WHERE `$campoLat` IS NOT NULL AND `$campoLng` IS NOT NULL
            LIMIT 100
        ");
        $stmt->execute();
        $coordenadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>JUNTOS</title>
    
    <!--CC TABLA-->
    <link rel="stylesheet" href="estilo.css">

    <!--MAPA-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SDK de TomTom -->
    <link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps.css">
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps-web.min.js"></script>

    <style>
        #map {
            width: 100%;
            height: 600px;
        }
        .custom-marker {
            background-color: #28a745;
            color: #fff;
            font-weight: bold;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            text-align: center;
            line-height: 32px;
            box-shadow: 0 0 4px rgba(0,0,0,0.5);
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <!--TABLA BD-->
    <h1>Tablas</h1>

    <style>
   .scroll-box {
    width: 400px;      
    height: 200px;    
    overflow: auto;    /* Scroll tanto vertical como horizontal */
    border: 1px solid #ccc;
    padding: 10px;
    font-family: Arial, sans-serif;
  }

  table {
    border-collapse: collapse;
    width: 600px; /* Hacemos la tabla más ancha para que salga scroll horizontal */
  }

  th, td {
    border: 1px solid #888;
    padding: 8px;
    text-align: left;
  }
</style>
  
    <?php if ($conexionExitosa): ?>
        <p style="color: green;">¡Conexión exitosa!</p>
    <?php else: ?>
        <p style="color: red;">Error al conectar: <?= $errorMensaje ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="tabla">Tablas:</label>
        <select name="tabla" id="tabla" required>
            <option value="">-- Selecciona una tabla --</option>
            <?php foreach ($tablas as $tabla): ?>
                <option value="<?= htmlspecialchars($tabla) ?>" <?= ($tabla === $tablaSeleccionada) ? "selected" : "" ?>>
                    <?= htmlspecialchars($tabla) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Mostrar datos</button>
    </form>
 
    <?php if ($datos): ?>
    <h2 class="datostabla">Datos de la tabla <?= htmlspecialchars($tablaSeleccionada) ?></h2>
<div class="tabla">   
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <?php foreach (array_keys($datos[0]) as $columna): ?>
                        <th><?= htmlspecialchars($columna) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datos as $fila): ?>
                    <tr>
                        <?php foreach ($fila as $valor): ?>
                            <td><?= htmlspecialchars($valor) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($tablaSeleccionada): ?>
        <p>No hay datos para mostrar en esta tabla.</p>
    <?php endif; ?>
</div>

<!--MAPA-->
<h2 id="mapa">Mapa: <?= htmlspecialchars($tablaSeleccionada) ?> </h2>

<div id="map"></div>

<script>
    const apiKey = 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y'; 
    const coordenadas = <?= json_encode($coordenadas); ?>;

    const map = tt.map({
        key: apiKey,
        container: 'map',
        center: coordenadas.length ? [parseFloat(coordenadas[0].lng), parseFloat(coordenadas[0].lat)] : [-99.19, 19.425],
        zoom: 13
    });

    map.addControl(new tt.NavigationControl());

    coordenadas.forEach(punto => {
        if (punto.lat && punto.lng) {
            const markerElement = document.createElement('div');
            markerElement.className = 'custom-marker';
            markerElement.textContent = punto.id;

            new tt.Marker({ element: markerElement })
                .setLngLat([parseFloat(punto.lng), parseFloat(punto.lat)])
                .addTo(map);
        }
    });
</script>




</body>

</html>