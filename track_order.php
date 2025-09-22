<?php
session_start();
include 'conn.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Handle deletion
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    $verifyStmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND email = ?");
    $verifyStmt->bind_param("is", $order_id, $email);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();

    if ($verifyResult->num_rows > 0) {
        $deleteStmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $deleteStmt->bind_param("i", $order_id);
        $deleteStmt->execute();
    }

    header("Location: track_order.php");
    exit();
}

// Handle cancellation
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // Only allow cancel if it's user's order and still Pending or Processing
    $checkStmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND email = ? AND status IN ('Pending', 'Processing')");
    $checkStmt->bind_param("is", $order_id, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $cancelStmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
        $cancelStmt->bind_param("i", $order_id);
        $cancelStmt->execute();
    }

    header("Location: track_order.php");
    exit();
}

// Fetch orders
$stmt = $conn->prepare("SELECT id, product_name, quantity, order_total, status, order_date 
                        FROM orders 
                        WHERE email = ? ORDER BY id DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Track My Orders</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; }
        .container { max-width: 900px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0px 5px 20px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: center; }
        th { background: #4CAF50; color: white; }
        .tracker { display: flex; justify-content: center; gap: 5px; margin-top: 5px; flex-wrap: wrap; }
        .step { flex: 1; padding: 5px 8px; font-size: 12px; border-radius: 4px; background-color: #ccc; color: white; }
        .active { background-color: #4CAF50; }
        .cancelled { background-color: #e53935; }
        .delete-button, .cancel-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
        }
        .delete-button { background-color: #f44336; }
        .delete-button:hover { background-color: #d32f2f; }
        .cancel-button { background-color: #ff9800; }
        .cancel-button:hover { background-color: #e67e22; }
        .status-info { margin: 10px 0 30px 0; background: #eef4ed; padding: 15px; border-radius: 10px; font-size: 14px; }
        .status-info ul { margin: 0; padding-left: 20px; }
    </style>
</head>
<body>
<div class="container">
<h2>My Orders</h2>
<a href="profile.php" style="display:block;text-align:center;margin-bottom:20px;">⬅ Back to Profile</a>


<?php if ($result->num_rows > 0): ?>
<table>
    <tr>
        <th>Order ID</th>
        <th>Product</th>
        <th>Qty</th>
        <th>Total Price</th>
        <th>Status</th>
        <th>Order Date</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td><?= htmlspecialchars($row['quantity']) ?></td>
        <td>₱<?= number_format($row['order_total'], 2) ?></td>
        <td>
            <?= htmlspecialchars($row['status']) ?>
            <div class="tracker">
                <?php
                    $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered'];
                    $currentStatus = $row['status'];
                    if ($currentStatus === 'Cancelled') {
                        echo '<div class="step cancelled">Cancelled</div>';
                    } else {
                        foreach ($statuses as $step) {
                            $class = array_search($step, $statuses) <= array_search($currentStatus, $statuses) ? 'step active' : 'step';
                            echo "<div class=\"$class\">$step</div>";
                        }
                    }
                ?>
            </div>
        </td>
        <td><?= date('M d, Y h:i A', strtotime($row['order_date'])) ?></td>
        <td>
            <?php if ($row['status'] !== 'Delivered' && $row['status'] !== 'Cancelled'): ?>
                <!-- Cancel Button -->
                <form method="POST" action="" style="margin-bottom:8px;" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="cancel_order" class="cancel-button">Cancel Order</button>
                </form>
                <!-- Delete Button -->
                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this order?');">
                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="delete_order" class="delete-button">Delete Order</button>
                </form>
            <?php else: ?>
                <span style="color:gray;">N/A</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;color:red;">You have no orders yet.</p>
<?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
