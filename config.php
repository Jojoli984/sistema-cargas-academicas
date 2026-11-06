<?php
$host     = "127.0.0.1";
$usuario  = "root";
$password = "";
$base     = "Universidad";

$conn = new mysqli($host, $usuario, $password, $base);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$mes  = intval(date('n'));
$anio = intval(date('Y'));

if ($mes >= 2 && $mes <= 7) {
    $nombre_periodo = "Febrero " . $anio . " - Julio " . $anio;
} elseif ($mes >= 9 || $mes == 1) {
    if ($mes >= 9) {
        $nombre_periodo = "Septiembre " . $anio . " - Enero " . ($anio + 1);
    } else {
        $nombre_periodo = "Septiembre " . ($anio - 1) . " - Enero " . $anio;
    }
} else {
    $nombre_periodo = null;
}

if ($nombre_periodo) {
    $check = $conn->prepare("SELECT id FROM Periodo WHERE nombre = ?");
    $check->bind_param("s", $nombre_periodo);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $conn->query("UPDATE Periodo SET activo = 0");
        $ins = $conn->prepare("INSERT INTO Periodo (nombre, activo) VALUES (?, 1)");
        $ins->bind_param("s", $nombre_periodo);
        $ins->execute();
    } else {
        $row = $result->fetch_assoc();
        $conn->query("UPDATE Periodo SET activo = 0");
        $act = $conn->prepare("UPDATE Periodo SET activo = 1 WHERE id = ?");
        $act->bind_param("i", $row['id']);
        $act->execute();
    }
}
?>