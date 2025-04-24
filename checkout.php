<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    echo "<script>
            alert('You must be logged in to view the cart.');
            window.location.href = 'login.php';
          </script>";
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
    <title>Your Cart</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            color: #333;
        }
        .cart-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2E7D32;
            margin-bottom: 30px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-item-details {
            flex-grow: 1;
        }
        .cart-item h3 {
            margin: 0;
            font-size: 1.2em;
        }
        .cart-item p {
            margin: 5px 0 0;
            color: #555;
        }
        .totals {
            text-align: center;
            margin-top: 20px;
            font-size: 1.2em;
        }
        .checkout-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin-top: 30px;
            background-color: #4CAF50;
            color: white;
            font-size: 1.1em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .checkout-btn:hover {
            background-color: #45a049;
        }
        .empty-cart-message {
            text-align: center;
            font-size: 1.3em;
            color: #999;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>

    <?php
    if (mysqli_num_rows($result) > 0) {
        echo '<form action="checkout.php" method="POST">';
        while($row = mysqli_fetch_assoc($result)) {
            echo '<div class="cart-item">';
            echo '<input type="checkbox" name="selected_items[]" value="' . $row["id"] . '" class="item-checkbox" data-price="' . $row["product_price"] . '">';
            echo '<img src="' . $row["image"] . '" alt="' . $row["product_name"] . '">';
            echo '<div class="cart-item-details">';
            echo '<h3>' . $row["product_name"] . '</h3>';
            echo '<p>₱' . number_format($row["product_price"], 2) . '</p>';
            echo '</div>';
            echo '</div>';
        }

        echo '<div class="totals">';
        echo 'Total Items Selected: <span id="total-quantity">0</span><br>';
        echo 'Total Price: ₱<span id="total-price">0.00</span>';
        echo '</div>';

        echo '<button type="submit" class="checkout-btn">Proceed to Checkout</button>';
        echo '</form>';
    } else {
        echo "<p class='empty-cart-message'>Your cart is empty.</p>";
    }
    ?>
</div>

<script>
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const totalQuantity = document.getElementById('total-quantity');
    const totalPrice = document.getElementById('total-price');

    function updateTotals() {
        let quantity = 0;
        let total = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                quantity++;
                total += parseFloat(cb.dataset.price);
            }
        });

        totalQuantity.textContent = quantity;
        totalPrice.textContent = total.toFixed(2);
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateTotals);
    });

    updateTotals(); // On page load
</script>

</body>
</html>
