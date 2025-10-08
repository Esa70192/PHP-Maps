<?php

require 'conexiondb.php';
require 'tablasdb.php';
require 'mapa.php';

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
    <!--Conexion-->
    <?php if ($conexionExitosa): ?>
    <?php else: ?>
        <p class = "bd">Error al conectar: <?= $errorMensaje ?></p>
    <?php endif; ?>

    <div class="inicio">
        <form action="login.php" method="POST">
            <button class="botonpag" type="submit">Iniciar sesion</button>
        </form>
    </div>
    
    <h1 class="titulo">Sistema de Información</h1>
    <div class= "texto">
        <form method="post">
            <label for="tabla">Elija la tabla:</label>
            <select class="ele_tabla" name="tabla" required onchange="this.form.submit()">
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
    <?php if ($tablaSeleccionada && !empty($columnas_muestra)): ?>
        <form method="post">
            <input type="hidden" name="tabla" value="<?= htmlspecialchars($tablaSeleccionada) ?>">
            <h3>Filtros</h3>
            <?php foreach ($columnas_muestra as $columna): ?>
                <label for="filtros[<?= htmlspecialchars($columna) ?>]"><?= htmlspecialchars($columna) ?>:</label>
                <select class="ele_tabla" name="filtros[<?= htmlspecialchars($columna) ?>]" id="filtros[<?= htmlspecialchars($columna) ?>] " onchange="this.form.submit()">
                    <option value="">-- Todos --</option>
                    <?php foreach ($valoresUnicos[$columna] as $valor): ?>
                        <option value="<?= htmlspecialchars($valor) ?>" <?= (isset($filtros[$columna]) && $filtros[$columna] == $valor) ? "selected" : "" ?>>
                            <?= htmlspecialchars($valor) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>
            <?php endforeach; ?>
        </form>
    <?php endif; ?>

    <!-------DATOS DE TABLA----->
    <?php if ($datos): ?>
    <h2 class="titulo">Información de <?= htmlspecialchars($tablaSeleccionada) ?></h2>
    <div class="fondotabla">
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
    </div>
    

    <!--MAPA-->
    <h2 class="titulo">Georeferenciación</h2>
    <div id="map"></div>
    <script>
      const coordenadas = <?= json_encode($puntosMapa); ?>;
    </script>
    <script src="mapa.js"></script>

</body>
</html>