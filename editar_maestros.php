<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

// Procesar acciones del formulario (editar, eliminar, agregar maestro y categorías)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['accion'])) {

    if ($_POST['accion'] === 'editar') {
        $clave            = intval($_POST['clave']);
        $id_categoria     = intval($_POST['id_categoria']);
        $activo           = intval($_POST['activo']);
        // Los grados y SNII/PRODEP vienen como array de checkboxes, se guardan separados por coma
        $grado            = isset($_POST['grado']) ? implode(', ', $_POST['grado']) : '';
        $snii_prodep      = isset($_POST['snii_prodep']) ? implode(', ', $_POST['snii_prodep']) : '';
        $fecha_de_ingreso = trim($_POST['fecha_de_ingreso']);
        $stmt = $conn->prepare("UPDATE Profesores SET id_categoria = ?, activo = ?, grado = ?, snii_prodep = ?, fecha_de_ingreso = ? WHERE clave = ?");
        $stmt->bind_param("iisssi", $id_categoria, $activo, $grado, $snii_prodep, $fecha_de_ingreso, $clave);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'eliminar') {
        $clave = intval($_POST['clave']);
        $stmt  = $conn->prepare("DELETE FROM Profesores WHERE clave = ?");
        $stmt->bind_param("i", $clave);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'agregar') {
        $clave            = intval($_POST['clave']);
        $nombre           = trim($_POST['nombre']);
        $id_categoria     = intval($_POST['id_categoria']);
        $email            = trim($_POST['email']);
        $grado            = isset($_POST['grado']) ? implode(', ', $_POST['grado']) : '';
        $snii_prodep      = isset($_POST['snii_prodep']) ? implode(', ', $_POST['snii_prodep']) : '';
        $fecha_de_ingreso = trim($_POST['fecha_de_ingreso']);
        $stmt = $conn->prepare("INSERT INTO Profesores (clave, nombre, id_categoria, email, grado, snii_prodep, fecha_de_ingreso, activo) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("isissss", $clave, $nombre, $id_categoria, $email, $grado, $snii_prodep, $fecha_de_ingreso);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'editar_categoria') {
        $id        = intval($_POST['id']);
        $horas_max = floatval($_POST['horas_max']);
        $horas_min = floatval($_POST['horas_min']);
        $stmt = $conn->prepare("UPDATE Categoria SET horas_max = ?, horas_min = ? WHERE id = ?");
        $stmt->bind_param("ddi", $horas_max, $horas_min, $id);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'agregar_categoria') {
        $tipo      = trim($_POST['tipo']);
        $horas_max = floatval($_POST['horas_max']);
        $horas_min = floatval($_POST['horas_min']);
        $stmt = $conn->prepare("INSERT INTO Categoria (tipo, horas_max, horas_min) VALUES (?, ?, ?)");
        $stmt->bind_param("sdd", $tipo, $horas_max, $horas_min);
        $stmt->execute();
    }

    if ($_POST['accion'] === 'eliminar_categoria') {
        $id   = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM Categoria WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: editar_maestros.php");
    exit();
}

// Buscar maestros por nombre o traer todos si no hay búsqueda
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
if ($busqueda !== '') {
    $like = "%$busqueda%";
    $stmt = $conn->prepare("SELECT p.clave, p.nombre, p.grado, p.id_categoria, p.activo, p.snii_prodep, p.fecha_de_ingreso, c.tipo FROM Profesores p LEFT JOIN Categoria c ON c.id = p.id_categoria WHERE p.nombre LIKE ? ORDER BY p.nombre ASC");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $maestros = $stmt->get_result();
} else {
    $maestros = $conn->query("SELECT p.clave, p.nombre, p.grado, p.id_categoria, p.activo, p.snii_prodep, p.fecha_de_ingreso, c.tipo FROM Profesores p LEFT JOIN Categoria c ON c.id = p.id_categoria ORDER BY p.nombre ASC");
}

// Cargar categorías para los desplegables
$categorias = $conn->query("SELECT id, tipo, horas_max, horas_min FROM Categoria ORDER BY tipo ASC");
$cats = [];
while ($c = $categorias->fetch_assoc()) {
    $cats[] = $c;
}

$grados = ['Licenciatura', 'Maestría', 'Doctorado', 'Especialidad'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISOF — Editar Maestros</title>
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
          <h2 class="seccion-titulo">Editar Maestros</h2>
          <p class="seccion-sub">Modifica la categoría, grado o estatus de cada maestro, o agrega uno nuevo.</p>
        </div>

        <!-- Buscador de maestros por nombre -->
        <form method="GET" action="editar_maestros.php" class="filtros-form">
          <input type="text" name="buscar" placeholder="Buscar maestro..." value="<?= htmlspecialchars($busqueda) ?>" class="buscador-input">
          <button type="submit" class="btn-guardar">Buscar</button>
          <?php if ($busqueda): ?>
            <a href="editar_maestros.php" class="btn-volver">Limpiar</a>
          <?php endif; ?>
        </form>

        <!-- Tabla de maestros con edición por fila -->
        <div class="tabla-wrapper">
          <table class="tabla">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Grado</th>
                <th>SNII/PRODEP</th>
                <th>Fecha Ingreso</th>
                <th>Categoría</th>
                <th>Estatus</th>
                <th>Guardar</th>
                <th>Eliminar</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $maestros->fetch_assoc()): ?>
              <tr>
                <form method="POST" action="editar_maestros.php">
                  <input type="hidden" name="accion" value="editar">
                  <input type="hidden" name="clave" value="<?= $row['clave'] ?>">
                  <td><?= htmlspecialchars($row['nombre']) ?></td>
                  <td>
                    <!-- Checkboxes de grado académico -->
                    <div class="checkboxes-grado">
                      <?php foreach ($grados as $g): ?>
                        <?php $grados_prof = explode(',', $row['grado'] ?? ''); ?>
                        <label class="check-label">
                          <input type="checkbox" name="grado[]" value="<?= $g ?>"
                            <?= in_array($g, array_map('trim', $grados_prof)) ? 'checked' : '' ?>>
                          <?= $g ?>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </td>
                  <td>
                    <!-- Checkboxes de SNII y PRODEP -->
                    <div class="checkboxes-grado">
                      <?php $sp = array_map('trim', explode(',', $row['snii_prodep'] ?? '')); ?>
                      <label class="check-label">
                        <input type="checkbox" name="snii_prodep[]" value="SNII" <?= in_array('SNII', $sp) ? 'checked' : '' ?>>
                        SNII
                      </label>
                      <label class="check-label">
                        <input type="checkbox" name="snii_prodep[]" value="PRODEP" <?= in_array('PRODEP', $sp) ? 'checked' : '' ?>>
                        PRODEP
                      </label>
                    </div>
                  </td>
                  <td>
                    <input type="date" name="fecha_de_ingreso" value="<?= htmlspecialchars($row['fecha_de_ingreso'] ?? '') ?>" class="input-tabla">
                  </td>
                  <td>
                    <select name="id_categoria" class="select-tabla">
                      <?php foreach ($cats as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $row['id_categoria'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['tipo']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td>
                    <select name="activo" class="select-tabla">
                      <option value="1" <?= $row['activo'] == 1 ? 'selected' : '' ?>>Activo</option>
                      <option value="0" <?= $row['activo'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                  </td>
                  <td>
                    <button type="submit" class="btn-guardar">Guardar</button>
                  </td>
                </form>
                <td>
                  <form method="POST" action="editar_maestros.php" onsubmit="return confirm('¿Seguro que deseas eliminar a este maestro?')">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="clave" value="<?= $row['clave'] ?>">
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- Formulario para agregar un nuevo maestro -->
        <div class="form-nuevo">
          <h3 class="form-titulo">Agregar Maestro</h3>
          <form method="POST" action="editar_maestros.php" class="form-grid">
            <input type="hidden" name="accion" value="agregar">
            <div class="campo">
              <label>No. Empleado</label>
              <input type="number" name="clave" required>
            </div>
            <div class="campo">
              <label>Nombre completo</label>
              <input type="text" name="nombre" required>
            </div>
            <div class="campo">
              <label>Email</label>
              <input type="email" name="email">
            </div>
            <div class="campo">
              <label>Grado</label>
              <div class="checkboxes-grado">
                <?php foreach ($grados as $g): ?>
                  <label class="check-label">
                    <input type="checkbox" name="grado[]" value="<?= $g ?>">
                    <?= $g ?>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="campo">
              <label>Fecha de Ingreso</label>
              <input type="date" name="fecha_de_ingreso">
            </div>
            <div class="campo">
              <label>SNII / PRODEP</label>
              <div class="checkboxes-grado">
                <label class="check-label">
                  <input type="checkbox" name="snii_prodep[]" value="SNII"> SNII
                </label>
                <label class="check-label">
                  <input type="checkbox" name="snii_prodep[]" value="PRODEP"> PRODEP
                </label>
              </div>
            </div>
            <div class="campo">
              <label>Categoría</label>
              <select name="id_categoria" class="select-tabla">
                <?php foreach ($cats as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['tipo']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="campo campo-full">
              <button type="submit" class="btn-login">Agregar Maestro</button>
            </div>
          </form>
        </div>

        <!-- Sección de gestión de categorías -->
        <div class="form-nuevo" style="margin-top:28px;">
          <h3 class="form-titulo">Categorías</h3>
          <div class="tabla-wrapper" style="margin-bottom:24px;">
            <table class="tabla">
              <thead>
                <tr>
                  <th>Tipo</th>
                  <th>Horas Máx</th>
                  <th>Horas Mín</th>
                  <th>Guardar</th>
                  <th>Eliminar</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result_cats = $conn->query("SELECT id, tipo, horas_max, horas_min FROM Categoria ORDER BY tipo ASC");
                while ($cat = $result_cats->fetch_assoc()):
                ?>
                <tr>
                  <form method="POST" action="editar_maestros.php">
                    <input type="hidden" name="accion" value="editar_categoria">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <td><?= htmlspecialchars($cat['tipo']) ?></td>
                    <td><input type="number" step="0.5" name="horas_max" value="<?= $cat['horas_max'] ?>" class="input-tabla" style="width:70px;"></td>
                    <td><input type="number" step="0.5" name="horas_min" value="<?= $cat['horas_min'] ?>" class="input-tabla" style="width:70px;"></td>
                    <td><button type="submit" class="btn-guardar">Guardar</button></td>
                  </form>
                  <td>
                    <form method="POST" action="editar_maestros.php" onsubmit="return confirm('¿Eliminar esta categoría?')">
                      <input type="hidden" name="accion" value="eliminar_categoria">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <h3 class="form-titulo">Agregar Categoría</h3>
          <form method="POST" action="editar_maestros.php" class="form-grid">
            <input type="hidden" name="accion" value="agregar_categoria">
            <div class="campo">
              <label>Tipo</label>
              <input type="text" name="tipo" required>
            </div>
            <div class="campo">
              <label>Horas Máximas</label>
              <input type="number" step="0.5" name="horas_max" required>
            </div>
            <div class="campo">
              <label>Horas Mínimas</label>
              <input type="number" step="0.5" name="horas_min" required>
            </div>
            <div class="campo campo-full">
              <button type="submit" class="btn-login">Agregar Categoría</button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

</body>
</html>