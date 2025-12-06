<?php
session_start();
require_once '../config/database.php';

$mensaje = '';

if ($_POST) {
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';
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

    if ($user) {
        $esAdmin = (int)$user['es_admin'] === 1;

        // Para usuarios normales: SOLO password_verify
        // Para admin: acepta tanto hash como texto plano (1234 en la BD)
        if ($esAdmin) {
            $passwordOk =
                password_verify($pass, $user['password'])   // si está hasheada
                || $pass === $user['password'];             // si está en texto plano
        } else {
            $passwordOk = password_verify($pass, $user['password']);
        }

        if ($passwordOk) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['es_admin']   = (int)$user['es_admin'];

            if ($esAdmin) {
                header("Location: ../admin/panel.php");
                exit();
            }

            header("Location: ../vistaUsuario/dashboard.php");
            exit();
        }
    }

    // Si ha llegado hasta aquí, algo ha fallado
    $mensaje = "Email o contraseña incorrectos";
}

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papel Manager</title>
    <link rel="stylesheet" href="login.css">
</head>
<body class="body-login">

<div class="login-card">
    <div class="login-header">
        <div class="logo-rollos float-animation">Rollos</div>
        <h1 class="app-title">Papel Manager</h1>
        <p class="subtitle">Control de rollos higiénicos</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="error-message">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- LOGIN -->
    <form method="POST" class="login-form">
        <input type="hidden" name="accion" value="login">

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
            class="input-field"
        >

        <input
            type="password"
            name="password"
            placeholder="Contraseña"
            required
            class="input-field"
        >

        <button type="submit" class="btn btn-primary">
            Entrar
        </button>
    </form>

    <div class="register-toggle">
        <p class="no-account-text">¿No tienes cuenta?</p>
        <button
            type="button"
            onclick="document.getElementById('registro').classList.toggle('hidden')"
            class="btn-link"
        >
            Regístrate (+150 puntos)
        </button>
    </div>

    <!-- REGISTRO -->
    <form method="POST" id="registro" class="register-form hidden">
        <input type="hidden" name="accion" value="registro">

        <input
            type="text"
            name="nombre"
            placeholder="Nombre"
            required
            class="input-field"
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
            class="input-field"
        >

        <input
            type="password"
            name="password"
            placeholder="Contraseña"
            required
            class="input-field"
        >

        <button type="submit" class="btn btn-secondary">
            Crear cuenta
        </button>
    </form>

    <p class="admin-info">
        Admin → admin@admin.com | 1234
    </p>
</div>

</body>
</html>
