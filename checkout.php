<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['email'];
$total = 0;
$items = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_items'])) {
    foreach ($_POST['selected_items'] as $id) {
        $id = intval($id);
        $qty = isset($_POST['quantities'][$id]) ? intval($_POST['quantities'][$id]) : 1;

        // Prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, product_name, product_price FROM cart WHERE id = ? AND email = ?");
        $stmt->bind_param("is", $id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $subtotal = $row['product_price'] * $qty;
            $total += $subtotal;
            $items[] = [
                'id' => $row['id'],
                'name' => $row['product_name'],
                'price' => $row['product_price'],
                'quantity' => $qty,
                'subtotal' => $subtotal
            ];
        }
        $stmt->close();
    }
} else {
    echo "<p>No items selected. <a href='cart.php'>Back to cart</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0px 5px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
        }
        ul li {
            background: #f9f9f9;
            margin-bottom: 10px;
            padding: 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
            color: #222;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: 0.3s;
        }
        button:hover {
            background: #43a047;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #2196F3;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        
        /* Payment Section Styles */
        .payment-section {
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .payment-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
        }
        
        .payment-options select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .payment-options select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        
        .payment-info {
            margin-top: 15px;
        }
        
        .info-content {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #4CAF50;
            animation: fadeIn 0.3s ease-in;
        }
        
        .info-content h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }
        
        .info-content p {
            margin: 5px 0;
            color: #666;
            line-height: 1.5;
        }
        
        .info-content ul {
            margin: 10px 0;
            padding-left: 0;
        }
        
        .info-content ul li {
            background: none;
            padding: 5px 0;
            margin: 0;
            color: #555;
            border-radius: 0;
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        button:disabled:hover {
            background: #ccc;
        }
    </style>
</head>
<body>
<div class="container">
<h2>Checkout Summary</h2>
<ul>
<?php foreach ($items as $item): ?>
    <li>
        <span><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
        <span>₱<?= number_format($item['subtotal'], 2) ?></span>
    </li>
<?php endforeach; ?>
</ul>
<p class="total">Total: ₱<?= number_format($total, 2) ?></p>

<form method="POST" action="place_order.php">
    <?php foreach ($items as $item): ?>
        <input type="hidden" name="selected_items[]" value="<?= $item['id'] ?>">
        <input type="hidden" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>">
    <?php endforeach; ?>
    
    <!-- Payment Method Selection -->
    <div class="payment-section">
        <h3>Payment Method</h3>
        <div class="payment-options">
            <select name="payment_method" id="payment_method" required>
                <option value="">Select Payment Method</option>
                <option value="cod">Cash on Delivery (COD)</option>
                
            </select>
        </div>
        
        <!-- Payment Instructions -->
        <div class="payment-info" id="payment-info">
            <div class="info-content" id="cod-info" style="display: none;">
                <h4>💰 Cash on Delivery</h4>
                <p>Pay when your order arrives at your doorstep. Please have the exact amount ready.</p>
                <ul>
                    <li>✅ No advance payment required</li>
                    <li>✅ Pay in cash to the delivery rider</li>
                    <li>✅ Inspect your items before payment</li>
                </ul>
            </div>
        
        </div>
    </div>
    
    <button type="submit" id="place-order-btn" disabled>Place Order</button>
</form>

<a href="cart.php" class="back-link">← Back to Cart</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentSelect = document.getElementById('payment_method');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const paymentInfo = document.getElementById('payment-info');
    
    // Handle payment method change
    paymentSelect.addEventListener('change', function() {
        const selectedMethod = this.value;
        
        // Hide all payment info sections
        const allInfoSections = document.querySelectorAll('.info-content');
        allInfoSections.forEach(section => {
            section.style.display = 'none';
        });
        
        // Show selected payment info
        if (selectedMethod) {
            const selectedInfo = document.getElementById(selectedMethod + '-info');
            if (selectedInfo) {
                selectedInfo.style.display = 'block';
            }
            
            // Enable place order button
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = 'Place Order';
            placeOrderBtn.style.background = '#4CAF50';
        } else {
            // Disable place order button if no payment method selected
            placeOrderBtn.disabled = true;
            placeOrderBtn.textContent = 'Select Payment Method';
            placeOrderBtn.style.background = '#ccc';
        }
    });
    
    // Form validation before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedPayment = paymentSelect.value;
        
        if (!selectedPayment) {
            e.preventDefault();
            alert('Please select a payment method before placing your order.');
            paymentSelect.focus();
            return false;
        }
        
        // Confirmation dialog
        const confirmMessage = `Confirm your order with ${getPaymentMethodName(selectedPayment)}?\n\nTotal: ₱<?= number_format($total, 2) ?>`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Helper function to get payment method display name
    function getPaymentMethodName(method) {
        const methods = {
            'cod': 'Cash on Delivery (COD)',
            'gcash': 'GCash',
            'paymaya': 'PayMaya',
            'bank_transfer': 'Bank Transfer'
        };
        return methods[method] || method;
    }
});
</script>

</body>
</html>
