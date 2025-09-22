<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['product_id']) || !is_numeric($_GET['product_id'])) {
    echo "Invalid product.";
    exit;
}

$product_id = intval($_GET['product_id']);
$qty = isset($_GET['qty']) ? intval($_GET['qty']) : 1;
if ($qty < 1) $qty = 1;

// Fetch product
$sql = "SELECT id, name, price FROM products WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "Product not found.";
    exit;
}

// Store in session (so checkout page can access)
$_SESSION['buy_now'] = [
    'id' => $product['id'],
    'name' => $product['name'],
    'price' => $product['price'],
    'quantity' => $qty,
    'subtotal' => $product['price'] * $qty
];

header("Location: checkout_single.php");
exit;
