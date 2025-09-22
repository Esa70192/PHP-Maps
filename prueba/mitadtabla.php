<?php
// Conexión a la base de datos
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
    <title>Tablas</title>
    <!--CC TABLA-->
    <link rel="stylesheet" href="estilo.css">
    <!--MAPA-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SDK de TomTom -->
    <link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps.css">
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps-web.min.js"></script>
</head>
<body>
    <!--TABLA BD-->
    <h1 class="titulo">Tablas</h1>

    <?php if ($conexionExitosa): ?>
        <p class = "texto">Conexion a BD</p>
    <?php else: ?>
        <p class = "texto">Error al conectar: <?= $errorMensaje ?></p>
    <?php endif; ?>
    <div class= "texto">
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
    </div>
    <?php if ($datos): ?>
    <h2 class="titulo">Datos de la tabla <?= htmlspecialchars($tablaSeleccionada) ?></h2>
    <div class="contenedor_tabla">   
        <table class = "tabla">
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
    <h2 class="titulo">Mapa de <?= htmlspecialchars($tablaSeleccionada) ?> </h2>
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