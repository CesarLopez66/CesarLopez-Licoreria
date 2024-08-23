<?php
session_start();
include 'db.php';

if (!isset($_POST['order_id'])) {
    header('Location: carrito.php');
    exit();
}

$order_id = $_POST['order_id'];

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

// Crear contenido del archivo
$filename = "recibo_$order_id.txt";
$content = "Recibo de Compra\n";
$content .= "Orden #: " . $order_id . "\n";
$content .= "Fecha de Compra: " . $current_date . "\n";
$content .= "Método de Pago: " . $order['payment_method'] . "\n\n";
$content .= "Productos:\n";

foreach ($order_details as $detail) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$detail['product_id']]);
    $product = $stmt->fetch();
    $content .= $product['name'] . " - Cantidad: " . $detail['quantity'] . " - Subtotal: " . $detail['subtotal'] . " $\n";
}

$content .= "\nTotal: " . $order['total'] . " $";

// Enviar el archivo TXT al navegador
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $content;
