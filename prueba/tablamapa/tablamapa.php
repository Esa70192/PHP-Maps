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

//Filtro Id
$idSeleccionado = $_POST['id_seleccionado'] ?? null;

//MAPA
$campoId = '';
$campoLat = '';
$campoLng = '';
$tablasConCoords = [];
$coordenadas = [];
$idsDisponibles = [];

//Para mostrar tabla
if($tablaSeleccionada && in_array($tablaSeleccionada,$tablas)){
    $stmt = $conn->prepare("SELECT * FROM `$tablaSeleccionada` ");
    $stmt->execute();
    $datos = $stmt->fetchALL(PDO::FETCH_ASSOC);
}

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

        //Si el usuario elije un ID especifico
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
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tablas</title>
    <!--CC TABLA-->
    <link rel="stylesheet" href="estilo.css">
    <!--MAPA-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="script" href="mapa.js">
    <!-- SDK de TomTom -->
    <link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps.css">
    <script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.20.0/maps/maps-web.min.js"></script>
</head>
<body>
    <!--TABLA BD-->
    <?php if ($conexionExitosa): ?>
        <p class = "bd">Conexión exitosa</p>
    <?php else: ?>
        <p class = "bd">Error al conectar: <?= $errorMensaje ?></p>
    <?php endif; ?>
    <h1 class="titulo">Sistema de geolocalizacion</h1>
    <div class= "texto">
        <form method="post">
            <label for="tabla">Elija una tabla:</label>
            <select name="tabla" id="tabla" required>
                <?php foreach ($tablas as $tabla): ?>
                    <option value="<?= htmlspecialchars($tabla) ?>" <?= ($tabla === $tablaSeleccionada) ? "selected" : "" ?>>
                        <?= htmlspecialchars($tabla) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <!--SELECTOR DE ID-->
            <?php if (!empty($idsDisponibles)): ?>
                <label for="id_seleccionado">Seleccionar ID:</label>
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
    <h2 class="titulo">Mapa de <?= htmlspecialchars($tablaSeleccionada ?? '' ) ?> </h2>
    <div id="map"></div>
    <script>
      const coordenadas = <?= json_encode($coordenadas); ?>;
    </script>
    <script src="mapa.js"></script>
</body>
</html>