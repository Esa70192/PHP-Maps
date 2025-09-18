<?php
// 1. Conectar a la base de datos
$host = "localhost";
$dbname = "capas";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// 2. Obtener los puntos
$sql = "SELECT ID_EMERGENCIA, LATITUD, LONGITUD FROM EMERGENCIA";
$result = $conn->query($sql);

// 3. Guardar datos en un array
$puntos = [];
while ($row = $result->fetch_assoc()) {
    $puntos[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mapa con TomTom</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS de TomTom -->
    <link rel="stylesheet" type="text/css" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.18.0/maps/maps.css"/>

    <!-- JS de TomTom -->
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.18.0/maps/maps-web.min.js"></script>

    <style>
        #map {
            width: 100%;
            height: 600px;
        }
    </style>
</head>
<body>

<h2>Mapa con puntos desde la base de datos</h2>
<div id="map"></div>

<script>
    // 1. API Key de TomTom (sustituye con tu clave real)
    const apiKey = "dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y";

    // 2. Crea el mapa
    const map = tt.map({
        key: apiKey,
        container: "map",
        center: [ 19.407718, -99.189453 ], // Coordenadas iniciales (CDMX en este ejemplo)
        zoom: 100
    });

    // 3. Puntos desde PHP (convertido a JSON)
    const puntos = <?php echo json_encode($puntos); ?>;

    // 4. Añadir marcadores
    puntos.forEach(punto => {
        const marker = new tt.Marker()
            .setLngLat([parseFloat(punto.latitud), parseFloat(punto.longitud)])
            .addTo(map);
    });
</script>

</body>
</html>
