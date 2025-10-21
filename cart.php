<?php
session_start();
include 'conn.php';

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    echo "<script>alert('Please log in first.'); window.location.href='login.php';</script>";
    exit;
}

$email = $_SESSION['email'];

/* -------------------- ADD TO CART -------------------- */

if (isset($_GET['add']) && isset($_GET['qty'])) {
    $product_id = intval($_GET['add']);
    $qty = intval($_GET['qty']);
    $email = $_SESSION['email']; 

    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'");
    $product = mysqli_fetch_assoc($product_query);

    if ($product) {
        $check = mysqli_query($conn, "SELECT * FROM cart WHERE product_id='$product_id' AND email='$email'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty WHERE product_id='$product_id' AND email='$email'");
        } else {
            $image = isset($product['images']) ? 'Admin/Product/' . json_decode($product['images'], true)[0]['url'] : '';
            mysqli_query($conn, "INSERT INTO cart (email, product_id, product_name, product_price, image, quantity)
                                 VALUES ('$email', '{$product['id']}', '{$product['name']}', '{$product['price']}', '$image', '$qty')");
        }
    }

    header("Location: cart.php");
    exit;
}


/* -------------------- DELETE SELECTED -------------------- */
if (isset($_POST['delete_selected']) && !empty($_POST['selected_items'])) {
    $selected = $_POST['selected_items'];
    $ids = implode(",", array_map('intval', $selected));
    mysqli_query($conn, "DELETE FROM cart WHERE id IN ($ids) AND email='$email'");
    echo "<script>alert('Selected items deleted successfully!'); window.location.href='cart.php';</script>";
    exit;
}

/* -------------------- DISPLAY CART -------------------- */
$query = "SELECT * FROM cart WHERE email='$email'";
$result = mysqli_query($conn, $query);
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        :root {
            --brand: #2E7D32;
            --brand-600: #1e6124;
            --brand-50: #e9f5eb;
            --danger: #E53935;
            --danger-600: #c62828;
            --ink: #1f2937;
            --muted: #6b7280;
            --ring: rgba(46, 125, 50, 0.35);
        }
        * { box-sizing: border-box; }
        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: radial-gradient(1200px 600px at 20% -10%, #f0fff4 0%, #ffffff 50%, #f6f7f9 100%);
            min-height: 100vh;
            padding: 32px 16px;
            color: var(--ink);
        }
        .cart-container {
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(16, 24, 40, 0.08), 0 2px 6px rgba(16, 24, 40, 0.06);
            border: 1px solid #eef2f7;
        }
        h1 {
            text-align: center;
            color: var(--brand);
            margin: 0 0 24px;
            font-size: 1.9rem;
            letter-spacing: 0.2px;
        }
        .back-profile {
            position: fixed;
            top: 18px;
            left: 18px;
            background-color: var(--brand);
            color: white;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.95rem;
            z-index: 1000;
            box-shadow: 0 6px 16px rgba(46,125,50,0.25);
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
        }
        .back-profile:hover { background: var(--brand-600); transform: translateY(-1px); }

        .cart-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #eef2f7;
            margin-bottom: 14px;
            background: #fff;
            transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
        }
        .cart-item:hover { box-shadow: 0 8px 18px rgba(16,24,40,0.06); border-color: #e5eaf1; transform: translateY(-1px); }
        .cart-item input[type="checkbox"] {
            width: 18px; height: 18px; margin-top: 6px; cursor: pointer;
            accent-color: var(--brand);
        }
        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #f0f3f7;
            background: #fafcff;
        }
        .cart-details { flex: 1; display: grid; gap: 6px; }
        .cart-details h3 {
            font-size: 1.1rem;
            margin: 0 0 2px;
            color: #0f172a;
        }
        .price { font-size: 1.05rem; color: #334155; font-weight: 600; }
        .quantity-control {
            margin-top: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f7faf9;
            padding: 6px;
            border-radius: 10px;
            border: 1px solid #e7efe9;
        }
        .quantity-control button {
            width: 32px;
            height: 32px;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
            background-color: #ecf6ef;
            color: var(--brand);
            border: 1px solid #d7eadb;
            border-radius: 8px;
            cursor: pointer;
            transition: background .12s ease, transform .08s ease;
        }
        .quantity-control button:hover { background: #e3f2e7; }
        .quantity-control button:active { transform: scale(0.98); }
        .quantity-control input {
            width: 54px;
            height: 32px;
            font-size: 1rem;
            text-align: center;
            border-radius: 8px;
            border: 1px solid #dfe6ee;
            background: #fff;
            outline: none;
            transition: box-shadow .12s ease, border-color .12s ease;
        }
        .quantity-control input:focus { border-color: var(--brand); box-shadow: 0 0 0 4px var(--ring); }

        .summary {
            position: sticky;
            bottom: 0;
            margin-top: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.85) 0%, #ffffff 60%);
            backdrop-filter: blur(6px);
            border-top: 1px solid #eef2f7;
            padding-top: 14px;
        }
        .total {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            font-size: 1.15rem;
            font-weight: 600;
            color: #0f172a;
            margin-top: 4px;
        }
        .btn-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 14px;
        }
        .checkout-btn, .delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 190px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 1rem;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background .12s ease, transform .08s ease, box-shadow .12s ease;
            box-shadow: 0 6px 16px rgba(16,24,40,0.06);
        }
        .checkout-btn { background: var(--brand); color: #fff; }
        .checkout-btn:hover { background: var(--brand-600); transform: translateY(-1px); }
        .delete-btn { background: var(--danger); color: #fff; }
        .delete-btn:hover { background: var(--danger-600); transform: translateY(-1px); }

        @media (max-width: 640px) {
            .cart-item { align-items: center; }
            .cart-item img { width: 96px; height: 96px; }
            .cart-container { padding: 18px; }
            .checkout-btn, .delete-btn { min-width: unset; width: 100%; }
            .back-profile { position: static; display: inline-block; margin-bottom: 10px; }
        }
    </style>
</head>
<body>

<a href="index.php" class="back-profile">← Back to Shop</a>

<div class="cart-container">
    <h1>Your Cart</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <form action="cart.php" method="POST" id="cart-form">
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $id = $row['id'];
                $name = $row['product_name'];
                $price = $row['product_price'];
                $image = $row['image'];
                $quantity = $row['quantity'];
            ?>
                <div class="cart-item" data-id="<?= $id ?>">
                    <input type="checkbox" name="selected_items[]" value="<?= $id ?>" class="item-checkbox" data-price="<?= $price ?>" data-qty="<?= $quantity ?>">
                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($name) ?>">
                    <div class="cart-details">
                        <h3><?= htmlspecialchars($name) ?></h3>
                        <p class="price">₱<?= number_format($price, 2) ?></p>
                        <div class="quantity-control">
                            <button type="button" class="decrease">−</button>
                            <input type="number" name="quantities[<?= $id ?>]" class="quantity" value="<?= $quantity ?>" min="1" data-price="<?= $price ?>">
                            <button type="button" class="increase">+</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <div class="total">Total: ₱<span id="total-price">0.00</span></div>

            <div class="btn-container">
                <button type="submit" name="delete_selected" class="delete-btn" onclick="return confirm('Delete selected items?')">🗑 Delete Selected</button>
                <button type="submit" formaction="checkout.php" class="checkout-btn">✅ Checkout</button>
            </div>
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