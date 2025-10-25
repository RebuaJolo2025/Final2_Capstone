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

    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'");
    $product = mysqli_fetch_assoc($product_query);

    if ($product) {
        $images = json_decode($product['images'], true);
        $mainImage = 'img/icon.png'; // fallback image

        if (is_array($images) && count($images) > 0) {
            if (is_array($images[0]) && isset($images[0]['url'])) {
                $mainImage = 'Admin/Product/' . $images[0]['url'];
            } elseif (is_string($images[0])) {
                $mainImage = 'Admin/Product/' . $images[0];
            }
        }

        $check = mysqli_query($conn, "SELECT * FROM cart WHERE product_id='$product_id' AND email='$email'");
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity + $qty WHERE product_id='$product_id' AND email='$email'");
        } else {
            mysqli_query($conn, "INSERT INTO cart (email, product_id, product_name, product_price, image, quantity)
                                 VALUES ('$email', '{$product['id']}', '{$product['name']}', '{$product['price']}', '$mainImage', '$qty')");
        }
    }

    header("Location: cart.php");
    exit;
}

/* -------------------- DELETE SELECTED ITEMS -------------------- */
if (isset($_POST['delete_selected']) && !empty($_POST['selected_items'])) {
    $selected = $_POST['selected_items'];
    $ids = implode(",", array_map('intval', $selected));
    mysqli_query($conn, "DELETE FROM cart WHERE id IN ($ids) AND email='$email'");
    echo "<script>alert('Selected items deleted successfully!'); window.location.href='cart.php';</script>";
    exit;
}

/* -------------------- FETCH CART ITEMS -------------------- */
$cart_query = mysqli_query($conn, "SELECT * FROM cart WHERE email='$email'");
$total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f7f7f7;
      margin: 0;
      padding: 0;
    }
    .cart-container {
      width: 90%;
      max-width: 1000px;
      margin: 40px auto;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      padding: 20px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table th, table td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }
    table th {
      background: #aaf595;
      color: #000;
    }
    table img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
    }
    .cart-total {
      text-align: right;
      font-size: 18px;
      margin-top: 20px;
      color: #333;
    }
    .btn {
      padding: 10px 18px;
      background: #aaf595;
      color: black;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      transition: 0.3s;
    }
    .btn:hover {
      background: #8ae581;
    }
    .delete-btn {
      background: #ff6961;
    }
    .delete-btn:hover {
      background: #ff4c4c;
    }
    .back-btn {
      display: inline-block;
      margin-top: 15px;
      background: #555;
      color: white;
      padding: 10px 18px;
      border-radius: 5px;
      text-decoration: none;
    }
    .back-btn:hover {
      background: #333;
    }
    input[type="checkbox"] {
      transform: scale(1.2);
      cursor: pointer;
    }
  </style>
</head>
<body>

  <div class="cart-container">
    <h2>Your Shopping Cart</h2>

    <?php if (mysqli_num_rows($cart_query) > 0): ?>
      <form action="cart.php" method="POST" id="cart-form">
        <table>
          <tr>
            <th><input type="checkbox" id="select-all"></th>
            <th>Image</th>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
          </tr>
          <?php while ($row = mysqli_fetch_assoc($cart_query)): 
            $subtotal = $row['product_price'] * $row['quantity'];
            $total += $subtotal;
          ?>
            <tr>
              <td><input type="checkbox" name="selected_items[]" value="<?= $row['id'] ?>" class="item-checkbox" data-price="<?= $row['product_price'] ?>" data-qty="<?= $row['quantity'] ?>"></td>
              <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="Product"></td>
              <td><?= htmlspecialchars($row['product_name']) ?></td>
              <td>₱<?= number_format($row['product_price'], 2) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>₱<?= number_format($subtotal, 2) ?></td>
            </tr>
          <?php endwhile; ?>
        </table>

        <div class="cart-total">
          <strong>Total (Selected): ₱<span id="total-price">0.00</span></strong>
        </div>
        <div style="text-align:right; margin-top:15px;">
          <button type="submit" name="delete_selected" class="btn delete-btn" onclick="return confirm('Delete selected items?')">🗑 Delete Selected</button>
          <button type="submit" formaction="checkout.php" class="btn">✅ Checkout Selected</button>
        </div>
      </form>
    <?php else: ?>
      <p style="text-align:center; color:#555;">Your cart is empty.</p>
    <?php endif; ?>

    <div style="text-align:center; margin-top:25px;">
      <a href="index.php" class="back-btn">← Back to Shop</a>
    </div>
  </div>

  <script>
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectAll = document.getElementById('select-all');
    const totalDisplay = document.getElementById('total-price');

    function updateTotal() {
      let total = 0;
      checkboxes.forEach(chk => {
        if (chk.checked) {
          total += parseFloat(chk.dataset.price) * parseInt(chk.dataset.qty);
        }
      });
      totalDisplay.textContent = total.toFixed(2);
    }

    checkboxes.forEach(chk => chk.addEventListener('change', updateTotal));
    selectAll?.addEventListener('change', () => {
      checkboxes.forEach(chk => chk.checked = selectAll.checked);
      updateTotal();
    });

    updateTotal();
  </script>

</body>
</html>
