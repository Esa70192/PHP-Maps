<!--siu-->
<?php
$latitud = 19.4678; // CDMX
$longitud = -99.2079;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mapa con TomTom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- TomTom Maps SDK CSS -->
    <link rel="stylesheet" type="text/css" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps.css">
    <style>
        #map {
            width: 100%;
            height: 500px;
        }
    </style>
</head>
<body>

<h2>Mapa con punto (TomTom)</h2>
<div id="map"></div>

<!-- TomTom Maps SDK JS -->
<script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps-web.min.js"></script>
<script>
    const apiKey = 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y';
    const lat = <?= $latitud ?>;
    const lon = <?= $longitud ?>;

    // Inicializar el mapa
    const map = tt.map({
        key: apiKey,
        container: 'map',
        center: [lon, lat],
        zoom: 14
    });

    // Agregar controles de navegación
    map.addControl(new tt.NavigationControl());

    // Agregar un marcador
    const marker = new tt.Marker().setLngLat([lon, lat]).addTo(map);
</script>

</body>
</html>
