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
  <title>ISOF — Carga Académica</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="dashboard-page">

  <div class="layout">

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

    <div class="main">

      <nav class="navbar">
        <div class="navbar-brand">ISOF</div>
        <div class="navbar-links">
          <a href="dashboard.php" class="nav-link">Inicio</a>
          <a href="maestros.php" class="nav-link">Maestros</a>
          <a href="materias.php" class="nav-link">Materias</a>
          <a href="cargas.php" class="nav-link activo">Carga Académica</a>
          <a href="logout.php" class="nav-link nav-logout">Cerrar Sesión</a>
        </div>
      </nav>

      <div class="contenido">
        <h2 class="seccion-titulo">Carga Académica</h2>
        <p class="seccion-sub">Selecciona una opción para continuar.</p>

        <div class="tarjetas">

          <a href="generar_carga.php" class="tarjeta">
            <div class="tarjeta-icono">⚡</div>
            <div class="tarjeta-titulo">Generar Carga Académica</div>
            <div class="tarjeta-desc">Asigna materias y grupos a los docentes para el periodo actual de forma automatizada.</div>
          </a>

          <a href="historial_cargas.php" class="tarjeta">
            <div class="tarjeta-icono">📂</div>
            <div class="tarjeta-titulo">Historial de Cargas</div>
            <div class="tarjeta-desc">Consulta las cargas académicas generadas en periodos anteriores.</div>
          </a>

          <a href="catalogos.php" class="tarjeta">
            <div class="tarjeta-icono">🏫</div>
            <div class="tarjeta-titulo">Catálogo de Grupos</div>
            <div class="tarjeta-desc">Administra los grupos activos e inactivos por semestre.</div>
          </a>

          <a href="catalogos.php" class="tarjeta">
            <div class="tarjeta-icono">📅</div>
            <div class="tarjeta-titulo">Catálogo de Periodos</div>
            <div class="tarjeta-desc">Define el periodo activo actual para la generación de cargas.</div>
          </a>

        </div>
      </div>

    </div>
  </div>

</body>
</html>