<?php
// Include database connection
include '../conn.php';

// Calculate revenue metrics from delivered orders
$currentMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));

try {
    // Total Revenue (all delivered orders)
    $totalRevenueQuery = "SELECT SUM(order_total) as total_revenue FROM orders WHERE status = 'delivered'";
    $totalRevenueResult = $conn->query($totalRevenueQuery);
    $totalRevenue = $totalRevenueResult->fetch_assoc()['total_revenue'] ?? 0;

    // Current Month Revenue
    $currentMonthQuery = "SELECT SUM(order_total) as month_revenue FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$currentMonth'";
    $currentMonthResult = $conn->query($currentMonthQuery);
    $currentMonthRevenue = $currentMonthResult->fetch_assoc()['month_revenue'] ?? 0;

    // Last Month Revenue
    $lastMonthQuery = "SELECT SUM(order_total) as last_month_revenue FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$lastMonth'";
    $lastMonthResult = $conn->query($lastMonthQuery);
    $lastMonthRevenue = $lastMonthResult->fetch_assoc()['last_month_revenue'] ?? 0;

    // Calculate percentage change
    $revenueChange = 0;
    if ($lastMonthRevenue > 0) {
        $revenueChange = (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
    }

    // Total Orders (delivered)
    $totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM orders WHERE status = 'delivered'";
    $totalOrdersResult = $conn->query($totalOrdersQuery);
    $totalOrders = $totalOrdersResult->fetch_assoc()['total_orders'] ?? 0;

    // Current Month Orders
    $currentMonthOrdersQuery = "SELECT COUNT(*) as month_orders FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$currentMonth'";
    $currentMonthOrdersResult = $conn->query($currentMonthOrdersQuery);
    $currentMonthOrders = $currentMonthOrdersResult->fetch_assoc()['month_orders'] ?? 0;

    // Last Month Orders
    $lastMonthOrdersQuery = "SELECT COUNT(*) as last_month_orders FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$lastMonth'";
    $lastMonthOrdersResult = $conn->query($lastMonthOrdersQuery);
    $lastMonthOrders = $lastMonthOrdersResult->fetch_assoc()['last_month_orders'] ?? 0;

    // Calculate orders percentage change
    $ordersChange = 0;
    if ($lastMonthOrders > 0) {
        $ordersChange = (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100;
    }

    // Active Customers (unique customers with delivered orders)
    $activeCustomersQuery = "SELECT COUNT(DISTINCT email) as active_customers FROM orders WHERE status = 'delivered'";
    $activeCustomersResult = $conn->query($activeCustomersQuery);
    $activeCustomers = $activeCustomersResult->fetch_assoc()['active_customers'] ?? 0;

    // Products Sold (total quantity of delivered products)
    $productsSoldQuery = "SELECT SUM(quantity) as products_sold FROM orders WHERE status = 'delivered'";
    $productsSoldResult = $conn->query($productsSoldQuery);
    $productsSold = $productsSoldResult->fetch_assoc()['products_sold'] ?? 0;

    // Current Month Products Sold
    $currentMonthProductsQuery = "SELECT SUM(quantity) as month_products FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$currentMonth'";
    $currentMonthProductsResult = $conn->query($currentMonthProductsQuery);
    $currentMonthProducts = $currentMonthProductsResult->fetch_assoc()['month_products'] ?? 0;

    // Last Month Products Sold
    $lastMonthProductsQuery = "SELECT SUM(quantity) as last_month_products FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$lastMonth'";
    $lastMonthProductsResult = $conn->query($lastMonthProductsQuery);
    $lastMonthProducts = $lastMonthProductsResult->fetch_assoc()['last_month_products'] ?? 0;

    // Calculate products percentage change
    $productsChange = 0;
    if ($lastMonthProducts > 0) {
        $productsChange = (($currentMonthProducts - $lastMonthProducts) / $lastMonthProducts) * 100;
    }

    // Average Order Value
    $avgOrderQuery = "SELECT AVG(order_total) as avg_order FROM orders WHERE status = 'delivered'";
    $avgOrderResult = $conn->query($avgOrderQuery);
    $avgOrderValue = $avgOrderResult->fetch_assoc()['avg_order'] ?? 0;

    // Top Selling Products
    $topProductsQuery = "SELECT product_name, SUM(quantity) as total_sold, SUM(order_total) as total_revenue 
                        FROM orders WHERE status = 'delivered' 
                        GROUP BY product_name 
                        ORDER BY total_sold DESC 
                        LIMIT 5";
    $topProductsResult = $conn->query($topProductsQuery);
    $topProducts = [];
    if ($topProductsResult && $topProductsResult->num_rows > 0) {
        while ($row = $topProductsResult->fetch_assoc()) {
            $topProducts[] = $row;
        }
    }

    // Recent Transactions
    $recentTransactionsQuery = "SELECT o.id, o.product_name, o.order_total, o.order_date, u.fullname as customer_name 
                               FROM orders o 
                               LEFT JOIN (SELECT DISTINCT email, fullname FROM userdata) u ON o.email = u.email 
                               WHERE o.status = 'delivered' 
                               ORDER BY o.order_date DESC 
                               LIMIT 5";
    $recentTransactionsResult = $conn->query($recentTransactionsQuery);
    $recentTransactions = [];
    if ($recentTransactionsResult && $recentTransactionsResult->num_rows > 0) {
        while ($row = $recentTransactionsResult->fetch_assoc()) {
            $recentTransactions[] = $row;
        }
    }

    // Monthly Revenue Data for Chart (last 6 months)
    $monthlyRevenueData = [];
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthName = date('M Y', strtotime("-$i months"));
        
        $monthlyQuery = "SELECT SUM(order_total) as revenue FROM orders WHERE status = 'delivered' AND DATE_FORMAT(order_date, '%Y-%m') = '$month'";
        $monthlyResult = $conn->query($monthlyQuery);
        $monthlyRevenue = $monthlyResult->fetch_assoc()['revenue'] ?? 0;
        
        $monthlyRevenueData[] = [
            'month' => $monthName,
            'revenue' => floatval($monthlyRevenue)
        ];
    }

} catch (Exception $e) {
    // Set default values if database error
    $totalRevenue = 0;
    $revenueChange = 0;
    $totalOrders = 0;
    $ordersChange = 0;
    $activeCustomers = 0;
    $productsSold = 0;
    $productsChange = 0;
    $avgOrderValue = 0;
    $topProducts = [];
    $recentTransactions = [];
    $monthlyRevenueData = [];
    $dbError = $e->getMessage();
}

// Helper function to format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Helper function to format percentage
function formatPercentage($value) {
    $sign = $value >= 0 ? '+' : '';
    return $sign . number_format($value, 1) . '% from last month';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTW Analytics Hub - Admin Dashboard</title>
    <meta name="description" content="Comprehensive admin dashboard for Ready-to-Wear fashion analytics, sales tracking, and business insights">
    <meta name="author" content="RTW Fashion">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <!-- Custom Styles -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Dashboard Layout -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo/Brand -->
            <div class="sidebar-header">
                <div class="brand">
                    <div class="brand-icon">
                        <i data-lucide="bar-chart-3"></i>
                    </div>
                    <div class="brand-text">
                        <h2>RTW Admin</h2>
                        <p>Dashboard</p>
                    </div>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i data-lucide="menu"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3 class="nav-section-title">Analytics</h3>
                    <ul class="nav-list">
                        <li><a href="#dashboard" class="nav-link active" data-page="dashboard">
                            <i data-lucide="home"></i><span>Dashboard</span>
                        </a></li>
                        
                        <li>
                            <a href="/Caps/Admin/Product/product.html" class="nav-link">
                                <i data-lucide="package"></i><span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="/Caps/Admin/payments.html" class="nav-link">
                                <i data-lucide="credit-card"></i><span>Payments</span>
                            </a>
                        </li>
                        <li><a href="/Caps/Admin/orders.php" class="nav-link">
                            <i data-lucide="shopping-bag"></i><span>Transactions</span>
                        </a></li>
                    </ul>
                </div>

                <div class="nav-section">
                    <h3 class="nav-section-title">Management</h3>
                    <ul class="nav-list">
                        <li>
                            <a href="/Caps/Admin/profiles.html" class="nav-link">
                                <i data-lucide="users"></i><span>Profile Reports</span>
                            </a>
                        </li> 
                        <li>
                            <a href="/Caps/Admin/refunds.html" class="nav-link">
                                <i data-lucide="refresh-cw"></i>
                                <span>Refunds</span>
                            </a>
                        </li>
                        <li><a href="/Caps/Admin/delivery.html" class="nav-link">
                            <i data-lucide="truck"></i><span>Delivery Status</span>
                        </a></li>
                        <li>
                            <a href="/Caps/Admin/settings.html" class="nav-link">
                                <i data-lucide="settings"></i>
                                <span>Settings</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i data-lucide="menu"></i>
                    </button>
                    <div class="search-container">
                        <i data-lucide="search"></i>
                        <input type="text" placeholder="Search products, orders, customers..." class="search-input">
                    </div>
                </div>
                
                <div class="header-right">
                    <button class="header-btn notification-btn">
                        <i data-lucide="bell"></i>
                        <span class="notification-badge"></span>
                    </button>
                    
                    <div class="user-menu">
                        <button class="user-avatar" id="userMenuToggle">
                            <span>AD</span>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <p class="user-name">Admin User</p>
                                <p class="user-email">admin@rtw-fashion.com</p>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="#profile" class="dropdown-item">Profile</a>
                            <a href="#settings" class="dropdown-item">Settings</a>
                            <a href="#support" class="dropdown-item">Support</a>
                            <div class="dropdown-divider"></div>
                            <a href="#logout" class="dropdown-item">Log out</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Dashboard Page -->
                <div id="dashboard-page" class="page active">
                    <div class="page-header">
                        <h1>Dashboard Overview</h1>
                        <p>Welcome back! Here's what's happening with your RTW store today.</p>
                    </div>

                    <?php if (isset($dbError)): ?>
                        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; border: 1px solid #fecaca;">
                            <strong>⚠️ Database Error:</strong> <?= htmlspecialchars($dbError) ?>
                            <br><small>Showing default values. Please check your database connection.</small>
                        </div>
                    <?php endif; ?>

                    <!-- Key Metrics -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Total Revenue</span>
                                <i data-lucide="dollar-sign" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?= formatCurrency($totalRevenue) ?></div>
                                <div class="metric-change <?= $revenueChange >= 0 ? 'positive' : 'negative' ?>"><?= formatPercentage($revenueChange) ?></div>
                                <div class="metric-description">From delivered orders</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Total Orders</span>
                                <i data-lucide="shopping-cart" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?= number_format($totalOrders) ?></div>
                                <div class="metric-change <?= $ordersChange >= 0 ? 'positive' : 'negative' ?>"><?= formatPercentage($ordersChange) ?></div>
                                <div class="metric-description">Delivered orders</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Active Customers</span>
                                <i data-lucide="users" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?= number_format($activeCustomers) ?></div>
                                <div class="metric-change positive">Unique customers</div>
                                <div class="metric-description">With delivered orders</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Products Sold</span>
                                <i data-lucide="package" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?= number_format($productsSold) ?></div>
                                <div class="metric-change <?= $productsChange >= 0 ? 'positive' : 'negative' ?>"><?= formatPercentage($productsChange) ?></div>
                                <div class="metric-description">Items delivered</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="charts-grid">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h3>Revenue Trend</h3>
                                <p>Monthly revenue from delivered orders</p>
                            </div>
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>

                        <div class="chart-card">
                            <div class="chart-header">
                                <h3>Sales by Category</h3>
                                <p>Product category performance</p>
                            </div>
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Secondary Metrics -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Average Order Value</span>
                                <i data-lucide="trending-up" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value"><?= formatCurrency($avgOrderValue) ?></div>
                                <div class="metric-change positive">Per delivered order</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Conversion Rate</span>
                                <i data-lucide="credit-card" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value">3.2%</div>
                                <div class="metric-change positive">+0.8% from last month</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Pending Refunds</span>
                                <i data-lucide="alert-triangle" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value">23</div>
                                <div class="metric-change neutral">5 new today</div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-header">
                                <span class="metric-title">Processing Time</span>
                                <i data-lucide="clock" class="metric-icon"></i>
                            </div>
                            <div class="metric-content">
                                <div class="metric-value">2.4 days</div>
                                <div class="metric-change positive">-0.3 days faster</div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Tables -->
                    <div class="tables-grid">
                        <div class="table-card">
                            <div class="table-header">
                                <h3>Top Selling Products</h3>
                            </div>
                            <div class="table-content">
                                <div class="table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Units Sold</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($topProducts)): ?>
                                                <?php foreach ($topProducts as $product): ?>
                                                    <tr>
                                                        <td class="product-name"><?= htmlspecialchars($product['product_name']) ?></td>
                                                        <td class="quantity"><?= number_format($product['total_sold']) ?> units</td>
                                                        <td class="revenue"><?= formatCurrency($product['total_revenue']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="no-data">No products sold yet</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="table-card">
                            <div class="table-header">
                                <h3>Recent Transactions</h3>
                            </div>
                            <div class="table-content">
                                <div class="table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Product</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recentTransactions)): ?>
                                                <?php foreach ($recentTransactions as $transaction): ?>
                                                    <tr>
                                                        <td class="order-id">#<?= $transaction['id'] ?></td>
                                                        <td class="customer"><?= htmlspecialchars($transaction['customer_name'] ?? 'N/A') ?></td>
                                                        <td class="product"><?= htmlspecialchars($transaction['product_name']) ?></td>
                                                        <td class="amount"><?= formatCurrency($transaction['order_total']) ?></td>
                                                        <td class="date"><?= date('M d, Y', strtotime($transaction['order_date'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="no-data">No transactions yet</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add more pages as needed -->
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="script.js"></script>
    
    <script>
        // Initialize charts with real data
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons first
            lucide.createIcons();
            
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                const monthlyData = <?= json_encode($monthlyRevenueData) ?>;
                
                console.log('Initializing Revenue Chart...');
                
                // Destroy existing chart if it exists
                if (window.revenueChartInstance) {
                    window.revenueChartInstance.destroy();
                }
                
                window.revenueChartInstance = new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(item => item.month),
                        datasets: [{
                            label: 'Revenue (₱)',
                            data: monthlyData.map(item => item.revenue),
                            borderColor: 'rgb(102, 126, 234)',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(102, 126, 234)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: 'rgb(102, 126, 234)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: ₱' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6b7280'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                ticks: {
                                    color: '#6b7280',
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }
            
            // Category Chart (Sales by Product)
            const categoryCtx = document.getElementById('categoryChart');
            if (categoryCtx) {
                const topProducts = <?= json_encode($topProducts) ?>;
                
                // Prepare data for doughnut chart
                const productNames = topProducts.map(product => product.product_name);
                const productRevenues = topProducts.map(product => parseFloat(product.total_revenue));
                
                // Generate colors for each product
                const colors = [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(168, 85, 247, 0.8)'
                ];
                
                const borderColors = [
                    'rgb(102, 126, 234)',
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)',
                    'rgb(168, 85, 247)'
                ];
                
                console.log('Initializing Category Chart...');
                
                // Destroy existing chart if it exists
                if (window.categoryChartInstance) {
                    window.categoryChartInstance.destroy();
                }
                
                window.categoryChartInstance = new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: productNames.length > 0 ? productNames : ['No Data'],
                        datasets: [{
                            data: productRevenues.length > 0 ? productRevenues : [1],
                            backgroundColor: productNames.length > 0 ? colors.slice(0, productNames.length) : ['rgba(156, 163, 175, 0.5)'],
                            borderColor: productNames.length > 0 ? borderColors.slice(0, productNames.length) : ['rgb(156, 163, 175)'],
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    color: '#374151',
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: 'rgb(102, 126, 234)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        if (productNames.length === 0) {
                                            return 'No sales data available';
                                        }
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ₱' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        });
    </script>
    
    <script>
        // Additional dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Animate metric cards on load
            const metricCards = document.querySelectorAll('.metric-card');
            metricCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Add refresh functionality
            const refreshBtn = document.createElement('button');
            refreshBtn.className = 'header-btn';
            refreshBtn.innerHTML = '<i data-lucide="refresh-cw"></i>';
            refreshBtn.title = 'Refresh Dashboard';
            refreshBtn.style.marginRight = '10px';
            refreshBtn.onclick = function() {
                location.reload();
            };
            
            const headerRight = document.querySelector('.header-right');
            if (headerRight) {
                headerRight.insertBefore(refreshBtn, headerRight.firstChild);
                lucide.createIcons();
            }
            
            // Add real-time clock
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour12: true,
                    hour: '2-digit',
                    minute: '2-digit'
                });
                const dateString = now.toLocaleDateString('en-US', {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric'
                });
                
                let clockElement = document.getElementById('dashboard-clock');
                if (!clockElement) {
                    clockElement = document.createElement('div');
                    clockElement.id = 'dashboard-clock';
                    clockElement.style.cssText = `
                        color: rgba(255, 255, 255, 0.8);
                        font-size: 0.9rem;
                        text-align: right;
                        margin-top: 0.5rem;
                    `;
                    
                    const pageHeader = document.querySelector('.page-header');
                    if (pageHeader) {
                        pageHeader.appendChild(clockElement);
                    }
                }
                
                clockElement.innerHTML = `${dateString} • ${timeString}`;
            }
            
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
    
    <style>
        /* Additional styles for tables */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            background: white;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .data-table th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table tr:hover {
            background: #f9fafb;
        }
        
        .data-table tr:last-child td {
            border-bottom: none;
        }
        
        .no-data {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 2rem;
        }
        
        .product-name {
            font-weight: 500;
            color: #111827;
        }
        
        .quantity {
            color: #059669;
            font-weight: 500;
        }
        
        .revenue,
        .amount {
            color: #dc2626;
            font-weight: 600;
        }
        
        .order-id {
            font-family: monospace;
            color: #6b7280;
            font-size: 0.85rem;
        }
        
        .customer {
            color: #374151;
            font-weight: 500;
        }
        
        .product {
            color: #6b7280;
        }
        
        .date {
            color: #9ca3af;
            font-size: 0.85rem;
        }
    </style>
</body>
</html>