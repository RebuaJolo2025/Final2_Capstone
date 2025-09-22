<?php
session_start();
include 'conn.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get and clean input
    $email = $_SESSION['email'];  // Assuming the user is logged in and email is in the session
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $fullName = trim($_POST['fullName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $status = 'Pending';  // Default status as "Pending"
    $order_date = date("Y-m-d H:i:s");  // Current timestamp

    // Validate required fields
    if ($product_id <= 0 || $price <= 0 || empty($fullName) || empty($phone) || empty($address) || $quantity <= 0) {
        echo "<div style='color:red; text-align:center; font-size:18px;'>❌ Error: Please fill out all required fields correctly.</div>";
        echo "<div style='text-align:center; margin-top:20px;'><a href='purchase.php?product_id=$product_id'>Return to Purchase</a></div>";
        exit();
    }

    // Calculate the total price
    $total_price = $price * $quantity;

    // Insert the order into the database using a prepared statement
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (email, product_name, quantity, order_total, status, order_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssisds", 
        $email,        // email (string)
        $product_name, // product_name (string)
        $quantity,     // quantity (integer)
        $total_price,  // order_total (double)
        $status,       // status (string)
        $order_date    // order_date (string)
    );

    // Get the product name from the `shop` table using the product_id
    $product_query = "SELECT name FROM shop WHERE product_id = ?";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();

    if ($product_result->num_rows > 0) {
        $product = $product_result->fetch_assoc();
        $product_name = $product['name'];
    } else {
        echo "<div style='color:red; text-align:center; font-size:18px;'>❌ Product not found in the database.</div>";
        exit();
    }

    // Execute the prepared statement to insert the order
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Order Confirmation</title>
        <style>
            body {
                background: #f4f6f9;
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
            }
            .card {
                background: #fff;
                border-radius: 12px;
                padding: 30px;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            }
            .success-icon {
                font-size: 60px;
                color: #4CAF50;
            }
            h2 {
                margin-top: 15px;
                color: #333;
            }
            p {
                color: #555;
                font-size: 16px;
            }
            .total {
                font-size: 18px;
                font-weight: bold;
                margin-top: 10px;
                color: #222;
            }
            a.button {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 20px;
                background: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: bold;
                transition: 0.3s;
            }
            a.button:hover {
                background: #43a047;
            }
        </style>
    </head>
    <body>
    <div class="card">
    <?php
    if ($stmt->execute()) {
        // Get the last inserted order ID
        $order_id = $stmt->insert_id;

        echo "<div class='success-icon'>✅</div>";
        echo "<h2>Order Placed Successfully!</h2>";
        echo "<p>Thank you for your order, <b>" . htmlspecialchars($fullName) . "</b>.</p>";
        echo "<p>Your order has been placed on <b>" . date("F j, Y, g:i a") . "</b>.</p>";
        echo "<p class='total'>Total: Php " . number_format($total_price, 2) . "</p>";

        // Buttons to go back to shop or track order
        echo "<a href='index.php' class='button'>Return to Shop</a>";
        echo "<a href='track_order.php?id=" . $order_id . "' class='button'>Track Order</a>";
    } else {
        echo "<div style='color:red; font-size:18px;'>❌ Error placing order: " . htmlspecialchars($stmt->error) . "</div>";
        echo "<a href='purchase.php?product_id=$product_id' class='button' style='background:#e74c3c;'>Try Again</a>";
    }
    ?>
    </div>
    </body>
    </html>
    <?php

    // Close the statement and database connection
    $stmt->close();
    $conn->close();

} else {
    echo "<p style='color:red;'>Invalid request method.</p>";
}
?>
