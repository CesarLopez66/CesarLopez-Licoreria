// recibo.php
<?php
session_start();
include 'db.php';

if (!isset($_GET['order_id'])) {
    header('Location: carrito.php');
    exit();
}

$order_id = $_GET['order_id'];

// Obtener detalles del pedido
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: carrito.php');
    exit();
}

// Obtener detalles de los productos
$stmt = $pdo->prepare('SELECT * FROM order_details WHERE order_id = ?');
$stmt->execute([$order_id]);
$order_details = $stmt->fetchAll();

// Obtener la fecha actual
$current_date = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recibo - Licorería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Recibo de Compra</h1>
        <h4 class="text-center">Orden #: <?php echo htmlspecialchars($order['id']); ?></h4>
        <p class="text-center">Fecha de Compra: <?php echo htmlspecialchars($order['created_at']); ?></p>
        <p class="text-center">Fecha Actual: <?php echo htmlspecialchars($current_date); ?></p>
        <p class="text-center">Método de Pago: <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_details as $detail) {
                    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
                    $stmt->execute([$detail['product_id']]);
                    $product = $stmt->fetch();
                    $subtotal = $detail['subtotal'];
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($detail['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($subtotal); ?> $</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <p class="h4 text-center">Total: <?php echo htmlspecialchars($order['total']); ?> $</p>

        <form method="post" action="generar_recibo.php" class="text-center">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
            <button type="submit" class="btn btn-primary">Generar Recibo en TXT</button>
        </form>

        <div class="text-center mt-3">
            <a href="catalogo.php" class="btn btn-secondary">Volver al Catálogo</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
