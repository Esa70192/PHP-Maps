<?php

require 'filtro.php';

?>

<!DOCTYPE html>
<html lang="es">
<!--prue-->
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
    <?php if ($conexionExitosa): ?>
    <?php else: ?>
        <p class = "bd">Error al conectar: <?= $errorMensaje ?></p>
    <?php endif; ?>

    <div class="inicio">
        <form action="pag_login.php" method="POST">
            <button class="botonpag" type="submit">Iniciar sesion</button>
        </form>
    </div>


    
    <h1 class="titulo">Sistema de Información</h1>
    
    <!-- FORMULARIO DE FILTROS -->
        <form method="get">
            <?php foreach ($filtros as $columna): ?>
                <?php
                // Obtener valores únicos de esta columna (de todas las tablas que la tienen)
                $valoresUnicos = [];
                foreach ($tablas as $tabla) {
                    if (in_array($columna, $columnasPorTabla[$tabla])) {
                        $stmt = $conn->query("SELECT DISTINCT `$columna` FROM `$tabla` WHERE `$columna` IS NOT NULL AND `$columna` != ''");
                        $valores = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        $valoresUnicos = array_merge($valoresUnicos, $valores);
                    }
                }
                $valoresUnicos = array_unique($valoresUnicos);
                sort($valoresUnicos);
                ?>
                <label for="<?= $columna ?>"><?= ucfirst($columna) ?>:</label>
                <select class="ele_tabla" name="<?= $columna ?>" onchange="this.form.submit()">
                    <option value="">-- Todos --</option>
                    <?php foreach ($valoresUnicos as $valor): ?>
                        <option value="<?= htmlspecialchars($valor) ?>" <?= (isset($_GET[$columna]) && $_GET[$columna] == $valor) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($valor) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endforeach; ?>
            <noscript><button type="submit">Filtrar</button></noscript>
        </form>

        <h2 class="titulo">Datos</h2>
        <!-- MOSTRAR TABLAS Y DATOS -->
        <div class="fondotabla">
        <div class="contenedor_tabla">
        <?php foreach ($datosTablas as $tabla => $registros): ?>
            <h2>Tabla: <?= htmlspecialchars($tabla) ?></h2>
            <?php if (empty($registros)): ?>
                <p>No hay registros para mostrar.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <?php foreach (array_keys($registros[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ($registros as $fila): ?>
                        <tr>
                            <?php foreach ($fila as $valor): ?>
                                <td><?= htmlspecialchars($valor) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <hr>
        <?php endforeach; ?>
         </div>
    </div>


<script>
    const coordenadas = [];

    <?php foreach ($datosFiltrados as $tabla => $filas): ?>
        <?php foreach ($filas as $fila): ?>
            coordenadas.push({
                id: <?= json_encode(current(array_filter($fila, fn($k) => str_starts_with($k, 'ID_'), ARRAY_FILTER_USE_KEY)) ?? 'Sin ID') ?>,
                lat: <?= json_encode($fila['LATITUD'] ?? null) ?>,
                lng: <?= json_encode($fila['LONGITUD'] ?? null) ?>,
                tipo:  <?= json_encode($tabla) ?>
            });
        <?php endforeach; ?>
    <?php endforeach; ?>
</script>



<!-------MAPA---------->
    <h2 class="titulo">Georeferenciación</h2>
    
    <div id="infoTipos"></div>

    <div id="map"></div>
    
    <script src="mapa1.js"></script>

</body>
</html>