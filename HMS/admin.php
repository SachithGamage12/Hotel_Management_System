<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: admin_login.php");
   exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Database connection
$servername = "localhost";
$username_db = "hotelgrandguardi_root";
$password_db = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get tomorrow's date
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $today = date('Y-m-d');

    // Fetch tomorrow's bookings with total amount
    $tomorrow_query = "
        SELECT wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax, 
               ft.name AS function_name, v.name AS venue_name,
               COALESCE(SUM(p.total_amount), 0) AS total_amount
        FROM wedding_bookings wb
        LEFT JOIN function_types ft ON wb.function_type_id = ft.id
        LEFT JOIN venues v ON wb.venue_id = v.id
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date = :tomorrow
        GROUP BY wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax, ft.name, v.name
        UNION
        SELECT wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax, 
               ft.name AS function_name, v.name AS venue_name,
               COALESCE(SUM(p.total_amount), 0) AS total_amount
        FROM wedding_bookings_history wbh
        LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
        LEFT JOIN venues v ON wbh.venue_id = v.id
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date = :tomorrow
        GROUP BY wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax, ft.name, v.name
    ";
    
    $stmt_tomorrow = $conn->prepare($tomorrow_query);
    $stmt_tomorrow->execute(['tomorrow' => $tomorrow]);
    $tomorrow_bookings = $stmt_tomorrow->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Fetch today's ongoing bookings with total amount
    $current_time = date('H:i:s');
    $today_query = "
        SELECT wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax, 
               ft.name AS function_name, v.name AS venue_name,
               wb.time_from, wb.time_from_am_pm, wb.time_to, wb.time_to_am_pm,
               COALESCE(SUM(p.total_amount), 0) AS total_amount
        FROM wedding_bookings wb
        LEFT JOIN function_types ft ON wb.function_type_id = ft.id
        LEFT JOIN venues v ON wb.venue_id = v.id
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date = :today
        AND (
            (wb.time_from_am_pm = 'AM' AND wb.time_to_am_pm = 'AM' AND :current_time BETWEEN wb.time_from AND wb.time_to)
            OR (wb.time_from_am_pm = 'PM' AND wb.time_to_am_pm = 'PM' AND :current_time BETWEEN wb.time_from AND wb.time_to)
            OR (wb.time_from_am_pm = 'AM' AND wb.time_to_am_pm = 'PM' AND (
                :current_time >= wb.time_from OR :current_time <= wb.time_to
            ))
        )
        GROUP BY wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax, ft.name, v.name,
                 wb.time_from, wb.time_from_am_pm, wb.time_to, wb.time_to_am_pm
        UNION
        SELECT wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax, 
               ft.name AS function_name, v.name AS venue_name,
               wbh.time_from, wbh.time_from_am_pm, wbh.time_to, wbh.time_to_am_pm,
               COALESCE(SUM(p.total_amount), 0) AS total_amount
        FROM wedding_bookings_history wbh
        LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
        LEFT JOIN venues v ON wbh.venue_id = v.id
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date = :today
        AND (
            (wbh.time_from_am_pm = 'AM' AND wbh.time_to_am_pm = 'AM' AND :current_time BETWEEN wbh.time_from AND wbh.time_to)
            OR (wbh.time_from_am_pm = 'PM' AND wbh.time_to_am_pm = 'PM' AND :current_time BETWEEN wbh.time_from AND wbh.time_to)
            OR (wbh.time_from_am_pm = 'AM' AND wbh.time_to_am_pm = 'PM' AND (
                :current_time >= wbh.time_from OR :current_time <= wbh.time_to
            ))
        )
        GROUP BY wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax, ft.name, v.name,
                 wbh.time_from, wbh.time_from_am_pm, wbh.time_to, wbh.time_to_am_pm
    ";
    
    $stmt_today = $conn->prepare($today_query);
    $stmt_today->execute(['today' => $today, 'current_time' => $current_time]);
    $today_bookings = $stmt_today->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Fetch daily payment data (last 30 days for smoother line chart)
    $daily_query = "
        SELECT DATE(payment_date) AS payment_date, 
               COALESCE(SUM(total_amount), 0) AS total_amount
        FROM (
            SELECT payment_date, total_amount FROM payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            SELECT payment_date, total_amount FROM room_payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ) AS combined_payments
        GROUP BY DATE(payment_date)
        ORDER BY payment_date ASC
    ";
    $stmt_daily = $conn->prepare($daily_query);
    $stmt_daily->execute();
    $daily_payments = $stmt_daily->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Fetch monthly payment data (last 24 months)
    $monthly_query = "
        SELECT DATE_FORMAT(payment_date, '%Y-%m') AS payment_month, 
               COALESCE(SUM(total_amount), 0) AS total_amount
        FROM (
            SELECT payment_date, total_amount FROM payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
            UNION ALL
            SELECT payment_date, total_amount FROM room_payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
        ) AS combined_payments
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY payment_month ASC
    ";
    $stmt_monthly = $conn->prepare($monthly_query);
    $stmt_monthly->execute();
    $monthly_payments = $stmt_monthly->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Fetch yearly payment data (last 10 years)
    $yearly_query = "
        SELECT YEAR(payment_date) AS payment_year, 
               COALESCE(SUM(total_amount), 0) AS total_amount
        FROM (
            SELECT payment_date, total_amount FROM payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 10 YEAR)
            UNION ALL
            SELECT payment_date, total_amount FROM room_payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 10 YEAR)
        ) AS combined_payments
        GROUP BY YEAR(payment_date)
        ORDER BY payment_year ASC
    ";
    $stmt_yearly = $conn->prepare($yearly_query);
    $stmt_yearly->execute();
    $yearly_payments = $stmt_yearly->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Fetch monthly payment totals (last 24 months) - changed to total amount instead of count
    $monthly_totals_query = "
        SELECT DATE_FORMAT(payment_date, '%Y-%m') AS payment_month, 
               COALESCE(SUM(total_amount), 0) AS total_amount
        FROM (
            SELECT payment_date, total_amount FROM payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
            UNION ALL
            SELECT payment_date, total_amount FROM room_payments
            WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
        ) AS combined_payments
        GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY payment_month ASC
    ";
    $stmt_monthly_totals = $conn->prepare($monthly_totals_query);
    $stmt_monthly_totals->execute();
    $monthly_payment_totals = $stmt_monthly_totals->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch(PDOException $e) {
    $error_message = "Connection failed: " . $e->getMessage();
    $tomorrow_bookings = [];
    $today_bookings = [];
    $daily_payments = [];
    $monthly_payments = [];
    $yearly_payments = [];
    $monthly_payment_totals = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6c5ce7;
            --new: #b61beaff;
            --end: #f31111ae;
            --secondary: #e84393;
            --dark: #2d3436;
            --light: #f5f6fa;
            --success: #00b894;
            --info: #0984e3;
            --warning: #fdcb6e;
            --danger: #d63031;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --background: #f9f9f9;
            --panel-bg: white;
            --text-color: #2d3436;
            --clock-bg-light: rgba(255, 255, 255, 0.9);
            --clock-bg-dark: rgba(30, 30, 30, 0.9);
            --clock-glow: 0 0 15px rgba(108, 92, 231, 0.5);
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
            background: linear-gradient(135deg, var(--new), var(--end));
            padding: 30px 20px;
            color: white;
            box-shadow: var(--shadow);
            z-index: 1000;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
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
        
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.sidebar-collapsed {
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
        
        .toggle-sidebar.collapsed {
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
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .dashboard-header h2 {
            font-size: 24px;
            color: var(--text-color);
            animation: slideInLeft 0.5s ease;
            margin-bottom: 10px;
        }
        
        .digital-clock {
            font-family: 'Orbitron', sans-serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            background: var(--clock-bg-light);
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: var(--clock-glow);
            border: 2px solid transparent;
            background-image: linear-gradient(var(--clock-bg-light), var(--clock-bg-light)), 
                             linear-gradient(45deg, var(--primary), var(--secondary));
            background-origin: border-box;
            background-clip: padding-box, border-box;
            backdrop-filter: blur(5px);
            letter-spacing: 2px;
            margin-bottom: 15px;
            animation: pulseGlow 2s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }
        
        .digital-clock span {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .digital-clock span.seconds {
            animation: tick 1s infinite;
        }
        
        [data-theme="dark"] .digital-clock {
            background: var(--clock-bg-dark);
            background-image: linear-gradient(var(--clock-bg-dark), var(--clock-bg-dark)), 
                             linear-gradient(45deg, var(--primary), var(--secondary));
            color: #ffffff;
        }
        
        @keyframes pulseGlow {
            0% { box-shadow: var(--clock-glow); }
            50% { box-shadow: 0 0 25px rgba(108, 92, 231, 0.7); }
            100% { box-shadow: var(--clock-glow); }
        }
        
        @keyframes tick {
            0% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0); }
        }
        
        @keyframes zoomIn {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes slideInLeft {
            0% { transform: translateX(-30px); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        
        .error-message {
            color: #d63031;
            text-align: center;
            margin-bottom: 20px;
            display: <?php echo isset($error_message) ? 'block' : 'none'; ?>;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .bookings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .booking-box {
            padding: 20px;
            border-radius: 12px;
            background: var(--panel-bg);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            overflow-x: auto;
        }
        
        .booking-box:hover {
            transform: translateY(-5px);
        }
        
        .booking-box h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
            table-layout: fixed;
        }
        
        .booking-table th, .booking-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            white-space: normal;
            word-wrap: break-word;
            vertical-align: top;
        }
        
        .booking-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }
        
        .booking-table td {
            color: var(--text-color);
        }
        
        .booking-table tr:hover {
            background: rgba(0, 0, 0, 0.05);
        }
        
        .booking-table th:nth-child(1), .booking-table td:nth-child(1) { width: 100px; }
        .booking-table th:nth-child(2), .booking-table td:nth-child(2) { width: 150px; }
        .booking-table th:nth-child(3), .booking-table td:nth-child(3) { width: 150px; }
        .booking-table th:nth-child(4), .booking-table td:nth-child(4) { width: 60px; }
        .booking-table th:nth-child(5), .booking-table td:nth-child(5) { width: 100px; }
        .booking-table th:nth-child(6), .booking-table td:nth-child(6) { width: 100px; }
        .booking-table th:nth-child(7), .booking-table td:nth-child(7) { width: 120px; }
        .booking-table th:nth-child(8), .booking-table td:nth-child(8) { width: 120px; }
        
        .no-bookings {
            text-align: center;
            color: var(--text-color);
            font-style: italic;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .chart-box {
            padding: 20px;
            border-radius: 12px;
            background: var(--panel-bg);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .chart-box:hover {
            transform: translateY(-5px);
        }
        
        .chart-box h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .chart-box canvas {
            max-width: 100%;
            height: 300px !important;
        }

        .totals-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .total-box {
            padding: 20px;
            border-radius: 12px;
            background: var(--panel-bg);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            overflow-x: auto;
        }

        .total-box:hover {
            transform: translateY(-5px);
        }

        .total-box h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
            table-layout: fixed;
        }

        .total-table th, .total-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            white-space: normal;
            word-wrap: break-word;
            vertical-align: top;
        }

        .total-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        .total-table td {
            color: var(--text-color);
        }

        .total-table tr:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .total-table th:nth-child(1), .total-table td:nth-child(1) { width: 150px; }
        .total-table th:nth-child(2), .total-table td:nth-child(2) { width: 150px; }
        
        @media screen and (max-width: 1440px) {
            .sidebar { width: 250px; }
            .main-content { margin-left: 250px; }
            .main-content.sidebar-collapsed { margin-left: 20px; }
            .toggle-sidebar { left: 270px; }
            .toggle-sidebar.collapsed { left: 40px; }
            .bookings-container, .charts-container, .totals-container { grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }
            .digital-clock { font-size: 28px; }
            .dashboard-header h2 { font-size: 22px; }
            .booking-table, .total-table { font-size: 13px; }
            .booking-table th, .booking-table td, .total-table th, .total-table td { padding: 8px 6px; }
            .chart-box canvas { height: 250px !important; }
        }
        
        @media screen and (min-width: 1920px) {
            .sidebar { width: 320px; }
            .main-content { margin-left: 320px; }
            .main-content.sidebar-collapsed { margin-left: 20px; }
            .toggle-sidebar { left: 340px; }
            .toggle-sidebar.collapsed { left: 40px; }
            .bookings-container, .charts-container, .totals-container { grid-template-columns: repeat(auto-fit, minmax(600px, 1fr)); }
            .digital-clock { font-size: 36px; }
            .dashboard-header h2 { font-size: 28px; }
            .booking-table, .total-table { font-size: 16px; }
            .chart-box canvas { height: 350px !important; }
        }
        
        @media screen and (max-width: 1200px) {
            .booking-box, .chart-box, .total-box { overflow-x: auto; }
            .booking-table, .total-table { min-width: 700px; }
            .chart-box canvas { min-width: 400px; }
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
                <div class="menu-item" onclick="window.location.href='admin/function_payment.php'">
                    <i class="fas fa-credit-card"></i>
                    <span>Function Payments</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin_menu.php'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Function Menus</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/supplier_payment.php'">
                    <i class="fas fa-receipt"></i>
                    <span>Supplier Payments</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/room_sale.php'">
                    <i class="fas fa-bed"></i>
                    <span>Room Payments</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/back_office_room_bill.html'">
                    <i class="fas fa-file-invoice"></i>
                    <span>Back/Off Room Bill</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/front_office_room_bill.php'">
                    <i class="fas fa-file-invoice"></i>
                    <span>Front/Off Room Bill</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/main_ordersheet.php'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>M/K Ordersheets</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/hgg_res_ordersheet.php'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>HGG B/R Ordersheets</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/sky_ordersheet.php'">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Sky/R Ordersheets</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/stores_po.php'">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Stores Purchased</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/stores_grn.php'">
                    <i class="fas fa-receipt"></i>
                    <span>Stores GRN History</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/logistic_po.php'">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Logistic Purchased</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin/logistic_grn.php'">
                    <i class="fas fa-receipt"></i>
                    <span>Logistic GRN History</span>
                </div>
                <div class="menu-item" onclick="window.location.href='admin_logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <div>
                    <button class="toggle-sidebar" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button class="theme-toggle" title="Toggle Dark/Light Mode" aria-label="Toggle Theme">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
            
            <div class="dashboard">
                <div class="dashboard-header">
                    <div class="digital-clock" id="digital-clock"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <h2>Dashboard</h2>
                        <div class="date">Today: <span id="current-date"></span></div>
                    </div>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="bookings-container">
                    <div class="booking-box">
                        <h3>Tomorrow's Bookings</h3>
                        <?php if (count($tomorrow_bookings) > 0): ?>
                            <div class="table-wrapper">
                                <table class="booking-table">
                                    <thead>
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Name</th>
                                            <th>Couple</th>
                                            <th>Pax</th>
                                            <th>Function Type</th>
                                            <th>Hall</th>
                                            <th>Total Amount (LKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tomorrow_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['booking_reference'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['full_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['couple_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['no_of_pax'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['function_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['venue_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo $booking['total_amount'] > 0 ? 'LKR ' . number_format($booking['total_amount'], 2) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-bookings">No bookings for tomorrow.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="booking-box">
                        <h3>Today's Ongoing Bookings</h3>
                        <?php if (count($today_bookings) > 0): ?>
                            <div class="table-wrapper">
                                <table class="booking-table">
                                    <thead>
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Name</th>
                                            <th>Couple</th>
                                            <th>Pax</th>
                                            <th>Function Type</th>
                                            <th>Hall</th>
                                            <th>Time</th>
                                            <th>Total Amount (LKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($today_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['booking_reference'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['full_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['couple_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['no_of_pax'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['function_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['venue_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars(
                                                    ($booking['time_from'] ?? 'N/A') . ' ' . 
                                                    ($booking['time_from_am_pm'] ?? '') . ' - ' . 
                                                    ($booking['time_to'] ?? 'N/A') . ' ' . 
                                                    ($booking['time_to_am_pm'] ?? '')
                                                ); ?></td>
                                                <td><?php echo $booking['total_amount'] > 0 ? 'LKR ' . number_format($booking['total_amount'], 2) : 'N/A'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-bookings">No ongoing bookings today.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="totals-container">
                    <div class="total-box">
                        <h3>Monthly Payment Totals (Last 24 Months)</h3>
                        <?php if (count($monthly_payment_totals) > 0): ?>
                            <div class="table-wrapper">
                                <table class="total-table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Total Amount (LKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($monthly_payment_totals as $total): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($total['payment_month']); ?></td>
                                                <td>LKR <?php echo number_format($total['total_amount'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="no-bookings">No payment records found for the last 24 months.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="charts-container">
                    <div class="chart-box">
                        <h3>Daily Payments (Last 30 Days)</h3>
                        <canvas id="dailyChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <h3>Monthly Payments (Last 24 Months)</h3>
                        <canvas id="monthlyChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <h3>Yearly Payments (Last 10 Years)</h3>
                        <canvas id="yearlyChart"></canvas>
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
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const toggleButton = this;
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed');
            toggleButton.classList.toggle('collapsed');
        });

        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', function() {
            document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            this.innerHTML = `<i class="fas fa-${document.body.dataset.theme === 'dark' ? 'sun' : 'moon'}"></i>`;
            updateChartColors();
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

        // Set current date and real-time clock
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);
        
        function updateClock() {
            const now = new Date();
            const timeOptions = {
                timeZone: 'Asia/Colombo',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const timeString = now.toLocaleTimeString('en-US', timeOptions);
            const [hours, minutes, secondsAndPeriod] = timeString.split(':');
            const [seconds, period] = secondsAndPeriod.split(' ');
            document.getElementById('digital-clock').innerHTML = 
                `${hours}:${minutes}:<span class="seconds">${seconds}</span> ${period}`;
        }
        
        // Update clock every second
        updateClock();
        setInterval(updateClock, 1000);

        // Chart data
        const dailyData = {
            labels: [<?php
                $labels = [];
                $startDate = new DateTime();
                $startDate->modify('-29 days');
                for ($i = 0; $i < 30; $i++) {
                    $labels[] = "'" . $startDate->format('Y-m-d') . "'";
                    $startDate->modify('+1 day');
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Daily Payments (LKR)',
                data: [<?php
                    $data = array_fill(0, 30, 0);
                    foreach ($daily_payments as $payment) {
                        $index = array_search($payment['payment_date'], array_map(function($d) {
                            return (new DateTime())->modify('-29 days')->modify("+$d days")->format('Y-m-d');
                        }, range(0, 29)));
                        if ($index !== false) {
                            $data[$index] = $payment['total_amount'];
                        }
                    }
                    echo implode(',', $data);
                ?>],
                borderColor: 'rgba(108, 92, 231, 1)',
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(108, 92, 231, 0.5)');
                    gradient.addColorStop(1, 'rgba(108, 92, 231, 0)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        };

        const monthlyData = {
            labels: [<?php
                $labels = [];
                $startDate = new DateTime();
                $startDate->modify('-23 months');
                for ($i = 0; $i < 24; $i++) {
                    $labels[] = "'" . $startDate->format('Y-m') . "'";
                    $startDate->modify('+1 month');
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Monthly Payments (LKR)',
                data: [<?php
                    $data = array_fill(0, 24, 0);
                    foreach ($monthly_payments as $payment) {
                        $index = array_search($payment['payment_month'], array_map(function($d) {
                            return (new DateTime())->modify('-23 months')->modify("+$d months")->format('Y-m');
                        }, range(0, 23)));
                        if ($index !== false) {
                            $data[$index] = $payment['total_amount'];
                        }
                    }
                    echo implode(',', $data);
                ?>],
                borderColor: 'rgba(232, 67, 147, 1)',
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(232, 67, 147, 0.5)');
                    gradient.addColorStop(1, 'rgba(232, 67, 147, 0)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        };

        const yearlyData = {
            labels: [<?php
                $labels = [];
                $startYear = (int)date('Y') - 9;
                for ($i = 0; $i < 10; $i++) {
                    $labels[] = "'" . ($startYear + $i) . "'";
                }
                echo implode(',', $labels);
            ?>],
            datasets: [{
                label: 'Yearly Payments (LKR)',
                data: [<?php
                    $data = array_fill(0, 10, 0);
                    foreach ($yearly_payments as $payment) {
                        $index = array_search($payment['payment_year'], array_map(function($d) use ($startYear) {
                            return (string)($startYear + $d);
                        }, range(0, 9)));
                        if ($index !== false) {
                            $data[$index] = $payment['total_amount'];
                        }
                    }
                    echo implode(',', $data);
                ?>],
                borderColor: 'rgba(0, 184, 148, 1)',
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(0, 184, 148, 0.5)');
                    gradient.addColorStop(1, 'rgba(0, 184, 148, 0)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        };

        // Chart configuration
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (LKR)',
                        color: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436'
                    },
                    ticks: {
                        color: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436'
                    },
                    grid: {
                        color: () => document.body.dataset.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436',
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436'
                    }
                },
                tooltip: {
                    backgroundColor: () => document.body.dataset.theme === 'dark' ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.8)',
                    titleColor: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436',
                    bodyColor: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436',
                    borderColor: () => document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436',
                    borderWidth: 1
                }
            },
            interaction: {
                mode: 'nearest',
                intersect: false
            },
            elements: {
                line: {
                    borderWidth: 2
                }
            }
        };

        // Initialize charts
        const dailyChart = new Chart(document.getElementById('dailyChart'), {
            type: 'line',
            data: dailyData,
            options: chartOptions
        });

        const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: monthlyData,
            options: chartOptions
        });

        const yearlyChart = new Chart(document.getElementById('yearlyChart'), {
            type: 'line',
            data: yearlyData,
            options: chartOptions
        });


        // Update chart colors on theme change
        function updateChartColors() {
            dailyChart.options.scales.y.title.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.options.scales.y.ticks.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.options.scales.x.ticks.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.options.plugins.legend.labels.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.options.scales.y.grid.color = document.body.dataset.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            dailyChart.options.plugins.tooltip.backgroundColor = document.body.dataset.theme === 'dark' ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.8)';
            dailyChart.options.plugins.tooltip.titleColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.options.plugins.tooltip.bodyColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.options.plugins.tooltip.borderColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            dailyChart.update();

            monthlyChart.options.scales.y.title.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.options.scales.y.ticks.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.options.scales.x.ticks.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.options.plugins.legend.labels.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.options.scales.y.grid.color = document.body.dataset.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            monthlyChart.options.plugins.tooltip.backgroundColor = document.body.dataset.theme === 'dark' ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.8)';
            monthlyChart.options.plugins.tooltip.titleColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.options.plugins.tooltip.bodyColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.options.plugins.tooltip.borderColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            monthlyChart.update();

            yearlyChart.options.scales.y.title.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.options.scales.y.ticks.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.options.scales.x.ticks.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.options.plugins.legend.labels.color = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.options.scales.y.grid.color = document.body.dataset.theme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            yearlyChart.options.plugins.tooltip.backgroundColor = document.body.dataset.theme === 'dark' ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.8)';
            yearlyChart.options.plugins.tooltip.titleColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.options.plugins.tooltip.bodyColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.options.plugins.tooltip.borderColor = document.body.dataset.theme === 'dark' ? '#ffffff' : '#2d3436';
            yearlyChart.update();
        }
    </script>
</body>
</html>