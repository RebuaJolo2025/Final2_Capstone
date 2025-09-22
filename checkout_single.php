<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Ensure buy_now session exists
if (!isset($_SESSION['buy_now'])) {
    echo "<p>No product selected for Buy Now. <a href='index.php'>Back to products</a></p>";
    exit;
}

// Get Buy Now item
$item = $_SESSION['buy_now'];
$total = $item['price'] * $item['quantity'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buy Now Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0px 5px 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
        .item { background: #f9f9f9; padding: 12px; border-radius: 6px; display: flex; justify-content: space-between; margin-bottom: 15px; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 10px; }
        button { background: #2e7d32; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-top: 15px; }
        button:hover { background: #256428; }
        .back-link { display: block; margin-top: 15px; text-align: center; color: #2196F3; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Buy Now - Checkout</h2>
    
    <div class="item">
        <span><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
        <span>₱<?= number_format($total, 2) ?></span>
    </div>
    
    <p class="total">Total: ₱<?= number_format($total, 2) ?></p>

    <form method="POST" action="place_order.php">
        <!-- Send as if it were a cart of one -->
        <input type="hidden" name="selected_items[]" value="<?= $item['id'] ?>">
        <input type="hidden" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>">
        <button type="submit">Place Order</button>
    </form>

    <a href="product-detail.php?product_id=<?= $item['id'] ?>" class="back-link">← Back to Product</a>
</div>
</body>
</html>
