<?php
include '../conn.php';
$result = mysqli_query($conn, "SELECT * FROM orders WHERE LOWER(status)='pending'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Notifications - Pending Orders</title>
<style>
body { font-family: Arial; padding: 20px; background: #f9f9f9; }
h2 { color: #333; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
th { background-color: #4CAF50; color: white; }
tr:nth-child(even) { background-color: #f2f2f2; }
</style>
</head>
<body>
<h2>Pending Orders</h2>
<table>
<tr>
  <th>ID</th><th>Customer</th><th>Product</th><th>Quantity</th><th>Status</th>
</tr>
<?php while ($row = mysqli_fetch_assoc($result)): ?>
<tr>
  <td><?= $row['id'] ?></td>
  <td><?= htmlspecialchars($row['email']) ?></td>
  <td><?= htmlspecialchars($row['product_name']) ?></td>
  <td><?= $row['quantity'] ?></td>
  <td><?= ucfirst($row['status']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
