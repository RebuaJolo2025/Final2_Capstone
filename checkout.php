<?php
session_start();
include 'conn.php';

// Redirect if user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            color: #333;
        }

        .checkout-container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #2E7D32;
            margin-bottom: 25px;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin-bottom: 20px;
        }

        li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 1.1em;
        }

        p strong {
            font-size: 1.2em;
            display: block;
            margin-bottom: 25px;
        }

        form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }

        textarea, input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
        }

        button[type="submit"] {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            background-color: #4CAF50;
            color: white;
            font-size: 1.1em;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2E7D32;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="checkout-container">
<?php
if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
    $selected_items = $_POST['selected_items'];
    $email = $_SESSION['email'];

    echo "<h1>Checkout Summary</h1>";
    echo "<ul>";

    $total = 0;

    foreach ($selected_items as $id) {
        $id = intval($id); // sanitize
        $query = "SELECT * FROM cart WHERE id = $id AND email = '$email'";
        $result = mysqli_query($conn, $query);

        if ($row = mysqli_fetch_assoc($result)) {
            echo "<li>{$row['product_name']} - ₱{$row['product_price']}</li>";
            $total += $row['product_price'];
        }
    }

    echo "</ul>";
    echo "<p><strong>Total: ₱{$total}</strong></p>";

    // Delivery form
    echo '<form action="place_order.php" method="POST">';
    foreach ($selected_items as $id) {
        echo '<input type="hidden" name="selected_items[]" value="' . intval($id) . '">';
    }
    echo '<label>Delivery Address:</label>';
    echo '<textarea name="delivery_address" required></textarea>';

    echo '<label>Phone Number:</label>';
    echo '<input type="text" name="phone" required>';

    echo '<button type="submit">Place Order</button>';
    echo '</form>';

    echo '<a class="back-link" href="cart.php">← Back to Cart</a>';
} else {
    echo "<p>No items selected. Please go back to your <a class='back-link' href='cart.php'>cart</a>.</p>";
}
?>
</div>

</body>
</html>
