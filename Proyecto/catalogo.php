<?php
session_start();
include 'db.php';

// Mostrar mensaje si existe
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Manejar la acción de agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    $_SESSION['message'] = "Producto agregado al carrito.";
    header('Location: catalogo.php');
    exit();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtener los productos
$stmt = $pdo->query('SELECT * FROM products');
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Catálogo - Licorería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Catálogo de Productos</h1>
        <?php if (isset($message)) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>

        <div class="row">
            <?php foreach ($products as $product) { ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text">Precio: <?php echo htmlspecialchars($product['price']); ?> $</p>
                            <form method="post" action="catalogo.php">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <div class="mb-3">
                                    <label for="quantity_<?php echo htmlspecialchars($product['id']); ?>" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="quantity_<?php echo htmlspecialchars($product['id']); ?>" name="quantity" value="1" min="1" required>
                                </div>
                                <button type="submit" name="add_to_cart" class="btn btn-primary">Agregar al Carrito</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <a href="carrito.php" class="btn btn-secondary mt-3">Ver Carrito</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
