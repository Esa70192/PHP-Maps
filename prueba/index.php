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
}
?>

<?php if ($datos && isset($datos[0]['latitud']) && isset($datos[0]['longitud'])): ?>
    <h2>Mapa de puntos (<?= htmlspecialchars($tablaSeleccionada) ?>)</h2>
    <div id="map" style="height: 500px; width: 100%;"></div>

    <script>
        const puntos = <?= json_encode($datos) ?>;

        function initMap() {
            const centro = {
                lat: parseFloat(puntos[0].latitud),
                lng: parseFloat(puntos[0].longitud)
            };

            const mapa = new google.maps.Map(document.getElementById('map'), {
                zoom: 5,
                center: centro
            });

            puntos.forEach(punto => {
                if (!punto.latitud || !punto.longitud) return;

                const marcador = new google.maps.Marker({
                    position: {
                        lat: parseFloat(punto.latitud),
                        lng: parseFloat(punto.longitud)
                    },
                    map: mapa,
                    title: `ID: ${punto.id ?? ''}`
                });

                const info = new google.maps.InfoWindow({
                    content: `<strong>ID:</strong> ${punto.id ?? 'N/A'}<br><strong>Lat:</strong> ${punto.latitud}<br><strong>Lng:</strong> ${punto.longitud}`
                });

                marcador.addListener('click', () => {
                    info.open(mapa, marcador);
                });
            });
        }
    </script>

    <!-- Tu clave API de Google Maps -->
    <script async defer
      src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&callback=initMap">
    </script>
<?php endif; ?>



<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8">
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

</body>

</html>