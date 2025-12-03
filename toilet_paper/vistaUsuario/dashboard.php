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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        .pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.8; } }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-pink-50 to-red-50 min-h-screen">

    <!-- Alerta roja -->
    <?php if ($alerta): ?>
    <div class="pulse fixed top-0 left-0 right-0 bg-gradient-to-r from-red-600 to-red-700 text-white text-center py-6 text-3xl font-black shadow-2xl z-50">
        ALERTA ROJA! Solo te quedan <?= $dias ?> días de papel
    </div>
    <?php endif; ?>

    <div class="container max-w-6xl mx-auto px-6 pt-24 pb-32">

        <div class="text-center mb-12">
            <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600">
                Hola, <?= htmlspecialchars($usuario['nombre']) ?>!
            </h1>
            <p class="text-2xl text-gray-700 mt-4">Control total de tu papel higiénico</p>
        </div>

        <!-- Cards principales con animación -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-16">
            <div class="bg-white rounded-3xl shadow-2xl p-10 text-center transform hover:scale-110 transition duration-500 float">
                <p class="text-5xl mb-4">Rollos</p>
                <p class="text-8xl font-black text-purple-600"><?= $usuario['rollos_actuales'] ?></p>
            </div>
            <div class="bg-white rounded-3xl shadow-2xl p-10 text-center transform hover:scale-110 transition duration-500 float" style="animation-delay: 0.2s;">
                <p class="text-5xl mb-4">Días restantes</p>
                <p class="text-8xl font-black <?= $dias <= 3 ? 'text-red-600' : 'text-green-600' ?>"><?= $dias ?></p>
            </div>
            <div class="bg-white rounded-3xl shadow-2xl p-10 text-center transform hover:scale-110 transition duration-500 float" style="animation-delay: float 6s ease-in-out infinite 0.4s;">
                <p class="text-5xl mb-4">Total usados</p>
                <p class="text-8xl font-black text-orange-500"><?= $usuario['rollos_total_usados'] ?></p>
                <p class="text-3xl text-green-600 mt-4">¡Buen trabajo!</p>
            </div>
        </div>

        <!-- Botones rápidos -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-16">
            <a href="tienda.php" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white text-center py-8 rounded-3xl text-2xl font-bold hover:scale-110 transition shadow-xl">
                Tienda
            </a>
            <a href="eventos.php" class="bg-gradient-to-r from-green-500 to-teal-600 text-white text-center py-8 rounded-3xl text-2xl font-bold hover:scale-110 transition shadow-xl">
                Eventos diarios
            </a>
            <a href="perfil.php" class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white text-center py-8 rounded-3xl text-2xl font-bold hover:scale-110 transition shadow-xl">
                Mi perfil
            </a>
            <?php if ($_SESSION['es_admin']): ?>
            <a href="../admin/panel.php" class="bg-gradient-to-r from-black to-gray-800 text-white text-center py-8 rounded-3xl text-2xl font-bold hover:scale-110 transition shadow-xl">
                Admin
            </a>
            <?php endif; ?>
        </div>

        <!-- Controles rápidos -->
        <div class="bg-white rounded-3xl shadow-2xl p-10 text-center">
            <h2 class="text-4xl font-bold mb-10">Gestión rápida</h2>
            <div class="flex flex-wrap justify-center gap-8">
                <form method="POST" action="usar_rollo.php" class="inline">
                    <button class="bg-gray-700 hover:bg-gray-800 text-white px-12 py-6 rounded-2xl text-3xl font-bold hover:scale-110 transition shadow-xl">
                        Usar 1 rollo
                    </button>
                </form>
                <a href="tienda.php" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-12 py-6 rounded-2xl text-3xl font-bold hover:scale-110 transition shadow-xl">
                    + Añadir rollos
                </a>
            </div>
        </div>
    </div>

    <!-- Barra fija inferior -->
    <div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-purple-800 via-pink-700 to-purple-900 text-white py-6 shadow-2xl">
        <div class="max-w-5xl mx-auto grid grid-cols-3 text-center">
            <div><p class="text-xl">Rollos</p><p class="text-6xl font-black"><?= $usuario['rollos_actuales'] ?></p></div>
            <div><p class="text-xl">Días</p><p class="text-6xl font-black <?= $dias <= 3 ? 'text-red-300' : '' ?>"><?= $dias ?></p></div>
            <div><p class="text-xl">Puntos</p><p class="text-6xl font-black"><?= $usuario['puntos'] ?></p></div>
        </div>
    </div>

</body>
</html>