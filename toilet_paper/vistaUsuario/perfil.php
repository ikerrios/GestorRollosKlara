<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch();

$historial = $pdo->prepare("
    SELECT tipo, cantidad, puntos, fecha 
    FROM transacciones 
    WHERE usuario_id = ? 
    ORDER BY fecha DESC 
    LIMIT 20
");
$historial->execute([$id]);
$transacciones = $historial->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Papel Manager</title>
    <link rel="stylesheet" href="vistaUsuario.css">
</head>
<body class="bg-perfil">

<div class="contenedor-perfil">

    <div class="perfil-header">
        <a href="dashboard.php" class="link-volver">Dashboard</a>
        <a href="../logout.php" class="btn-logout">
            Cerrar sesión
        </a>
    </div>

    <h1 class="titulo-perfil">
        Mi Perfil
    </h1>

    <div class="card-perfil">
        <div class="icono-user">User</div>
        <h2 class="nombre-usuario">
            <?= htmlspecialchars($usuario['nombre']) ?>
        </h2>
        <p class="email-usuario"><?= htmlspecialchars($usuario['email']) ?></p>

        <div class="grid-estadisticas">
            <div class="estadistica estadistica-morada">
                <p class="estadistica-label">Rollos actuales</p>
                <p class="estadistica-valor"><?= $usuario['rollos_actuales'] ?></p>
            </div>
            <div class="estadistica estadistica-verde">
                <p class="estadistica-label">Puntos</p>
                <p class="estadistica-valor"><?= $usuario['puntos'] ?></p>
            </div>
            <div class="estadistica estadistica-naranja">
                <p class="estadistica-label">Total usados</p>
                <p class="estadistica-valor"><?= $usuario['rollos_total_usados'] ?></p>
            </div>
            <div class="estadistica estadistica-azul">
                <p class="estadistica-label">Días registrado</p>
                <p class="estadistica-valor">
                    <?= floor((time() - strtotime($usuario['fecha_registro'])) / 86400) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="card-historial">
        <h2 class="titulo-historial">Últimas acciones</h2>
        <div class="lista-historial">
            <?php foreach($transacciones as $t): ?>
            <div class="item-historial">
                <div>
                    <p class="historial-tipo">
                        <?php
                        switch($t['tipo']) {
                            case 'compra': echo "Compraste rollos"; break;
                            case 'uso': echo "Usaste 1 rollo"; break;
                            case 'evento': echo "Evento diario"; break;
                            case 'registro': echo "Registro"; break;
                            default: echo ucfirst($t['tipo']);
                        }
                        ?>
                    </p>
                    <?php if($t['cantidad'] != 0): ?>
                        <span class="historial-cantidad">
                            (<?= $t['cantidad'] > 0 ? '+' : '' ?><?= $t['cantidad'] ?> rollos)
                        </span>
                    <?php endif; ?>
                </div>
                <div class="historial-derecha">
                    <?php if($t['puntos'] != 0): ?>
                        <p class="historial-puntos <?= $t['puntos'] > 0 ? 'puntos-positivos' : 'puntos-negativos' ?>">
                            <?= $t['puntos'] > 0 ? '+' : '' ?><?= $t['puntos'] ?> pts
                        </p>
                    <?php endif; ?>
                    <p class="historial-fecha">
                        <?= date('d/m/Y H:i', strtotime($t['fecha'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div class="barra-inferior">
    <div class="barra-item">
        <p class="barra-label">Rollos</p>
        <p class="barra-valor"><?= $usuario['rollos_actuales'] ?></p>
    </div>
    <div class="barra-item">
        <p class="barra-label">Días</p>
        <p class="barra-valor"><?= intval($usuario['rollos_actuales']/0.5) ?></p>
    </div>
    <div class="barra-item">
        <p class="barra-label">Puntos</p>
        <p class="barra-valor"><?= $usuario['puntos'] ?></p>
    </div>
</div>

</body>
</html>
