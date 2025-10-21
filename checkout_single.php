<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Ensure buy_now session exists
if (!isset($_SESSION['buy_now'])) {
    echo "<p>No product selected for Buy Now. <a href='index.php'>Back to products</a></p>";
    exit;
}

// Get Buy Now item
$item = $_SESSION['buy_now'];
$total = $item['price'] * $item['quantity'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buy Now Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0px 5px 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2e7d32; margin-bottom: 25px; }
        .item { background: #f9f9f9; padding: 12px; border-radius: 6px; display: flex; justify-content: space-between; margin-bottom: 15px; }
        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 10px; }
        button { background: #2e7d32; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-top: 15px; }
        button:hover { background: #256428; }
        .back-link { display: block; margin-top: 15px; text-align: center; color: #2196F3; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        
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
            border-color: #2e7d32;
        }
        
        .payment-info {
            margin-top: 15px;
        }
        
        .info-content {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #2e7d32;
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
            list-style: none;
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
    <h2>Buy Now - Checkout</h2>
    
    <div class="item">
        <span><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
        <span>₱<?= number_format($total, 2) ?></span>
    </div>
    
    <p class="total">Total: ₱<?= number_format($total, 2) ?></p>

    <form method="POST" action="place_order.php">
        <!-- Send as if it were a cart of one -->
        <input type="hidden" name="selected_items[]" value="<?= $item['id'] ?>">
        <input type="hidden" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>">
        
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

    <a href="product-detail.php?product_id=<?= $item['id'] ?>" class="back-link">← Back to Product</a>
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
            placeOrderBtn.style.background = '#2e7d32';
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
        const confirmMessage = `Confirm your order with Cash on Delivery (COD)?\n\nTotal: ₱<?= number_format($total, 2) ?>`;
        
        if (!confirm(confirmMessage)) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

</body>
</html>
