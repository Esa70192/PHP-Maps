<?php

require "conexiondb.php";
require "filtro.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Filtros y Datos</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { color: #444; margin-top: 40px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        th { background-color: #eee; }
        select { margin: 5px 0; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Datos con Filtros</h1>

    <?php if ($errores): ?>
        <p style="color:red;">Error: <?= htmlspecialchars($errores) ?></p>
    <?php else: ?>

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
                <select name="<?= $columna ?>" onchange="this.form.submit()">
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

        <!-- MOSTRAR TABLAS Y DATOS -->
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
    <?php endif; ?>
</body>
</html>
