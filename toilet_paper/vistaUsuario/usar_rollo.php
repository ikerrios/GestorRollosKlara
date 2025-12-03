<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {
        // Restamos 1 rollo (solo si tiene que tener al menos 1)
        $stmt = $pdo->prepare("UPDATE usuarios SET 
            rollos_actuales = CASE WHEN rollos_actuales > 0 THEN rollos_actuales - 1 ELSE 0 END,
            rollos_total_usados = rollos_total_usados + 1
            WHERE id = ? AND rollos_actuales > 0");
        $stmt->execute([$id]);

        // Registramos la transacción
        $pdo->prepare("INSERT INTO transacciones (usuario_id, tipo, cantidad) VALUES (?, 'uso', -1)")
             ->execute([$id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
    }
}

// Redirigimos al dashboard para ver el cambio
header("Location: dashboard.php");
exit();