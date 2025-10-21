<?php
// ---------- BACKEND (PHP) ----------
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => 'Error occurred'];

    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;

    $uploadedFiles = [];
    $uploadDir = 'uploads/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $filename = uniqid('img_') . '.' . pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($tmpName, $filepath)) {
                $uploadedFiles[] = $filepath;
            }
        }
    }

    $imagesJson = json_encode($uploadedFiles);

    $stmt = $conn->prepare("INSERT INTO products (name, description, category, price, stock, images) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdis", $name, $description, $category, $price, $stock, $imagesJson);

    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Product added successfully!'];
    } else {
        $response['message'] = 'Database error: ' . $stmt->error;
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Product</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f4f6f8;
      margin: 0;
      padding: 0;
      color: #333;
    }

    header {
      background: #2c3e50;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      margin: 0;
      font-size: 1.5rem;
    }

    .container {
      max-width: 900px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    .form-group {
      margin-bottom: 1.2rem;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    input, select, textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 1rem;
    }

    .image-upload {
      border: 2px dashed #ccc;
      padding: 2rem;
      border-radius: 10px;
      text-align: center;
      transition: border-color 0.3s;
      cursor: pointer;
    }

    .image-upload:hover {
      border-color: #3498db;
    }

    .image-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      margin-top: 1rem;
    }

    .image-preview img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
      border: 1px solid #ddd;
    }

    .btn {
      background: #3498db;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #2980b9;
    }

    .toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #333;
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      opacity: 0;
      transition: opacity 0.4s ease-in-out;
    }

    .toast.show {
      opacity: 1;
    }
  </style>
</head>

<body>
  <header>
    <h1>Add New Product</h1>
  </header>

  <div class="container">
    <form id="productForm" enctype="multipart/form-data">
      <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" required />
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="4"></textarea>
      </div>

      <div class="form-group">
        <label>Category</label>
        <select name="category" required>
          <option value="">Select category</option>
          <option value="fashion">Fashion</option>
          <option value="electronics">Electronics</option>
          <option value="beauty">Beauty</option>
        </select>
      </div>

      <div class="form-group">
        <label>Price (₱)</label>
        <input type="number" name="price" step="0.01" required />
      </div>

      <div class="form-group">
        <label>Stock Quantity</label>
        <input type="number" name="stock" min="0" required />
      </div>

      <div class="form-group">
        <label>Upload Product Images</label>
        <div class="image-upload" onclick="document.getElementById('imageUpload').click()">
          <input type="file" id="imageUpload" name="images[]" multiple accept="image/*" hidden />
          <p><i class="fa fa-cloud-upload-alt"></i> Click or Drag to Upload</p>
        </div>
        <div class="image-preview" id="imagePreview"></div>
      </div>

      <button type="submit" class="btn">Save Product</button>
    </form>
  </div>

  <div id="toast" class="toast"></div>

  <script>
    const form = document.getElementById('productForm');
    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const toast = document.getElementById('toast');
    let images = [];

    imageUpload.addEventListener('change', (e) => {
      const files = Array.from(e.target.files);
      imagePreview.innerHTML = '';
      images = files;

      files.forEach(file => {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (event) => {
            const img = document.createElement('img');
            img.src = event.target.result;
            imagePreview.appendChild(img);
          };
          reader.readAsDataURL(file);
        }
      });
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(form);
      images.forEach((file) => formData.append('images[]', file));

      const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData,
      });

      const result = await response.json();
      showToast(result.message);

      if (result.success) {
        form.reset();
        imagePreview.innerHTML = '';
      }
    });

    function showToast(message) {
      toast.textContent = message;
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 3000);
    }
  </script>
</body>
</html>
