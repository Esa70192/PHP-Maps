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

$tablaSeleccionada = $_POST['tabla'] ?? null;
$datos = [];

if($tablaSeleccionada && in_array($tablaSeleccionada,$tablas)){
    $stmt = $conn->prepare("SELECT * FROM `$tablaSeleccionada` LIMIT 100");
    $stmt->execute();
    $datos = $stmt->fetchALL(PDO::FETCH_ASSOC);
    $sql = "SELECT LATITUD, LONGITUD FROM `$tablaSeleccionada`";
    $result = $conn -> query($sql);
    $puntos = [];

    while($row = $result->fetch(PDO::FETCH_ASSOC)){
        $puntos[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($puntos);
}
?>





<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, width=device-width">
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.18.0/maps/maps-web.min.js"></script>
    <link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.18.0/maps/maps.css">
    <style>
        #map { width: 100%; height: 500px; }
    </style>
    <title>Conexion a BD</title>
</head>

<body>

    <h1>Conexión a la base de datos</h1>

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
        <h2>Datos de la tabla <?= htmlspecialchars($tablaSeleccionada) ?></h2>
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

    <h2>Mapa con TomTom y PHP</h2>
    <div id="map"></div>

    <script>
        // Inicia el mapa
        const map = tt.map({
            key: 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y',
            container: 'map',
            center: [19.40771965424571, -99.18955248149084], 
            zoom: 5
        });

        // Agrega controles
        map.addControl(new tt.NavigationControl());

        // Cargar los puntos desde PHP
        fetch('get-points.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(punto => {
                    new tt.Marker()
                        .setLngLat([punto.lng, punto.lat])
                        .addTo(map);
                });
            });
    </script>

</body>

</html>