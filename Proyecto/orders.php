<?php
session_start();
include 'db.php';

if ($_SESSION['role'] !== 'cliente') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'];
    $tax = 0.15;  // Impuesto fijo de 15%
    $total = 0;

    foreach ($_POST['products'] as $productId => $quantity) {
        if ($quantity > 0) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            $total += $product['price'] * $quantity;
        }
    }

    $total_with_tax = $total + ($total * $tax);

    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, payment_method, tax) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $total_with_tax, $payment_method, $total * $tax]);

    $orderId = $pdo->lastInsertId();

    foreach ($_POST['products'] as $productId => $quantity) {
        if ($quantity > 0) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            $subtotal = $product['price'] * $quantity;

            $stmt = $pdo->prepare('INSERT INTO order_details (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)');
            $stmt->execute([$orderId, $productId, $quantity, $subtotal]);
        }
    }

    header('Location: receipt.php?order_id=' . $orderId);
    exit();
}

$categories = $pdo->query('SELECT * FROM categories')->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Hacer Pedido - Licorería</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Hacer Pedido</h1>
        <form method="post">
            <?php foreach ($categories as $category) { ?>
                <h2><?php echo $category['name']; ?></h2>
                <?php
                $stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = ?');
                $stmt->execute([$category['id']]);
                $products = $stmt->fetchAll();
                ?>
                <?php foreach ($products as $product) { ?>
                    <div>
                        <label><?php echo $product['name']; ?> (<?php echo $product['price']; ?> $)</label>
                        <input type="number" name="products[<?php echo $product['id']; ?>]" min="0" placeholder="Cantidad">
                    </div>
                <?php } ?>
            <?php } ?>
            <label>Método de Pago:</label>
            <select name="payment_method" required>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
            </select>
            <button type="submit">Realizar Pedido</button>
        </form>
    </div>
</body>
</html>
