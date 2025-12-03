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

// Historial de transacciones (últimas 20)
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .hover-lift:hover { transform: translateY(-10px); }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen pt-20 pb-32">

<div class="max-w-5xl mx-auto px-6">

    <!-- Header con botón cerrar sesión -->
    <div class="flex justify-between items-center mb-12">
        <a href="dashboard.php" class="text-purple-700 font-bold text-2xl hover:underline">Dashboard</a>
        <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white px-10 py-5 rounded-2xl text-2xl font-bold shadow-xl hover-lift transition">
            Cerrar sesión
        </a>
    </div>

    <h1 class="text-7xl font-black text-center bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-12">
        Mi Perfil
    </h1>

    <!-- Info del usuario -->
    <div class="bg-white rounded-3xl shadow-2xl p-12 mb-12 text-center">
        <div class="text-9xl mb-6">User</div>
        <h2 class="text-5xl font-black text-purple-700 mb-4">
            <?= htmlspecialchars($usuario['nombre']) ?>
        </h2>
        <p class="text-3xl text-gray-600 mb-8"><?= htmlspecialchars($usuario['email']) ?></p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-12">
            <div class="bg-purple-100 rounded-2xl p-6 hover-lift transition">
                <p class="text-2xl text-purple-600">Rollos actuales</p>
                <p class="text-6xl font-black text-purple-800"><?= $usuario['rollos_actuales'] ?></p>
            </div>
            <div class="bg-green-100 rounded-2xl p-6 hover-lift transition">
                <p class="text-2xl text-green-600">Puntos</p>
                <p class="text-6xl font-black text-green-800"><?= $usuario['puntos'] ?></p>
            </div>
            <div class="bg-orange-100 rounded-2xl p-6 hover-lift transition">
                <p class="text-2xl text-orange-600">Total usados</p>
                <p class="text-6xl font-black text-orange-800"><?= $usuario['rollos_total_usados'] ?></p>
            </div>
            <div class="bg-blue-100 rounded-2xl p-6 hover-lift transition">
                <p class="text-2xl text-blue-600">Días registrado</p>
                <p class="text-6xl font-black text-blue-800">
                    <?= floor((time() - strtotime($usuario['fecha_registro'])) / 86400) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Historial reciente -->
    <div class="bg-white rounded-3xl shadow-2xl p-10">
        <h2 class="text-5xl font-bold text-center mb-10 text-purple-700">Últimas acciones</h2>
        <div class="space-y-6 max-h-96 overflow-y-auto">
            <?php foreach($transacciones as $t): ?>
            <div class="bg-gray-50 rounded-2xl p-6 flex justify-between items-center hover-lift transition">
                <div>
                    <p class="text-2xl font-bold">
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
                        <span class="text-gray-600 text-xl">(<?= $t['cantidad'] > 0 ? '+' : '' ?><?= $t['cantidad'] ?> rollos)</span>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <?php if($t['puntos'] != 0): ?>
                        <p class="text-4xl font-black <?= $t['puntos'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $t['puntos'] > 0 ? '+' : '' ?><?= $t['puntos'] ?> pts
                        </p>
                    <?php endif; ?>
                    <p class="text-gray-500 text-lg">
                        <?= date('d/m/Y H:i', strtotime($t['fecha'])) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Barra inferior fija -->
<div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-purple-800 via-pink-700 to-purple-900 text-white py-6 shadow-2xl">
    <div class="max-w-5xl mx-auto grid grid-cols-3 text-center">
        <div><p class="text-xl">Rollos</p><p class="text-6xl font-black"><?= $usuario['rollos_actuales'] ?></p></div>
        <div><p class="text-xl">Días</p><p class="text-6xl font-black"><?= intval($usuario['rollos_actuales']/0.5) ?></p></div>
        <div><p class="text-xl">Puntos</p><p class="text-6xl font-black"><?= $usuario['puntos'] ?></p></div>
    </div>
</div>

</body>
</html>