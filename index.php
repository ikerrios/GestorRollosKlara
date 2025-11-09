<?php
session_start();

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if($metodo != 'POST') {
   
    if(isset($_SESSION['usuario'])) { 
        header('Location: gestor.php'); 
        exit;
    }
}

if($metodo === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = trim($_POST['contraseña'] ?? '');

    if($usuario === '' || $contraseña === '') { 
        $error = 'Asegurese de escribir su nombre y contraseña'; 
        ?> <script> alert("Nombre obligatorio") </script> <?php
        
    } else {
        $_SESSION['usuario'] = $usuario; 
        $_SESSION['contraseña'] = $contraseña;
        header('Location: gestor.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Rollos</title>
    <link rel="icon" type="image/png" href="includes/logo.png">
</head>
<body>
    <h2>Inicia Sesión</h2>

    <form method="post" action="">
        <label for="usuario">Usuario: </label>
        <input type="text" id="usuario" name="usuario"></br></br>

        <label for="contraseña">Contraseña: </label>
        <input type="password" id="contraseña" name="contraseña"></br></br>

        <button type="submit">Entrar</button>
    </form>

</body>
</html>