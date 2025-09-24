<?php
// Configuración de conexión
$host = 'localhost';
$dbname = 'capas';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener todas las tablas
    $stmt = $conn->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $conexionExitosa = true;
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$tablasConCoords = [];
$coordenadas = [];

$tablaSeleccionada = $_POST['tabla'] ?? ($tablas[0] ?? null);
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
        ");
        $stmt->execute();
        $coordenadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mapa con IDs desde MySQL</title>
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

<h2>Mapa con Marcadores desde Tabla: <em><?= htmlspecialchars($tablaSeleccionada) ?></em></h2>

<form method="POST" style="margin-bottom: 1rem;">
    <label for="tabla">Seleccionar tabla:</label>
    <select name="tabla" id="tabla" onchange="this.form.submit()">
        <?php foreach ($tablas as $tabla): ?>
            <option value="<?= $tabla ?>" <?= $tabla === $tablaSeleccionada ? 'selected' : '' ?>>
                <?= $tabla ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<div id="map"></div>


<script>
    const apiKey = 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y'; // Reemplaza con tu clave real
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

    function irAPuntoPorId(id) {
    const marker = markersPorId[id];
    if (marker) {
      const coords = marker.getLngLat();
      map.flyTo({
        center: coords,
        zoom: 14,
        speed: 1
      });
    } else {
      alert("Punto no encontrado: " + id);
    }
  }

</script>

</body>
</html>