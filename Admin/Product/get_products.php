<?php
 
header('Content-Type: application/json');

 
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ifx";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

 $sql = "SELECT id, name, description, category, price, compare_price, stock, sku, weight, length, width, height, tags, images, status, created_at 
        FROM products ORDER BY created_at DESC";

$result = $conn->query($sql);

$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       
        $images = json_decode($row['images'], true) ?? [];
        $tags = json_decode($row['tags'], true) ?? [];

         $mainImage = 'uploads/placeholder.png'; 
        if (!empty($images)) {
            if (is_array($images[0])) {
                 $mainImage = $images[0]['url'] ?? 'uploads/placeholder.png';
            } else {
                 $mainImage = $images[0];
            }
        }

       
        $sold = intval($row['sold'] ?? 0);

        $products[] = [
            'id' => intval($row['id']),
            'name' => $row['name'],
            'description' => $row['description'],
            'category' => $row['category'],
            'price' => floatval($row['price']),
            'comparePrice' => floatval($row['compare_price']),
            'stock' => intval($row['stock']),
            'sku' => $row['sku'],
            'weight' => floatval($row['weight']),
            'dimensions' => [
                'length' => floatval($row['length']),
                'width' => floatval($row['width']),
                'height' => floatval($row['height']),
            ],
            'tags' => $tags,
            'images' => $images,
            'image' => $mainImage,
            'status' => $row['status'],
            'sold' => $sold,
            'createdAt' => $row['created_at'],
        ];
    }
}

$conn->close();


echo json_encode($products);
?>
