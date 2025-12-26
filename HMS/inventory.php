<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: inventory_login.php");
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
    
    // Fetch pending and issued order counts
    $pending_query = $conn->query("SELECT COUNT(DISTINCT order_sheet_no) as count FROM hggorder_sheet WHERE status = 'pending'");
    $pending_count = $pending_query->fetch(PDO::FETCH_ASSOC)['count'];
    
    $issued_query = $conn->query("SELECT COUNT(DISTINCT order_sheet_no) as count FROM hggorder_sheet WHERE status = 'issued'");
    $issued_count = $issued_query->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Fetch data for charts
    $monthly_data = $conn->query("SELECT DATE_FORMAT(request_date, '%Y-%m') as period, 
                                 COUNT(DISTINCT order_sheet_no) as orders, 
                                 SUM(requested_qty) as quantity,
                                 function_type
                                 FROM hggorder_sheet 
                                 GROUP BY period, function_type 
                                 ORDER BY period DESC LIMIT 12");
    $monthly_results = $monthly_data->fetchAll(PDO::FETCH_ASSOC);
    
    $daily_data = $conn->query("SELECT DATE(request_date) as period, 
                               COUNT(DISTINCT order_sheet_no) as orders, 
                               SUM(requested_qty) as quantity,
                               function_type
                               FROM hggorder_sheet 
                               WHERE request_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                               GROUP BY period, function_type 
                               ORDER BY period DESC");
    $daily_results = $daily_data->fetchAll(PDO::FETCH_ASSOC);
    
    $yearly_data = $conn->query("SELECT YEAR(request_date) as period, 
                                COUNT(DISTINCT order_sheet_no) as orders, 
                                SUM(requested_qty) as quantity,
                                function_type
                                FROM hggorder_sheet 
                                GROUP BY period, function_type 
                                ORDER BY period DESC LIMIT 5");
    $yearly_results = $yearly_data->fetchAll(PDO::FETCH_ASSOC);

    // Fetch locations
    $locations_query = $conn->query("SELECT id, name FROM inv_locations");
    $locations = $locations_query->fetchAll(PDO::FETCH_ASSOC);
    $loc_map = [];
    foreach ($locations as $loc) {
        $loc_map[$loc['id']] = $loc['name'];
    }

    // Fetch items
    $items_query = $conn->query("SELECT id, name FROM inv_items");
    $items = $items_query->fetchAll(PDO::FETCH_ASSOC);
    $item_map = [];
    foreach ($items as $item) {
        $item_map[$item['id']] = $item['name'];
    }

    // Fetch inventory history data for location line chart
    $inventory_query = $conn->query("SELECT present_date, location_id, SUM(present_qty) as total
                                     FROM inv_history
                                     WHERE present_date IS NOT NULL AND present_qty IS NOT NULL
                                     GROUP BY present_date, location_id
                                     ORDER BY present_date ASC");
    $inventory_results = $inventory_query->fetchAll(PDO::FETCH_ASSOC);

    // Fetch inventory history data for item line chart (assuming inv_history has item_id column)
    $item_inventory_query = $conn->query("SELECT present_date, item_id, SUM(present_qty) as total
                                          FROM inv_history
                                          WHERE present_date IS NOT NULL AND present_qty IS NOT NULL
                                          GROUP BY present_date, item_id
                                          ORDER BY present_date ASC");
    $item_inventory_results = $item_inventory_query->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error_message = "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hgg Inventory Panel</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #6c5ce7;
            --new: #d91beaff;
            --end: #f31186b1;
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
        
        .error-message {
            color: #d63031;
            text-align: center;
            margin-bottom: 20px;
            display: <?php echo isset($error_message) ? 'block' : 'none'; ?>;
            animation: shake 0.5s ease;
        }
        
        .status-boxes {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .status-box {
            flex: 1;
            min-width: 200px;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            color: white;
            transition: transform 0.3s ease;
            box-shadow: var(--shadow);
        }
        
        .status-box:hover {
            transform: translateY(-5px);
        }
        
        .status-box.pending {
            background: var(--warning);
        }
        
        .status-box.issued {
            background: var(--success);
        }
        
        .status-box h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .status-box p {
            font-size: 32px;
            font-weight: 600;
            margin: 0;
        }
        
        .chart-container {
            margin-top: 40px;
            padding: 20px;
            background: var(--panel-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .chart-container h3 {
            margin-bottom: 20px;
            color: var(--text-color);
        }
        
        .chart-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .chart-tab {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            background: var(--light);
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .chart-tab.active {
            background: var(--primary);
            color: white;
        }
        
        canvas {
            max-width: 100%;
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
            
            .status-box {
                min-width: 100%;
            }
            
            .digital-clock {
                font-size: 24px;
                padding: 10px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .digital-clock {
                font-size: 20px;
                padding: 8px 16px;
            }
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
                <div class="menu-item" onclick="window.location.href='inventory/add.php'">
                    <i class="fas fa-plus"></i>
                    <span>Add Inventory Items</span>
                </div>
                 <div class="menu-item" onclick="window.location.href='inventory/inventory.php'">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Inventory Details</span>
                </div>
                <div class="menu-item" onclick="window.location.href='inventory/inventory.php'">
                    <i class="fas fa-search"></i>
                    <span>View Inventory Reports</span>
                </div>
                <div class="menu-item" onclick="window.location.href='logistic/inventory_audit.php'">
                    <i class="fas fa-area-chart"></i>
                    <span>Logistic Stock Count</span>
                </div>
              
                <div class="menu-item" onclick="window.location.href='inventory_logout.php'">
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
                    <div class="digital-clock" id="digital-clock"></div>
                    <div style="display: flex; justify-content: space-between; width: 100%;">
                        <h2>Dashboard</h2>
                        <div class="date">Today: <span id="current-date"></span></div>
                    </div>
                </div>
                
                
                <div class="chart-container">
                    <h3>Inventory Count by Location Over Time</h3>
                    <canvas id="inventoryChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3>Inventory Count by Item Over Time</h3>
                    <canvas id="itemInventoryChart"></canvas>
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
        
        // For mobile view
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar').classList.add('collapsed');
        }

        // Chart data
        const monthlyData = <?php echo json_encode($monthly_results); ?>;
        const dailyData = <?php echo json_encode($daily_results); ?>;
        const yearlyData = <?php echo json_encode($yearly_results); ?>;
        const inventoryData = <?php echo json_encode($inventory_results); ?>;
        const itemInventoryData = <?php echo json_encode($item_inventory_results); ?>;
        const locMap = <?php echo json_encode($loc_map); ?>;
        const itemMap = <?php echo json_encode($item_map); ?>;

        // Random color generator
        function getRandomColor() {
            var letters = '0123456789ABCDEF';
            var color = '#';
            for (var i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * 16)];
            }
            return color;
        }

        // Inventory chart (by location)
        let dates = [...new Set(inventoryData.map(d => d.present_date))].sort();
        let invDatasets = [];
        for (let locId in locMap) {
            let data = dates.map(date => {
                let found = inventoryData.find(d => d.present_date === date && d.location_id == locId);
                return found ? found.total : 0;
            });
            invDatasets.push({
                label: locMap[locId],
                data: data,
                borderColor: getRandomColor(),
                tension: 0.1
            });
        }
        new Chart('inventoryChart', {
            type: 'line',
            data: {
                labels: dates,
                datasets: invDatasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Item inventory chart (by item)
        let itemDates = [...new Set(itemInventoryData.map(d => d.present_date))].sort();
        let itemDatasets = [];
        for (let itemId in itemMap) {
            let data = itemDates.map(date => {
                let found = itemInventoryData.find(d => d.present_date === date && d.item_id == itemId);
                return found ? found.total : 0;
            });
            itemDatasets.push({
                label: itemMap[itemId],
                data: data,
                borderColor: getRandomColor(),
                tension: 0.1
            });
        }
        new Chart('itemInventoryChart', {
            type: 'line',
            data: {
                labels: itemDates,
                datasets: itemDatasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>