<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Luv By Ten</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #f7f9f7;
      color: #292626ff;
      overflow-x: hidden;
    }

    /* ---------- HEADER ---------- */
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: linear-gradient(90deg, #2d7a38, #4caf50);
      color: white;
      padding: 15px 40px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    }

    .search-container {
      flex: 1;
      display: flex;
      justify-content: center;
      position: relative;
    }

    .search-container input {
      padding: 10px 15px 10px 40px;
      border-radius: 25px;
      border: none;
      width: 350px;
      outline: none;
      font-size: 15px;
      transition: box-shadow 0.3s ease;
    }

    .search-container input:focus {
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
    }

    .search-container i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #555;
      font-size: 16px;
    }

    .nav-icons {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .cart-link, .profile-link {
      color: white;
      text-decoration: none;
      font-size: 22px;
      position: relative;
      transition: color 0.3s;
    }

    .cart-link:hover, .profile-link:hover {
      color: #c8e6c9;
    }

    .profile-image {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .profile-image:hover {
      transform: scale(1.1);
    }

    /* ---------- HERO ---------- */
    .hero-section {
      position: relative;
      background: url('img/dsssss.jpg') center center / cover no-repeat;
      height: 400px;
    }

    /* ---------- PRODUCT SECTION ---------- */
    .product-section {
      padding: 70px 60px;
      text-align: center;
    }

    .product-section h2 {
      color: #2d7a38;
      font-size: 28px;
      margin-bottom: 35px;
      position: relative;
    }

    .product-section h2::after {
      content: "";
      width: 80px;
      height: 3px;
      background: #2d7a38;
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 3px;
    }

    .product-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      justify-items: center;
    }

    /* ---------- PRODUCT CARD ---------- */
    .product-item {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      width: 100%;
      max-width: 280px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .product-item:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .product-item img {
      width: 100%;
      height: 260px;
      object-fit: cover;
      background-color: #f9faf9;
      border-bottom: 3px solid #2d7a38;
    }

    .product-item h3 {
      font-size: 17px;
      font-weight: 600;
      color: #2d2d2d;
      margin: 12px 10px 6px;
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }

    .product-item p {
      color: #2d7a38;
      font-weight: bold;
      font-size: 16px;
      margin-bottom: 8px;
    }

    .buy-now {
      display: inline-block;
      background-color: #2d7a38;
      color: white;
      padding: 10px 25px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 500;
      margin: 10px auto 15px;
      transition: background 0.3s, transform 0.3s;
    }

    .buy-now:hover {
      background-color: #256b30;
      transform: translateY(-3px);
    }

    /* ---------- FOOTER ---------- */
    footer {
      background-color: #2d7a38;
      color: #fff;
      text-align: center;
      padding: 20px;
      font-size: 14px;
      letter-spacing: 0.5px;
      box-shadow: 0 -3px 10px rgba(0,0,0,0.15);
    }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 768px) {
      header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
        padding: 15px 20px;
      }

      .search-container {
        order: 3;
        width: 100%;
      }

      .search-container input {
        width: 85%;
      }

      .product-section {
        padding: 40px 20px;
      }

      .product-item img {
        height: 220px;
      }

      .product-item {
        max-width: 100%;
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
      <h1>Luv By Ten</h1>
    </div>

    <div class="search-container">
      <i class="fas fa-search"></i>
      <input type="text" id="searchBar" placeholder="Search for products..." onkeyup="searchProducts()" />
    </div>

    <div class="nav-icons">
      <a href="cart.php" class="cart-link">
        <i class="fas fa-shopping-cart"></i>
      </a>
      <a href="profile.php" class="profile-link">
        <img src="img/icon.png" alt="User Profile" class="profile-image" />
      </a>
    </div>
  </header>

  <section class="hero-section"></section>

  <section class="product-section" id="products">
    <div class="product-container">
      <?php
      include 'conn.php';
      $sql = "SELECT * FROM products ORDER BY id DESC";
      $result = mysqli_query($conn, $sql);

      if ($result && mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {
              $images = json_decode($row['images'], true);
              $mainImage = 'img/icon.png';
              
              if (is_array($images) && count($images) > 0) {
                  if (is_array($images[0]) && isset($images[0]['url'])) {
                      $mainImage = 'Admin/Product/' . $images[0]['url'];
                  } elseif (is_string($images[0])) {
                      $mainImage = 'Admin/Product/' . $images[0];
                  }
              }

              echo '<div class="product-item">';
              echo '<img src="' . htmlspecialchars($mainImage) . '" alt="' . htmlspecialchars($row["name"]) . '" onerror="this.src=\'img/icon.png\'; this.onerror=null;">';
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
    </div>
  </section>

 
</body>
</html>
