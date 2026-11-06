<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "config.php";

// Recibir los datos enviados desde el formulario de generar carga
$clave_profesor  = intval($_POST['clave_profesor']);
$clave_materia   = trim($_POST['clave_materia']);
$id_grupo        = intval($_POST['id_grupo']);
$periodo         = trim($_POST['periodo']);
$horas_asignadas = floatval($_POST['horas_asignadas']);

// Validar que el profesor no tenga ya una asignación en ese mismo grupo
$check = $conn->prepare("SELECT id FROM Carga_Academica WHERE clave_profesor = ? AND id_grupo = ? AND periodo = ?");
$check->bind_param("iis", $clave_profesor, $id_grupo, $periodo);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    header("Location: generar_carga.php?error=El+profesor+ya+tiene+asignacion+en+este+grupo");
    exit();
}

// Verificar que el profesor no exceda su máximo de horas según su categoría
$prof = $conn->prepare("SELECT p.clave, c.horas_max, COALESCE(SUM(ca.horas_asignadas), 0) as horas_actuales FROM Profesores p LEFT JOIN Categoria c ON c.id = p.id_categoria LEFT JOIN Carga_Academica ca ON ca.clave_profesor = p.clave AND ca.periodo = ? WHERE p.clave = ? GROUP BY p.clave");
$prof->bind_param("si", $periodo, $clave_profesor);
$prof->execute();
$datos_prof = $prof->get_result()->fetch_assoc();

if (($datos_prof['horas_actuales'] + $horas_asignadas) > $datos_prof['horas_max']) {
    header("Location: generar_carga.php?error=El+profesor+excede+su+maximo+de+horas");
    exit();
}

// Si pasa las validaciones, guardar la asignación en la base de datos
$stmt = $conn->prepare("INSERT INTO Carga_Academica (clave_profesor, clave_materia, id_grupo, periodo, horas_asignadas, estatus) VALUES (?, ?, ?, ?, ?, 'activa')");
$stmt->bind_param("isiss", $clave_profesor, $clave_materia, $id_grupo, $periodo, $horas_asignadas);
$stmt->execute();

header("Location: generar_carga.php?ok=1");
exit();
?>