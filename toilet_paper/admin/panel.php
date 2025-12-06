<?php
session_start();
require_once '../config/database.php';

// Solo admin puede entrar
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header("Location: ../login/login.php");
    exit();
}

$mensaje = "";

// --- ACCIONES ADMIN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // 1) Vaciar todos los rollos actuales
    if ($accion === 'vaciar_rollos') {
        $pdo->exec("UPDATE usuarios SET rollos_actuales = 0");
        $mensaje = "Se han vaciado los rollos de todos los usuarios.";
    }

    // 2) Dar puntos extra a todos
    if ($accion === 'dar_puntos') {
        $cantidad = (int)($_POST['cantidad_puntos'] ?? 0);
        if ($cantidad !== 0) {
            $stmt = $pdo->prepare("UPDATE usuarios SET puntos = puntos + ?");
            $stmt->execute([$cantidad]);
            $mensaje = "Se han añadido {$cantidad} puntos a todos los usuarios.";
        } else {
            $mensaje = "Debes introducir una cantidad de puntos distinta de 0.";
        }
    }

    // 3) Reiniciar eventos diarios (marcar todos como no completados)
    if ($accion === 'reset_eventos') {
        $pdo->exec("TRUNCATE TABLE eventos_completados");
        $mensaje = "Se han reiniciado todos los eventos diarios (nadie los tiene completados).";
    }

    // 4) Actualizar puntos de eventos diarios
    if ($accion === 'actualizar_eventos') {
        if (!empty($_POST['evento_id']) && is_array($_POST['evento_id'])) {
            $ids = $_POST['evento_id'];
            $titulos = $_POST['titulo'] ?? [];
            $puntos  = $_POST['puntos'] ?? [];

            $stmt = $pdo->prepare("UPDATE eventos_diarios SET titulo = ?, puntos = ? WHERE id = ?");
            foreach ($ids as $idEvento) {
                $idEvento = (int)$idEvento;
                $titulo   = trim($titulos[$idEvento] ?? '');
                $pts      = (int)($puntos[$idEvento] ?? 0);

                if ($idEvento > 0 && $titulo !== '') {
                    $stmt->execute([$titulo, $pts, $idEvento]);
                }
            }
            $mensaje = "Eventos diarios actualizados correctamente.";
        }
    }
}

// --- DATOS PARA MOSTRAR EN EL PANEL ---

// Datos del admin logueado
$adminId   = $_SESSION['usuario_id'];
$adminName = $_SESSION['nombre'] ?? 'Admin';

// Stats generales
$totalUsuarios = (int)$pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$rollosTotales = (int)$pdo->query("SELECT COALESCE(SUM(rollos_actuales),0) FROM usuarios")->fetchColumn();
$puntosTotales = (int)$pdo->query("SELECT COALESCE(SUM(puntos),0) FROM usuarios")->fetchColumn();

// Eventos diarios
$stmtEventos = $pdo->query("SELECT id, titulo, puntos FROM eventos_diarios ORDER BY puntos DESC");
$eventos = $stmtEventos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | Papel Manager</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">

<div class="admin-container">
    <header class="admin-header">
        <div>
            <h1 class="admin-title">ADMIN</h1>
            <p class="admin-subtitle">ID: <?= $adminId ?> | <?= htmlspecialchars($adminName) ?></p>
        </div>
        <a href="../vistaUsuario/dashboard.php" class="admin-link">
            Ir al Dashboard
        </a>
    </header>

    <?php if ($mensaje): ?>
        <div class="admin-message">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- STATS -->
    <section class="admin-section">
        <h2 class="section-title">Resumen general</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <p class="stat-label">Usuarios</p>
                <p class="stat-value"><?= $totalUsuarios ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Rollos actuales (total)</p>
                <p class="stat-value"><?= $rollosTotales ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Puntos totales</p>
                <p class="stat-value"><?= $puntosTotales ?></p>
            </div>
        </div>
    </section>

    <!-- ACCIONES RÁPIDAS -->
    <section class="admin-section">
        <h2 class="section-title">Acciones rápidas</h2>
        <div class="actions-grid">

            <!-- Vaciar rollos -->
            <form method="POST" class="action-card">
                <h3 class="action-title">Vaciar rollos</h3>
                <p class="action-text">
                    Pone <strong>rollos_actuales = 0</strong> para todos los usuarios.
                </p>
                <input type="hidden" name="accion" value="vaciar_rollos">
                <button type="submit" class="btn btn-danger">
                    Vaciar todos los rollos
                </button>
            </form>

            <!-- Dar puntos -->
            <form method="POST" class="action-card">
                <h3 class="action-title">Dar puntos a todos</h3>
                <p class="action-text">
                    Suma la cantidad indicada de puntos a <strong>todos</strong> los usuarios.
                </p>
                <input type="hidden" name="accion" value="dar_puntos">
                <label class="action-label">
                    Cantidad de puntos:
                    <input type="number" name="cantidad_puntos" class="action-input" value="50">
                </label>
                <button type="submit" class="btn btn-primary">
                    Aplicar a todos
                </button>
            </form>

            <!-- Reset eventos -->
            <form method="POST" class="action-card">
                <h3 class="action-title">Reiniciar eventos diarios</h3>
                <p class="action-text">
                    Borra la tabla <strong>eventos_completados</strong>, como si nadie hubiera hecho los eventos hoy.
                </p>
                <input type="hidden" name="accion" value="reset_eventos">
                <button type="submit" class="btn btn-warning">
                    Reiniciar eventos
                </button>
            </form>

        </div>
    </section>

    <!-- EDITAR EVENTOS DIARIOS (OFERTAS / RECOMPENSAS) -->
    <section class="admin-section">
        <h2 class="section-title">Editar eventos diarios (puntos / “ofertas”)</h2>

        <form method="POST" class="eventos-form">
            <input type="hidden" name="accion" value="actualizar_eventos">

            <table class="eventos-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $ev): ?>
                        <tr>
                            <td><?= $ev['id'] ?></td>
                            <td>
                                <input
                                    type="text"
                                    name="titulo[<?= $ev['id'] ?>]"
                                    value="<?= htmlspecialchars($ev['titulo']) ?>"
                                    class="event-input-title"
                                >
                            </td>
                            <td>
                                <input
                                    type="number"
                                    name="puntos[<?= $ev['id'] ?>]"
                                    value="<?= (int)$ev['puntos'] ?>"
                                    class="event-input-points"
                                >
                                <input
                                    type="hidden"
                                    name="evento_id[]"
                                    value="<?= $ev['id'] ?>"
                                >
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-save">
                Guardar cambios de eventos
            </button>
        </form>
    </section>

</div>

</body>
</html>
