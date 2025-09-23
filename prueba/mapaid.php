<?php
// Configuración de conexión
$host = 'localhost';
$dbname = 'capas';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$tablaSeleccionada = $_POST['tabla'] ?? ($tablas[0] ?? null);
$idSeleccionado = $_POST['id_seleccionado'] ?? null;

$campoId = '';
$campoLat = '';
$campoLng = '';
$idsDisponibles = [];
$coordenadas = [];

if ($tablaSeleccionada && in_array($tablaSeleccionada, $tablas)) {
    $stmt = $conn->query("DESCRIBE `$tablaSeleccionada`");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columnas as $col) {
        if (preg_match('/^ID_/', $col)) {
            $campoId = $col;
            break;
        }
    }

    foreach ($columnas as $col) {
        if (in_array(strtolower($col), ['latitud'])) {
            $campoLat = $col;
        } elseif (in_array(strtolower($col), ['longitud'])) {
            $campoLng = $col;
        }
    }

    if ($campoId && $campoLat && $campoLng) {
        // Obtener todos los IDs disponibles
        $stmt = $conn->prepare("
            SELECT `$campoId` AS id
            FROM `$tablaSeleccionada`
            WHERE `$campoLat` IS NOT NULL AND `$campoLng` IS NOT NULL
            ORDER BY id
        ");
        $stmt->execute();
        $idsDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Si el usuario eligió un ID específico, traer solo esa coordenada
        if ($idSeleccionado && in_array($idSeleccionado, $idsDisponibles)) {
            $stmt = $conn->prepare("
                SELECT `$campoId` AS id, `$campoLat` AS lat, `$campoLng` AS lng
                FROM `$tablaSeleccionada`
                WHERE `$campoId` = :id
            ");
            $stmt->execute(['id' => $idSeleccionado]);
        } else {
            // Por defecto mostrar todos (limitados)
            $stmt = $conn->prepare("
                SELECT `$campoId` AS id, `$campoLat` AS lat, `$campoLng` AS lng
                FROM `$tablaSeleccionada`
                WHERE `$campoLat` IS NOT NULL AND `$campoLng` IS NOT NULL
            ");
            $stmt->execute();
        }

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

    <!-- TomTom SDK -->
    <link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps.css">
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps-web.min.js"></script>

    <style>
        #map {
            width: 100%;
            height: 600px;
        }
        .custom-marker {
            background-color: #007bff;
            color: white;
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

    <?php if (!empty($idsDisponibles)): ?>
        <label for="id_seleccionado" style="margin-left: 20px;">Seleccionar ID:</label>
        <select name="id_seleccionado" id="id_seleccionado" onchange="this.form.submit()">
            <option value="">-- Mostrar todos --</option>
            <?php foreach ($idsDisponibles as $id): ?>
                <option value="<?= $id ?>" <?= $id == $idSeleccionado ? 'selected' : '' ?>>
                    <?= $id ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</form>

<div id="map"></div>

<script>
    const apiKey = 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y'; // Tu API Key de TomTom
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
