<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: stores_login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']); // Sanitize username for display

// Database connection
$servername = "localhost";
$db_username = "hotelgrandguardi_root";
$db_password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $db_username, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query for low buffer stock (from inventory table)
    $stmt = $conn->prepare("SELECT item_name, buffer_stock, threshold, unit FROM inventory WHERE buffer_stock < threshold");
    $stmt->execute();
    $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query for low purchased stock (total stock < 20)
    $stmt = $conn->prepare("
        SELECT i.item_name, SUM(p.stock) as total_stock, i.unit
        FROM purchased_items p
        JOIN inventory i ON p.item_id = i.id
        GROUP BY p.item_id, i.item_name, i.unit
        HAVING total_stock < 20
    ");
    $stmt->execute();
    $low_purchased_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query for pending orders in order_sheet (HGG Main Kitchen)
    $stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM order_sheet WHERE status = 'pending'");
    $stmt->execute();
    $hgg_main_pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending_count'] ?? 0;

    // Query for pending orders in skyorder_sheet (SKY Restaurant)
    $stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM skyorder_sheet WHERE status = 'pending'");
    $stmt->execute();
    $sky_pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending_count'] ?? 0;

    // Query for pending orders in hggorder_sheet (HGG Bar & Restaurant)
    $stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM hggorder_sheet WHERE status = 'pending'");
    $stmt->execute();
    $hgg_bar_pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending_count'] ?? 0;

    // Query for purchased stock counts (daily, monthly, yearly)
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN DATE(purchased_date) = CURDATE() THEN stock ELSE 0 END), 0) as daily_stock,
            COALESCE(SUM(CASE WHEN YEAR(purchased_date) = YEAR(CURDATE()) AND MONTH(purchased_date) = MONTH(CURDATE()) THEN stock ELSE 0 END), 0) as monthly_stock,
            COALESCE(SUM(CASE WHEN YEAR(purchased_date) = YEAR(CURDATE()) THEN stock ELSE 0 END), 0) as yearly_stock
        FROM purchased_items_backup
    ");
    $stmt->execute();
    $purchased_stock = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure default values to avoid undefined issues
    $purchased_stock = [
        'daily_stock' => $purchased_stock['daily_stock'] ?? 0,
        'monthly_stock' => $purchased_stock['monthly_stock'] ?? 0,
        'yearly_stock' => $purchased_stock['yearly_stock'] ?? 0
    ];

    // Query for requested stock counts from order sheets (daily, monthly, yearly)
    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN DATE(request_date) = CURDATE() THEN requested_qty ELSE 0 END), 0) as hgg_main_daily,
            COALESCE(SUM(CASE WHEN YEAR(request_date) = YEAR(CURDATE()) AND MONTH(request_date) = MONTH(CURDATE()) THEN requested_qty ELSE 0 END), 0) as hgg_main_monthly,
            COALESCE(SUM(CASE WHEN YEAR(request_date) = YEAR(CURDATE()) THEN requested_qty ELSE 0 END), 0) as hgg_main_yearly
        FROM order_sheet
    ");
    $stmt->execute();
    $hgg_main_request = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN DATE(request_date) = CURDATE() THEN requested_qty ELSE 0 END), 0) as sky_daily,
            COALESCE(SUM(CASE WHEN YEAR(request_date) = YEAR(CURDATE()) AND MONTH(request_date) = MONTH(CURDATE()) THEN requested_qty ELSE 0 END), 0) as sky_monthly,
            COALESCE(SUM(CASE WHEN YEAR(request_date) = YEAR(CURDATE()) THEN requested_qty ELSE 0 END), 0) as sky_yearly
        FROM skyorder_sheet
    ");
    $stmt->execute();
    $sky_request = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN DATE(request_date) = CURDATE() THEN requested_qty ELSE 0 END), 0) as hgg_bar_daily,
            COALESCE(SUM(CASE WHEN YEAR(request_date) = YEAR(CURDATE()) AND MONTH(request_date) = MONTH(CURDATE()) THEN requested_qty ELSE 0 END), 0) as hgg_bar_monthly,
            COALESCE(SUM(CASE WHEN YEAR(request_date) = YEAR(CURDATE()) THEN requested_qty ELSE 0 END), 0) as hgg_bar_yearly
        FROM hggorder_sheet
    ");
    $stmt->execute();
    $hgg_bar_request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure default values for requested stock
    $hgg_main_request = [
        'hgg_main_daily' => $hgg_main_request['hgg_main_daily'] ?? 0,
        'hgg_main_monthly' => $hgg_main_request['hgg_main_monthly'] ?? 0,
        'hgg_main_yearly' => $hgg_main_request['hgg_main_yearly'] ?? 0
    ];
    $sky_request = [
        'sky_daily' => $sky_request['sky_daily'] ?? 0,
        'sky_monthly' => $sky_request['sky_monthly'] ?? 0,
        'sky_yearly' => $sky_request['sky_yearly'] ?? 0
    ];
    $hgg_bar_request = [
        'hgg_bar_daily' => $hgg_bar_request['hgg_bar_daily'] ?? 0,
        'hgg_bar_monthly' => $hgg_bar_request['hgg_bar_monthly'] ?? 0,
        'hgg_bar_yearly' => $hgg_bar_request['hgg_bar_yearly'] ?? 0
    ];

} catch (PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stores Panel</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #e84393;
            --dark: #2d3436;
            --light: #f5f6fa;
            --success: #00b894;
            --info: #0984e3;
            --warning: #ff6b6b;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --background: #f9f9f9;
            --panel-bg: white;
            --text-color: #2d3436;
            --logout-bg: #cc0000; /* Darker red for logout */
            --mk-item-bg: #d8b4fe; /* Light purple for M/K Item Issue */
            --hgg-item-bg: #86efac; /* Light green for HGG/R Item Issue */
            --sky-item-bg: #60a5fa; /* Blue for SKY/R Item Issue */
        }
        
        [data-theme="dark"] {
            --background: #1a1a1a;
            --panel-bg: #2d2d2d;
            --text-color: #ffffff;
        }
        
        [data-theme="light"] {
            --text-color: #000000;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--text-color);
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }
        
        .admin-panel {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            animation: gradientShift 10s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px 20px;
            color: white;
            transform: translateX(0);
            transition: all 0.4s ease;
            box-shadow: var(--shadow);
            z-index: 10;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            transform: translateX(-260px);
        }
        
        .user-greeting {
            font-size: 18px;
            font-weight: 500;
            color: white;
            margin-bottom: 20px;
            padding-left: 10px;
            animation: slideInLeft 0.5s ease;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            padding-left: 10px;
        }
        
        .grand-guardian-logo {
            width: 120px;
            height: auto;
            transition: transform 0.3s ease, opacity 0.3s ease;
            animation: logoPulse 2s ease infinite;
        }
        
        .grand-guardian-logo:hover {
            transform: scale(1.1);
            opacity: 0.9;
        }
        
        @keyframes logoPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .menu {
            margin-top: 40px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
        }
        
        .menu-item:hover::before {
            left: 0;
        }
        
        .menu-item.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .menu-item i {
            font-size: 20px;
            margin-right: 15px;
        }
        
        .menu-item span {
            font-size: 16px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .menu-item.logout {
            background: var(--logout-bg);
            animation: pulseRed 2s ease infinite;
            margin-top: 30px; /* Separate from other menu items */
        }
        
        .menu-item.mk-item {
            background: var(--mk-item-bg);
            animation: pulsePurple 2s ease infinite;
        }
        
        .menu-item.hgg-item {
            background: var(--hgg-item-bg);
            animation: pulseGreen 2s ease infinite;
        }
        
        .menu-item.sky-item {
            background: var(--sky-item-bg);
            animation: pulseBlue 2s ease infinite;
        }
        
        @keyframes pulseRed {
            0% { background-color: var(--logout-bg); }
            50% { background-color: #990000; }
            100% { background-color: var(--logout-bg); }
        }
        
        @keyframes pulsePurple {
            0% { background-color: var(--mk-item-bg); }
            50% { background-color: #c084fc; }
            100% { background-color: var(--mk-item-bg); }
        }
        
        @keyframes pulseGreen {
            0% { background-color: var(--hgg-item-bg); }
            50% { background-color: #4ade80; }
            100% { background-color: var(--hgg-item-bg); }
        }
        
        @keyframes pulseBlue {
            0% { background-color: var(--sky-item-bg); }
            50% { background-color: #3b82f6; }
            100% { background-color: var(--sky-item-bg); }
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            transition: all 0.4s ease;
            margin-left: 280px;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 20px;
        }
        
        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .toggle-sidebar {
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
            position: fixed;
            top: 20px;
            left: 300px;
            z-index: 9;
        }
        
        .sidebar.collapsed ~ .main-content .toggle-sidebar {
            left: 40px;
        }
        
        .toggle-sidebar:hover {
            transform: rotate(180deg);
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.5);
        }
        
        .theme-toggle {
            background: var(--secondary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(232, 67, 147, 0.3);
            margin-left: 10px;
        }
        
        .theme-toggle:hover {
            transform: rotate(90deg);
            box-shadow: 0 4px 20px rgba(232, 67, 147, 0.5);
        }
        
        .dashboard {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            animation: zoomIn 0.6s ease;
            color: var(--text-color);
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h2 {
            font-size: 24px;
            color: var(--text-color);
            animation: slideInLeft 0.5s ease;
        }
        
        .error-message {
            color: #d63031;
            text-align: center;
            margin-bottom: 20px;
            display: none;
            animation: shake 0.5s ease;
        }
        
        .alert-section {
            margin-top: 20px;
        }
        
        .alert-section h3 {
            font-size: 20px;
            color: var(--warning);
            margin-bottom: 15px;
        }
        
        .alert-item {
            background: rgba(255, 107, 107, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideInLeft 0.5s ease;
        }
        
        .alert-item span {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .alert-item .alert-icon {
            color: var(--warning);
            font-size: 20px;
        }
        
        .no-alerts {
            color: var(--success);
            font-size: 16px;
            text-align: center;
            padding: 15px;
        }
        
        .pending-orders {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .order-box {
            width: 30%;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            color: white;
            font-weight: 500;
            animation: zoomIn 0.6s ease;
        }
        
        .order-box.hgg-main {
            background: var(--mk-item-bg);
        }
        
        .order-box.hgg-bar {
            background: var(--hgg-item-bg);
        }
        
        .order-box.sky {
            background: var(--sky-item-bg);
        }
        
        .order-box span {
            font-size: 18px;
        }
        
        .order-box .count {
            font-size: 24px;
            font-weight: 700;
        }
        
        .charts-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .chart-container {
            width: 48%;
            min-height: 300px;
            margin-bottom: 20px;
        }
        
        .chart-container h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .no-data {
            color: var(--warning);
            text-align: center;
            font-size: 16px;
            padding: 15px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-280px);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                left: 20px;
            }
            
            .sidebar.collapsed ~ .main-content .toggle-sidebar {
                left: 20px;
            }
            
            .user-greeting {
                font-size: 16px;
            }
            
            .pending-orders {
                flex-direction: column;
            }
            
            .order-box {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .chart-container {
                width: 100%;
            }
        }
        
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="sidebar">
            <div class="user-greeting">Hey, <?php echo $username; ?>!</div>
            <div class="logo">
                <img src="images/logo.avif" alt="Grand Guardian Logo" class="grand-guardian-logo">
            </div>
            <div class="menu">
                <div class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/supplier.php'">
                    <i class="fas fa-user"></i>
                    <span>Supplier Register</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/purchase_order.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Purchase Order</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/grn_print.php'">
                    <i class="fas fa-shopping-bag"></i>
                    <span>GRN</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/grn_view.php'">
                    <i class="fas fa-shopping-bag"></i>
                    <span>View GRN</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/purchased_items.php'">
                    <i class="fas fa-edit"></i>
                    <span>Add New Stock</span>
                </div>
                  <div class="menu-item" onclick="window.location.href='stores/purchased_items_view.php'">
                    <i class="fas fa-eye"></i>
                    <span>View Stock</span>
                </div>
                 <div class="menu-item" onclick="window.location.href='stores/item_price.php'">
                    <i class="fas fa-eye"></i>
                    <span>View Price</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/function_unload.php'">
                    <i class="fas fa-truck-loading"></i>
                    <span>Mark Unload</span>
                </div>
                <div class="menu-item" onclick="window.location.href='stores/staff_issue_items.php'">
                    <i class="fas fa-cutlery"></i>
                    <span>Staff Item Issue</span>
                </div>
                <div class="menu-item mk-item" onclick="window.location.href='stores/issue_items.php'">
                    <i class="fas fa-cutlery"></i>
                    <span>M/K Item Issue</span>
                </div>
                <div class="menu-item hgg-item" onclick="window.location.href='stores/hgg_issue_items.php'">
                    <i class="fas fa-cutlery"></i>
                    <span>HGG/R Item Issue</span>
                </div>
                <div class="menu-item sky-item" onclick="window.location.href='stores/sky_issue_items.php'">
                    <i class="fas fa-cutlery"></i>
                    <span>SKY/R Item Issue</span>
                </div>
              
                <div class="menu-item logout" onclick="window.location.href='stores_logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <div>
                    <button class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button class="theme-toggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
            
            <div class="dashboard">
                <div class="dashboard-header">
                    <h2>Dashboard</h2>
                    <div class="date">Today: <span id="current-date"></span></div>
                </div>
                
                <div class="pending-orders">
                    <div class="order-box hgg-main">
                        <span>HGG Main Kitchen Orders</span><br>
                        <span class="count"><?php echo $hgg_main_pending; ?> Pending</span>
                    </div>
                    <div class="order-box hgg-bar">
                        <span>HGG Bar & Restaurant Orders</span><br>
                        <span class="count"><?php echo $hgg_bar_pending; ?> Pending</span>
                    </div>
                    <div class="order-box sky">
                        <span>SKY Restaurant Orders</span><br>
                        <span class="count"><?php echo $sky_pending; ?> Pending</span>
                    </div>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message" style="display: block;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="alert-section">
                    <h3>Low Buffer Stock Alerts</h3>
                    <?php if (!empty($low_stock_items)): ?>
                        <?php foreach ($low_stock_items as $item): ?>
                            <div class="alert-item">
                                <span>
                                    <?php echo htmlspecialchars($item['item_name']); ?> is low on stock. 
                                    Current: <?php echo htmlspecialchars($item['buffer_stock']); ?> 
                                    <?php echo htmlspecialchars($item['unit']); ?>, 
                                    Threshold: <?php echo htmlspecialchars($item['threshold']); ?> 
                                    <?php echo htmlspecialchars($item['unit']); ?>
                                </span>
                                <i class="fas fa-exclamation-circle alert-icon"></i>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-alerts">No low buffer stock alerts at the moment.</div>
                    <?php endif; ?>
                </div>
                
                <div class="alert-section">
                    <h3>Low Purchased Stock Alerts</h3>
                    <?php if (!empty($low_purchased_stock_items)): ?>
                        <?php foreach ($low_purchased_stock_items as $item): ?>
                            <div class="alert-item">
                                <span>
                                    <?php echo htmlspecialchars($item['item_name']); ?> has low purchased stock. 
                                    Total: <?php echo htmlspecialchars($item['total_stock']); ?> 
                                    <?php echo htmlspecialchars($item['unit']); ?> (Threshold: 20)
                                </span>
                                <i class="fas fa-exclamation-circle alert-icon"></i>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-alerts">No low purchased stock alerts at the moment.</div>
                    <?php endif; ?>
                </div>
                
                <div class="charts-section">
                    <div class="chart-container">
                        <h3>Purchased Stock History</h3>
                        <?php if ($purchased_stock['daily_stock'] > 0 || $purchased_stock['monthly_stock'] > 0 || $purchased_stock['yearly_stock'] > 0): ?>
                            <canvas id="purchasedStockChart"></canvas>
                        <?php else: ?>
                            <div class="no-data">No purchased stock data available.</div>
                        <?php endif; ?>
                    </div>
                    <div class="chart-container">
                        <h3>Requested Stock by Order Sheets</h3>
                        <?php if ($hgg_main_request['hgg_main_daily'] > 0 || $hgg_main_request['hgg_main_monthly'] > 0 || $hgg_main_request['hgg_main_yearly'] > 0 ||
                                  $sky_request['sky_daily'] > 0 || $sky_request['sky_monthly'] > 0 || $sky_request['sky_yearly'] > 0 ||
                                  $hgg_bar_request['hgg_bar_daily'] > 0 || $hgg_bar_request['hgg_bar_monthly'] > 0 || $hgg_bar_request['hgg_bar_yearly'] > 0): ?>
                            <canvas id="requestedStockChart"></canvas>
                        <?php else: ?>
                            <div class="no-data">No requested stock data available.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Scroll to top on page load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', function() {
            document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            this.innerHTML = `<i class="fas fa-${document.body.dataset.theme === 'dark' ? 'sun' : 'moon'}"></i>`;
        });
        
        // Ripple effect for buttons
        function createRipple(event) {
            const button = event.currentTarget;
            const circle = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
            circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
            circle.classList.add('ripple');
            
            const ripple = button.getElementsByClassName('ripple')[0];
            if (ripple) {
                ripple.remove();
            }
            
            button.appendChild(circle);
        }
        
        const buttons = document.querySelectorAll('.toggle-sidebar, .theme-toggle');
        buttons.forEach(button => {
            button.addEventListener('click', createRipple);
        });
        
        // Set current date
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);
        
        // For mobile view
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar').classList.add('collapsed');
        }

        // Purchased Stock Pie Chart
        <?php if ($purchased_stock['daily_stock'] > 0 || $purchased_stock['monthly_stock'] > 0 || $purchased_stock['yearly_stock'] > 0): ?>
        const purchasedStockCtx = document.getElementById('purchasedStockChart').getContext('2d');
        new Chart(purchasedStockCtx, {
            type: 'pie',
            data: {
                labels: ['Daily', 'Monthly', 'Yearly'],
                datasets: [{
                    data: [
                        <?php echo $purchased_stock['daily_stock']; ?>,
                        <?php echo $purchased_stock['monthly_stock']; ?>,
                        <?php echo $purchased_stock['yearly_stock']; ?>
                    ],
                    backgroundColor: ['#60a5fa', '#d8b4fe', '#86efac'],
                    borderColor: '#ffffff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Purchased Stock Distribution',
                        font: {
                            size: 18
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return `${label}: ${value} units`;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Requested Stock Pie Chart
        <?php if ($hgg_main_request['hgg_main_daily'] > 0 || $hgg_main_request['hgg_main_monthly'] > 0 || $hgg_main_request['hgg_main_yearly'] > 0 ||
                  $sky_request['sky_daily'] > 0 || $sky_request['sky_monthly'] > 0 || $sky_request['sky_yearly'] > 0 ||
                  $hgg_bar_request['hgg_bar_daily'] > 0 || $hgg_bar_request['hgg_bar_monthly'] > 0 || $hgg_bar_request['hgg_bar_yearly'] > 0): ?>
        const requestedStockCtx = document.getElementById('requestedStockChart').getContext('2d');
        new Chart(requestedStockCtx, {
            type: 'pie',
            data: {
                labels: [
                    'HGG Main Daily', 'HGG Main Monthly', 'HGG Main Yearly',
                    'SKY Daily', 'SKY Monthly', 'SKY Yearly',
                    'HGG Bar Daily', 'HGG Bar Monthly', 'HGG Bar Yearly'
                ],
                datasets: [{
                    data: [
                        <?php echo $hgg_main_request['hgg_main_daily']; ?>,
                        <?php echo $hgg_main_request['hgg_main_monthly']; ?>,
                        <?php echo $hgg_main_request['hgg_main_yearly']; ?>,
                        <?php echo $sky_request['sky_daily']; ?>,
                        <?php echo $sky_request['sky_monthly']; ?>,
                        <?php echo $sky_request['sky_yearly']; ?>,
                        <?php echo $hgg_bar_request['hgg_bar_daily']; ?>,
                        <?php echo $hgg_bar_request['hgg_bar_monthly']; ?>,
                        <?php echo $hgg_bar_request['hgg_bar_yearly']; ?>
                    ],
                    backgroundColor: [
                        '#60a5fa', '#d8b4fe', '#86efac',
                        '#3b82f6', '#c084fc', '#4ade80',
                        '#93c5fd', '#e9d5ff', '#bbf7d0'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Requested Stock Distribution',
                        font: {
                            size: 18
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return `${label}: ${value} units`;
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>