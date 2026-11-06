<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

// Procesar acciones del formulario (editar, agregar, eliminar)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'])) {

    if ($_POST['accion'] === 'editar') {
        $clave_original = trim($_POST['clave_original']);
        $clave_nueva    = trim($_POST['clave_nueva']);
        $estatus        = trim($_POST['estatus']);
        $modalidad      = trim($_POST['modalidad']);
        $nivel          = trim($_POST['nivel']);
        $stmt = $conn->prepare("UPDATE Materia SET clave = ?, estatus = ?, modalidad = ?, nivel = ? WHERE clave = ?");
        $stmt->bind_param("sssss", $clave_nueva, $estatus, $modalidad, $nivel, $clave_original);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'agregar') {
        $clave     = trim($_POST['clave']);
        $nombre    = trim($_POST['nombre']);
        $semestre  = trim($_POST['semestre']);
        $estatus   = trim($_POST['estatus']);
        $modalidad = trim($_POST['modalidad']);
        $nivel     = trim($_POST['nivel']);
        $stmt = $conn->prepare("INSERT INTO Materia (clave, nombre, semestre, estatus, modalidad, nivel) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $clave, $nombre, $semestre, $estatus, $modalidad, $nivel);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'eliminar') {
        $clave = trim($_POST['clave']);
        $stmt  = $conn->prepare("DELETE FROM Materia WHERE clave = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
    }

    header("Location: materias.php");
    exit();
}

// Capturar filtros y búsqueda desde la URL
$busqueda         = isset($_GET['buscar'])    ? trim($_GET['buscar'])    : '';
$filtro_estatus   = isset($_GET['estatus'])   ? trim($_GET['estatus'])   : 'A';
$filtro_modalidad = isset($_GET['modalidad']) ? trim($_GET['modalidad']) : '';
$filtro_semestre  = isset($_GET['semestre'])  ? trim($_GET['semestre'])  : '';
$filtro_nivel     = isset($_GET['nivel'])     ? trim($_GET['nivel'])     : '';

// Construir el query dinámicamente según los filtros activos
$where = [];
$params = [];
$types = '';

if ($busqueda !== '') {
    $like = "%$busqueda%";
    $where[] = '(nombre LIKE ? OR clave LIKE ?)';
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}
if ($filtro_estatus !== '') {
    $where[] = 'estatus = ?';
    $params[] = $filtro_estatus;
    $types .= 's';
}
if ($filtro_nivel !== '') {
    $where[] = 'nivel = ?';
    $params[] = $filtro_nivel;
    $types .= 's';
}
if ($filtro_modalidad !== '') {
    $where[] = 'modalidad = ?';
    $params[] = $filtro_modalidad;
    $types .= 's';
}
if ($filtro_semestre !== '') {
    $where[] = 'semestre = ?';
    $params[] = $filtro_semestre;
    $types .= 's';
}

$sql = "SELECT clave, nombre, semestre, estatus, modalidad, nivel FROM Materia";
if (count($where)) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY nombre ASC';

$stmt = $conn->prepare($sql);
if (count($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$materias = $stmt->get_result();

// Obtener semestres disponibles para el filtro
$semestres = $conn->query("SELECT DISTINCT semestre FROM Materia WHERE semestre IS NOT NULL AND semestre != '' ORDER BY semestre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISOF — Materias</title>
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
          <a href="materias.php" class="nav-link activo">Materias</a>
          <a href="cargas.php" class="nav-link">Carga Académica</a>
          <a href="logout.php" class="nav-link nav-logout">Cerrar Sesión</a>
        </div>
      </nav>

      <div class="contenido">
        <div class="seccion-header">
          <h2 class="seccion-titulo">Materias</h2>
          <p class="seccion-sub">Consulta, edita el estatus o la clave de cada materia.</p>
        </div>

        <!-- Filtros de búsqueda, por defecto muestra solo activas -->
        <form method="GET" action="materias.php" class="filtros-form">
          <input type="text" name="buscar" placeholder="Buscar por nombre o clave..." value="<?= htmlspecialchars($busqueda) ?>" class="buscador-input">

          <select name="estatus" class="select-tabla">
            <option value="">Todos los estatus</option>
            <option value="A" <?= $filtro_estatus === 'A' ? 'selected' : '' ?>>Activas</option>
            <option value="I" <?= $filtro_estatus === 'I' ? 'selected' : '' ?>>Inactivas</option>
          </select>

          <select name="nivel" class="select-tabla">
            <option value="">Todos los niveles</option>
            <option value="Licenciatura" <?= $filtro_nivel === 'Licenciatura' ? 'selected' : '' ?>>Licenciatura</option>
            <option value="Posgrado" <?= $filtro_nivel === 'Posgrado' ? 'selected' : '' ?>>Posgrado</option>
          </select>

          <select name="modalidad" class="select-tabla">
            <option value="">Todas las modalidades</option>
            <option value="Presencial" <?= $filtro_modalidad === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
            <option value="En línea" <?= $filtro_modalidad === 'En línea' ? 'selected' : '' ?>>En línea</option>
          </select>

          <select name="semestre" class="select-tabla">
            <option value="">Todos los semestres</option>
            <?php while ($sem = $semestres->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($sem['semestre']) ?>" <?= $filtro_semestre === $sem['semestre'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($sem['semestre']) ?>
              </option>
            <?php endwhile; ?>
          </select>

          <button type="submit" class="btn-guardar">Filtrar</button>
          <a href="materias.php" class="btn-volver">Limpiar</a>
        </form>

        <!-- Tabla de materias con opciones de edición por fila -->
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Clave</th>
                <th>Semestre</th>
                <th>Nivel</th>
                <th>Modalidad</th>
                <th>Estatus</th>
                <th>Guardar</th>
                <th>Eliminar</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $materias->fetch_assoc()): ?>
              <tr>
                <form method="POST" action="materias.php">
                  <input type="hidden" name="accion" value="editar">
                  <input type="hidden" name="clave_original" value="<?= htmlspecialchars($row['clave']) ?>">
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td>
                    <input type="text" name="clave_nueva" value="<?= htmlspecialchars($row['clave']) ?>" class="input-tabla">
                  </td>
                  <td><?= htmlspecialchars($row['semestre']) ?></td>
                  <td>
                    <select name="nivel" class="select-tabla">
                      <option value="Licenciatura" <?= $row['nivel'] === 'Licenciatura' ? 'selected' : '' ?>>Licenciatura</option>
                      <option value="Posgrado" <?= $row['nivel'] === 'Posgrado' ? 'selected' : '' ?>>Posgrado</option>
                    </select>
                  </td>
                  <td>
                    <select name="modalidad" class="select-tabla">
                      <option value="Presencial" <?= $row['modalidad'] === 'Presencial' ? 'selected' : '' ?>>Presencial</option>
                      <option value="En línea" <?= $row['modalidad'] === 'En línea' ? 'selected' : '' ?>>En línea</option>
                    </select>
                  </td>
                  <td>
                    <select name="estatus" class="select-tabla">
                      <option value="A" <?= $row['estatus'] === 'A' ? 'selected' : '' ?>>Activa</option>
                      <option value="I" <?= $row['estatus'] === 'I' ? 'selected' : '' ?>>Inactiva</option>
                    </select>
                  </td>
                  <td>
                    <button type="submit" class="btn-guardar">Guardar</button>
                  </td>
                </form>
                <td>
                  <form method="POST" action="materias.php" onsubmit="return confirm('¿Seguro que deseas eliminar esta materia?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="clave" value="<?= htmlspecialchars($row['clave']) ?>">
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Formulario para agregar una nueva materia -->
        <div class="form-nuevo">
          <h3 class="form-titulo">Agregar Materia</h3>
          <form method="POST" action="materias.php" class="form-grid">
            <input type="hidden" name="accion" value="agregar">
            <div class="campo">
              <label>Clave</label>
              <input type="text" name="clave" required>
            </div>
            <div class="campo">
              <label>Nombre</label>
              <input type="text" name="nombre" required>
            </div>
            <div class="campo">
              <label>Semestre</label>
              <input type="text" name="semestre">
            </div>
            <div class="campo">
              <label>Nivel</label>
              <select name="nivel" class="select-tabla">
                <option value="Licenciatura">Licenciatura</option>
                <option value="Posgrado">Posgrado</option>
              </select>
            </div>
            <div class="campo">
              <label>Modalidad</label>
              <select name="modalidad" class="select-tabla">
                <option value="Presencial">Presencial</option>
                <option value="En línea">En línea</option>
              </select>
            </div>
            <div class="campo">
              <label>Estatus</label>
              <select name="estatus" class="select-tabla">
                <option value="A">Activa</option>
                <option value="I">Inactiva</option>
              </select>
            </div>
            <div class="campo campo-full">
              <button type="submit" class="btn-login">Agregar Materia</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

</body>
</html>