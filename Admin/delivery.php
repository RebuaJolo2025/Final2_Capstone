<?php
session_start();
if (!isset($_SESSION['email'])) {
  header('Location: /Caps/Admin/login.php');
  exit;
}
include '../conn.php';

function safe($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Handle status updates via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
  $orderId = (int)$_POST['order_id'];
  $action = strtolower(trim($_POST['action']));
  $newStatus = '';
  if ($action === 'ship') $newStatus = 'shipped';
  if ($action === 'deliver') $newStatus = 'delivered';
  if ($newStatus) {
    $stmt = $conn->prepare("UPDATE orders SET status=?, order_date=order_date WHERE id=?");
    if ($stmt) {
      $stmt->bind_param('si', $newStatus, $orderId);
      $stmt->execute();
      $stmt->close();
    }
  }
  header('Location: '.$_SERVER['PHP_SELF']);
  exit;
}

// Metrics & data
$counts = ['pending'=>0,'processing'=>0,'shipped'=>0,'delivered'=>0,'rejected'=>0];
if ($res = $conn->query("SELECT LOWER(status) s, COUNT(*) c FROM orders GROUP BY s")) {
  while ($r = $res->fetch_assoc()) {
    $s = $r['s']; $c = (int)$r['c'];
    if (isset($counts[$s])) $counts[$s] = $c;
  }
  $res->close();
}
$totalOrders = array_sum($counts);

// Monthly delivered counts last 10 months
$monthLabels = []; $monthDelivered = [];
for ($i = 9; $i >= 0; $i--) {
  $ym = date('Y-m', strtotime("-{$i} months"));
  $label = date('M', strtotime("-{$i} months"));
  $q = $conn->query("SELECT COUNT(*) c FROM orders WHERE LOWER(status)='delivered' AND DATE_FORMAT(order_date,'%Y-%m')='{$ym}'");
  $c = 0; if ($q) { $c = (int)($q->fetch_assoc()['c'] ?? 0); }
  $monthLabels[] = $label; $monthDelivered[] = $c;
}

// Recent deliveries (latest 20 rows)
$recent = [];
$sql = "SELECT o.id, COALESCE(u.fullname, o.email) AS customer, o.status, DATE(o.order_date) AS date
        FROM orders o
        LEFT JOIN (SELECT DISTINCT email, fullname FROM userdata) u ON u.email=o.email
        ORDER BY o.order_date DESC, o.id DESC
        LIMIT 20";
