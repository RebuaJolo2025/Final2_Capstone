<?php
session_start();
include '../conn.php';

// Fetch single seller info
$sellerResult = $conn->query("SELECT fullname, email, address, phonenumber FROM seller LIMIT 1");
$seller = $sellerResult->fetch_assoc();

// Fetch all processing orders
$sql = "
SELECT DISTINCT
    o.id, 
    o.email AS customer_email, 
    u.fullname AS customer_name, 
    o.product_name, 
    o.quantity, 
    o.order_total, 
    o.order_date
FROM orders o
LEFT JOIN userdata u ON o.email = u.email
WHERE o.status = 'processing'
ORDER BY o.order_date ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Waybills</title>
<style>
body { 
    font-family: Arial, sans-serif; 
    background: #f4f6f9; 
    margin: 0; 
    padding: 10px 20px; 
}

.waybill-container { 
    max-width: 1000px; 
    margin: 0 auto; 
}

h1 { 
    text-align: center; 
    margin: 10px 0 20px 0; 
}

.waybill { 
    border: 2px solid #333; 
    padding: 20px; 
    margin-bottom: 20px; 
    border-radius: 10px; 
    background: white; 
    page-break-inside: avoid;
}

.waybill-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 15px; 
}

.grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 10px; 
    margin-bottom: 15px; 
}

.grid div { padding: 5px; }
.grid .label { font-weight: bold; color: #333; }

.product-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-top: 10px; 
}

.product-table th, .product-table td { 
    border: 1px solid #333; 
    padding: 8px; 
    text-align: center; 
    font-size: 14px; 
}

.product-table th { background: #f0f0f0; }

.btn-print { 
    display: block; 
    margin: 20px auto; 
    padding: 10px 20px; 
    background: #4CAF50; 
    color: white; 
    border: none; 
    border-radius: 6px; 
    cursor: pointer; 
}

/* Back Button Style */
.btn-back {
    display: inline-block;
    margin-bottom: 15px;
    padding: 10px 20px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-back:hover {
    background: #5a67d8;
    transform: translateY(-2px);
}

/* Print Styles */
@media print {
    body { background: white; padding: 0; }
    .btn-print, .btn-back { display: none; }
    .waybill { page-break-inside: avoid; margin-bottom: 30px; }
    .waybill-container { max-width: 100%; }
}
</style>
</head>
<body>

<div class="waybill-container">

    <!-- Back Button -->
    <button class="btn-back" onclick="window.history.back();">← Back</button>

    <h1>Waybills</h1>

    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="waybill">
                <!-- Header -->
                <div class="waybill-header">
                    <h2>Order #<?= $row['id'] ?></h2>
                    <div><strong>Date:</strong> <?= date('M d, Y', strtotime($row['order_date'])) ?></div>
                </div>

                <!-- Seller & Customer Info -->
                <div class="grid">
                    <div>
                        <div class="label">Seller:</div>
                        <div>
                            <?= htmlspecialchars($seller['fullname']) ?><br>
                            <?= htmlspecialchars($seller['address']) ?><br>
                            <?= htmlspecialchars($seller['phonenumber']) ?><br>
                            <?= htmlspecialchars($seller['email']) ?>
                        </div>
                    </div>
                    <div>
                        <div class="label">Customer:</div>
                        <div>
                            <?= htmlspecialchars($row['customer_name']) ?><br>
                            <?= htmlspecialchars($row['customer_email']) ?>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td>₱<?= number_format($row['order_total'] / $row['quantity'], 2) ?></td>
                            <td>₱<?= number_format($row['order_total'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer / Notes -->
                <div class="grid" style="margin-top:20px;">
                    <div>
                        <div class="label">Notes:</div>
                        <div>Handle with care</div>
                    </div>
                    <div>
                        <div class="label">Signature:</div>
                        <div>_____________________</div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>

        <button class="btn-print" onclick="window.print()">Print All Waybills</button>

    <?php else: ?>
        <p style="text-align:center;">No orders ready for waybill.</p>
    <?php endif; ?>
</div>

</body>
</html>
