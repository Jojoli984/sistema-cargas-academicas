<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

// Si hay búsqueda, filtrar maestros por nombre; si no, traer todos los activos
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if ($busqueda !== '') {
    $like = "%$busqueda%";
    $stmt = $conn->prepare("SELECT clave, nombre FROM Profesores WHERE activo = 1 AND nombre LIKE ? ORDER BY nombre ASC");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $maestros = $stmt->get_result();
} else {
    $maestros = $conn->query("SELECT clave, nombre FROM Profesores WHERE activo = 1 ORDER BY nombre ASC");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISOF — Historial de Maestros</title>
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
          <a href="maestros.php" class="nav-link activo">Maestros</a>
          <a href="materias.php" class="nav-link">Materias</a>
          <a href="cargas.php" class="nav-link">Carga Académica</a>
          <a href="logout.php" class="nav-link nav-logout">Cerrar Sesión</a>
        </div>
      </nav>

      <div class="contenido">
        <div class="seccion-header">
          <a href="maestros.php" class="btn-volver">← Volver</a>
          <h2 class="seccion-titulo">Historial de Maestros</h2>
          <p class="seccion-sub">Selecciona un maestro para generar su historial en PDF.</p>
        </div>

        <!-- Buscador de maestros por nombre -->
        <form method="GET" action="historial_maestros.php" class="filtros-form">
          <input type="text" name="buscar" placeholder="Buscar maestro..." value="<?= htmlspecialchars($busqueda) ?>" class="buscador-input">
          <button type="submit" class="btn-guardar">Buscar</button>
          <?php if ($busqueda): ?>
            <a href="historial_maestros.php" class="btn-volver">Limpiar</a>
          <?php endif; ?>
        </form>

        <!-- Lista de maestros con botón para generar PDF individual -->
        <div class="lista-maestros">
          <?php while ($row = $maestros->fetch_assoc()): ?>
            <div class="maestro-item">
              <div class="maestro-nombre"><?= htmlspecialchars($row['nombre']) ?></div>
              <a href="generar_historial.php?clave=<?= $row['clave'] ?>" target="_blank" class="btn-pdf">Generar PDF</a>
            </div>
          <?php endwhile; ?>
        </div>

      </div>
    </div>
  </div>

</body>
</html>