<?php
session_start();
include 'conn.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Get product ID and quantity from URL
if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo "Invalid product ID.";
    exit;
}

$productId = intval($_GET['product_id']);
$qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
if ($qty < 1) $qty = 1;

// Fetch product
$sql = "SELECT * FROM products WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "Product not found.";
    exit;
}

// Calculate total
$total = $product['price'] * $qty;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quick Checkout - <?= htmlspecialchars($product['name']) ?></title>
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; margin:0; padding:0; }
.container { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
ul { list-style:none; padding:0; }
ul li { padding: 10px 0; display:flex; justify-content:space-between; border-bottom:1px solid #eee; }
.total { font-weight:bold; text-align:right; margin-top:15px; font-size:18px; }
button { width: 100%; padding: 15px; background:#2e7d32; color:#fff; border:none; border-radius:6px; font-size:16px; cursor:pointer; margin-top:20px; }
button:hover { background:#256628; }
</style>
</head>
<body>
<div class="container">
<h2>Quick Checkout</h2>

<ul>
    <li>
        <span><?= htmlspecialchars($product['name']) ?> x <?= $qty ?></span>
        <span>₱<?= number_format($total, 2) ?></span>
    </li>
</ul>

<p class="total">Total: ₱<?= number_format($total, 2) ?></p>

<form method="POST" action="place_order.php">
    <input type="hidden" name="user_id" value="<?= $userId ?>">
    <input type="hidden" name="selected_items[]" value="<?= $product['id'] ?>">
    <input type="hidden" name="quantities[<?= $product['id'] ?>]" value="<?= $qty ?>">
    <input type="hidden" name="order_total" value="<?= $total ?>">
    <button type="submit">Place Order</button>
</form>

<a href="product-detail.php?product_id=<?= $product['id'] ?>" style="display:block; margin-top:15px; text-align:center; color:#2196F3; text-decoration:none;">← Back to Product</a>
</div>
</body>
</html>
