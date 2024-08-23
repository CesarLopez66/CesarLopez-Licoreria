<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
    $stmt->execute([$username, $password, 'cliente']);

    header('Location: login.php');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro - Licorería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body class="login-background">
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card p-4" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Registro</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Registrarse</button>
            </form>
            <p class="text-center mt-3"><a href="login.php">Ya tienes una cuenta? Inicia sesión</a></p>
        </div>
    </div>
</body>
</html>
