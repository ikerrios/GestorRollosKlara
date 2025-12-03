<?php
session_start();
require_once '../config/database.php';

$mensaje = '';

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $accion = $_POST['accion'] ?? '';

    // ====== REGISTRO ======
    if ($accion == 'registro') {
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre) || empty($email) || empty($pass)) {
            $mensaje = "Todos los campos son obligatorios";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $mensaje = "Este email ya está registrado";
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO usuarios (nombre, email, password, puntos) VALUES (?, ?, ?, 150)")
                     ->execute([$nombre, $email, $hash]);
                $mensaje = "¡Cuenta creada! Ya puedes entrar";
            }
        }
    }

    // ====== LOGIN ======
    if ($accion == 'login') {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password'])) {
            // Guardamos sesión
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['es_admin']   = $user['es_admin'];

            
            if ($user['es_admin'] === 1) {
                header("Location: ../admin/panel.php");
                exit();
            } else {
                header("Location: ../vistaUsuario/dashboard.php");
                exit();
            }
        } else {
            $mensaje = "Email o contraseña incorrectos";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papel Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-600 via-pink-500 to-red-500 min-h-screen flex items-center justify-center">

<div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl p-10 w-full max-w-md">
    <div class="text-center mb-8">
        <div class="text-8xl float inline-block">Rollos</div>
        <h1 class="text-4xl font-bold text-purple-700 mt-4">Papel Manager</h1>
        <p class="text-gray-600">Control de rollos higiénicos</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-center">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- LOGIN -->
    <form method="POST" class="space-y-6">
        <input type="hidden" name="accion" value="login">
        <input type="email" name="email" placeholder="Email" required class="w-full px-6 py-4 rounded-xl border-2 border-purple-300 focus:border-purple-600 outline-none text-lg">
        <input type="password" name="password" placeholder="Contraseña" required class="w-full px-6 py-4 rounded-xl border-2 border-purple-300 focus:border-purple-600 outline-none text-lg">
        <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-xl text-xl hover:scale-105 transition">
            Entrar
        </button>
    </form>

    <div class="mt-8 text-center">
        <p class="text-gray-600">¿No tienes cuenta?</p>
        <button onclick="document.getElementById('registro').classList.toggle('hidden')" class="text-purple-600 font-bold">
            Regístrate (+150 puntos)
        </button>
    </div>

    <!-- REGISTRO -->
    <form method="POST" id="registro" class="hidden mt-8 space-y-6">
        <input type="hidden" name="accion" value="registro">
        <input type="text" name="nombre" placeholder="Nombre" required class="w-full px-6 py-4 rounded-xl border-2">
        <input type="email" name="email" placeholder="Email" required class="w-full px-6 py-4 rounded-xl border-2">
        <input type="password" name="password" placeholder="Contraseña" required class="w-full px-6 py-4 rounded-xl border-2">
        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-teal-600 text-white font-bold py-4 rounded-xl text-xl hover:scale-105 transition">
            Crear cuenta
        </button>
    </form>

    <p class="text-center text-xs text-gray-500 mt-8">
        Admin → admin@admin.com | 1234
    </p>
</div>

</body>
</html>