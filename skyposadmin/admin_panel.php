<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);
require_once '../hggpos/db_connect.php';

// Fetch stats
$total_items = $conn->query("SELECT COUNT(*) AS cnt FROM hggitems")->fetch_assoc()['cnt'];
$active_items = $conn->query("SELECT COUNT(*) AS cnt FROM hggitems WHERE status = 'active'")->fetch_assoc()['cnt'];
$total_categories = $conn->query("SELECT COUNT(*) AS cnt FROM categories")->fetch_assoc()['cnt'];
$discounted_items = $conn->query("SELECT COUNT(*) AS cnt FROM hggitems WHERE discount_price IS NOT NULL AND discount_price > 0")->fetch_assoc()['cnt'];

// Recent items (last 5)
$recent_items = $conn->query("
    SELECT item_code, item_name, price, status 
    FROM hggitems 
    ORDER BY id DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --primary: #6366f1; --primary-dark: #4f46e5; --secondary: #ec4899; --accent: #f59e0b;
            --bg-primary: #0f0f23; --bg-secondary: #1a1a2e; --glass: rgba(255,255,255,0.05);
            --glass-border: rgba(255,255,255,0.1); --text-primary: #ffffff; --text-secondary: #a1a1aa;
            --navbar-bg: #1e40af; --success: #22c55e; --warning: #f59e0b; --danger: #ef4444;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -2;
            background: linear-gradient(45deg, #0f0f23, #1a1a2e, #16213e);
        }
        .bg-animation::before {
            content: ''; position: absolute; width: 200%; height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99,102,241,0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236,72,153,0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(245,158,11,0.2) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }
        @keyframes float {
            0%,100% { transform: translate(-50%,-50%) rotate(0deg) scale(1); }
            33% { transform: translate(-45%,-55%) rotate(120deg) scale(1.1); }
            66% { transform: translate(-55%,-45%) rotate(240deg) scale(0.9); }
        }

        /* Navbar */
        .navbar {
            background: var(--navbar-bg);
            padding: 16px 32px;
            display: flex; justify-content: space-between; align-items: center;
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: #fff; font-size: 1.5rem; font-weight: 700; text-decoration: none;
            background: linear-gradient(45deg, #fff, #a855f7); -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .navbar-user {
            display: flex; align-items: center; gap: 8px; font-weight: 500;
        }
        .user-icon { width: 24px; height: 24px; fill: currentColor; }

        /* Sidebar */
        .sidebar {
            position: fixed; left: 0; top: 70px; bottom: 0; width: 260px;
            background: rgba(26, 26, 46, 0.7); backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border); padding: 24px 16px;
            transition: transform 0.3s ease; z-index: 900;
        }
        .sidebar.collapsed { transform: translateX(-100%); }
        .nav-link {
            display: flex; align-items: center; gap: 12px; padding: 12px 16px;
            color: var(--text-secondary); text-decoration: none; border-radius: 10px;
            margin-bottom: 8px; transition: all 0.3s ease; font-weight: 500;
        }
        .nav-link:hover, .nav-link.active {
            background: var(--glass); color: var(--text-primary);
            box-shadow: 0 4px 12px rgba(99,102,241,0.2);
        }
        .nav-link svg { width: 20px; height: 20px; }

        /* Main Content */
        .main-content {
            margin-left: 260px; margin-top: 70px; padding: 32px;
            transition: margin-left 0.3s ease;
        }
        .main-content.full { margin-left: 0; }

        /* Page Header */
        .page-header {
            margin-bottom: 32px;
        }
        .page-title {
            font-size: 2.2rem; font-weight: 700;
            background: linear-gradient(45deg, #fff, #c4b5fd);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shine 2s ease-in-out infinite;
        }
        @keyframes shine { 0%,100% { filter: brightness(1); } 50% { filter: brightness(1.3); } }

        /* Stats Cards */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 32px;
        }
        .stat-card {
            background: var(--glass); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 16px;
            padding: 24px; position: relative; overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .stat-value {
            font-size: 2.2rem; font-weight: 800; margin: 8px 0;
            background: linear-gradient(45deg, var(--primary), #a855f7);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .stat-label {
            color: var(--text-secondary); font-size: 0.9rem; font-weight: 500;
        }
        .stat-icon {
            position: absolute; top: 16px; right: 16px; width: 40px; height: 40px;
            opacity: 0.15; fill: var(--primary);
        }

        /* Recent Activity */
        .activity-card {
            background: var(--glass); backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border); border-radius: 16px;
            padding: 24px; margin-bottom: 32px;
        }
        .card-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 16px;
        }
        .card-title {
            font-size: 1.1rem; font-weight: 600; color: var(--text-primary);
        }
        .view-all {
            color: var(--primary); font-size: 0.85rem; text-decoration: none;
        }
        .activity-list {
            list-style: none;
        }
        .activity-item {
            display: flex; justify-content: space-between; padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 0.9rem;
        }
        .activity-item:last-child { border-bottom: none; }
        .status-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600;
        }
        .status-active { background: rgba(34,197,94,0.2); color: #86efac; }
        .status-disabled { background: rgba(239,68,68,0.2); color: #fca5a5; }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none; background: none; border: none; color: #fff;
            font-size: 1.5rem; cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: block; }
            .sidebar.open { transform: translateX(0); }
        }
        @media (max-width: 576px) {
            .navbar { padding: 12px 16px; }
            .main-content { padding: 16px; }
            .page-title { font-size: 1.8rem; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Animated BG -->
    <div class="bg-animation"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <button class="mobile-toggle" id="sidebarToggle">Menu</button>
        <a href="admin_panel.php" class="navbar-brand">Guardia POS</a>
        <div class="navbar-user">
            <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>Admin: <?php echo $username; ?></span>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <nav>
            <a href="admin_panel.php" class="nav-link active">
                <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                Dashboard
            </a>
            <a href="add_item.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Add Item
            </a>
            <a href="edit_item.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 0 0 0-1.41l-2.34-2.34a.996.996 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                Edit Items
            </a>
            <a href="categories.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 6h-6c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1z"/></svg>
                Categories
            </a>
            <a href="reports.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14H8v-4h2v4zm0-6H8V7h2v4zm4 6h-2v-2h2v2zm0-4h-2V9h2v4z"/></svg>
                Reports
            </a>
            <a href="logout.php" class="nav-link" style="margin-top: auto; color: #fca5a5;">
                <svg viewBox="0 0 24 24"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5a2 2 0 0 0-2 2v4h2V5h14v14H5v-4H3v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Admin Dashboard</h1>
            <p style="color: var(--text-secondary); margin-top: 8px;">Manage your POS system with real-time insights.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14H8v-4h2v4zm0-6H8V7h2v4zm4 6h-2v-2h2v2zm0-4h-2V9h2v4z"/></svg>
                </div>
                <div class="stat-value" data-count="<?php echo $total_items; ?>">0</div>
                <div class="stat-label">Total Items</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
                <div class="stat-value" data-count="<?php echo $active_items; ?>">0</div>
                <div class="stat-label">Active Items</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 6h-6c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1z"/></svg>
                </div>
                <div class="stat-value" data-count="<?php echo $total_categories; ?>">0</div>
                <div class="stat-label">Categories</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                </div>
                <div class="stat-value" data-count="<?php echo $discounted_items; ?>">0</div>
                <div class="stat-label">Discounted Items</div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-card">
            <div class="card-header">
                <h3 class="card-title">Recently Added Items</h3>
                <a href="edit_item.php" class="view-all">View All →</a>
            </div>
            <?php if (!empty($recent_items)): ?>
                <ul class="activity-list">
                    <?php foreach ($recent_items as $item): ?>
                        <li class="activity-item">
                            <div>
                                <strong><?php echo htmlspecialchars($item['item_code']); ?></strong> – <?php echo htmlspecialchars($item['item_name']); ?>
                            </div>
                            <div>
                                LKR <?php echo number_format($item['price'], 2); ?>
                                <span class="status-badge <?php echo $item['status'] === 'active' ? 'status-active' : 'status-disabled'; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="color: var(--text-secondary); text-align:center; padding: 16px;">No items added yet.</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('sidebarToggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Animate counter
        document.querySelectorAll('.stat-value').forEach(el => {
            const target = parseInt(el.getAttribute('data-count'));
            let count = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                count += increment;
                if (count >= target) {
                    el.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(count).toLocaleString();
                }
            }, 30);
        });
    </script>
</body>
</html>