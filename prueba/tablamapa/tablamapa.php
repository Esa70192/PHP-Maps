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
$filtros = $_POST['filtros'] ?? [];
$datos = [];
$columnas=[];

//MAPA
$tablasConCoords = [];
$coordenadas = [];
$campoId = '';
$campoLat = '';
$campoLng = '';
$idsDisponibles=[];
$idSeleccionado=$_POST['id_seleccionado']?? null;


if ($tablaSeleccionada && in_array($tablaSeleccionada, $tablas)) {
    $stmt = $conn->prepare("DESCRIBE `$tablaSeleccionada` ");
    $stmt->execute();
    $columnas = $stmt->fetchALL(PDO::FETCH_COLUMN);
    
    $where=[];
    $params=[];

    foreach($columnas as $columna){
        if(!empty($filtros[$columna])){
            $where[]="`$columna`=:$columna";
            $params[$columna]=$filtros[$columna];
        }
    }

    $sql="SELECT * FROM `$tablaSeleccionada`";
    if(!empty($where)){
        $sql .= "WHERE" . implode("AND",$where);
    }

    $stmt=$conn->prepare($sql);
    $stmt->execute($params);
    $datos=$stmt->fetchALL(PDO::FETCH_ASSOC);

    $valoresUnicos=[];
    foreach($columnas as $columna){
        $stmt=$conn->prepare("SELECT DISTINCT `$columna` FROM `$tablaSeleccionada` ORDER BY `$columna`");
        $stmt->execute();
        $valoresUnicos[$columna]=$stmt->fetchALL(PDO::FETCH_COLUMN);
    }

    //---------------MAPA------------------
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

    // Reusar condiciones y agregar que latitud y longitud no sean NULL
    $whereMapa = $where; // copiar condiciones existentes
    $whereMapa[] = "`$campoLat` IS NOT NULL";
    $whereMapa[] = "`$campoLng` IS NOT NULL";

    $sqlMapa = "SELECT `$campoId` AS id, `$campoLat` AS lat, `$campoLng` AS lng
                FROM `$tablaSeleccionada`";

    if (!empty($whereMapa)) {
        $sqlMapa .= " WHERE " . implode(" AND ", $whereMapa);
    }

    $sqlMapa .= " ORDER BY id";

    $stmt = $conn->prepare($sqlMapa);
    $stmt->execute($params); // usamos los mismos parámetros que antes
    $puntosMapa = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <h1 class="titulo">Tablas</h1>
    <div class= "texto">
        <form method="post">
            <label for="tabla">Elija la tabla:</label>
            <select name="tabla" id="tabla" required onchange="this.form.submit()">
                <option value="">-- Selecciona una tabla --</option>
                <?php foreach ($tablas as $tabla): ?>
                    <option value="<?= htmlspecialchars($tabla) ?>" <?= ($tabla === $tablaSeleccionada) ? "selected" : "" ?>>
                        <?= htmlspecialchars($tabla) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!------FILTO------->
    <?php if ($tablaSeleccionada && !empty($columnas)): ?>
        <form method="post">
            <input type="hidden" name="tabla" value="<?= htmlspecialchars($tablaSeleccionada) ?>">
            <h3>Filtros</h3>
            <?php foreach ($columnas as $columna): ?>
                <label for="filtros[<?= htmlspecialchars($columna) ?>]"><?= htmlspecialchars($columna) ?>:</label>
                <select name="filtros[<?= htmlspecialchars($columna) ?>]" id="filtros[<?= htmlspecialchars($columna) ?>]">
                    <option value="">-- Todos --</option>
                    <?php foreach ($valoresUnicos[$columna] as $valor): ?>
                        <option value="<?= htmlspecialchars($valor) ?>" <?= (isset($filtros[$columna]) && $filtros[$columna] == $valor) ? "selected" : "" ?>>
                            <?= htmlspecialchars($valor) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>
            <?php endforeach; ?>

            <button type="submit">Filtrar</button>
        </form>
    <?php endif; ?>

    <!-------DATOS DE TABLA----->
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
      const coordenadas = <?= json_encode($puntosMapa); ?>;
    </script>
    <script src="mapa.js"></script>
</body>
</html>