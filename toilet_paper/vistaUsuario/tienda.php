<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare("SELECT puntos, rollos_actuales FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comprar'])) {
    $paquete = (int)$_POST['paquete'];
    $costes = [4 => 30, 12 => 80, 24 => 140];

    if (!isset($costes[$paquete])) die('Error');

    $costo = $costes[$paquete];

    if ($u['puntos'] >= $costo) {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE usuarios SET puntos = puntos - ?, rollos_actuales = rollos_actuales + ? WHERE id = ?")
             ->execute([$costo, $paquete, $id]);
        $pdo->prepare("INSERT INTO transacciones (usuario_id, tipo, cantidad, puntos) VALUES (?, 'compra', ?, ?)")
             ->execute([$id, $paquete, -$costo]);
        $pdo->commit();

        $mensaje = "<div class='mensaje-compra-ok'>Compra exitosa +$paquete rollos</div>";
        $stmt->execute([$id]);
        $u = $stmt->fetch();
    } else {
        $mensaje = "<div class='mensaje-compra-error'>Puntos insuficientes</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda | Papel Manager</title>
    <link rel="stylesheet" href="vistaUsuario.css">
</head>
<body class="bg-tienda">

<div class="contenedor-tienda">

    <a href="dashboard.php" class="link-volver">← Volver</a>

    <h1 class="titulo-tienda">
        Tienda de Rollos
    </h1>

    <?= $mensaje ?>

    <div class="puntos-disponibles">
        Puntos disponibles: <?= $u['puntos'] ?>
    </div>

    <div class="grid-paquetes">

        <div class="paquete-card">
            <h3 class="paquete-titulo">Paquete Básico</h3>
            <div class="paquete-cantidad paquete-cantidad-azul">4</div>
            <p class="paquete-precio">30 puntos</p>
            <form method="POST">
                <input type="hidden" name="paquete" value="4">
                <button name="comprar" class="btn-paquete btn-paquete-azul">
                    Comprar
                </button>
            </form>
        </div>

        <div class="paquete-card">
            <h3 class="paquete-titulo paquete-titulo-morado">Paquete Popular</h3>
            <div class="paquete-cantidad paquete-cantidad-morado">12</div>
            <p class="paquete-precio">80 puntos</p>
            <span class="paquete-etiqueta-morada">−11% más barato</span>
            <form method="POST" class="form-paquete">
                <input type="hidden" name="paquete" value="12">
                <button name="comprar" class="btn-paquete btn-paquete-morado">
                    ¡Lo quiero!
                </button>
            </form>
        </div>

        <div class="paquete-card mega-card">
            <div class="mega-etiqueta">
                MÁS VENDIDO
            </div>
            <div class="mega-contenido">
                <h3 class="paquete-titulo-mega">MegaPack</h3>
                <div class="paquete-cantidad-mega">24</div>
                <p class="paquete-precio-mega">140 puntos</p>
                <span class="mega-mejor-precio">−27% Mejor precio</span>
                <form method="POST" class="form-paquete">
                    <input type="hidden" name="paquete" value="24">
                    <button name="comprar" class="btn-megapack">
                        ¡COMPRAR MEGAPACK!
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<div class="barra-inferior">
    <div class="barra-item">
        <p class="barra-label">Rollos</p>
        <p class="barra-valor"><?= $u['rollos_actuales'] ?></p>
    </div>
    <div class="barra-item">
        <p class="barra-label">Días</p>
        <p class="barra-valor"><?= intval($u['rollos_actuales']/0.5) ?></p>
    </div>
    <div class="barra-item">
        <p class="barra-label">Puntos</p>
        <p class="barra-valor"><?= $u['puntos'] ?></p>
    </div>
</div>

</body>
</html>
