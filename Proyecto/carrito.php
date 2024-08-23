<?php
session_start();
include 'db.php';

// Inicializar el carrito si no está definido
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Manejar las acciones del carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_to_cart'])) {
        // Código para agregar al carrito (ya proporcionado anteriormente)
    } elseif (isset($_POST['checkout'])) {
        // Guardar método de pago en la sesión
        $_SESSION['payment_method'] = $_POST['payment_method'];

        // Registrar el pedido en la base de datos
        $total = 0;
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            $subtotal = $product['price'] * $quantity;
            $total += $subtotal;
        }

        // Insertar datos del pedido en la base de datos
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Ajustar según cómo manejes a los usuarios
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, payment_method) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $total, $_SESSION['payment_method']]);
        $order_id = $pdo->lastInsertId(); // Obtener el ID del nuevo pedido

        // Insertar detalles del pedido
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            $subtotal = $product['price'] * $quantity;

            $stmt = $pdo->prepare('INSERT INTO order_details (order_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)');
            $stmt->execute([$order_id, $product_id, $quantity, $subtotal]);
        }

        // Vaciar el carrito
        unset($_SESSION['cart']);

        // Redirigir a la página de recibo
        header('Location: recibo.php?order_id=' . $order_id);
        exit();
    }
}

// Eliminar un producto del carrito
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $product_id = $_GET['id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['message'] = "Producto eliminado del carrito.";
    }
    header('Location: carrito.php');
    exit();
}

// Calcular el total del carrito
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Carrito - Licorería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Carrito de Compras</h1>
        <?php if (isset($_SESSION['message'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php } ?>

        <?php if (empty($_SESSION['cart'])) { ?>
            <div class="alert alert-warning" role="alert">
                Tu carrito está vacío.
            </div>
            <a href="catalogo.php" class="btn btn-primary">Volver al Catálogo</a>
        <?php } else { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $product_id => $quantity) {
                        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
                        $stmt->execute([$product_id]);
                        $product = $stmt->fetch();
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($quantity); ?></td>
                        <td><?php echo htmlspecialchars($subtotal); ?> $</td>
                        <td>
                            <a href="carrito.php?action=remove&id=<?php echo urlencode($product_id); ?>" class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p class="h4">Total: <?php echo htmlspecialchars($total); ?> $</p>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">Proceder al Pago</button>
            <a href="catalogo.php" class="btn btn-secondary mt-3">Seguir Comprando</a>
        <?php } ?>
    </div>

    <!-- Modal de Método de Pago -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Método de Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Selecciona el método de pago</label>
                            <select id="payment_method" name="payment_method" class="form-select" required>
                                <option value="credit_card">Tarjeta de Crédito</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Transferencia Bancaria</option>
                            </select>
                        </div>
                        <button type="submit" name="checkout" class="btn btn-primary">Confirmar Pago</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
