<?php
// Ensure JSON response
header('Content-Type: application/json');

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ifx";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

function clean($data) { return htmlspecialchars(trim($data)); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = clean($_POST['name'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $category = clean($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($id <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid product ID."]); exit;
    }
    if (!$name || !$category || $price <= 0) {
        echo json_encode(["success" => false, "message" => "Product name, category, and valid price are required."]); exit;
    }

    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category = ?, price = ?, stock = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]); exit;
    }

    $stmt->bind_param("sssddi", $name, $description, $category, $price, $stock, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Product updated successfully!"]);    
    } else {
        echo json_encode(["success" => false, "message" => "Error updating product: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