if ($res = $conn->query($sql)) {
  while ($r = $res->fetch_assoc()) {
    $recent[] = [
      'id' => (int)$r['id'],
      'customer' => $r['customer'] ?? 'N/A',
      'status' => ucfirst(strtolower($r['status'] ?? 'pending')),
      'date' => $r['date'] ?? ''
    ];
  }
  $res->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>RTW Analytics Hub - Deliveries</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  :root { --bg:#f8fafc; --card:#ffffff; --accent:#4f46e5; --muted:#6b7280; --success:#16a34a; --warning:#eab308; --danger:#dc2626; --info:#0284c7; }
  body { font-family:"Segoe UI",Arial,sans-serif; margin:0; background:var(--bg); color:#111827; }
  header { background:linear-gradient(90deg,var(--accent),#4338ca); color:white; padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; }
  header h1 { margin:0; font-size:1.25rem; }
  .back-btn { background:rgba(255,255,255,0.12); color:white; border:0; padding:0.45rem 0.9rem; border-radius:8px; cursor:pointer; font-weight:600; }
  .container { max-width:1200px; margin:1.25rem auto; padding:0 1rem; }
  .metrics { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-bottom:1rem; }
  .card { background:var(--card); border-radius:12px; padding:1rem; box-shadow:0 6px 18px rgba(17,24,39,0.06); }
  .metric-title { color:var(--muted); font-size:0.85rem; margin-bottom:0.35rem; }
  .metric-value { font-size:1.4rem; font-weight:700; }
  .grid { display:grid; grid-template-columns:1fr 420px; gap:1rem; margin-bottom:1rem; }
  @media(max-width:980px){ .grid{grid-template-columns:1fr;} }
  .chart-card { padding:1rem; border-radius:12px; background:var(--card); box-shadow:0 6px 18px rgba(17,24,39,0.06); }
  .table-card{ overflow:auto; }
  table{ width:100%; border-collapse:collapse; min-width:700px; }
  thead th{ text-align:left; font-weight:700; padding:0.75rem; background:#f3f4f6; color:#374151; font-size:0.9rem; }
  tbody td{ padding:0.7rem; border-bottom:1px solid #eef2f7; font-size:0.9rem; }
  tbody tr:nth-child(even){ background:#fbfdff; }
  .badge{ display:inline-block; padding:6px 10px; border-radius:999px; font-weight:700; font-size:0.8rem; }
  .badge.pending{ background:#fef9c3; color:#92400e; }
  .badge.shipped{ background:#e0f2fe; color:var(--info); }
  .badge.delivered{ background:#ecfdf5; color:var(--success); }
  .badge.failed{ background:#fee2e2; color:var(--danger); }
  .btn { background:var(--accent); color:white; border:0; padding:6px 10px; border-radius:8px; cursor:pointer; font-weight:700; font-size:0.85rem; }
  .btn.secondary { background:#eef2ff; color:var(--accent); }
  .actions button{ margin-right:6px; }
  .small-muted{ color:var(--muted); font-size:0.85rem; }
</style>
</head>
<body>

<header>
  <h1>Delivery Status</h1>
  <div>
    <button class="back-btn" onclick="location.href='/Caps/Admin/index.php'">← Back to Dashboard</button>
  </div>
</header>

<div class="container">
  <!-- Metrics -->
  <div class="metrics">
    <div class="card"><div class="metric-title">Total Orders</div><div class="metric-value" id="totalOrders"><?= (int)$totalOrders ?></div></div>
    <div class="card"><div class="metric-title">Pending</div><div class="metric-value" id="pendingOrders"><?= (int)$counts['pending'] ?></div></div>
    <div class="card"><div class="metric-title">Shipped</div><div class="metric-value" id="shippedOrders"><?= (int)$counts['shipped'] ?></div></div>
    <div class="card"><div class="metric-title">Delivered</div><div class="metric-value" id="deliveredOrders"><?= (int)$counts['delivered'] ?></div></div>
  </div>

  <!-- Charts + Table -->
  <div class="grid">
    <div>
      <style>
  #statusChart { width: 100% !important; height: 300px !important; }
  #monthlyChart { width: 100% !important; height: 220px !important; }
  .chart-card { max-width: 100%; }
</style>

<div class="chart-card card">
  <strong>Deliveries by Status</strong>
  <div class="small-muted" style="margin-bottom:0.6rem">Overview of all active deliveries</div>
  <canvas id="statusChart"></canvas>
</div>


      <div style="height: 10px;"></div>

      <div class="chart-card card">
        <strong>Monthly Deliveries</strong>
        <div class="small-muted" style="margin-bottom:0.6rem">Completed deliveries by month</div>
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>

    <div class="card table-card">
      <strong>Recent Deliveries</strong>
      <div class="small-muted" style="margin-bottom:0.6rem">Update delivery progress</div>
      <table id="deliveryTable">
        <thead>
          <tr><th>Order ID</th><th>Customer</th><th>Status</th><th>Date</th><th style="text-align:right">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $d): ?>
            <tr>
              <td>#<?= (int)$d['id'] ?></td>
              <td><?= safe($d['customer']) ?></td>
              <?php $low = strtolower($d['status']); $cls = in_array($low,['pending','shipped','delivered']) ? $low : 'failed'; ?>
              <td><span class="badge <?= $cls ?>"><?= safe(ucfirst($low)) ?></span></td>
              <td><?= safe($d['date']) ?></td>
              <td style="text-align:right">
                <?php if ($low === 'pending'): ?>
                  <form method="POST" style="display:inline"><input type="hidden" name="order_id" value="<?= (int)$d['id'] ?>"><input type="hidden" name="action" value="ship"><button class="btn" type="submit">Ship</button></form>
                <?php elseif ($low === 'shipped'): ?>
                  <form method="POST" style="display:inline"><input type="hidden" name="order_id" value="<?= (int)$d['id'] ?>"><input type="hidden" name="action" value="deliver"><button class="btn secondary" type="submit">Mark Delivered</button></form>
                <?php else: ?>
                  <button class="btn secondary" type="button" onclick="alert('Order #<?= (int)$d['id'] ?> for <?= safe($d['customer']) ?> on <?= safe($d['date']) ?>')">View</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const statusCounts = {
  pending: <?= (int)$counts['pending'] ?>,
  shipped: <?= (int)$counts['shipped'] ?>,
  delivered: <?= (int)$counts['delivered'] ?>,
  failed: <?= (int)$counts['rejected'] ?>
};

const monthLabels = <?= json_encode($monthLabels) ?>;
const monthDelivered = <?= json_encode($monthDelivered) ?>;

let statusChart, monthlyChart;

function createCharts(){
  statusChart = new Chart(document.getElementById('statusChart').getContext('2d'),{
    type:'doughnut',
    data:{labels:['Pending','Shipped','Delivered','Failed'],datasets:[{data:[statusCounts.pending,statusCounts.shipped,statusCounts.delivered,statusCounts.failed],backgroundColor:['#facc15','#38bdf8','#22c55e','#ef4444']}]},
    options:{
      responsive:true,
      maintainAspectRatio:false,
      cutout:'60%',
      plugins:{legend:{position:'bottom'}}
    }
  });

  monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'),{
    type:'bar',
    data:{labels:monthLabels,datasets:[{label:'Deliveries',data:monthDelivered,backgroundColor:getComputedStyle(document.documentElement).getPropertyValue('--accent')}]},
    options:{
      responsive:true,
      maintainAspectRatio:false,
      scales:{y:{beginAtZero:true}}
    }
  });
}

document.addEventListener('DOMContentLoaded', createCharts);
</script>
</body>
</html>
