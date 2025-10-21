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
          case 'assign_rider':
              $riderId = (int)$_POST['rider_id'];
              if ($riderId > 0) {
                  // Get rider name
                  $riderStmt = $conn->prepare("SELECT name FROM riders WHERE id = ? AND status = 'active'");
                  $riderStmt->bind_param("i", $riderId);
                  $riderStmt->execute();
                  $riderResult = $riderStmt->get_result();
                  
                  if ($riderResult->num_rows > 0) {
                      $rider = $riderResult->fetch_assoc();
                      $assignedAt = date('Y-m-d H:i:s');
                      
                      // Update order with rider assignment and mark as shipped
                      $updateStmt = $conn->prepare("UPDATE orders SET status='shipped', rider_id=?, rider_name=?, assigned_at=? WHERE id=?");
                      $updateStmt->bind_param("issi", $riderId, $rider['name'], $assignedAt, $orderId);
                      $updateStmt->execute();
                      $updateStmt->close();
                  }
                  $riderStmt->close();
              }
              break;
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

  // Status filter
  $filter = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : '';
  $allowed = ['pending','processing','shipped','rejected','delivered'];
  if (!in_array($filter, $allowed)) { $filter = ''; }

  // Metric counts across all orders
  $pendingCount = $processingCount = $shippedCount = $rejectedCount = $deliveredCount = 0;
  $countSql = "SELECT LOWER(status) s, COUNT(*) c FROM orders GROUP BY s";
  if ($countRes = $conn->query($countSql)) {
      while ($r = $countRes->fetch_assoc()) {
          switch ($r['s']) {
              case 'pending': $pendingCount = (int)$r['c']; break;
              case 'processing': $processingCount = (int)$r['c']; break;
              case 'shipped': $shippedCount = (int)$r['c']; break;
              case 'rejected': $rejectedCount = (int)$r['c']; break;
              case 'delivered': $deliveredCount = (int)$r['c']; break;
          }
      }
      $countRes->close();
  }

  // Fetch active riders for assignment dropdown
  $riders = [];
  $riderResult = $conn->query("SELECT id, name FROM riders WHERE status = 'active' ORDER BY name ASC");
  if ($riderResult && $riderResult->num_rows > 0) {
      while ($row = $riderResult->fetch_assoc()) {
          $riders[] = $row;
      }
  }

  // Fetch orders (optionally filtered)
  $orders = [];
  $baseSql = "
  SELECT o.id, o.email, u.fullname AS customer_name, o.product_name, o.quantity, o.order_total, o.status, o.rating, o.order_date, o.rider_id, o.rider_name
  FROM orders o
  LEFT JOIN (SELECT DISTINCT email, fullname FROM userdata) u
  ON o.email = u.email";

  if ($filter) {
      $baseSql .= " WHERE LOWER(o.status) = ?";
  }
  $baseSql .= " ORDER BY o.order_date DESC, o.product_name ASC";

  if ($filter) {
      $stmt = $conn->prepare($baseSql);
      $stmt->bind_param("s", $filter);
      $stmt->execute();
      $result = $stmt->get_result();
  } else {
      $result = $conn->query($baseSql);
  }

  if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          $orders[] = $row;
      }
  }
  if (isset($stmt) && $stmt) { $stmt->close(); }
  if ($result instanceof mysqli_result) { $result->close(); }
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
      .metric-link { display: block; text-decoration: none; color: inherit; border: 1px solid transparent; border-radius: 12px; }
      .metric-link.active { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(79,70,229,0.15); }

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
      .status-delivered { background: #d1fae5; color: #065f46; }

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
      
      /* Rider Link Styles */
      .rider-link-container {
        min-width: 300px;
      }
      
      .link-display {
        margin-bottom: 0.5rem;
      }
      
      .link-input {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.8rem;
        font-family: monospace;
        background: #f9fafb;
        color: #374151;
        cursor: pointer;
      }
      
      .link-input:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
      }
      
      .link-actions {
        display: flex;
        gap: 0.3rem;
        flex-wrap: wrap;
      }
      
      .btn-copy {
        background: #10b981;
        color: white;
      }
      
      .btn-copy:hover {
        background: #059669;
      }
      
      .btn-copy.copied {
        background: #22c55e;
      }
      
      .btn-open {
        background: #f59e0b;
        color: white;
        text-decoration: none;
      }
      
      .btn-open:hover {
        background: #d97706;
      }
      
      .btn-share {
        background: #6366f1;
        color: white;
      }
      
      .btn-share:hover {
        background: #4f46e5;
      }
  </style>
  </head>
  <body>
    <!-- HEADER -->
    <header>
      <h1>📦 Order Management</h1>
      <div>
        <button class="btn" onclick="location.href='/Caps/Admin/index.php'">← Dashboard</button>
        <button class="btn" onclick="location.href='riders.php'">🏍️ Riders</button>
        <button class="btn" onclick="location.href='waybill.php'">Way Bills</button>
      </div>
    </header>

    <!-- METRICS -->
    <div class="metrics">
      <a class="metric metric-link <?= $filter==='pending' ? 'active' : '' ?>" href="?status=pending"><h2><?= $pendingCount ?></h2><p>Pending</p></a>
      <a class="metric metric-link <?= $filter==='processing' ? 'active' : '' ?>" href="?status=processing"><h2><?= $processingCount ?></h2><p>Processing</p></a>
      <a class="metric metric-link <?= $filter==='shipped' ? 'active' : '' ?>" href="?status=shipped"><h2><?= $shippedCount ?></h2><p>Shipped</p></a>
      <a class="metric metric-link <?= $filter==='delivered' ? 'active' : '' ?>" href="?status=delivered"><h2><?= $deliveredCount ?></h2><p>Delivered</p></a>
      <a class="metric metric-link <?= $filter==='rejected' ? 'active' : '' ?>" href="?status=rejected"><h2><?= $rejectedCount ?></h2><p>Rejected</p></a>
    </div>
    <?php if ($filter): ?>
    <div class="table-container" style="margin-top:0.5rem;">
      <div style="display:flex; align-items:center; justify-content:space-between;">
        <div><strong>Filter:</strong> <?= ucfirst($filter) ?></div>
        <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>" style="text-decoration:none; color:#2563eb;">Clear</a>
      </div>
    </div>
    <?php endif; ?>

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
            <th>Rider</th>
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
                <?php if ($order['rider_name']): ?>
                  <span style="color: #059669; font-weight: 600;"><?= htmlspecialchars($order['rider_name']) ?></span>
                <?php else: ?>
                  <span style="color: #9ca3af;">—</span>
                <?php endif; ?>
              </td>
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
                  <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                      <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                      <select name="rider_id" required style="padding: 0.4rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.8rem;">
                        <option value="">Select Rider</option>
                        <?php foreach ($riders as $rider): ?>
                          <option value="<?= $rider['id'] ?>"><?= htmlspecialchars($rider['name']) ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" name="action" value="assign_rider" class="btn-action btn-ship">Assign & Ship</button>
                    </form>
                  </div>
                <?php elseif(strtolower($order['status']) === 'shipped'): ?>
                  <?php 
                    // Generate rider confirmation link
                    $rider_token = md5($order['id'] . 'delivery_secret_key_2024');
                    $rider_link = '/Caps/rider_confirm.php?order=' . $order['id'] . '&token=' . $rider_token;
                    $full_link = 'http://' . $_SERVER['HTTP_HOST'] . $rider_link;
                  ?>
                  <div class="rider-link-container">
                    <div class="link-display">
                      <input type="text" value="<?= $full_link ?>" readonly class="link-input" id="link-<?= $order['id'] ?>">
                    </div>
                    <div class="link-actions">
                      <button onclick="copyRiderLink('<?= $order['id'] ?>', '<?= $full_link ?>')" class="btn-action btn-copy" id="copy-btn-<?= $order['id'] ?>">📋 Copy Link</button>
                      <a href="<?= $rider_link ?>" target="_blank" class="btn-action btn-open">📱 Open</a>
                      <button onclick="shareLink('<?= $full_link ?>')" class="btn-action btn-share">📤 Share</button>
                    </div>
                  </div>
                <?php else: ?>
                  <span style="color:#9ca3af;">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="10" style="text-align:center; color:#9ca3af;">No orders found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  
  <script>
    // Copy rider link function
    function copyRiderLink(orderId, fullLink) {
        const linkInput = document.getElementById('link-' + orderId);
        const copyBtn = document.getElementById('copy-btn-' + orderId);
        
        // Select the input text
        linkInput.select();
        linkInput.setSelectionRange(0, 99999); // For mobile devices
        
        // Try modern clipboard API first
        if (navigator.clipboard) {
            navigator.clipboard.writeText(fullLink).then(function() {
                showCopySuccess(copyBtn);
            }).catch(function() {
                // Fallback to execCommand
                fallbackCopy(linkInput, copyBtn);
            });
        } else {
            // Fallback for older browsers
            fallbackCopy(linkInput, copyBtn);
        }
    }
    
    // Fallback copy method
    function fallbackCopy(linkInput, copyBtn) {
        try {
            document.execCommand('copy');
            showCopySuccess(copyBtn);
        } catch (err) {
            // Last resort - show prompt
            prompt('Copy this rider link:', linkInput.value);
        }
    }
    
    // Show copy success feedback
    function showCopySuccess(btn) {
        const originalText = btn.innerHTML;
        const originalClass = btn.className;
        
        btn.innerHTML = '✅ Copied!';
        btn.className = originalClass + ' copied';
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.className = originalClass;
        }, 2000);
    }
    
    // Share link function
    function shareLink(link) {
        if (navigator.share) {
            // Use native share API if available
            navigator.share({
                title: 'Rider Confirmation Link',
                text: 'Please use this link to confirm delivery:',
                url: link
            }).catch(function(err) {
                console.log('Share failed:', err);
                fallbackShare(link);
            });
        } else {
            fallbackShare(link);
        }
    }
    
    // Fallback share method
    function fallbackShare(link) {
        const message = encodeURIComponent('Please use this link to confirm delivery: ' + link);
        
        // Create share options
        const shareOptions = [
            { name: 'WhatsApp', url: 'https://wa.me/?text=' + message },
            { name: 'Telegram', url: 'https://t.me/share/url?url=' + encodeURIComponent(link) + '&text=' + encodeURIComponent('Please use this link to confirm delivery:') },
            { name: 'SMS', url: 'sms:?body=' + message },
            { name: 'Email', url: 'mailto:?subject=Rider Confirmation Link&body=' + message }
        ];
        
        // Show share options
        let shareMenu = 'Choose sharing method:\n\n';
        shareOptions.forEach((option, index) => {
            shareMenu += (index + 1) + '. ' + option.name + '\n';
        });
        
        const choice = prompt(shareMenu + '\nEnter number (1-4) or press Cancel to copy link:');
        const choiceNum = parseInt(choice);
        
        if (choiceNum >= 1 && choiceNum <= 4) {
            window.open(shareOptions[choiceNum - 1].url, '_blank');
        } else if (choice !== null) {
            // Copy to clipboard as fallback
            navigator.clipboard.writeText(link).then(function() {
                alert('Link copied to clipboard!');
            }).catch(function() {
                prompt('Copy this link:', link);
            });
        }
    }
    
    // Auto-select link when clicked
    document.addEventListener('DOMContentLoaded', function() {
        const linkInputs = document.querySelectorAll('.link-input');
        linkInputs.forEach(function(input) {
            input.addEventListener('click', function() {
                this.select();
                this.setSelectionRange(0, 99999);
            });
        });
    });
  </script>
  </body>
  </html>
