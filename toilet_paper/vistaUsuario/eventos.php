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

// Obtener eventos
$eventos = $pdo->query("SELECT * FROM eventos_diarios ORDER BY puntos DESC")->fetchAll();

// Comprobar cuáles ya hizo hoy
$hechos = [];
$stmt = $pdo->prepare("SELECT evento_id FROM eventos_completados WHERE usuario_id = ? AND fecha = ?");
$stmt->execute([$id, $hoy]);
while ($row = $stmt->fetchColumn()) {
    $hechos[$row] = true;
}

// Procesar evento completado
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

            $mensaje = "<div class='bg-gradient-to-r from-green-500 to-emerald-600 text-white p-8 rounded-3xl text-4xl font-black text-center shadow-2xl animate-pulse'>
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .evento:hover { transform: translateY(-15px) scale(1.05); }
        .check { animation: check 0.6s ease-out; }
        @keyframes check { 0% { transform: scale(0); } 100% { transform: scale(1); } }
    </style>
</head>
<body class="bg-gradient-to-br from-teal-50 via-cyan-50 to-green-50 min-h-screen pt-20 pb-32">

<div class="max-w-5xl mx-auto px-6 text-center">

    <a href="dashboard.php" class="inline-block mb-8 text-purple-700 font-bold text-xl hover:underline">← Volver al dashboard</a>

    <h1 class="text-7xl font-black bg-gradient-to-r from-green-600 to-teal-600 bg-clip-text text-transparent mb-6">
        Eventos del Día
    </h1>
    <p class="text-3xl text-gray-700 mb-12">¡Gana puntos fáciles todos los días!</p>

    <?= $mensaje ?>

    <div class="text-6xl font-black text-purple-600 mb-12">
        Puntos actuales: <?= $puntos ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

        <?php foreach ($eventos as $e): 
            $completado = isset($hechos[$e['id']]);
        ?>
        <div class="evento bg-white rounded-3xl shadow-2xl p-10 transition duration-500 <?= $completado ? 'opacity-70' : '' ?>">
            <div class="flex justify-between items-start mb-8">
                <h3 class="text-4xl font-black text-gray-800 text-left">
                    <?= htmlspecialchars($e['titulo']) ?>
                </h3>
                <div class="text-7xl font-black <?= $completado ? 'text-gray-400' : 'text-green-600' ?>">
                    +<?= $e['puntos'] ?> pts
                </div>
            </div>

            <p class="text-2xl text-gray-700 mb-10 text-left leading-relaxed">
                <?= nl2br(htmlspecialchars($e['descripcion'])) ?>
            </p>

            <?php if ($completado): ?>
                <div class="text-6xl text-center text-green-500 check">Check</div>
                <p class="text-2xl text-gray-600 mt-4">Completado hoy</p>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="evento_id" value="<?= $e['id'] ?>">
                    <button class="w-full bg-gradient-to-r from-green-500 to-teal-600 text-white py-6 rounded-2xl text-3xl font-black hover:scale-110 transition shadow-2xl">
                        ¡Lo hice! DAME PUNTOS
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    </div>

    <a href="tienda.php" class="inline-block mt-16 text-3xl text-purple-600 font-bold hover:underline">
        Ir a la tienda a gastar puntos
    </a>
</div>

<!-- Barra inferior -->
<div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-purple-800 via-pink-700 to-purple-900 text-white py-6 shadow-2xl">
    <div class="max-w-5xl mx-auto grid grid-cols-3 text-center">
        <div><p class="text-xl">Rollos</p><p class="text-6xl font-black">
            <?= $pdo->query("SELECT rollos_actuales FROM usuarios WHERE id=$id")->fetchColumn() ?>
        </p></div>
        <div><p class="text-xl">Días</p><p class="text-6xl font-black">
            <?= intval($pdo->query("SELECT rollos_actuales FROM usuarios WHERE id=$id")->fetchColumn() / 0.5) ?>
        </p></div>
        <div><p class="text-xl">Puntos</p><p class="text-6xl font-black"><?= $puntos ?></p></div>
    </div>
</div>

</body>
</html>