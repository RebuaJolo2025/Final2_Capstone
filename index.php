<?php
 session_start();
 if (!isset($_SESSION['email'])) {
   header("Location: login.php");
   exit();
 }
?>
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

    .filters-bar {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
      margin: 10px 0 25px;
    }

    .filters-bar label {
      color: #2d7a38;
      font-weight: 600;
      font-size: 14px;
    }

    .category-select {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid #cfd8cf;
      outline: none;
      font-size: 14px;
      background: #ffffff;
      color: #2d2d2d;
      min-width: 200px;
      transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .category-select:focus {
      border-color: #4caf50;
      box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.15);
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
      position: relative;
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
      transition: transform 0.35s ease;
    }

    .product-item:hover img {
      transform: scale(1.03);
    }

    .product-item::before {
      content: attr(data-category);
      position: absolute;
      top: 12px;
      left: 12px;
      background: rgba(45, 122, 56, 0.95);
      color: #fff;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
      text-transform: capitalize;
      border: 1px solid rgba(255, 255, 255, 0.35);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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
      var categorySelect = document.getElementById("categoryFilter");
      var selectedCategory = categorySelect ? (categorySelect.value || "").toLowerCase() : "";
      function normalizeCategory(str) {
        return (str || "").toLowerCase().replace(/[^a-z0-9]+/g, "_").replace(/^_+|_+$/g, "");
      }
      var normSelected = normalizeCategory(selectedCategory);
      var productItems = document.getElementsByClassName("product-item");
      for (var i = 0; i < productItems.length; i++) {
        var productName = productItems[i].getElementsByTagName("h3")[0].innerText.toLowerCase();
        var productCategoryRaw = (productItems[i].getAttribute("data-category") || "");
        var normCategory = normalizeCategory(productCategoryRaw);
        var matchesText = productName.includes(input);
        var matchesCategory = !normSelected;
        if (!matchesCategory) {
          var tokens = normCategory.split("_");
          matchesCategory = tokens.includes(normSelected) || normCategory.startsWith(normSelected + "_") || normCategory === normSelected;
        }
        productItems[i].style.display = (matchesText && matchesCategory) ? "" : "none";
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
    <h2>Products</h2>

    <div class="filters-bar">
      <label for="categoryFilter">Category:</label>
      <select id="categoryFilter" class="category-select" onchange="searchProducts()">
        <option value="">All Categories</option>
        <option value="men">Men’s Collection</option>
        <option value="women">Women’s Collection</option>
        <option value="kids">Kids’ Wear</option>
        <option value="baby_toddler">Baby & Toddler</option>
        <option value="teens">Teens & Young Adults</option>
        <option value="couple">Couple Outfits</option>
        <option value="family">Family Collection</option>
        <option value="formal">Formal & Officewear</option>
        <option value="casual">Casual & Everyday Wear</option>
        <option value="seasonal">Seasonal & Holiday Fashion</option>
      </select>
    </div>

    <div class="product-container">
      <?php
      include 'conn.php';
      $sql = "SELECT * FROM products ORDER BY id DESC";
      $result = mysqli_query($conn, $sql);

      if ($result && mysqli_num_rows($result) > 0) {
          while($row = mysqli_fetch_assoc($result)) {
              $images = json_decode($row['images'], true);
              $mainImage = 'img/icon.png';
              $category = isset($row['category']) && $row['category'] !== '' ? $row['category'] : 'Uncategorized';
              
              if (is_array($images) && count($images) > 0) {
                  if (is_array($images[0]) && isset($images[0]['url'])) {
                      $mainImage = 'Admin/Product/' . $images[0]['url'];
                  } elseif (is_string($images[0])) {
                      $mainImage = 'Admin/Product/' . $images[0];
                  }
              }

              echo '<div class="product-item" data-category="' . htmlspecialchars(strtolower($category)) . '">';
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
