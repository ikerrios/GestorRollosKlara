<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_id'] != 4 && !$_SESSION['es_admin'])) {
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <h1 class="admin-title">ADMIN</h1>

    <p class="admin-info">
        ID: <?= $_SESSION['usuario_id'] ?> | <?= htmlspecialchars($_SESSION['nombre']) ?>
    </p>

    <a href="../vistaUsuario/dashboard.php" class="admin-link">
        Ir al Dashboard
    </a>

</body>
</html>
