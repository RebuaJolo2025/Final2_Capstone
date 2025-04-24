<?php
session_start();
include 'conn.php';

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];
$query = "SELECT * FROM cart WHERE email = '$email'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .cart-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .cart-item {
            display: flex;
            gap: 15px;
            align-items: center;
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            background: #fafafa;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .cart-item img {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            object-fit: cover;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .checkout-btn {
            display: block;
            width: 100%;
            background: #28a745;
            color: #fff;
            padding: 15px;
            font-size: 18px;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .checkout-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
    <form action="checkout.php" method="POST">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="cart-item">
                <input type="checkbox" name="selected_items[]" value="<?= $row['id'] ?>">
                <img src="<?= $row['image'] ?>" alt="<?= $row['product_name'] ?>">
                <div class="cart-item-details">
                    <h3><?= $row['product_name'] ?></h3>
                    <p>₱<?= number_format($row['product_price'], 2) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
        <button type="submit" class="checkout-btn">Proceed to Checkout</button>
    </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>

</body>
</html>
