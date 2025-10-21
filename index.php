<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InfluenceX</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
   
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #f5f5f5;
      color: #333;
    }

    
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #1e1e2f;
      color: white;
      padding: 15px 40px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(255, 255, 255, 0.2);
    }

    .logo h1 {
      font-size: 22px;
      color: #f4b400;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .search-container input {
      padding: 10px 15px;
      border-radius: 25px;
      border: none;
      width: 260px;
      outline: none;
      font-size: 15px;
      transition: box-shadow 0.3s ease;
    }

    .search-container input:focus {
      box-shadow: 0 0 10px rgba(244, 180, 0, 0.6);
    }

    .cart-link, .profile-link {
      color: white;
      text-decoration: none;
      margin-left: 20px;
      font-size: 22px;
      transition: color 0.3s;
    }

    .cart-link:hover, .profile-link:hover {
      color: #f4b400;
    }

    .profile-container {
      display: flex;
      align-items: center;
    }

    .profile-image {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      margin-right: 8px;
      border: 2px solid #fffbf1ff;
    }

    
    .hero-section {
      background: linear-gradient(rgba(30, 30, 47, 0.6), rgba(30, 30, 47, 0.6)), url('./img/ifx_bg.jpg');
      background-size: cover;
      background-position: center;
      padding: 90px 20px;
      text-align: center;
      color: white;
    }

    .hero-section h2 {
      font-size: 38px;
      margin-bottom: 10px;
      text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
    }

    .hero-section p {
      font-size: 18px;
      opacity: 0.9;
    }

   
    .product-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      padding: 50px 60px;
    }

    .product-item {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      overflow: hidden;
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .product-item:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    }

    .product-item img {
      width: 100%;
      height: 230px;
      object-fit: cover;
      border-bottom: 3px solid #f4b400;
    }

    .product-item h3 {
      font-size: 18px;
      font-weight: 600;
      margin: 15px 0 5px;
      color: #333;
    }

    .product-item p {
      color: #f4b400;
      font-weight: bold;
      font-size: 17px;
      margin-bottom: 10px;
    }

    .buy-now {
      display: inline-block;
      background-color: #1e1e2f;
      color: white;
      padding: 10px 25px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 500;
      margin-bottom: 15px;
      transition: background 0.3s, color 0.3s;
    }

    .buy-now:hover {
      background-color: #f4b400;
      color: #1e1e2f;
    }


    @media (max-width: 768px) {
      header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
      }

      .search-container input {
        width: 80%;
      }

      .product-container {
        padding: 20px;
      }
    }
  </style>

  <script>
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
      <h1>InfluenceX</h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchBar" placeholder="Search for products..." onkeyup="searchProducts()" />
    </div>

    <div>
      <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i></a>
      <a href="profile.php" class="profile-link">
        <img src="img/icon.png" alt="User Profile" class="profile-image">
      </a>
    </div>
  </header>

  <section class="hero-section">
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
            $mainImage = 'Admin/Product/uploads/products/placeholder.jpg';
            if (is_array($images) && count($images) > 0 && isset($images[0]['url'])) {
                $mainImage = 'Admin/Product/' . $images[0]['url'];
            }
            echo '<div class="product-item">';
            echo '<img src="' . htmlspecialchars($mainImage) . '" alt="' . htmlspecialchars($row["name"]) . '">';
            echo '<h3>' . htmlspecialchars($row["name"]) . '</h3>';
            echo '<p>₱' . number_format($row["price"], 2) . '</p>';
            echo '<a href="product-detail.php?product_id=' . htmlspecialchars($row["id"]) . '" class="buy-now">View Details</a>';
            echo '</div>';
        }
    } else {
        echo "<p style='text-align:center;'>No products found.</p>";
    }
    mysqli_close($conn);
    ?>
  </section>
</body>
</html>
