<?php
session_start();
include '../conn.php';

// Handle rider actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_rider'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        
        if ($name && $phone) {
            $stmt = $conn->prepare("INSERT INTO riders (name, phone, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $phone, $email);
            $stmt->execute();
            $stmt->close();
        }
    } elseif (isset($_POST['toggle_status'])) {
        $rider_id = (int)$_POST['rider_id'];
        $new_status = $_POST['current_status'] === 'active' ? 'inactive' : 'active';
        
        $stmt = $conn->prepare("UPDATE riders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $rider_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch riders
$riders = [];
$result = $conn->query("SELECT * FROM riders ORDER BY name ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $riders[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rider Management</title>
    <style>
        :root {
            --primary: #4f46e5;
            --success: #22c55e;
            --danger: #ef4444;
            --bg: #f9fafb;
            --card: #ffffff;
        }
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            background: var(--bg);
            color: #111827;
        }
        header {
            background: linear-gradient(90deg, var(--primary), #4338ca);
            color: white;
            padding: 1.2rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            background: var(--card);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        .form-group input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-success {
            background: var(--success);
            color: white;
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }
        .btn-danger {
            background: var(--danger);
            color: white;
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-active {
            background: #dcfce7;
            color: #166534;
        }
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <header>
        <h1>🏍️ Rider Management</h1>
        <a href="orders.php" class="btn btn-primary">← Back to Orders</a>
    </header>
    
    <div class="container">
        <!-- Add Rider Form -->
        <div class="card">
            <h2>Add New Rider</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Rider Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Email (Optional)</label>
                        <input type="email" name="email">
                    </div>
                    <button type="submit" name="add_rider" class="btn btn-primary">Add Rider</button>
                </div>
            </form>
        </div>
        
        <!-- Riders List -->
        <div class="card">
            <h2>All Riders (<?= count($riders) ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($riders)): ?>
                        <?php foreach ($riders as $rider): ?>
                            <tr>
                                <td>#<?= $rider['id'] ?></td>
                                <td><?= htmlspecialchars($rider['name']) ?></td>
                                <td><?= htmlspecialchars($rider['phone']) ?></td>
                                <td><?= htmlspecialchars($rider['email'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="status-badge status-<?= $rider['status'] ?>">
                                        <?= ucfirst($rider['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="rider_id" value="<?= $rider['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $rider['status'] ?>">
                                        <button type="submit" name="toggle_status" 
                                                class="btn <?= $rider['status'] === 'active' ? 'btn-danger' : 'btn-success' ?>">
                                            <?= $rider['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #9ca3af;">No riders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>