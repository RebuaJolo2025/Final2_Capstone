  <?php
  session_start();
  include '../conn.php'; // Database connection

  // Handle order actions
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

  // Fetch orders
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
  <style>
      :root {
        --primary: #4f46e5;
        --primary-dark: #4338ca;
        --danger: #ef4444;
        --success: #22c55e;
        --warning: #f59e0b;
        --info: #3b82f6;
        --bg: #f9fafb;
        --card: #ffffff;
        --muted: #6b7280;
      }
      body {
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
        background: var(--bg);
        color: #111827;
      }
      header {
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 1.2rem 2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      }
      header h1 { font-size: 1.4rem; margin: 0; }
      header .btn {
        background: rgba(255,255,255,0.15);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: 0.2s;
      }
      header .btn:hover { background: rgba(255,255,255,0.3); }

      /* Metric Cards */
      .metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.2rem;
        margin: 1.5rem;
      }
      .metric {
        background: var(--card);
        padding: 1.2rem;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s;
      }
      .metric:hover { transform: translateY(-3px); }
      .metric h2 {
        margin: 0;
        color: var(--primary);
        font-size: 1.6rem;
      }
      .metric p { margin: 0.4rem 0 0; font-size: 0.9rem; color: var(--muted); }

      /* Orders Table */
      .table-container {
        margin: 1.5rem;
        background: var(--card);
        padding: 1.2rem;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        overflow-x: auto;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      th, td {
        padding: 0.9rem;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        font-size: 0.9rem;
      }
      th {
        background: #f3f4f6;
        font-weight: 600;
      }
      tr:hover td { background: #f9fafb; }

      /* Status Badges */
      .status-badge {
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
      }
      .status-pending { background: #fef3c7; color: #92400e; }
      .status-processing { background: #dbeafe; color: #1e40af; }
      .status-shipped { background: #dcfce7; color: #166534; }
      .status-rejected { background: #fee2e2; color: #991b1b; }

      /* Action Buttons */
      .btn-action {
        border: none;
        padding: 0.45rem 0.8rem;
        border-radius: 8px;
        font-size: 0.8rem;
        cursor: pointer;
        margin: 0 0.2rem;
        font-weight: 600;
        transition: 0.2s;
      }
      .btn-accept { background: var(--success); color: white; }
      .btn-reject { background: var(--danger); color: white; }
      .btn-ship { background: var(--info); color: white; }
      .btn-accept:hover { background: #16a34a; }
      .btn-reject:hover { background: #dc2626; }
      .btn-ship:hover { background: #2563eb; }
  </style>
  </head>
  <body>
    <!-- HEADER -->
    <header>
      <h1>📦 Order Management</h1>
      <div>
        <button class="btn" onclick="location.href='/Caps/Admin/index.html'">← Dashboard</button>
        <button class="btn" onclick="location.href='waybill.php'">Way Bills</button>
      </div>
    </header>

    <!-- METRICS -->
    <div class="metrics">
      <div class="metric"><h2><?= $pendingCount ?></h2><p>Pending Orders</p></div>
      <div class="metric"><h2><?= $processingCount ?></h2><p>Processing</p></div>
      <div class="metric"><h2><?= $shippedCount ?></h2><p>Shipped</p></div>
      <div class="metric"><h2><?= $rejectedCount ?></h2><p>Rejected</p></div>
    </div>

    <!-- TABLE -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Status</th>
            <th>Rating</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($orders)): ?>
            <?php foreach($orders as $order): ?>
            <tr>
              <td>#<?= $order['id'] ?></td>
              <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($order['product_name']) ?></td>
              <td><?= $order['quantity'] ?></td>
              <td>₱<?= number_format($order['order_total'],2) ?></td>
              <td><span class="status-badge status-<?= strtolower($order['status']) ?>"><?= ucfirst($order['status']) ?></span></td>
              <td>
                <?php if($order['rating']): ?>
                  <?php
                    $stars = str_repeat('★', $order['rating']) . str_repeat('☆', 5 - $order['rating']);
                    echo '<span style="color:#f59e0b;">'.$stars.'</span>';
                  ?>
                <?php else: ?>
                  <span style="color:#9ca3af;">No rating</span>
                <?php endif; ?>
              </td>
              <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
              <td>
                <?php if(strtolower($order['status']) === 'pending'): ?>
                  <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" name="action" value="accept" class="btn-action btn-accept">Accept</button>
                  </form>
                  <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" name="action" value="reject" class="btn-action btn-reject">Reject</button>
                  </form>
                <?php elseif(strtolower($order['status']) === 'processing'): ?>
                  <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button type="submit" name="action" value="shipped" class="btn-action btn-ship">Shipped</button>
                  </form>
                <?php else: ?>
                  <span style="color:#9ca3af;">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="9" style="text-align:center; color:#9ca3af;">No orders found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </body>
  </html>
