<?php
session_start();

// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit();
}

require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario  = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    // Buscar usuario con contraseña encriptada en SHA2
    $stmt = $conn->prepare("SELECT id, nombre FROM Usuarios WHERE usuario = ? AND password = SHA2(?, 256)");
    $stmt->bind_param("ss", $usuario, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION["usuario"] = $row["id"];
        $_SESSION["nombre"]  = $row["nombre"];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="login-page">

<div class="card">
  <div class="logo-area">
    <div class="logo-sigla">ISOF</div>
    <div class="logo-sub">Sistema de Cargas Académicas</div>
    <div class="divider"></div>
  </div>

  <p class="titulo">Ingresa tus credenciales para continuar</p>

  <!-- Mostrar error si las credenciales son incorrectas -->
  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="InicioSesion.php">
    <div class="campo">
      <label for="usuario">Usuario</label>
      <input type="text" id="usuario" name="usuario" placeholder="Número de usuario" required autofocus>
    </div>
    <div class="campo">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn-login">Iniciar sesión</button>
  </form>

  <div class="footer-card">
    Universidad Autónoma de Occidente · UAdeO
  </div>
</div>

</body>
</html>