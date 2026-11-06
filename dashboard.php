<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Cargas Académicas</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="dashboard-page">

  <div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="sidebar-sigla">UAdeO</div>
        <div class="sidebar-nombre">Universidad Autónoma<br>de Occidente</div>
      </div>
      <div class="sidebar-avatar">
        <div class="avatar-circulo"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
        <div class="avatar-nombre"><?= htmlspecialchars($_SESSION['nombre']) ?></div>
      </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main">

      <!-- NAVBAR -->
      <nav class="navbar">
        <div class="navbar-brand">ISOF</div>
        <div class="navbar-links">
          <a href="maestros.php" class="nav-link">Maestros</a>
          <a href="materias.php" class="nav-link">Materias</a>
          <a href="cargas.php" class="nav-link">Carga Académica</a>
          <a href="logout.php" class="nav-link nav-logout">Cerrar Sesión</a>
        </div>
      </nav>

      <!-- CONTENIDO -->
      <div class="contenido">
        <div class="bienvenida">
          <h1 class="bienvenida-titulo">Gestión de Cargas Académicas</h1>
          <p class="bienvenida-sub">Bienvenida, <?= htmlspecialchars($_SESSION['nombre']) ?>. Este sistema te permite administrar y generar las cargas académicas de los docentes de ISOF de manera automatizada.</p>
        </div>
      </div>

    </div>
  </div>

</body>
</html>