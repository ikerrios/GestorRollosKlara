<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_SESSION['usuario_id'];
$hoy = date('Y-m-d');

$stmt = $pdo->prepare("SELECT puntos FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$puntos = $stmt->fetchColumn();

$eventos = $pdo->query("SELECT * FROM eventos_diarios ORDER BY puntos DESC")->fetchAll();

$hechos = [];
$stmt = $pdo->prepare("SELECT evento_id FROM eventos_completados WHERE usuario_id = ? AND fecha = ?");
$stmt->execute([$id, $hoy]);
while ($row = $stmt->fetchColumn()) {
    $hechos[$row] = true;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evento_id'])) {
    $evento_id = (int)$_POST['evento_id'];

    if (isset($hechos[$evento_id])) {
        $mensaje = "Ya completaste este evento hoy";
    } else {
        $ev = $pdo->prepare("SELECT titulo, puntos FROM eventos_diarios WHERE id = ?");
        $ev->execute([$evento_id]);
        $e = $ev->fetch();

        if ($e) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE usuarios SET puntos = puntos + ? WHERE id = ?")
                 ->execute([$e['puntos'], $id]);
            $pdo->prepare("INSERT INTO eventos_completados (usuario_id, evento_id, fecha) VALUES (?, ?, ?)")
                 ->execute([$id, $evento_id, $hoy]);
            $pdo->prepare("INSERT INTO transacciones (usuario_id, tipo, puntos) VALUES (?, 'evento', ?)")
                 ->execute([$id, $e['puntos']]);
            $pdo->commit();

            $mensaje = "<div class='mensaje-evento-ok'>
                          ¡+{$e['puntos']} puntos por “{$e['titulo']}”!
                        </div>";
            $puntos += $e['puntos'];
            $hechos[$evento_id] = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos Diarios | Papel Manager</title>
    <link rel="stylesheet" href="vistaUsuario.css">
</head>
<body class="bg-eventos">

<div class="contenedor-eventos">

    <a href="dashboard.php" class="link-volver">← Volver al dashboard</a>

    <h1 class="titulo-eventos">
        Eventos del Día
    </h1>
    <p class="subtitulo-eventos">¡Gana puntos fáciles todos los días!</p>

    <?php if ($mensaje): ?>
        <?= $mensaje ?>
    <?php endif; ?>

    <div class="puntos-actuales">
        Puntos actuales: <?= $puntos ?>
    </div>

    <div class="grid-eventos">
        <?php foreach ($eventos as $e): 
            $completado = isset($hechos[$e['id']]);
        ?>
        <div class="card-evento <?= $completado ? 'evento-completado' : '' ?>">
            <div class="cabecera-evento">
                <h3 class="titulo-card-evento">
                    <?= htmlspecialchars($e['titulo']) ?>
                </h3>
                <div class="puntos-card-evento <?= $completado ? 'puntos-gris' : 'puntos-verde' ?>">
                    +<?= $e['puntos'] ?> pts
                </div>
            </div>

            <p class="descripcion-evento">
                <?= nl2br(htmlspecialchars($e['descripcion'])) ?>
            </p>

            <?php if ($completado): ?>
                <div class="evento-check">Check</div>
                <p class="evento-completado-texto">Completado hoy</p>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="evento_id" value="<?= $e['id'] ?>">
                    <button class="btn-evento-completar">
                        ¡Lo hice! DAME PUNTOS
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <a href="tienda.php" class="link-tienda">
        Ir a la tienda a gastar puntos
    </a>
</div>

<div class="barra-inferior">
    <div class="barra-item">
        <p class="barra-label">Rollos</p>
        <p class="barra-valor">
            <?= $pdo->query("SELECT rollos_actuales FROM usuarios WHERE id=$id")->fetchColumn() ?>
        </p>
    </div>
    <div class="barra-item">
        <p class="barra-label">Días</p>
        <p class="barra-valor">
            <?= intval($pdo->query("SELECT rollos_actuales FROM usuarios WHERE id=$id")->fetchColumn() / 0.5) ?>
        </p>
    </div>
    <div class="barra-item">
        <p class="barra-label">Puntos</p>
        <p class="barra-valor"><?= $puntos ?></p>
    </div>
</div>

</body>
</html>
