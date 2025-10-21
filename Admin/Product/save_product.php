<?php
// ---------- BACKEND (PHP) ----------
// Prevent any output before JSON
ob_start();

// Set content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output

// Include database connection (Admin version)
include '../conn.php';

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . ($conn->connect_error ?? 'Connection not established')]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response = ['success' => false, 'message' => 'Error occurred'];

        // Get all form data
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $comparePrice = floatval($_POST['comparePrice'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $sku = $_POST['sku'] ?? '';
        $weight = floatval($_POST['weight'] ?? 0);
        $length = floatval($_POST['length'] ?? 0);
        $width = floatval($_POST['width'] ?? 0);
        $height = floatval($_POST['height'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $tags = $_POST['tags'] ?? '[]';

        // Validate required fields
        if (empty($name)) {
            $response['message'] = 'Product name is required';
            echo json_encode($response);
            exit;
        }

        if (empty($category)) {
            $response['message'] = 'Category is required';
            echo json_encode($response);
            exit;
        }

        if ($price <= 0) {
            $response['message'] = 'Price must be greater than 0';
            echo json_encode($response);
            exit;
        }

        // Handle image uploads
        $uploadedFiles = [];
        $uploadDir = 'uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (isset($_FILES['images']) && !empty($_FILES['images']['tmp_name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if (!empty($tmpName)) {
                    $originalName = $_FILES['images']['name'][$key];
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $filename = uniqid('product_') . '.' . $extension;
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($tmpName, $filepath)) {
                        $uploadedFiles[] = $filepath;
                    }
                }
            }
        }

        $imagesJson = json_encode($uploadedFiles);

        // Generate SKU if not provided
        if (empty($sku)) {
            $sku = 'SKU-' . time() . '-' . strtoupper(substr(md5($name), 0, 4));
        }

        // Create products table if it doesn't exist
        $createTableQuery = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            compare_price DECIMAL(10,2) DEFAULT 0,
            stock INT NOT NULL DEFAULT 0,
            sku VARCHAR(100) UNIQUE,
            weight DECIMAL(8,2) DEFAULT 0,
            length DECIMAL(8,2) DEFAULT 0,
            width DECIMAL(8,2) DEFAULT 0,
            height DECIMAL(8,2) DEFAULT 0,
            status ENUM('active', 'draft', 'inactive') DEFAULT 'active',
            tags JSON,
            images JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->query($createTableQuery);

        // Insert product
        $stmt = $conn->prepare("INSERT INTO products (name, description, category, price, compare_price, stock, sku, weight, length, width, height, status, tags, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
            exit;
        }

        // Fixed parameter types: s=string, d=double, i=integer
        // name(s), description(s), category(s), price(d), compare_price(d), stock(i), sku(s), weight(d), length(d), width(d), height(d), status(s), tags(s), images(s)
        $stmt->bind_param("sssddisd dddsss", 
            $name, $description, $category, $price, $comparePrice, $stock, $sku, 
            $weight, $length, $width, $height, $status, $tags, $imagesJson
        );

        if ($stmt->execute()) {
            $productId = $conn->insert_id;
            $response = [
                'success' => true, 
                'message' => 'Product saved successfully!',
                'product_id' => $productId
            ];
        } else {
            $response['message'] = 'Database error: ' . $stmt->error;
        }

        $stmt->close();
        
        // Clean output buffer and send JSON
        ob_clean();
        echo json_encode($response);
        exit;
    } else {
        // Not a POST request
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}
?>
