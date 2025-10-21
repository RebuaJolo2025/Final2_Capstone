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

// Helper function to sanitize input
function clean($data) {
    return htmlspecialchars(trim($data));
}

// Helper function to generate SKU
function generateSKU() {
    return 'SKU-' . time() . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $name = clean($_POST['name'] ?? '');
    $description = clean($_POST['description'] ?? '');
    $category = clean($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $comparePrice = floatval($_POST['comparePrice'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $sku = clean($_POST['sku'] ?? '');
    $weight = floatval($_POST['weight'] ?? 0);
    $length = floatval($_POST['length'] ?? 0);
    $width = floatval($_POST['width'] ?? 0);
    $height = floatval($_POST['height'] ?? 0);
    $status = clean($_POST['status'] ?? 'active');

    // Tags sent as JSON string
    $tags = isset($_POST['tags']) ? $_POST['tags'] : '[]';

    // Auto-generate SKU if empty
    if (!$sku) $sku = generateSKU();

    // Validate required fields
    if (!$name || !$category || $price <= 0) {
        echo json_encode(["success" => false, "message" => "Product name, category, and price are required."]);
        exit;
    }

    // Create uploads folder if needed
    $uploadDir = "uploads/products/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

    // Handle images
    $images = [];
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $nameFile) {
            $tmpName = $_FILES['images']['tmp_name'][$key] ?? null;
            if (!$tmpName) continue;

            $ext = pathinfo($nameFile, PATHINFO_EXTENSION);
            $newName = uniqid('img_') . '.' . $ext;
            $destination = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $destination)) {
                $images[] = ['url' => $destination, 'name' => $nameFile];
            }
        }
    }
    $imagesJson = json_encode($images);

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO products 
        (name, description, category, price, compare_price, stock, sku, weight, length, width, height, tags, images, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->bind_param(
        "sssdidddddssss",
        $name,
        $description,
        $category,
        $price,
        $comparePrice,
        $stock,
        $sku,
        $weight,
        $length,
        $width,
        $height,
        $tags,
        $imagesJson,
        $status
    );

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Product saved successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error saving product: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
