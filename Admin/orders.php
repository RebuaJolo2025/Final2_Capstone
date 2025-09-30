<?php
session_start();
include '../conn.php'; // Your database connection file

// Handle Accept/Reject/Shipped actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    $action = '';
    switch ($_POST['action']) {
        case 'accept': $action = 'processing'; break;
        case 'reject': $action = 'rejected'; break;
        case 'shipped': $action = 'shipped'; break;
    }

    if ($action) {
        $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
        $stmt->bind_param("si", $action, $orderId);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Fetch orders from DB
$sql = "
SELECT o.id, u.fullname AS customer_name, o.product_name, o.quantity, o.order_total, o.status, o.rating, o.order_date
FROM orders o
LEFT JOIN (SELECT DISTINCT email, fullname FROM userdata) u
ON o.email = u.email
ORDER BY o.order_date DESC, o.product_name ASC
";

$result = $conn->query($sql);

$orders = [];
$pendingCount = $processingCount = $shippedCount = $rejectedCount = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
        switch (strtolower($row['status'])) {
            case 'pending': $pendingCount++; break;
            case 'processing': $processingCount++; break;
            case 'shipped': $shippedCount++; break;
            case 'rejected': $rejectedCount++; break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Management - Seller Dashboard</title>
<link rel="stylesheet" href="orders.css">
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-flex">
            <div class="header-center">
                <h1>Order Management Dashboard</h1>
                <p>Manage your customer orders efficiently</p>
            </div>
            <div class="header-right">
                <button class="btn-waybills" onclick="window.location.href='waybill.php'">Way Bills</button>
            </div>
            <div class="header-left">
                <button class="btn-waybills" onclick="window.history.back();">← Back</button>
            </div>
        </div>
    </div>



    
    <div class="content">
        <!-- Status Summary -->
        <div class="status-summary">
            <div class="status-card pending">
                <h3><?= $pendingCount ?></h3>
                <p>Pending Orders</p>
            </div>
            <div class="status-card processing">
                <h3><?= $processingCount ?></h3>
                <p>Processing Orders</p>
            </div>
            <div class="status-card shipped">
                <h3><?= $shippedCount ?></h3>
                <p>Shipped Orders</p>
            </div>
            <div class="status-card rejected">
                <h3><?= $rejectedCount ?></h3>
                <p>Rejected Orders</p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="table-container" id="tableContainer">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                        <tr data-status="<?= strtolower($order['status']) ?>">
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td><?= $order['quantity'] ?></td>
                            <td>₱<?= number_format($order['order_total'],2) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($order['rating']): ?>
                                    <?php
                                        $stars = str_repeat('★', $order['rating']) . str_repeat('☆', 5 - $order['rating']);
                                        echo '<div class="rating-display"><span class="rating-stars">'.$stars.'</span><span class="rating-number">('.$order['rating'].'/5)</span></div>';
                                    ?>
                                <?php else: ?>
                                    <span class="no-rating">No rating</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                            <td>
                                <?php if(strtolower($order['status']) === 'pending'): ?>
                                    <div class="action-buttons">
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" name="action" value="accept" class="btn btn-accept">✓ Accept</button>
                                        </form>
                                        <form method="POST" style="display:inline-block;">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-reject">✕ Reject</button>
                                        </form>
                                    </div>
                                <?php elseif(strtolower($order['status']) === 'processing'): ?>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                        <button type="submit" name="action" value="shipped" class="btn btn-ship">Shipped</button>
                                    </form>
                                <?php else: ?>
                                    <span class="no-actions">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="text-align:center;">No orders found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
