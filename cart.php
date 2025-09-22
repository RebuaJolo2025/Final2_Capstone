<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    echo "<script>alert('Login first'); window.location.href='login.php';</script>";
    exit;
}

$email = $_SESSION['email'];
$query = "SELECT * FROM cart WHERE email='$email'";
$result = mysqli_query($conn, $query);
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .cart-container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }
        h1 {
            text-align: center;
            color: #2E7D32;
            margin-bottom: 20px;
        }
        .back-profile {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #2E7D32;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 1em;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .cart-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px 10px;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            background: #fff;
        }
        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }
        .cart-details {
            flex: 1;
        }
        .cart-details h3 {
            font-size: 1.3em;
            margin-bottom: 5px;
        }
        .price {
            font-size: 1.2em;
            color: #333;
        }
        .quantity-control {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .quantity-control button {
            width: 32px;
            height: 32px;
            font-size: 20px;
            font-weight: bold;
            background-color: #eee;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .quantity-control input {
            width: 45px;
            height: 32px;
            font-size: 1.1em;
            text-align: center;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .total {
            text-align: right;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .checkout-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            background: #4CAF50;
            color: white;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-size: 1.1em;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- ✅ Updated button text and link -->
<a href="index.php" class="back-profile">← Back to Shop</a>

<div class="cart-container">
    <h1>Your Cart</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <form action="checkout.php" method="POST" id="cart-form">
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $id = $row['id'];
                $name = $row['product_name'];
                $price = $row['product_price'];
                $image = $row['image'];
                $total += $price;
            ?>
                <div class="cart-item" data-id="<?= $id ?>">
                    <input type="checkbox" name="selected_items[]" value="<?= $id ?>" class="item-checkbox" data-price="<?= $price ?>">
                    <img src="<?= $image ?>" alt="<?= $name ?>">
                    <div class="cart-details">
                        <h3><?= $name ?></h3>
                        <p class="price">₱<?= number_format($price, 2) ?></p>
                        <div class="quantity-control">
                            <button type="button" class="decrease">−</button>
                            <input type="number" name="quantities[<?= $id ?>]" class="quantity" value="1" min="1" data-price="<?= $price ?>">
                            <button type="button" class="increase">+</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="total">Total: ₱<span id="total-price"><?= number_format($total, 2) ?></span></div>
            <button type="submit" class="checkout-btn">Checkout</button>
        </form>
    <?php else: ?>
        <p>No items in your cart.</p>
    <?php endif; ?>
</div>

<script>
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const checkbox = item.querySelector('.item-checkbox');
            const quantityInput = item.querySelector('.quantity');
            const price = parseFloat(quantityInput.dataset.price);
            const quantity = parseInt(quantityInput.value);

            if (checkbox.checked) {
                total += price * quantity;
            }
        });
        document.getElementById('total-price').textContent = total.toFixed(2);
    }

    document.querySelectorAll('.increase').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            input.value = parseInt(input.value) + 1;
            updateTotal();
        });
    });

    document.querySelectorAll('.decrease').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.nextElementSibling;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateTotal();
            }
        });
    });

    document.querySelectorAll('.quantity, .item-checkbox').forEach(input => {
        input.addEventListener('input', updateTotal);
        input.addEventListener('change', updateTotal);
    });

    updateTotal();
</script>

</body>
</html>
