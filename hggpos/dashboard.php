<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$session_username = htmlspecialchars($_SESSION['username']);

require_once 'db_connection.php'; // Updated file with different variable names

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get current date ranges
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    $month_start = date('Y-m-01 00:00:00');
    $month_end = date('Y-m-t 23:59:59');
    $year_start = date('Y-01-01 00:00:00');
    $year_end = date('Y-12-31 23:59:59');
    
    // KOT (Kitchen Order Ticket) Statistics
    // Today's KOT orders
    $stmt_kot_today = $pdo->prepare("
        SELECT COUNT(*) as kot_count, 
               COALESCE(SUM(inv.grand_total), 0) as kot_total
        FROM invoices inv
        WHERE inv.created_at BETWEEN ? AND ? 
        AND inv.status = 'completed'
        AND inv.payment_type != 'foc'
        AND inv.id IN (
            SELECT DISTINCT ii.invoice_id 
            FROM invoice_items ii
            JOIN hggitems hi ON ii.item_id = hi.id
            WHERE hi.type = 'KOT'
            AND hi.status = 'active'
        )
    ");
    $stmt_kot_today->execute([$today_start, $today_end]);
    $kot_today = $stmt_kot_today->fetch(PDO::FETCH_ASSOC);
    
    // Monthly KOT orders
    $stmt_kot_monthly = $pdo->prepare("
        SELECT COUNT(*) as kot_count, 
               COALESCE(SUM(inv.grand_total), 0) as kot_total
        FROM invoices inv
        WHERE inv.created_at BETWEEN ? AND ? 
        AND inv.status = 'completed'
        AND inv.payment_type != 'foc'
        AND inv.id IN (
            SELECT DISTINCT ii.invoice_id 
            FROM invoice_items ii
            JOIN hggitems hi ON ii.item_id = hi.id
            WHERE hi.type = 'KOT'
            AND hi.status = 'active'
        )
    ");
    $stmt_kot_monthly->execute([$month_start, $month_end]);
    $kot_monthly = $stmt_kot_monthly->fetch(PDO::FETCH_ASSOC);
    
    // Pending KOT orders
    $stmt_kot_pending = $pdo->prepare("
        SELECT COUNT(*) as pending_count
        FROM invoices inv
        WHERE inv.status = 'pending'
        AND inv.payment_type != 'foc'
        AND inv.id IN (
            SELECT DISTINCT ii.invoice_id 
            FROM invoice_items ii
            JOIN hggitems hi ON ii.item_id = hi.id
            WHERE hi.type = 'KOT'
            AND hi.status = 'active'
        )
    ");
    $stmt_kot_pending->execute();
    $kot_pending = $stmt_kot_pending->fetch(PDO::FETCH_ASSOC)['pending_count'];
    
    // Popular KOT items today
    $stmt_kot_items = $pdo->prepare("
        SELECT hi.item_name, COUNT(ii.item_id) as order_count
        FROM invoice_items ii
        JOIN hggitems hi ON ii.item_id = hi.id
        JOIN invoices inv ON ii.invoice_id = inv.id
        WHERE inv.created_at BETWEEN ? AND ? 
        AND inv.status = 'completed'
        AND hi.type = 'KOT'
        AND hi.status = 'active'
        GROUP BY hi.item_name
        ORDER BY order_count DESC
        LIMIT 5
    ");
    $stmt_kot_items->execute([$today_start, $today_end]);
    $popular_kot_items = $stmt_kot_items->fetchAll(PDO::FETCH_ASSOC);
    
    // BOT (Bar Order Ticket) Statistics
    // Today's BOT orders
    $stmt_bot_today = $pdo->prepare("
        SELECT COUNT(*) as bot_count, 
               COALESCE(SUM(inv.grand_total), 0) as bot_total
        FROM invoices inv
        WHERE inv.created_at BETWEEN ? AND ? 
        AND inv.status = 'completed'
        AND inv.payment_type != 'foc'
        AND inv.id IN (
            SELECT DISTINCT ii.invoice_id 
            FROM invoice_items ii
            JOIN hggitems hi ON ii.item_id = hi.id
            WHERE hi.type = 'BOT'
            AND hi.status = 'active'
        )
    ");
    $stmt_bot_today->execute([$today_start, $today_end]);
    $bot_today = $stmt_bot_today->fetch(PDO::FETCH_ASSOC);
    
    // Monthly BOT orders
    $stmt_bot_monthly = $pdo->prepare("
        SELECT COUNT(*) as bot_count, 
               COALESCE(SUM(inv.grand_total), 0) as bot_total
        FROM invoices inv
        WHERE inv.created_at BETWEEN ? AND ? 
        AND inv.status = 'completed'
        AND inv.payment_type != 'foc'
        AND inv.id IN (
            SELECT DISTINCT ii.invoice_id 
            FROM invoice_items ii
            JOIN hggitems hi ON ii.item_id = hi.id
            WHERE hi.type = 'BOT'
            AND hi.status = 'active'
        )
    ");
    $stmt_bot_monthly->execute([$month_start, $month_end]);
    $bot_monthly = $stmt_bot_monthly->fetch(PDO::FETCH_ASSOC);
    
    // Pending BOT orders
    $stmt_bot_pending = $pdo->prepare("
        SELECT COUNT(*) as pending_count
        FROM invoices inv
        WHERE inv.status = 'pending'
        AND inv.payment_type != 'foc'
        AND inv.id IN (
            SELECT DISTINCT ii.invoice_id 
            FROM invoice_items ii
            JOIN hggitems hi ON ii.item_id = hi.id
            WHERE hi.type = 'BOT'
            AND hi.status = 'active'
        )
    ");
    $stmt_bot_pending->execute();
    $bot_pending = $stmt_bot_pending->fetch(PDO::FETCH_ASSOC)['pending_count'];
    
    // Popular BOT items today
    $stmt_bot_items = $pdo->prepare("
        SELECT hi.item_name, COUNT(ii.item_id) as order_count
        FROM invoice_items ii
        JOIN hggitems hi ON ii.item_id = hi.id
        JOIN invoices inv ON ii.invoice_id = inv.id
        WHERE inv.created_at BETWEEN ? AND ? 
        AND inv.status = 'completed'
        AND hi.type = 'BOT'
        AND hi.status = 'active'
        GROUP BY hi.item_name
        ORDER BY order_count DESC
        LIMIT 5
    ");
    $stmt_bot_items->execute([$today_start, $today_end]);
    $popular_bot_items = $stmt_bot_items->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $kot_today = ['kot_count' => 0, 'kot_total' => 0];
    $kot_monthly = ['kot_count' => 0, 'kot_total' => 0];
    $kot_pending = 0;
    $popular_kot_items = [];
    $bot_today = ['bot_count' => 0, 'bot_total' => 0];
    $bot_monthly = ['bot_count' => 0, 'bot_total' => 0];
    $bot_pending = 0;
    $popular_bot_items = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #f59e0b;
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --navbar-bg: #1e40af;
            --purple: #a855f7;
            --purple-dark: #7e22ce;
            --red: #ef4444;
            --red-dark: #dc2626;
            --green: #10b981;
            --green-dark: #059669;
            --blue: #3b82f6;
            --blue-dark: #2563eb;
            --teal: #14b8a6;
            --teal-dark: #0d9488;
            --orange: #f97316;
            --orange-dark: #ea580c;
            --cyan: #06b6d4;
            --cyan-dark: #0891b2;
            --indigo: #4f46e5;
            --indigo-dark: #4338ca;
            --amber: #f59e0b;
            --amber-dark: #d97706;
            --emerald: #10b981;
            --emerald-dark: #059669;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            overflow-x: hidden;
            position: relative;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(45deg, #0f0f23, #1a1a2e, #16213e);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(245, 158, 11, 0.2) 0%, transparent 50%);
            animation: floatingBg 20s ease-in-out infinite;
        }

        @keyframes floatingBg {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
            33% { transform: translate(-45%, -55%) rotate(120deg) scale(1.1); }
            66% { transform: translate(-55%, -45%) rotate(240deg) scale(0.9); }
        }

        .navbar {
            background: var(--navbar-bg);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
        }

        .user-icon {
            width: 24px;
            height: 24px;
            color: var(--text-primary);
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.2);
            color: var(--text-primary);
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }

        .logout-icon {
            width: 16px;
            height: 16px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            height: 100px;
            border: none;
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 10px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .action-btn.billing {
            background: linear-gradient(135deg, var(--purple), var(--purple-dark));
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.3);
        }

        .action-btn.billing:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.5);
            background: linear-gradient(135deg, var(--purple-dark), var(--purple));
        }

        .action-btn.void {
            background: linear-gradient(135deg, var(--red), var(--red-dark));
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .action-btn.void:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.5);
            background: linear-gradient(135deg, var(--red-dark), var(--red));
        }

        .action-btn.cash {
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .action-btn.cash:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, var(--green-dark), var(--green));
        }

        .action-btn.credit {
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .action-btn.credit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, var(--blue-dark), var(--blue));
        }

        .action-btn.card {
            background: linear-gradient(135deg, var(--teal), var(--teal-dark));
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.3);
        }

        .action-btn.card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(20, 184, 166, 0.5);
            background: linear-gradient(135deg, var(--teal-dark), var(--teal));
        }

        .action-btn.foc {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        }

        .action-btn.foc:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.5);
            background: linear-gradient(135deg, var(--orange-dark), var(--orange));
        }

        .action-btn.takeaway {
            background: linear-gradient(135deg, var(--cyan), var(--cyan-dark));
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .action-btn.takeaway:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(6, 182, 212, 0.5);
            background: linear-gradient(135deg, var(--cyan-dark), var(--cyan));
        }

        .action-btn.delivery {
            background: linear-gradient(135deg, var(--indigo), var(--indigo-dark));
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }

        .action-btn.delivery:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.5);
            background: linear-gradient(135deg, var(--indigo-dark), var(--indigo));
        }

        .action-btn:active {
            transform: translateY(-1px);
        }

        .dashboard-container {
            margin-top: 80px;
            padding: 40px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            color: var(--text-primary);
        }

        .column {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out;
            min-height: 200px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .column:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .button-group-horizontal {
            display: flex;
            flex-direction: row;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Column 2 specific styles - KOT (Kitchen) */
        .column-2 .stat-card {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .column-2 .stat-value {
            color: var(--emerald);
        }

        .column-2 .pending-orders {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            margin-bottom: 16px;
        }

        .column-2 .pending-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--red);
            margin-bottom: 4px;
        }

        .column-2 .pending-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        /* Column 3 specific styles - BOT (Bar) */
        .column-3 .stat-card {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .column-3 .stat-value {
            color: var(--amber);
        }

        .column-3 .pending-orders {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            margin-bottom: 16px;
        }

        .column-3 .pending-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--red);
            margin-bottom: 4px;
        }

        .column-3 .pending-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .popular-items {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 16px;
        }

        .popular-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
            text-align: center;
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .item-name {
            font-size: 0.85rem;
            color: var(--text-primary);
            flex: 1;
        }

        .item-count {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 8px;
        }

        .column-2 .item-count {
            color: var(--emerald);
            background: rgba(16, 185, 129, 0.2);
        }

        .column-3 .item-count {
            color: var(--amber);
            background: rgba(245, 158, 11, 0.2);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .action-btn {
                width: 80px;
                height: 80px;
                font-size: 0.9rem;
                padding: 8px;
            }

            .column {
                flex-direction: column;
                justify-content: center;
            }

            .button-group-horizontal {
                flex-direction: row;
                gap: 12px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 12px 16px;
            }

            .navbar-user {
                flex-direction: column;
                gap: 8px;
                align-items: flex-end;
            }

            .dashboard-container {
                padding: 20px;
                margin-top: 60px;
            }

            .action-btn {
                width: 60px;
                height: 60px;
                font-size: 0.8rem;
                padding: 6px;
            }

            .column {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }

            .button-group-horizontal {
                flex-direction: column;
                gap: 12px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>

    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Guardia POS</a>
        <div class="navbar-user">
            <div class="user-info">
                <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                <span>User: <?php echo $session_username; ?></span>
            </div>
            <a href="logout.php" class="logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                <svg class="logout-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="column">
            <div class="button-group-horizontal">
                <a href="invoice.php" class="action-btn billing">Billing</a>
                <a href="void.php" class="action-btn void">Void</a>
                <a href="cash.php" class="action-btn cash">Cash</a>
            </div>
            <div class="button-group-horizontal">
                <a href="credit.php" class="action-btn credit">Credit</a>
                <a href="card.php" class="action-btn card">Card</a>
                <a href="foc.php" class="action-btn foc">FOC</a>
            </div>
            <div class="button-group-horizontal">
                <a href="takeaway.php" class="action-btn takeaway">Take Away</a>
                <a href="delivery.php" class="action-btn delivery">Delivery</a>
                <a href="canceled.php" class="action-btn delivery">Cancelled</a>
            </div>
        </div>
        
        <!-- Column 2: KOT (Kitchen Order Ticket) -->
        <div class="column column-2">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $kot_today['kot_count']; ?></div>
                    <div class="stat-label">Today's KOT</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">LKR <?php echo number_format($kot_today['kot_total'], 2); ?></div>
                    <div class="stat-label">KOT Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $kot_monthly['kot_count']; ?></div>
                    <div class="stat-label">Monthly KOT</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">LKR <?php echo number_format($kot_monthly['kot_total'], 2); ?></div>
                    <div class="stat-label">Monthly KOT Revenue</div>
                </div>
            </div>
            
            <div class="pending-orders">
                <div class="pending-value"><?php echo $kot_pending; ?></div>
                <div class="pending-label">Pending KOT Orders</div>
            </div>
            
            <div class="popular-items">
                <div class="popular-title">Popular KOT Items Today</div>
                <div class="item-list">
                    <?php if (!empty($popular_kot_items)): ?>
                        <?php foreach ($popular_kot_items as $item): ?>
                            <div class="item-row">
                                <span class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <span class="item-count"><?php echo $item['order_count']; ?> orders</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="item-row">
                            <span class="item-name">No KOT orders today</span>
                            <span class="item-count">-</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Column 3: BOT (Bar Order Ticket) -->
        <div class="column column-3">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $bot_today['bot_count']; ?></div>
                    <div class="stat-label">Today's BOT</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">LKR <?php echo number_format($bot_today['bot_total'], 2); ?></div>
                    <div class="stat-label">BOT Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $bot_monthly['bot_count']; ?></div>
                    <div class="stat-label">Monthly BOT</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">LKR <?php echo number_format($bot_monthly['bot_total'], 2); ?></div>
                    <div class="stat-label">Monthly BOT Revenue</div>
                </div>
            </div>
            
            <div class="pending-orders">
                <div class="pending-value"><?php echo $bot_pending; ?></div>
                <div class="pending-label">Pending BOT Orders</div>
            </div>
            
            <div class="popular-items">
                <div class="popular-title">Popular BOT Items Today</div>
                <div class="item-list">
                    <?php if (!empty($popular_bot_items)): ?>
                        <?php foreach ($popular_bot_items as $item): ?>
                            <div class="item-row">
                                <span class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                                <span class="item-count"><?php echo $item['order_count']; ?> orders</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="item-row">
                            <span class="item-name">No BOT orders today</span>
                            <span class="item-count">-</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prevent browser back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Replace history state to prevent back navigation
        history.replaceState({}, "", "dashboard.php");

        document.querySelectorAll('.column').forEach((column, index) => {
            column.style.animationDelay = `${index * 0.1}s`;
            column.addEventListener('click', (e) => {
                if (!e.target.classList.contains('action-btn')) {
                    column.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        column.style.transform = 'translateY(-5px)';
                    }, 200);
                }
            });
        });
    </script>
</body>
</html>