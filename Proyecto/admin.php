<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];

        $stmt = $pdo->prepare('INSERT INTO products (name, price, stock, category_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $price, $stock, $category_id]);
    } elseif (isset($_POST['update'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $category_id = $_POST['category_id'];

        $stmt = $pdo->prepare('UPDATE products SET name = ?, price = ?, stock = ?, category_id = ? WHERE id = ?');
        $stmt->execute([$name, $price, $stock, $category_id, $id]);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];

        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
    }
}

$categories = $pdo->query('SELECT * FROM categories')->fetchAll();
$products = $pdo->query('SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id')->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administrar Productos - Licorería</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Administrar Productos</h1>
        <form method="post">
            <input type="text" name="name" placeholder="Nombre del producto" required>
            <input type="text" name="price" placeholder="Precio" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <select name="category_id" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categories as $category) { ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                <?php } ?>
            </select>
            <button type="submit" name="create">Agregar Producto</button>
        </form>

        <h2>Productos Existentes</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Categoría</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($products as $product) { ?>
            <tr>
                <td><?php echo $product['id']; ?></td>
                <td><?php echo $product['name']; ?></td>
                <td><?php echo $product['price']; ?></td>
                <td><?php echo $product['stock']; ?></td>
                <td><?php echo $product['category_name']; ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <input type="text" name="name" value="<?php echo $product['name']; ?>">
                        <input type="text" name="price" value="<?php echo $product['price']; ?>">
                        <input type="number" name="stock" value="<?php echo $product['stock']; ?>">
                        <select name="category_id">
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button type="submit" name="update">Actualizar</button>
                        <button type="submit" name="delete">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
