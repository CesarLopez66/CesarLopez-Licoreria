<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, $password, $role]);
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role = $_POST['role'];

        $stmt = $pdo->prepare('UPDATE users SET username = ?, role = ? WHERE id = ?');
        $stmt->execute([$username, $role, $id]);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];

        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}

$users = $pdo->query('SELECT * FROM users')->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administrar Usuarios - Licorería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Administrar Usuarios</h1>
        <form method="post" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="username" placeholder="Nombre de Usuario" required class="form-control">
                </div>
                <div class="col-md-4">
                    <input type="password" name="password" placeholder="Contraseña" required class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="role" required class="form-select">
                        <option value="admin">Administrador</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="create" class="btn btn-primary mt-3">Agregar Usuario</button>
        </form>

        <h2 class="text-center">Usuarios Existentes</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) { ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['username']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td>
                        <form method="post" class="d-inline-block">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <input type="text" name="username" value="<?php echo $user['username']; ?>" class="form-control d-inline-block w-auto">
                            <select name="role" class="form-select d-inline-block w-auto">
                                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="cliente" <?php echo ($user['role'] == 'cliente') ? 'selected' : ''; ?>>Cliente</option>
                            </select>
                            <button type="submit" name="update" class="btn btn-warning btn-sm">Actualizar</button>
                            <button type="submit" name="delete" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
