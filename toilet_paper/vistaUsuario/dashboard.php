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

$dias = $usuario['rollos_actuales'] > 0 ? intval($usuario['rollos_actuales'] / 0.5) : 0;
$alerta = $dias <= 3;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Papel Manager</title>
    <link rel="stylesheet" href="vistaUsuario.css">
</head>
<body class="bg-dashboard">

    <?php if ($alerta): ?>
    <div class="alerta-roja pulse">
        ALERTA ROJA! Solo te quedan <?= $dias ?> días de papel
    </div>
    <?php endif; ?>

    <div class="contenedor-principal">

        <div class="cabecera-dashboard">
            <h1 class="titulo-dashboard">
                Hola, <?= htmlspecialchars($usuario['nombre']) ?>!
            </h1>
            <p class="subtitulo-dashboard">Control total de tu papel higiénico</p>
        </div>

        <div class="grid-cards">
            <div class="card-dashboard float">
                <p class="card-titulo">Rollos</p>
                <p class="card-numero card-numero-morado"><?= $usuario['rollos_actuales'] ?></p>
            </div>
            <div class="card-dashboard float float-delay-1">
                <p class="card-titulo">Días restantes</p>
                <p class="card-numero <?= $dias <= 3 ? 'card-numero-rojo' : 'card-numero-verde' ?>"><?= $dias ?></p>
            </div>
            <div class="card-dashboard float float-delay-2">
                <p class="card-titulo">Total usados</p>
                <p class="card-numero card-numero-naranja"><?= $usuario['rollos_total_usados'] ?></p>
                <p class="card-mensaje-verde">¡Buen trabajo!</p>
            </div>
        </div>

        <div class="grid-botones-rapidos">
            <a href="tienda.php" class="btn-rapido btn-morado-rosa">Tienda</a>
            <a href="eventos.php" class="btn-rapido btn-verde-teal">Eventos diarios</a>
            <a href="perfil.php" class="btn-rapido btn-azul-indigo">Mi perfil</a>
            <?php if ($_SESSION['es_admin']): ?>
            <a href="../admin/panel.php" class="btn-rapido btn-negro-gris">Admin</a>
            <?php endif; ?>
        </div>

        <div class="card-gestion">
            <h2 class="titulo-gestion">Gestión rápida</h2>
            <div class="botones-gestion">
                <form method="POST" action="usar_rollo.php">
                    <button class="btn-gestion btn-oscuro">
                        Usar 1 rollo
                    </button>
                </form>
                <a href="tienda.php" class="btn-gestion btn-morado-rosa">
                    + Añadir rollos
                </a>
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
            <p class="barra-valor <?= $dias <= 3 ? 'barra-valor-rojo' : '' ?>"><?= $dias ?></p>
        </div>
        <div class="barra-item">
            <p class="barra-label">Puntos</p>
            <p class="barra-valor"><?= $usuario['puntos'] ?></p>
        </div>
    </div>

</body>
</html>
