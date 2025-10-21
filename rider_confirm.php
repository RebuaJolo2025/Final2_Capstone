<?php
session_start();
include 'conn.php';

$message = '';
$error = '';
$order = null;

// Get parameters
$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

// Validate token and get order
if ($order_id && $token) {
    $stmt = $conn->prepare("SELECT o.*, u.fullname AS customer_name 
                          FROM orders o 
                          LEFT JOIN (SELECT email, MAX(fullname) AS fullname FROM userdata GROUP BY email) u 
                          ON o.email = u.email 
                          WHERE o.id = ? AND o.status IN ('shipped', 'processing', 'delivered')");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        // Simple token validation (you can enhance this with proper token storage)
        $expected_token = md5($order['id'] . 'delivery_secret_key_2024');
        if ($token !== $expected_token) {
            $error = 'Invalid delivery token.';
            $order = null;
        }
    } else {
        $error = 'Order not found or not ready for delivery.';
    }
    $stmt->close();
}

// Handle delivery confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delivery']) && $order) {
    $proof_photo = '';
    
    // Handle photo upload
    if (isset($_FILES['proof_photo']) && $_FILES['proof_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/delivery_proof/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['proof_photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $filename = 'delivery_' . $order['id'] . '_' . time() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['proof_photo']['tmp_name'], $filepath)) {
                $proof_photo = $filepath;
            }
        }
    }
    
    // Update order status to delivered with timestamp
    $delivered_at = date('Y-m-d H:i:s');
    $update_stmt = $conn->prepare("UPDATE orders SET status = 'delivered', order_date = ? WHERE id = ?");
    $update_stmt->bind_param("si", $delivered_at, $order['id']);
    
    if ($update_stmt->execute()) {
        $message = 'Delivery confirmed successfully!';
        $order['status'] = 'delivered';
    } else {
        $error = 'Failed to confirm delivery. Please try again.';
    }
    $update_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Confirmation</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0;
            color: #2d3748;
            font-size: 1.5rem;
        }
        .order-info {
            background: #f7fafc;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .order-info h3 {
            margin: 0 0 12px;
            color: #4a5568;
            font-size: 1.1rem;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .info-label {
            font-weight: 600;
            color: #718096;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-shipped { background: #bee3f8; color: #2b6cb0; }
        .status-delivered { background: #c6f6d5; color: #2f855a; }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #4a5568;
        }
        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .file-input:hover {
            border-color: #a0aec0;
        }
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #4299e1;
            color: white;
        }
        .btn-primary:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            font-weight: 600;
        }
        .message.success {
            background: #c6f6d5;
            color: #2f855a;
        }
        .message.error {
            background: #fed7d7;
            color: #c53030;
        }
        .camera-icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Delivery Confirmation</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($order): ?>
            <div class="order-info">
                <h3>Order Details</h3>
                <div class="info-row">
                    <span class="info-label">Order ID:</span>
                    <span>#<?= $order['id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer:</span>
                    <span><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Product:</span>
                    <span><?= htmlspecialchars($order['product_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Quantity:</span>
                    <span><?= $order['quantity'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total:</span>
                    <span>₱<?= number_format($order['order_total'], 2) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge status-<?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span>
                </div>
                <?php if (!empty($order['rider_name'])): ?>
                <div class="info-row">
                    <span class="info-label">Assigned Rider:</span>
                    <span style="color: #059669; font-weight: 600;"><?= htmlspecialchars($order['rider_name']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($order['status'] !== 'delivered'): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Proof of Delivery (Optional)</label>
                        <div class="file-input" onclick="document.getElementById('photo').click()">
                            <div class="camera-icon">📷</div>
                            <div>Tap to take photo</div>
                            <input type="file" id="photo" name="proof_photo" accept="image/*" capture="camera" style="display: none;">
                        </div>
                    </div>
                    
                    <button type="submit" name="confirm_delivery" class="btn btn-primary">
                        ✅ Confirm Delivery
                    </button>
                </form>
            <?php else: ?>
                <div class="message success">
                    ✅ This order has been delivered!
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="message error">
                Invalid delivery link. Please check with your supervisor.
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.getElementById('photo')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileInput = document.querySelector('.file-input');
                fileInput.innerHTML = `
                    <div class="camera-icon">✅</div>
                    <div>Photo selected: ${file.name}</div>
                `;
            }
        });
    </script>
</body>
</html>