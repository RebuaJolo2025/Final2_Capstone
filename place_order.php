<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];

// Ensure `orders` table exists with payment_method column
$conn->query("
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    order_total DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cod',
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    rating INT DEFAULT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Add payment_method column if it doesn't exist (for existing tables)
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) NOT NULL DEFAULT 'cod' AFTER order_total");

// Array to collect all items to insert
$items_to_order = [];

// --- HANDLE BUY NOW ---
$buy_now_id = 0;
if (isset($_SESSION['buy_now'])) {
    $item = $_SESSION['buy_now'];
    $items_to_order[] = [
        'id' => $item['id'],
        'quantity' => $item['quantity'],
        'is_buy_now' => true
    ];
    $buy_now_id = $item['id'];
}

// --- HANDLE CART CHECKOUT ---
if (!empty($_POST['selected_items']) && is_array($_POST['selected_items'])) {
    $quantities = $_POST['quantities'] ?? [];
    foreach ($_POST['selected_items'] as $id) {
        $id = intval($id);

        // Skip if this is the Buy Now item
        if ($id === $buy_now_id) continue;

        $qty = isset($quantities[$id]) ? max(1, intval($quantities[$id])) : 1;
        $items_to_order[] = [
            'id' => $id,
            'quantity' => $qty,
            'is_buy_now' => false
        ];
    }
}

// Get payment method from POST data
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';

// Validate payment method
$allowed_methods = ['cod', 'gcash', 'paymaya', 'bank_transfer'];
if (!in_array($payment_method, $allowed_methods)) {
    $payment_method = 'cod'; // Default to COD if invalid
}

// --- INSERT ITEMS INTO ORDERS ---
if (!empty($items_to_order)) {
    $insert_stmt = $conn->prepare("
        INSERT INTO orders (email, product_name, quantity, order_total, payment_method, status)
        VALUES (?, ?, ?, ?, ?, 'Pending')
    ");

    foreach ($items_to_order as $order_item) {
        $id = $order_item['id'];
        $quantity = $order_item['quantity'];

        if ($order_item['is_buy_now']) {
            // Buy Now: $id is a product ID
            $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
        } else {
            // From cart: $id is a cart row ID; resolve via cart -> products for this user
            $stmt = $conn->prepare("SELECT p.name AS name, p.price AS price FROM cart c JOIN products p ON p.id = c.product_id WHERE c.id = ? AND c.email = ?");
            $stmt->bind_param("is", $id, $email);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $product_name = $row['name'];
            $order_total = floatval($row['price']) * $quantity;

            // Insert into orders
            $insert_stmt->bind_param("ssids", $email, $product_name, $quantity, $order_total, $payment_method);
            $insert_stmt->execute();

            // If from cart, remove from cart table
            if (!$order_item['is_buy_now']) {
                $delete_stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND email = ?");
                $delete_stmt->bind_param("is", $id, $email);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }
        $stmt->close();
    }

    $insert_stmt->close();

    // Clear Buy Now session
    if ($buy_now_id) {
        unset($_SESSION['buy_now']);
    }

    $conn->close();

    // Get payment method display name
    $payment_display = [
        'cod' => 'Cash on Delivery (COD)',
        'gcash' => 'GCash',
        'paymaya' => 'PayMaya',
        'bank_transfer' => 'Bank Transfer'
    ];
    $payment_name = $payment_display[$payment_method] ?? $payment_method;

    // Success message
    echo "<div style='text-align:center; margin-top:50px; font-family:Arial; max-width:600px; margin:50px auto; padding:20px; background:#f8f9fa; border-radius:10px; border:1px solid #e9ecef;'>
            <h2 style='color:green; margin-bottom:20px;'>✅ Order Placed Successfully!</h2>
            <div style='background:white; padding:15px; border-radius:8px; margin:20px 0; border-left:4px solid #28a745;'>
                <p style='margin:5px 0; color:#333;'><strong>Payment Method:</strong> {$payment_name}</p>";
    
    if ($payment_method === 'cod') {
        echo "<p style='margin:5px 0; color:#666;'>💰 You will pay when your order arrives</p>";
    } else {
        echo "<p style='margin:5px 0; color:#666;'>📱 Please complete your payment and send confirmation</p>";
    }
    
    echo "    </div>
            <div style='margin-top:20px;'>
                <p><a href='track_order.php' style='color:white; background:#007bff; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block; margin:5px;'>📦 Track Your Order</a></p>
                <p><a href='index.php' style='color:#666; text-decoration:none; font-weight:bold;'>← Continue Shopping</a></p>
            </div>
          </div>";
    exit;
}

// --- NO ITEMS TO ORDER ---
echo "<div style='text-align:center; margin-top:50px; font-family:Arial;'>
        <p style='color:red;'>⚠ No items in your order.</p>
        <p><a href='cart.php' style='color:blue; text-decoration:none;'>← Go back to your cart</a></p>
      </div>";
?>
