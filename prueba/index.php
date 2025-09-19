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