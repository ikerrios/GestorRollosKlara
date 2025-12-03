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

        $mensaje = "<div class='bg-green-500 text-white p-8 rounded-3xl text-3xl font-bold text-center mb-10 animate-bounce'>Compra exitosa +$paquete rollos</div>";
        // Actualizamos datos
        $stmt->execute([$id]);
        $u = $stmt->fetch();
    } else {
        $mensaje = "<div class='bg-red-500 text-white p-8 rounded-3xl text-3xl font-bold text-center mb-10'>Puntos insuficientes</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda | Papel Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .card:hover { transform: translateY(-20px) scale(1.05); }
        .mega { border: 8px solid #ffa500; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen pt-20 pb-32">

<div class="max-w-6xl mx-auto px-6 text-center">

    <a href="dashboard.php" class="inline-block mb-8 text-purple-700 font-bold text-xl hover:underline">← Volver</a>

    <h1 class="text-7xl font-black bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-6">
        Tienda de Rollos
    </h1>

    <?= $mensaje ?>

    <div class="text-6xl font-black text-purple-600 mb-12">
        Puntos disponibles: <?= $u['puntos'] ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

        <!-- Paquete 4 -->
        <div class="card bg-white rounded-3xl shadow-2xl p-10 transition duration-500">
            <h3 class="text-4xl font-bold mb-6">Paquete Básico</h3>
            <div class="text-9xl font-black text-blue-600 mb-6">4</div>
            <p class="text-4xl font-bold text-gray-700 mb-8">30 puntos</p>
            <form method="POST">
                <input type="hidden" name="paquete" value="4">
                <button name="comprar" class="w-full bg-gradient-to-r from-blue-500 to-blue-700 text-white py-6 rounded-2xl text-3xl font-bold hover:scale-110 transition shadow-xl">
                    Comprar
                </button>
            </form>
        </div>

        <!-- Paquete 12 -->
        <div class="card bg-white rounded-3xl shadow-2xl p-10 transition duration-500">
            <h3 class="text-4xl font-bold mb-6 text-purple-600">Paquete Popular</h3>
            <div class="text-9xl font-black text-purple-600 mb-6">12</div>
            <p class="text-4xl font-bold text-gray-700 mb-4">80 puntos</p>
            <span class="bg-purple-200 text-purple-800 px-6 py-2 rounded-full text-xl font-bold">−11% más barato</span>
            <form method="POST" class="mt-8">
                <input type="hidden" name="paquete" value="12">
                <button name="comprar" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-6 rounded-2xl text-3xl font-bold hover:scale-110 transition shadow-xl">
                    ¡Lo quiero!
                </button>
            </form>
        </div>

        <!-- MegaPack 24 -->
        <div class="card mega bg-gradient-to-br from-orange-400 to-red-600 text-white rounded-3xl shadow-2xl p-10 transition duration-500 relative overflow-hidden">
            <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-black px-10 py-3 rounded-full text-2xl font-black shadow-2xl">
                MÁS VENDIDO
            </div>
            <div class="pt-10">
                <h3 class="text-5xl font-black mb-6">MegaPack</h3>
                <div class="text-9xl font-black mb-6">24</div>
                <p class="text-5xl font-black mb-6">140 puntos</p>
                <span class="bg-yellow-400 text-black px-8 py-3 rounded-full text-2xl font-black">−27% Mejor precio</span>
                <form method="POST" class="mt-10">
                    <input type="hidden" name="paquete" value="24">
                    <button name="comprar" class="w-full bg-white text-orange-600 py-7 rounded-2xl text-4xl font-black hover:scale-110 transition shadow-2xl">
                        ¡COMPRAR MEGAPACK!
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- Barra inferior fija -->
<div class="fixed bottom-0 left-0 right-0 bg-gradient-to-r from-purple-800 via-pink-700 to-purple-900 text-white py-6 shadow-2xl">
    <div class="max-w-5xl mx-auto grid grid-cols-3 text-center">
        <div><p class="text-xl">Rollos</p><p class="text-6xl font-black"><?= $u['rollos_actuales'] ?></p></div>
        <div><p class="text-xl">Días</p><p class="text-6xl font-black"><?= intval($u['rollos_actuales']/0.5) ?></p></div>
        <div><p class="text-xl">Puntos</p><p class="text-6xl font-black"><?= $u['puntos'] ?></p></div>
    </div>
</div>

</body>
</html>