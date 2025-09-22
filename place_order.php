<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

$email = $_SESSION['email'];

// ✅ Ensure table exists
$conn->query("
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ✅ Ensure product_name column exists
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'product_name'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN product_name VARCHAR(255) NOT NULL AFTER email");
}

if (!empty($_POST['selected_items']) && is_array($_POST['selected_items'])) {
    $selected_items = $_POST['selected_items'];
    $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];

    $select_stmt = $conn->prepare("SELECT product_name, product_price FROM cart WHERE id = ? AND email = ?");
    $insert_stmt = $conn->prepare("INSERT INTO orders (email, product_name, quantity, order_total, status) VALUES (?, ?, ?, ?, 'Pending')");
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND email = ?");

    foreach ($selected_items as $id) {
        $id = intval($id);
        $quantity = isset($quantities[$id]) ? max(1, intval($quantities[$id])) : 1;

        $select_stmt->bind_param("is", $id, $email);
        $select_stmt->execute();
        $result = $select_stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $product_name = $row['product_name'];
            $price = $row['product_price'];
            $total_price = $price * $quantity;

            $insert_stmt->bind_param("ssii", $email, $product_name, $quantity, $total_price);
            if ($insert_stmt->execute()) {
                $delete_stmt->bind_param("is", $id, $email);
                $delete_stmt->execute();
            }
        }
    }

    $select_stmt->close();
    $insert_stmt->close();
    $delete_stmt->close();
    $conn->close();

    echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>
            <h2 style='color:green;'>✅ Your order has been placed successfully!</h2>
            <p><a href='track_order.php' style='color:blue; text-decoration:none; font-weight:bold;'>📦 Track your order</a></p>
            <p><a href='index.php' style='color:#555; text-decoration:none;'>Continue Shopping</a></p>
          </div>";
} else {
    echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>
            <p style='color:red;'>⚠ No items in your order.</p>
            <p><a href='cart.php' style='color:blue; text-decoration:none;'>← Go back to your cart</a></p>
          </div>";
}
?>
