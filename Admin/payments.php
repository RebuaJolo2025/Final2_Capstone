<?php
session_start();
include '../conn.php';

// Ensure user is logged in if your admin requires auth (optional)
// if (!isset($_SESSION['email'])) { header('Location: login.php'); exit; }

function formatCurrency($amount) {
  return '₱' . number_format((float)$amount, 2);
}

// Helpers
$SUCCESS_STATUSES = ['delivered', 'completed', 'success'];

// Fetch recent payments (latest 50 orders)
$payments = [];
$sql = "
  SELECT o.id, o.email, o.product_name, o.quantity, o.order_total, o.payment_method, o.status, o.order_date,
         u.fullname AS customer_name
  FROM orders o
  LEFT JOIN (SELECT DISTINCT email, fullname FROM userdata) u ON u.email = o.email
  ORDER BY o.order_date DESC
  LIMIT 50
";
if ($res = $conn->query($sql)) {
  while ($row = $res->fetch_assoc()) {
    $payments[] = $row;
  }
  $res->close();
}

// Aggregate metrics
$codCount = 0; $onlineCount = 0; $grandTotal = 0.0;
$monthLabels = [];
$codByMonth = array_fill(0, 12, 0);
$onlineByMonth = array_fill(0, 12, 0);

// Build labels for last 12 months starting Jan..Dec for chart display convenience
for ($i = 0; $i < 12; $i++) { $monthLabels[$i] = date('M', mktime(0,0,0,$i+1,1)); }

foreach ($payments as $p) {
  $status = strtolower(trim($p['status'] ?? ''));
  $method = strtolower(trim($p['payment_method'] ?? ''));
  $amount = (float)($p['order_total'] ?? 0);
  $ts = strtotime($p['order_date'] ?? '');
  $monthIndex = $ts ? (int)date('n', $ts) - 1 : null; // 0..11

  // Count successful only for KPIs/total
  if (in_array($status, $SUCCESS_STATUSES, true)) {
    $grandTotal += $amount;
    if ($method === 'cod') { $codCount++; if ($monthIndex !== null) { $codByMonth[$monthIndex] += 1; } }
    else if ($method === 'gcash' || $method === 'paymaya' || $method === 'bank_transfer' || $method === 'online') {
      $onlineCount++; if ($monthIndex !== null) { $onlineByMonth[$monthIndex] += 1; }
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RTW Analytics Hub - Payments</title>
  <link rel="stylesheet" href="styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
  <style>
    body {
      font-family: "Inter", sans-serif;
      background: #f8fafc;
      color: #333;
    }
    .dashboard-header { margin-bottom: 2rem; }
    .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
    .metric-card { background: #fff; border-radius: 12px; padding: 1.2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .metric-title { font-size: 0.9rem; color: #666; margin-bottom: 0.5rem; }
    .metric-value { font-size: 1.5rem; font-weight: bold; color: #222; }
    .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .chart-card { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .chart-card h3 { font-size: 1rem; margin-bottom: 1rem; font-weight: 600; color: #444; }
    .table-card { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    table { width: 100%; border-collapse: collapse; }
    thead { background: #f1f5f9; }
    th, td { padding: 0.9rem 0.75rem; font-size: 0.9rem; text-align: left; }
    th { color: #555; font-weight: 600; }
    tr:nth-child(even) { background: #f9fafb; }
    .status { padding: 6px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
    .status.success { background:#e6f9ed; color:#16a34a; }
    .status.pending { background:#fff4e6; color:#ea580c; }
    .status.failed { background:#fde2e1; color:#dc2626; }
    .method { font-weight: 600; color: #2563eb; text-transform: uppercase; }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div class="brand">
          <div class="brand-icon"><i data-lucide="bar-chart-3"></i></div>
          <div class="brand-text"><h2>RTW Admin</h2><p>Payments</p></div>
        </div>
      </div>
      <nav class="sidebar-nav">
        <ul class="nav-list">
          <li><a href="/Caps/Admin/index.php" class="nav-link"><i data-lucide="home"></i><span>Dashboard</span></a></li>
          <li><a href="/Caps/Admin/payments.php" class="nav-link active"><i data-lucide="credit-card"></i><span>Payments</span></a></li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="dashboard-header">
        <h1>Payments Overview</h1>
      </header>

      <!-- Metrics -->
      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-title">Total Payments</div>
          <div class="metric-value" id="totalPaymentsValue"><?= formatCurrency($grandTotal) ?></div>
        </div>
        <div class="metric-card">
          <div class="metric-title">COD Customers</div>
          <div class="metric-value" id="codCount"><?= (int)$codCount ?></div>
        </div>
        <div class="metric-card">
          <div class="metric-title">Online Customers</div>
          <div class="metric-value" id="onlineCount"><?= (int)$onlineCount ?></div>
        </div>
        <div class="metric-card">
          <div class="metric-title">Refunded</div>
          <div class="metric-value">₱0.00</div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-grid">
        <div class="chart-card">
          <h3>COD vs Online Distribution</h3>
          <canvas id="codVsOnlineChart"></canvas>
        </div>
        <div class="chart-card">
          <h3>Payments Trend</h3>
          <canvas id="paymentsTrendChart"></canvas>
        </div>
      </div>

      <!-- Table -->
      <div class="table-card">
        <h3>Recent Payments</h3>
        <table id="paymentsTable">
          <thead>
            <tr>
              <th>ID</th><th>Customer</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($payments)): ?>
              <?php foreach ($payments as $row): ?>
                <?php
                  $status = strtolower($row['status']);
                  $statusClass = ($status === 'delivered' || $status === 'completed' || $status === 'success') ? 'success' : (($status === 'pending' || $status === 'processing' || $status === 'shipped') ? 'pending' : 'failed');
                  $methodLabel = strtoupper($row['payment_method']);
                ?>
                <tr>
                  <td>#<?= htmlspecialchars($row['id']) ?></td>
                  <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                  <td><?= formatCurrency($row['order_total']) ?></td>
                  <td class="method"><?= htmlspecialchars($methodLabel === 'ONLINE' ? 'Online' : $methodLabel) ?></td>
                  <td><span class="status <?= $statusClass ?>"><?php
                    if ($statusClass === 'success') echo 'Completed';
                    elseif ($statusClass === 'pending') echo 'Pending';
                    else echo 'Failed';
                  ?></span></td>
                  <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['order_date']))) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" style="text-align:center; color:#9ca3af;">No payments found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script>
    const codCount = <?= (int)$codCount ?>;
    const onlineCount = <?= (int)$onlineCount ?>;
    const monthLabels = <?= json_encode($monthLabels) ?>;
    const codByMonth = <?= json_encode(array_values($codByMonth)) ?>;
    const onlineByMonth = <?= json_encode(array_values($onlineByMonth)) ?>;

    new Chart(document.getElementById('codVsOnlineChart'), {
      type: 'doughnut',
      data: {
        labels: ['COD', 'Online'],
        datasets: [{ data: [codCount, onlineCount], backgroundColor: ['#f97316', '#22c55e'] }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('paymentsTrendChart'), {
      type: 'line',
      data: {
        labels: monthLabels,
        datasets: [
          { label: 'COD', data: codByMonth, borderColor: '#f97316', fill: false },
          { label: 'Online', data: onlineByMonth, borderColor: '#22c55e', fill: false }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    lucide.createIcons();
  </script>
</body>
</html>
