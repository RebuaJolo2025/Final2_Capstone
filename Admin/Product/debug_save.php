<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
ob_start();

try {
   
    include '../conn.php';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        
         
        if (empty($name) || empty($category) || $price <= 0) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Missing required fields: name, category, or price'
            ]);
            exit;
        }
        
       
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
        
  
        $createTable = "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            category VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            images JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createTable)) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Table creation failed: ' . $conn->error
            ]);
            exit;
        }
        
       
        $stmt = $conn->prepare("INSERT INTO products (name, description, category, price, stock, images) VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Prepare failed: ' . $conn->error
            ]);
            exit;
        }
        
        $stmt->bind_param("sssdis", $name, $description, $category, $price, $stock, $imagesJson);
        
        if ($stmt->execute()) {
            $productId = $conn->insert_id;
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Product saved successfully!',
                'product_id' => $productId,
                'images_uploaded' => count($uploadedFiles),
                'debug_info' => [
                    'name' => $name,
                    'category' => $category,
                    'price' => $price,
                    'stock' => $stock,
                    'images' => $uploadedFiles
                ]
            ]);
        } else {
            ob_clean();
            echo json_encode([
                'success' => false,
                'message' => 'Insert failed: ' . $stmt->error
            ]);
        }
        
        $stmt->close();
    } else {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
