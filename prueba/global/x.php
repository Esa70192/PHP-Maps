<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Menú desplegable</title>
  <style>
    .menu-desplegable {
      position: relative;
      display: inline-block;
    }

    .boton-menu {
      background-color: #3498db;
      color: white;
      padding: 10px 20px;
      border: none;
      cursor: pointer;
    }

    .opciones {
      display: none;
      position: absolute;
      background-color: #f1f1f1;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
      z-index: 1;
    }

    .opciones a {
      color: black;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
    }

    .opciones a:hover {
      background-color: #ddd;
    }

    .menu-desplegable:hover .opciones {
      display: block;
    }
  </style>
</head>
<body>

<div class="menu-desplegable">
  <button class="boton-menu">Menú</button>
  <div class="opciones">
    <a href="#">Opción 1</a>
    <a href="#">Opción 2</a>
    <a href="#">Opción 3</a>
  </div>
</div>

</body>
</html>
