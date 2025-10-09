<?php
session_start();
include '../conn.php';

// Fetch seller info
$sellerResult = $conn->query("SELECT fullname, email, address, phonenumber FROM seller LIMIT 1");
$seller = $sellerResult->fetch_assoc();

// Fetch all processing orders
$sql = "
SELECT 
    o.id, 
    o.email AS customer_email, 
    u.fullname AS customer_name, 
    u.address AS customer_address,
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
            font-family: 'Arial', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .waybill-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        h1 {
            font-size: 24px;
            text-align: center;
            color: #333;
            margin: 0;
        }

        .waybill {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 8px;
        }

        .waybill-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f1f1;
        }

        .waybill-header h2 {
            color: #FF5722;
        }

        .waybill-header .date {
            font-size: 14px;
            color: #777;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .grid div {
            padding: 12px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 1px solid #ddd;
        }

        .product-table th, .product-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            font-size: 14px;
        }

        .product-table th {
            background-color: #f0f0f0;
            color: #333;
        }

        .product-table td {
            color: #555;
        }

        .btn-print {
            display: block;
            margin: 30px auto;
            padding: 12px 25px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        .btn-back {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background-color: #5a67d8;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .btn-print, .btn-back {
                display: none;
            }

            .waybill-container {
                max-width: 100%;
            }

            .waybill {
                page-break-inside: avoid;
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>

<div class="waybill-container">

    <div class="header">
        <h1>Waybill</h1>
    </div>

    <button class="btn-back" onclick="window.history.back();">← Back</button>

    <?php 
    if ($result->num_rows > 0): 
        $orders = [];

        while ($row = $result->fetch_assoc()) {
            $orderId = $row['id'];
            $orders[$orderId]['order'] = $row;
            $orders[$orderId]['products'][] = $row;
        }

        foreach ($orders as $order):
            $first_product = $order['products'][0];

            // Group products by name and sum quantities and totals
            $groupedProducts = [];
            foreach ($order['products'] as $product) {
                $name = $product['product_name'];
                if (!isset($groupedProducts[$name])) {
                    $groupedProducts[$name] = [
                        'quantity' => $product['quantity'],
                        'total' => $product['order_total']
                    ];
                } else {
                    $groupedProducts[$name]['quantity'] += $product['quantity'];
                    $groupedProducts[$name]['total'] += $product['order_total'];
                }
            }
    ?>
        <div class="waybill">
            <!-- Header -->
            <div class="waybill-header">
                <h2>Order #<?= $first_product['id'] ?></h2>
                <div class="date"><strong>Date:</strong> <?= date('M d, Y', strtotime($first_product['order_date'])) ?></div>
            </div>

            <!-- Seller & Customer Info -->
            <div class="grid">
                <div>
                    <div class="label">Seller Information:</div>
                    <div>
                        <strong><?= htmlspecialchars($seller['fullname']) ?></strong><br>
                        📍 <?= htmlspecialchars($seller['address']) ?><br>
                        📞 <?= htmlspecialchars($seller['phonenumber']) ?><br>
                        ✉️ <?= htmlspecialchars($seller['email']) ?>
                    </div>
                </div>
                <div>
                    <div class="label">Customer Information:</div>
                    <div>
                        <strong><?= htmlspecialchars($first_product['customer_name']) ?></strong><br>
                        <?= htmlspecialchars($first_product['customer_email']) ?><br>
                        📍 <?= htmlspecialchars($first_product['customer_address'] ?? 'No address provided') ?>
                    </div>
                </div>
            </div>

            <!-- Product Table -->
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
                    <?php foreach ($groupedProducts as $productName => $data): 
                        $price_per_unit = $data['total'] / $data['quantity'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($productName) ?></td>
                            <td><?= $data['quantity'] ?></td>
                            <td>₱<?= number_format($price_per_unit, 2) ?></td>
                            <td>₱<?= number_format($data['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Footer -->
            <div class="grid" style="margin-top: 20px;">
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
    <?php endforeach; ?>

    <button class="btn-print" onclick="window.print()">🖨 Print All Waybills</button>

    <?php else: ?>
        <p style="text-align: center;">No orders ready for waybill.</p>
    <?php endif; ?>

</div>

</body>
</html>
