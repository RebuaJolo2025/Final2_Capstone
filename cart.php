<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="style/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }

        .cart-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-header h1 {
            font-size: 2.5em;
            color: #2E7D32;
        }

        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px 0;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #fdfdfd;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .cart-item p {
            color: #555;
            font-size: 1.1em;
        }

        .cart-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }

        .empty-cart-message {
            text-align: center;
            font-size: 1.5em;
            color: #999;
            margin-top: 30px;
        }

        .totals {
            margin-top: 30px;
            text-align: center;
            font-size: 1.2em;
        }

        .checkout-btn {
            display: block;
            width: 200px;
            margin: 30px auto;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            font-size: 1.2em;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .checkout-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="cart-container">
    <div class="cart-header">
        <h1>Your Shopping Cart</h1>
    </div>

    <?php 
    session_start();
    include 'conn.php';

    if (!isset($_SESSION['email'])) {
        echo "<script>
                alert('You must be logged in to add items to the cart');
                window.location.href = 'login.php';
            </script>";
    }

    $email = $_SESSION['email'];

    $query = "SELECT * FROM cart WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<form action="checkout.php" method="POST">';
        echo '<div class="cart-items">';
        $totalPrice = 0;
        $totalQuantity = 0;

        while($row = mysqli_fetch_assoc($result)){
            echo '<div class="cart-item">';
            echo '<input type="checkbox" name="selected_items[]" value="' . $row["id"] . '" data-price="' . $row["product_price"] . '" class="item-checkbox">';
            echo '<img src="' . $row["image"] . '" alt="' . $row["product_name"] . '">';
            echo '<div class="cart-item-details">';
            echo '<h3>' . $row["product_name"] . '</h3>';
            echo '<p>₱' . $row["product_price"] . '</p>';
            echo '</div>';
            echo '</div>';

            // Calculate total price and quantity for selected items
            $totalPrice += $row["product_price"];
            $totalQuantity++;
        }
        echo '</div>';

        echo '<div class="totals">';
        echo 'Total Items: <span id="total-quantity">' . $totalQuantity . '</span><br>';
        echo 'Total Price: ₱<span id="total-price">' . number_format($totalPrice, 2) . '</span>';
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

    // Function to update totals dynamically
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

    // Event listener for when a checkbox is changed
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateTotals);
    });

    updateTotals(); // On page load
</script>

</body>
</html>
