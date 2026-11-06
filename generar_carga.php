<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

// Obtener el periodo activo actual
$periodo = $conn->query("SELECT id, nombre FROM Periodo WHERE activo = 1 LIMIT 1")->fetch_assoc();

if (!$periodo) {
    die("No hay un periodo activo. Ve a Catálogos y activa un periodo.");
}

// Definir qué semestres corresponden según el mes actual
// Febrero-Julio: semestres pares | Resto del año: semestres nones
$mes = intval(date('n'));
if ($mes >= 2 && $mes <= 7) {
    $semestres_activos = ['2', '4', '6', '8'];
} else {
    $semestres_activos = ['1', '3', '5', '7'];
}

// Traer las materias activas de los semestres correspondientes al periodo
$placeholders = implode(',', array_fill(0, count($semestres_activos), '?'));
$tipos = str_repeat('s', count($semestres_activos));

$stmt = $conn->prepare("SELECT clave, nombre, semestre FROM Materia WHERE estatus = 'A' AND semestre IN ($placeholders) ORDER BY semestre ASC, nombre ASC");
$stmt->bind_param($tipos, ...$semestres_activos);
$stmt->execute();
$materias = $stmt->get_result();

// Cargar los grupos activos agrupados por semestre
$grupos_query = $conn->query("SELECT id, numero_grupo, semestre, turno FROM Grupos WHERE activo = 1 ORDER BY semestre ASC, numero_grupo ASC");
$grupos = [];
while ($g = $grupos_query->fetch_assoc()) {
    $grupos[$g['semestre']][] = $g;
}

// Función que sugiere profesores para una materia
// Ordena por: categoría (Tiempo Completo > Medio Tiempo > Base) → antigüedad → si ya dio la materia antes
function getSugerenciasProfe($conn, $clave_materia, $periodo_nombre) {
    $stmt = $conn->prepare("
        SELECT p.clave, p.nombre, p.fecha_de_ingreso, p.snii_prodep,
               c.tipo as categoria, c.horas_max, c.horas_min,
               CASE WHEN h.clave_profesor IS NOT NULL THEN 1 ELSE 0 END as tiene_historial,
               COALESCE(SUM(ca.horas_asignadas), 0) as horas_actuales
        FROM Profesores p
        LEFT JOIN Categoria c ON c.id = p.id_categoria
        LEFT JOIN Historial_Profesor h ON h.clave_profesor = p.clave AND h.clave_materia = ?
        LEFT JOIN Carga_Academica ca ON ca.clave_profesor = p.clave AND ca.periodo = ?
        WHERE p.activo = 1
        GROUP BY p.clave
        ORDER BY 
            CASE 
                WHEN c.tipo LIKE 'Tiempo Completo%' THEN 1
                WHEN c.tipo LIKE 'Medio Tiempo%' THEN 2
                WHEN c.tipo LIKE 'Base%' THEN 3
                ELSE 4
            END ASC,
            p.fecha_de_ingreso ASC,
            tiene_historial DESC
    ");
    $stmt->bind_param("ss", $clave_materia, $periodo_nombre);
    $stmt->execute();
    return $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ISOF — Generar Carga</title>
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

        <!-- Mensajes de éxito o error tras guardar una asignación -->
        <?php if (isset($_GET['ok'])): ?>
          <div class="alerta-ok">✅ Asignación guardada correctamente.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
          <div class="alerta-error">⚠️ <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="seccion-header">
          <a href="cargas.php" class="btn-volver">← Volver</a>
          <h2 class="seccion-titulo">Generar Carga Académica</h2>
          <p class="seccion-sub">Periodo: <strong><?= htmlspecialchars($periodo['nombre']) ?></strong> · Semestres activos: <?= implode(', ', $semestres_activos) ?></p>
        </div>

        <?php
        // Recorrer materias agrupadas por semestre
        $semestre_actual = '';
        while ($materia = $materias->fetch_assoc()):
          if ($materia['semestre'] !== $semestre_actual):
            if ($semestre_actual !== '') echo '</div>';
            $semestre_actual = $materia['semestre'];
        ?>
        <div class="carga-semestre">
          <h3 class="carga-semestre-titulo">Semestre <?= htmlspecialchars($semestre_actual) ?></h3>
        <?php endif; ?>

          <!-- Tarjeta de asignación por materia -->
          <div class="carga-materia">
            <div class="carga-materia-nombre">
              <span class="carga-materia-titulo"><?= htmlspecialchars($materia['nombre']) ?></span>
              <span class="carga-clave"><?= htmlspecialchars($materia['clave']) ?></span>
            </div>

            <form method="POST" action="guardar_asignacion.php" class="carga-form">
              <input type="hidden" name="clave_materia" value="<?= htmlspecialchars($materia['clave']) ?>">
              <input type="hidden" name="periodo" value="<?= htmlspecialchars($periodo['nombre']) ?>">

              <div class="carga-campos">
                <!-- Seleccionar grupo del mismo semestre -->
                <div class="campo">
                  <label>Grupo</label>
                  <select name="id_grupo" class="select-tabla" required>
                    <option value="">Seleccionar grupo</option>
                    <?php if (isset($grupos[$materia['semestre']])): ?>
                      <?php foreach ($grupos[$materia['semestre']] as $g): ?>
                        <option value="<?= $g['id'] ?>">
                          Grupo <?= $g['numero_grupo'] ?> · <?= $g['turno'] ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>

                <!-- Lista de profesores ordenada por sugerencia (★ = ya dio la materia antes) -->
                <div class="campo">
                  <label>Profesor sugerido</label>
                  <select name="clave_profesor" class="select-tabla" required>
                    <option value="">Seleccionar profesor</option>
                    <?php
                    $sugerencias = getSugerenciasProfe($conn, $materia['clave'], $periodo['nombre']);
                    $primero = true;
                    while ($prof = $sugerencias->fetch_assoc()):
                      $horas_restantes = $prof['horas_max'] - $prof['horas_actuales'];
                      $etiqueta = $prof['tiene_historial'] ? '★ ' : '';
                      $etiqueta .= $prof['nombre'] . ' · ' . $prof['categoria'] . ' · ' . $horas_restantes . 'h disp.';
                    ?>
                      <option value="<?= $prof['clave'] ?>" <?= $primero ? 'selected' : '' ?>>
                        <?= htmlspecialchars($etiqueta) ?>
                      </option>
                    <?php
                      $primero = false;
                    endwhile;
                    ?>
                  </select>
                </div>

                <div class="campo">
                  <label>Horas a asignar</label>
                  <input type="number" name="horas_asignadas" step="0.5" min="1" value="1.5" class="input-tabla" style="width:80px;" required>
                </div>

                <div class="campo" style="align-self:flex-end;">
                  <button type="submit" class="btn-guardar">Asignar</button>
                </div>
              </div>
            </form>
          </div>

        <?php endwhile; ?>
        <?php if ($semestre_actual !== '') echo '</div>'; ?>

      </div>
    </div>
  </div>

</body>
</html>