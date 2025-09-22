<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfluenceX</title>
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        // JavaScript function to filter products based on search input
        function searchProducts() {
            var input = document.getElementById("searchBar").value.toLowerCase();
            var productItems = document.getElementsByClassName("product-item");

            for (var i = 0; i < productItems.length; i++) {
                var productName = productItems[i].getElementsByTagName("h3")[0].innerText.toLowerCase();
                productItems[i].style.display = productName.includes(input) ? "" : "none";
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>The Digital Purchase Path</h1>
        </div>
        <div class="search-container">
            <input type="text" id="searchBar" placeholder="Search for products..." onkeyup="searchProducts()" />
        </div>

        <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i></a>
 
        <div class="profile-container">
            <a href="profile.php" class="profile-link">
                <img src="img/icon.png" alt="User Profile" class="profile-image">
                <span class="profile-text">Profile</span>
            </a>
        </div>
    </header>

    <section class="hero-section" style="background-image: url(./img/ifx_bg.jpg);">
        <h2>Find the Best Deals!</h2>
        <p>Explore a variety of products from your favorite brands.</p>
    </section>

    <section class="product-container">
    <?php
    include 'conn.php';

    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $images = json_decode($row['images'], true);

            // fallback image
            $mainImage = 'Admin/Product/uploads/products/placeholder.jpg';

            if (is_array($images) && count($images) > 0 && isset($images[0]['url'])) {
                $mainImage = 'Admin/Product/' . $images[0]['url'];
                // results in Admin/Product/uploads/products/img_68d0cd34d4a55.jpg
            }

            echo '<div class="product-item">';
            echo '<img src="' . htmlspecialchars($mainImage) . '" alt="' . htmlspecialchars($row["name"]) . '">';
            echo '<h3>' . htmlspecialchars($row["name"]) . '</h3>';
            echo '<p>₱' . number_format($row["price"], 2) . '</p>';
            echo '<a href="product-detail.php?product_id=' . htmlspecialchars($row["id"]) . '" class="buy-now">View Details</a>';
            echo '</div>';
        }


    } else {
        echo "<p>No products found.</p>";
    }

    mysqli_close($conn);
    ?>
    </section>

   
</body>
</html>
