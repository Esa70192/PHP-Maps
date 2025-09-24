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
$filtros = $_POST['filtros'] ?? [];
$datos = [];
$columnas = [];

if ($tablaSeleccionada && in_array($tablaSeleccionada, $tablas)) {
    // Obtener columnas de la tabla seleccionada
    $stmt = $conn->prepare("DESCRIBE `$tablaSeleccionada`");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Construir la consulta con filtros dinámicos
    $where = [];
    $params = [];

    foreach ($columnas as $columna) {
        if (!empty($filtros[$columna])) {
            $where[] = "`$columna` = :$columna";
            $params[$columna] = $filtros[$columna];
        }
    }

    $sql = "SELECT * FROM `$tablaSeleccionada`";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $datos = $stmt->fetchALL(PDO::FETCH_ASSOC);

    // Obtener valores únicos por columna para los selects
    $valoresUnicos = [];
    foreach ($columnas as $columna) {
        $stmt = $conn->prepare("SELECT DISTINCT `$columna` FROM `$tablaSeleccionada` ORDER BY `$columna`");
        $stmt->execute();
        $valoresUnicos[$columna] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Filtrado por columnas</title>
</head>
<body>

<h1>Conexión a la base de datos</h1>

<?php if ($conexionExitosa): ?>
    <p style="color: green;">Conexión exitosa</p>
<?php else: ?>
    <p style="color: red;">Error al conectar: <?= $errorMensaje ?></p>
<?php endif; ?>

<form method="post">
    <label for="tabla">Tablas:</label>
    <select name="tabla" id="tabla" required onchange="this.form.submit()">
        <option value="">-- Selecciona una tabla --</option>
        <?php foreach ($tablas as $tabla): ?>
            <option value="<?= htmlspecialchars($tabla) ?>" <?= ($tabla === $tablaSeleccionada) ? "selected" : "" ?>>
                <?= htmlspecialchars($tabla) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

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
    <p>No hay datos para mostrar.</p>
<?php endif; ?>

</body>
</html>
