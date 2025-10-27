<?php
session_start();
if (!isset($_SESSION['email'])) {
  header('Location: /Caps/Admin/login.php');
  exit;
}
include '../conn.php';

function formatCurrency($amount) {
  return '₱' . number_format((float)$amount, 2);
}

// Metrics based on orders
$currentMonth = date('Y-m');

$totalCustomers = 0; $newThisMonth = 0; $returningCount = 0; $activeProfiles = 0;
try {
  $q1 = $conn->query("SELECT COUNT(DISTINCT email) c FROM orders WHERE status='delivered'");
  $totalCustomers = (int)($q1->fetch_assoc()['c'] ?? 0);

  $q2 = $conn->query("SELECT COUNT(DISTINCT email) c FROM orders WHERE status='delivered' AND DATE_FORMAT(order_date,'%Y-%m')='{$currentMonth}'");
  $newThisMonth = (int)($q2->fetch_assoc()['c'] ?? 0);

  $q3 = $conn->query("SELECT COUNT(*) c FROM (SELECT email, COUNT(*) n FROM orders WHERE status='delivered' GROUP BY email HAVING n>1) t");
  $returningCount = (int)($q3->fetch_assoc()['c'] ?? 0);

  $q4 = $conn->query("SELECT COUNT(DISTINCT email) c FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
  $activeProfiles = (int)($q4->fetch_assoc()['c'] ?? 0);
} catch (Exception $e) {}

$returningPct = $totalCustomers > 0 ? round(($returningCount / $totalCustomers) * 100) : 0;

// Top Customers table
$topCustomers = [];
try {
  $sqlTop = "SELECT COALESCE(u.fullname, o.email) AS name, COUNT(*) AS orders, SUM(o.order_total) AS total
             FROM orders o
             LEFT JOIN (SELECT DISTINCT email, fullname FROM userdata) u ON u.email=o.email
             WHERE o.status='delivered'
             GROUP BY o.email, name
             ORDER BY total DESC
             LIMIT 4";
  if ($res = $conn->query($sqlTop)) {
    while ($r = $res->fetch_assoc()) { $topCustomers[] = $r; }
    $res->close();
  }
} catch (Exception $e) {}

// Profiles by Month (distinct customers per month, last 9 months)
$labelsMonths = []; $dataMonths = [];
for ($i = 8; $i >= 0; $i--) {
  $ym = date('Y-m', strtotime("-{$i} months"));
  $labelsMonths[] = date('M', strtotime("-{$i} months"));
  $cnt = 0;
  $rs = $conn->query("SELECT COUNT(DISTINCT email) c FROM orders WHERE status='delivered' AND DATE_FORMAT(order_date,'%Y-%m')='{$ym}'");
  if ($rs) { $cnt = (int)($rs->fetch_assoc()['c'] ?? 0); }
  $dataMonths[] = $cnt;
}

// Profiles by Location (top 5 addresses)
$labelsLoc = []; $dataLoc = [];
try {
  $sqlLoc = "SELECT COALESCE(NULLIF(TRIM(u.address),''),'Unknown') addr, COUNT(DISTINCT o.email) c
             FROM orders o
             LEFT JOIN userdata u ON u.email=o.email
             GROUP BY addr
             ORDER BY c DESC
             LIMIT 5";
  if ($res = $conn->query($sqlLoc)) {
    while ($r = $res->fetch_assoc()) { $labelsLoc[] = $r['addr']; $dataLoc[] = (int)$r['c']; }
    $res->close();
  }
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RTW Analytics Hub - Profile Reports</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: "Segoe UI", Arial, sans-serif;
      margin: 0;
      background: #f9fafb;
      color: #333;
    }
    header {
      background: linear-gradient(90deg, #4f46e5, #4338ca);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    header h1 { font-size: 1.5rem; margin: 0; }
    .back-btn {
      background: white;
      color: #4f46e5;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s ease;
    }
    .back-btn:hover { background: #e0e7ff; }
    .metrics {
      display: flex;
      justify-content: space-around;
      background: white;
      margin: 1rem;
      padding: 1rem;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .metric { text-align: center; }
    .metric h2 { margin: 0; font-size: 1.4rem; color: #4f46e5; }
    .metric p { margin: 0; font-size: 0.9rem; color: #555; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
      gap: 1rem;
      margin: 1rem;
    }
    .card { background: white; border-radius: 10px; padding: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .card h3 { font-size: 1rem; margin-bottom: 0.5rem; color: #444; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f3f4f6; font-size: 0.9rem; }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <h1>Profile Reports</h1>
    <button class="back-btn" onclick="window.location.href='/Caps/Admin/index.php'">
      ← Back to Dashboard
    </button>
  </header>

  <!-- TOP METRICS -->
  <div class="metrics">
    <div class="metric">
      <h2><?= number_format($totalCustomers) ?></h2>
      <p>Total Customers</p>
    </div>
    <div class="metric">
      <h2><?= number_format($newThisMonth) ?></h2>
      <p>New This Month</p>
    </div>
    <div class="metric">
      <h2><?= $returningPct ?>%</h2>
      <p>Returning Customers</p>
    </div>
    <div class="metric">
      <h2><?= number_format($activeProfiles) ?></h2>
      <p>Active Profiles</p>
    </div>
  </div>

  <!-- CHARTS & ANALYTICS -->
  <div class="grid">
    <div class="card">
      <h3>New Profiles by Month</h3>
      <canvas id="profilesByMonth"></canvas>
    </div>
    <div class="card">
      <h3>Profiles by Location</h3>
      <canvas id="profilesByLocation"></canvas>
    </div>
    <div class="card">
      <h3>Profiles by Gender</h3>
      <canvas id="profilesByGender"></canvas>
    </div>
    <div class="card">
      <h3>Top Customers</h3>
      <table>
        <thead>
          <tr><th>Name</th><th>Orders</th><th>Total Spent</th></tr>
        </thead>
        <tbody>
          <?php if (!empty($topCustomers)): ?>
            <?php foreach ($topCustomers as $tc): ?>
              <tr>
                <td><?= htmlspecialchars($tc['name'] ?? 'Unknown') ?></td>
                <td><?= (int)($tc['orders'] ?? 0) ?></td>
                <td><?= formatCurrency($tc['total'] ?? 0) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="3" style="color:#9ca3af; text-align:center;">No data</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Profiles by Month
    new Chart(document.getElementById("profilesByMonth"), {
      type: "bar",
      data: {
        labels: <?= json_encode($labelsMonths) ?>,
        datasets: [{
          label: "Distinct Customers",
          data: <?= json_encode($dataMonths) ?>,
          backgroundColor: "#4f46e5"
        }]
      },
      options: { responsive: true }
    });

    // Profiles by Location
    new Chart(document.getElementById("profilesByLocation"), {
      type: "bar",
      data: {
        labels: <?= json_encode($labelsLoc) ?>,
        datasets: [{
          label: "Customers",
          data: <?= json_encode($dataLoc) ?>,
          backgroundColor: "#10b981"
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true
      }
    });

    // Profiles by Gender
    new Chart(document.getElementById("profilesByGender"), {
      type: "doughnut",
      data: {
        labels: ["Male", "Female", "Other"],
        datasets: [{
          data: [480, 380, 32],
          backgroundColor: ["#3b82f6", "#ec4899", "#f59e0b"]
        }]
      },
      options: { plugins: { legend: { position: "bottom" } } }
    });
  </script>
</body>
</html>
