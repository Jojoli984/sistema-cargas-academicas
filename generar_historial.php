<?php
session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once "config.php";
require_once "fpdf/fpdf.php";

// Obtener la clave del maestro desde la URL
$clave = intval($_GET['clave']);

// Buscar el nombre del maestro en la base de datos
$stmt = $conn->prepare("SELECT nombre FROM Profesores WHERE clave = ?");
$stmt->bind_param("i", $clave);
$stmt->execute();
$result = $stmt->get_result();
$maestro = $result->fetch_assoc();

if (!$maestro) {
    die("Maestro no encontrado.");
}

// Obtener el historial de materias del maestro ordenado por año
$stmt2 = $conn->prepare("
    SELECT h.periodo, m.nombre AS materia, h.clave_materia
    FROM Historial_Profesor h
    JOIN Materia m ON m.clave = h.clave_materia
    WHERE h.clave_profesor = ?
    ORDER BY SUBSTRING(h.periodo, -4) ASC, h.periodo ASC, m.nombre ASC
");
$stmt2->bind_param("i", $clave);
$stmt2->execute();
$historial = $stmt2->get_result();

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();

// Logo de la universidad
$pdf->Image('img/logo_uadeo.png', 10, 10, 35);

// Encabezado con nombre de la universidad
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(123, 21, 53);
$pdf->SetXY(50, 14);
$pdf->Cell(0, 8, 'Universidad Autonoma de Occidente', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetX(50);
$pdf->Cell(0, 6, 'Ingenieria en Software - ISOF', 0, 1);

// Línea separadora en color vino
$pdf->SetDrawColor(123, 21, 53);
$pdf->SetLineWidth(0.5);
$pdf->Line(10, 48, 200, 48);

// Título y nombre del docente
$pdf->SetY(54);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell(0, 8, 'Historial Academico del Docente', 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 7, 'Nombre: ' . $maestro['nombre'], 0, 1);

$pdf->Ln(4);

// Encabezado de la tabla en color vino
$pdf->SetFillColor(123, 21, 53);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(50, 8, 'Periodo', 1, 0, 'C', true);
$pdf->Cell(90, 8, 'Materia', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Clave', 1, 1, 'C', true);

// Filas del historial con color alternado
$pdf->SetFont('Arial', '', 9);
$fill = false;
while ($row = $historial->fetch_assoc()) {
    $pdf->SetTextColor(40, 40, 40);
    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 240 : 255, $fill ? 242 : 255);
    $pdf->Cell(50, 7, $row['periodo'], 1, 0, 'L', $fill);
    $pdf->Cell(90, 7, utf8_decode($row['materia']), 1, 0, 'L', $fill);
    $pdf->Cell(50, 7, $row['clave_materia'], 1, 1, 'L', $fill);
    $fill = !$fill;
}

// Pie de página
$pdf->SetY(-20);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(150, 150, 150);
$pdf->Cell(0, 10, 'Generado por el Sistema de Gestion de Cargas Academicas - ISOF', 0, 0, 'C');

// Descargar el PDF con el nombre del maestro
$nombre_archivo = 'Historial_' . str_replace(' ', '_', $maestro['nombre']) . '.pdf';
$pdf->Output('D', $nombre_archivo);
?>