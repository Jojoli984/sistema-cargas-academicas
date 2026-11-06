<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'])) {

    if ($_POST['accion'] === 'agregar_periodo') {
        $nombre = trim($_POST['nombre']);
        $conn->query("UPDATE Periodo SET activo = 0");
        $stmt = $conn->prepare("INSERT INTO Periodo (nombre, activo) VALUES (?, 1)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'activar_periodo') {
        $id = intval($_POST['id']);
        $conn->query("UPDATE Periodo SET activo = 0");
        $stmt = $conn->prepare("UPDATE Periodo SET activo = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'eliminar_periodo') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM Periodo WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'agregar_grupo') {
        $numero   = trim($_POST['numero_grupo']);
        $semestre = trim($_POST['semestre']);
        $turno    = trim($_POST['turno']);
        $stmt = $conn->prepare("INSERT INTO Grupos (numero_grupo, semestre, turno, activo) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $numero, $semestre, $turno);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'toggle_grupo') {
        $id     = intval($_POST['id']);
        $activo = intval($_POST['activo']);
        $nuevo  = $activo ? 0 : 1;
        $stmt = $conn->prepare("UPDATE Grupos SET activo = ? WHERE id = ?");
        $stmt->bind_param("ii", $nuevo, $id);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'eliminar_grupo') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM Grupos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: catalogos.php");
    exit();
}

$periodos = $conn->query("SELECT id, nombre, activo FROM Periodo ORDER BY id DESC");
$grupos   = $conn->query("SELECT id, numero_grupo, semestre, turno, activo FROM Grupos ORDER BY semestre ASC, numero_grupo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISOF — Catálogos</title>
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
        <div class="seccion-header">
          <a href="cargas.php" class="btn-volver">← Volver</a>
          <h2 class="seccion-titulo">Catálogos</h2>
          <p class="seccion-sub">Administra los periodos y grupos del sistema.</p>
        </div>

        <div class="dos-columnas">

          <!-- PERIODOS -->
          <div>
            <div class="form-nuevo" style="margin-bottom:24px;">
              <h3 class="form-titulo">Periodos</h3>
              <div class="tabla-wrapper" style="margin-bottom:20px;">
                <table class="tabla">
                  <thead>
                    <tr>
                      <th>Periodo</th>
                      <th>Estatus</th>
                      <th>Activar</th>
                      <th>Eliminar</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($p = $periodos->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($p['nombre']) ?></td>
                      <td>
                        <span class="badge-estatus <?= $p['activo'] ? 'badge-activo' : 'badge-inactivo' ?>">
                          <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                      </td>
                      <td>
                        <?php if (!$p['activo']): ?>
                        <form method="POST" action="catalogos.php">
                          <input type="hidden" name="accion" value="activar_periodo">
                          <input type="hidden" name="id" value="<?= $p['id'] ?>">
                          <button type="submit" class="btn-guardar">Activar</button>
                        </form>
                        <?php else: ?>
                          <span style="color:#aaa;font-size:0.78rem;">Actual</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <form method="POST" action="catalogos.php" onsubmit="return confirm('¿Eliminar este periodo?')">
                          <input type="hidden" name="accion" value="eliminar_periodo">
                          <input type="hidden" name="id" value="<?= $p['id'] ?>">
                          <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
              <h3 class="form-titulo">Agregar Periodo</h3>
              <form method="POST" action="catalogos.php" class="form-grid">
                <input type="hidden" name="accion" value="agregar_periodo">
                <div class="campo campo-full">
                  <label>Nombre del periodo</label>
                  <input type="text" name="nombre" placeholder="ej. Agosto 2025 - Enero 2026" required>
                </div>
                <div class="campo campo-full">
                  <button type="submit" class="btn-login">Agregar Periodo</button>
                </div>
              </form>
            </div>
          </div>

          <!-- GRUPOS -->
          <div>
            <div class="form-nuevo">
              <h3 class="form-titulo">Grupos</h3>
              <div class="tabla-wrapper" style="margin-bottom:20px;">
                <table class="tabla">
                  <thead>
                    <tr>
                      <th>Semestre</th>
                      <th>Grupo</th>
                      <th>Turno</th>
                      <th>Estatus</th>
                      <th>Eliminar</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while ($g = $grupos->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($g['semestre']) ?></td>
                      <td><?= htmlspecialchars($g['numero_grupo']) ?></td>
                      <td><?= htmlspecialchars($g['turno']) ?></td>
                      <td>
                        <form method="POST" action="catalogos.php">
                          <input type="hidden" name="accion" value="toggle_grupo">
                          <input type="hidden" name="id" value="<?= $g['id'] ?>">
                          <input type="hidden" name="activo" value="<?= $g['activo'] ?>">
                          <button type="submit" class="<?= $g['activo'] ? 'btn-guardar' : 'btn-eliminar' ?>">
                            <?= $g['activo'] ? 'Activo' : 'Inactivo' ?>
                          </button>
                        </form>
                      </td>
                      <td>
                        <form method="POST" action="catalogos.php" onsubmit="return confirm('¿Eliminar este grupo?')">
                          <input type="hidden" name="accion" value="eliminar_grupo">
                          <input type="hidden" name="id" value="<?= $g['id'] ?>">
                          <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
              <h3 class="form-titulo">Agregar Grupo</h3>
              <form method="POST" action="catalogos.php" class="form-grid">
                <input type="hidden" name="accion" value="agregar_grupo">
                <div class="campo">
                  <label>Semestre</label>
                  <input type="text" name="semestre" placeholder="ej. 3" required>
                </div>
                <div class="campo">
                  <label>Número de grupo</label>
                  <input type="text" name="numero_grupo" placeholder="ej. 01" required>
                </div>
                <div class="campo">
                  <label>Turno</label>
                  <select name="turno" class="select-tabla">
                    <option value="Mañana">Mañana</option>
                    <option value="Tarde">Tarde</option>
                  </select>
                </div>
                <div class="campo campo-full">
                  <button type="submit" class="btn-login">Agregar Grupo</button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</body>
</html>