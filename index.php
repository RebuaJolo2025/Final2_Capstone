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

            // Loop through each product and check if it matches the search input
            for (var i = 0; i < productItems.length; i++) {
                var productName = productItems[i].getElementsByTagName("h3")[0].innerText.toLowerCase();
                
                // Check if the product name includes the search input
                if (productName.includes(input)) {
                    productItems[i].style.display = ""; // Show the product if it matches
                } else {
                    productItems[i].style.display = "none"; // Hide the product if it doesn't match
                }
            }
        }
    </script>
    <style>
   
    </style>
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
        // Include database connection
        include 'conn.php';
        
        // Fetch all products from database
        $sql = "SELECT * FROM shop";
        $result = mysqli_query($conn, $sql);
        
        // Check if products exist
        if (mysqli_num_rows($result) > 0) {
            // Loop through each product
            while($row = mysqli_fetch_assoc($result)) {
                echo '<div class="product-item">';
                echo '<img src="' . $row["image"] . '" alt="' . $row["name"] . '">';
                echo '<h3>' . $row["name"] . '</h3>';
                echo '<p>' . '₱' . $row["price"] . '</p>';
                echo '<a href="product-detail.php?product_id=' . $row["product_id"] . '" class="buy-now">View Details</a>';
                echo '</div>';
            }
        } else {
            echo "No products found";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </section>
    
    <footer>
    </footer>
</body>
</html>

