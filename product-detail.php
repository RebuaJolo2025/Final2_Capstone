<?php
include 'conn.php';

// Get product ID from URL
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Fetch product from database
    $sql = "SELECT * FROM products WHERE id = $product_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);

        // Decode images JSON
        $images = json_decode($product['images'], true);
        $mainImage = 'Admin/Product/uploads/products/placeholder.jpg';
        if (is_array($images) && count($images) > 0 && isset($images[0]['url'])) {
            $mainImage = 'Admin/Product/' . $images[0]['url'];
        }
    } else {
        echo "<p>Product not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid product ID.</p>";
    exit;
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - InfluenceX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            padding: 15px 30px;
            background: #2e7d32;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 { margin: 0; }
        header a { color: #fff; text-decoration: none; font-weight: bold; }
        .product-detail {
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            display: flex;
            gap: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        .product-gallery { flex: 1; }
        .product-gallery img.main-image {
            width: 100%;
            border-radius: 10px;
        }
        .thumbnails {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .thumbnails img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: 0.3s;
        }
        .thumbnails img:hover {
            border: 2px solid #2e7d32;
        }
        .product-info { flex: 1.5; }
        .product-info h2 {
            font-size: 28px;
            margin: 0 0 15px;
        }
        .price {
            font-size: 26px;
            color: #2e7d32;
            margin: 15px 0;
            font-weight: bold;
        }
        .description {
            line-height: 1.6;
            margin-bottom: 25px;
            color: #444;
        }
        .quantity {
            display: flex;
            align-items: center;
            margin: 20px 0;
            gap: 10px;
        }
        .quantity button {
            width: 35px;
            height: 35px;
            border: 1px solid #2e7d32;
            background: #fff;
            color: #2e7d32;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
        }
        .quantity button:hover {
            background: #2e7d32;
            color: #fff;
        }
        .quantity input {
            width: 60px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px;
            font-size: 16px;
        }
        .buttons {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .add-to-cart, .buy-now {
            flex: 1;
            padding: 16px;
            text-align: center;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            font-size: 16px;
            transition: 0.3s;
        }
        .add-to-cart {
            background: #fff;
            color: #2e7d32;
            border: 2px solid #2e7d32;
        }
        .add-to-cart:hover {
            background: #e8f5e9;
        }
        .buy-now {
            background: #2e7d32;
            color: #fff;
            border: 2px solid #2e7d32;
        }
        .buy-now:hover {
            background: #256628;
        }
    </style>
</head>
<body>

<header>
    <h1>Product Details</h1>
    <a href="index.php">⬅ Back to Products</a>
</header>

<section class="product-detail">
    <div class="product-gallery">
        <img id="mainImage" src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-image">
        
        <?php if (!empty($images) && is_array($images) && count($images) > 1): ?>
        <div class="thumbnails">
            <?php foreach ($images as $img): ?>
                <img src="Admin/Product/<?php echo htmlspecialchars($img['url']); ?>" onclick="document.getElementById('mainImage').src=this.src;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
        <p class="description"><?php echo nl2br(htmlspecialchars($product['description'] ?? "No description available.")); ?></p>
        
        <!-- Quantity Selector -->
        <div class="quantity">
            <span>Quantity:</span>
            <button onclick="changeQty(-1)">-</button>
            <input type="text" id="quantity" value="1" min="1">
            <button onclick="changeQty(1)">+</button>
        </div>
        
        <!-- Buttons -->
        <div class="buttons">
            <a href="#" onclick="addToCart(<?php echo $product['id']; ?>)" class="add-to-cart">
                <i class="fas fa-cart-plus"></i> Add to Cart
            </a>
            <a href="#" onclick="buyNow(<?php echo $product['id']; ?>)" class="buy-now">
                <i class="fas fa-bolt"></i> Buy Now
            </a>
        </div>
    </div>
</section>

<script>
    function changeQty(val) {
        let qtyInput = document.getElementById("quantity");
        let current = parseInt(qtyInput.value);
        if (isNaN(current)) current = 1;
        let newVal = current + val;
        if (newVal < 1) newVal = 1;
        qtyInput.value = newVal;
    }

    function addToCart(productId) {
        let qty = document.getElementById("quantity").value;
        window.location.href = "cart.php?add=" + productId + "&qty=" + qty;
    }

    function buyNow(productId) {
        let qty = document.getElementById("quantity").value;
        window.location.href = "buy_now.php?product_id=" + productId + "&qty=" + qty;
    }
</script>

</body>
</html>
