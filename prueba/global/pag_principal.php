<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: pag_login.php");
    exit();
}

require 'conexiondb.php';
//require 'tablasdb.php';
require 'filtro.php';
require 'mapa3.php';

if (isset($_SESSION['error_nombre'])) {
    $error = $_SESSION["error_nombre"];
    unset($_SESSION['error_nombre']);
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
    <?php if ($conexionExitosa): ?>
    <?php else: ?>
        <p class = "bd">Error al conectar: <?= $errorMensaje ?></p>
    <?php endif; ?>

    <div class="encabezado">
        <div class="izquierda">
            <h3>Selecione el archivo</h3>
            <form class="subirarchivo" action="subir.php" method="POST" enctype="multipart/form-data">
                <label for="archivo" class="seleccion_arch">
                    Seleccionar archivo
                </label>
                <input id="archivo" type="file" name="archivo" accept=".csv" onchange="mostrarNombre(this)">
                <span class="filename" id="nombreArchivo">Ningún archivo seleccionado</span>
                <script src="subir.js"></script>
                <div class="nombre_tabla">
                    <text class="texto">Ingrese nombre de tabla</text>
                    <input type="text" name="nombre_tabla">
                </div>
                <button class="botonpag" type="submit">Subir</button>
                <?php if(!empty($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
            </form>
        </div>
        <div class="derecha">
            <form class="form_cerrar" action="logout.php" method="POST">
                <button class="botonpag" type="submit">Cerrar sesión</button>
            </form>
        </div>
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


<!-------MAPA---------->
    <h2 class="titulo">Georeferenciación</h2>

     <div id="map"></div>
    <script>
      const coordenadas = <?= json_encode($puntosMapa); ?>;
    </script>
    <script src="mapa.js"></script>                           

</body>
</html>