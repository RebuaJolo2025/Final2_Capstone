<?php
session_start();
include 'conn.php';

// Check if required fields are submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $fullName = mysqli_real_escape_string($conn, $_POST['fullName']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $quantity = (int)$_POST['quantity'];

    $total_price = $price * $quantity;
    $order_date = date("Y-m-d H:i:s");

    // Insert into orders table
    $insert = "INSERT INTO orders (product_id, full_name, phone, address, notes, quantity, total_price, order_date)
               VALUES ('$product_id', '$fullName', '$phone', '$address', '$notes', '$quantity', '$total_price', '$order_date')";

    if (mysqli_query($conn, $insert)) {
        echo "<h2>Order Placed Successfully!</h2>";
        echo "<p>Thank you, $fullName. Your order has been placed.</p>";
        echo "<p>Total: Php " . number_format($total_price, 2) . "</p>";
        echo "<a href='index.php'>Return to Shop</a>";
    } else {
        echo "Error placing order: " . mysqli_error($conn);
    }

    mysqli_close($conn);
} else {
    echo "<p>Invalid request method.</p>";
}
?>
