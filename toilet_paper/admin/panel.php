<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_id'] != 4 && !$_SESSION['es_admin'])) {
    header("Location: ../login/login.php");
    exit();
}

echo '<h1 style="text-align:center; margin-top:100px; font-size:80px; color:#00ff00;">ADMIN FUNCIONANDO PERFECTO</h1>';
echo '<p style="text-align:center; font-size:30px;">ID: '.$_SESSION['usuario_id'].' | '.$_SESSION['nombre'].'</p>';
echo '<a href="../vistaUsuario/dashboard.php" style="display:block; text-align:center; margin-top:50px; font-size:25px; color:#a855f7;">Ir al Dashboard</a>';