<?php
session_start();
include 'conn.php'; // your database connection here ($conn)

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Simulate logged-in user (for demo)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

// Create table if not exists
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_total DECIMAL(10,2) NOT NULL,
        order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )
");

// Initialize messages
$message = '';
$error = '';

// Insert order if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_total'])) {
    $userId = $_SESSION['user_id'];
    $orderTotal = $_POST['order_total'];

    // Validate order total (simple float check)
    if (filter_var($orderTotal, FILTER_VALIDATE_FLOAT) !== false && $orderTotal > 0) {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_total) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("id", $userId, $orderTotal);
            if ($stmt->execute()) {
                $message = "Order recorded successfully!";
            } else {
                $error = "Failed to record order: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "Invalid order total value.";
    }
}

// Days in week starting Monday (fixed order)
$allDays = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

// Initialize orders count per day (0 by default)
$ordersByDay = array_fill_keys($allDays, 0);

// Fetch order counts grouped by day name for last 7 days
$sql = "
    SELECT DAYNAME(order_date) AS day, COUNT(*) AS total_orders
    FROM orders
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY day
";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Make sure day name matches our keys (case sensitive)
        $day = $row['day'];
        if (isset($ordersByDay[$day])) {
            $ordersByDay[$day] = (int) $row['total_orders'];
        }
    }
}

// Prepare JSON for JS chart
$lineLabelsJson = json_encode($allDays);
$ordersJson = json_encode(array_values($ordersByDay));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Track Order Purchases</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f4f8;
        padding: 40px 20px;
        margin: 0;
    }
    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 30px;
        font-weight: 700;
        font-size: 2rem;
    }
    form {
        text-align: center;
        margin-bottom: 30px;
    }
    input[type="hidden"] {
        display: none;
    }
    button {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 14px 30px;
        font-size: 1.2rem;
        border-radius: 50px;
        cursor: pointer;
        box-shadow: 0 6px 15px rgba(76,175,80,0.5);
        transition: background 0.3s ease, transform 0.2s ease;
        position: relative;
    }
    button:disabled {
        background: #a5d6a7;
        cursor: not-allowed;
        box-shadow: none;
    }
    button:hover:not(:disabled) {
        background: #43a047;
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(67,160,71,0.7);
    }
    .message {
        text-align: center;
        margin-bottom: 20px;
        font-weight: 600;
        font-size: 1rem;
    }
    .message.success {
        color: #2e7d32;
    }
    .message.error {
        color: #c62828;
    }
    .chart-box {
        max-width: 100%;
        margin: auto;
        padding: 15px;
    }
    canvas {
        width: 100% !important;
        height: 400px !important;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Order Tracking</h2>

    <?php if ($message): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="orderForm" method="POST" action="">
        <input type="hidden" name="order_total" value="59.99" />
        <button type="submit" id="submitBtn">✅ Track This Order Purchase</button>
    </form>

    <div class="chart-box">
        <canvas id="lineChart"></canvas>
    </div>
</div>

<script>
const form = document.getElementById('orderForm');
const submitBtn = document.getElementById('submitBtn');

form.addEventListener('submit', function(e) {
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Tracking...';
});

const ctx = document.getElementById('lineChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= $lineLabelsJson ?>,
        datasets: [{
            label: 'Orders per Day',
            data: <?= $ordersJson ?>,
            borderColor: '#2196F3',
            backgroundColor: 'rgba(33,150,243,0.15)',
            fill: true,
            tension: 0.3,
            borderWidth: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#2196F3',
            pointRadius: 6,
            pointHoverRadius: 8,
            cubicInterpolationMode: 'monotone'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: { size: 14, weight: 'bold' },
                    color: '#333'
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: '#2196F3',
                titleFont: { size: 16, weight: 'bold' },
                bodyFont: { size: 14 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    color: '#666',
                    font: { size: 13 }
                },
                grid: {
                    color: '#eee'
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#666',
                    font: { size: 13 }
                }
            }
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false
        }
    }
});
</script>

</body>
</html>
