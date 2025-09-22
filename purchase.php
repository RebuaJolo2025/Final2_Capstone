<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase</title>
    <link rel="stylesheet" href="./style/purchase_style.css">
</head>
<body>

    <header>
        <h1>Confirm Your Purchase</h1>
        <a href="index.php">Back to Homepage</a>
    </header>

    <div class="purchase-container">
        <?php
        include 'conn.php';

        // Validate if the product_id exists and is numeric
        if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
            echo "<p>Invalid product ID.</p>";
            exit;
        }

        $product_id = mysqli_real_escape_string($conn, $_GET['product_id']);
        $query = "SELECT * FROM shop WHERE product_id = $product_id";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);
        ?>
            <div class="product-info">
                <h2><?php echo $product['name']; ?></h2>
                <p><strong>Price:</strong> Php <?php echo $product['price']; ?></p>
                <p><strong>Description:</strong> <?php echo $product['description']; ?></p>

                <!-- Purchase Form with Shopee-style details -->
                <form action="process_order.php" method="post">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="price" value="<?php echo $product['price']; ?>">

                    <label for="fullName">Full Name:</label>
                    <input type="text" id="fullName" name="fullName" required>

                    <label for="phone">Phone Number:</label>
                    <input type="tel" id="phone" name="phone" required>

                    <label for="address">Delivery Address:</label>
                    <textarea id="address" name="address" rows="3" required></textarea>

                    <label for="notes">Order Notes (optional):</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="e.g. Leave at door, call before delivery"></textarea>

                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" min="1" value="1" required>

                    <button type="submit" class="buy-now">Place Order</button>
                </form>
            </div>
        <?php
        } else {
            echo "<p>Product not found.</p>";
        }

        mysqli_close($conn);
        ?>
    </div>

</body>
</html>
