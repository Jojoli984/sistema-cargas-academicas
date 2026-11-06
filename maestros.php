<?php
session_start();

// Si no hay sesión activa, redirigir al login
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
  <title>Maestros</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="dashboard-page">

  <div class="layout">

    <!-- Barra lateral con logo y nombre del usuario -->
    <aside class="sidebar">
      <div class="sidebar-logo">
        <div class="sidebar-sigla">UAdeO</div>
        <div class="sidebar-nombre">Universidad Autónoma<br>de Occidente</div>
      </div>
      <div class="sidebar-avatar">
        <!-- Muestra la inicial del nombre del usuario en sesión -->
        <div class="avatar-circulo"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
        <div class="avatar-nombre"><?= htmlspecialchars($_SESSION['nombre']) ?></div>
      </div>
    </aside>

    <div class="main">

      <!-- Barra de navegación principal -->
      <nav class="navbar">
        <div class="navbar-brand">ISOF</div>
        <div class="navbar-links">
          <a href="dashboard.php" class="nav-link">Inicio</a>
          <a href="maestros.php" class="nav-link activo">Maestros</a>
          <a href="materias.php" class="nav-link">Materias</a>
          <a href="cargas.php" class="nav-link">Carga Académica</a>
          <a href="logout.php" class="nav-link nav-logout">Cerrar Sesión</a>
        </div>
      </nav>

      <div class="contenido">
        <h2 class="seccion-titulo">Maestros</h2>
        <p class="seccion-sub">Selecciona una opción para continuar.</p>

        <!-- Tarjetas de opciones disponibles -->
        <div class="tarjetas">

          <a href="historial_maestros.php" class="tarjeta">
            <div class="tarjeta-icono">📋</div>
            <div class="tarjeta-titulo">Generar Historial</div>
            <div class="tarjeta-desc">Consulta y genera un PDF con las materias que ha impartido cada maestro a lo largo de los periodos.</div>
          </a>

          <a href="editar_maestros.php" class="tarjeta">
            <div class="tarjeta-icono">✏️</div>
            <div class="tarjeta-titulo">Editar Información</div>
            <div class="tarjeta-desc">Modifica la categoría de un maestro o agrega nuevos docentes al sistema.</div>
          </a>

        </div>
      </div>

    </div>
  </div>

</body>
</html>