<?php
// Start output buffering to prevent header errors
ob_start();

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    ob_end_clean();
    header("Location: index.php");
    exit();
}

// Initialize held invoices if not set
if (!isset($_SESSION['held_invoices'])) {
    $_SESSION['held_invoices'] = array_fill(1, 10, null);
}

$username = htmlspecialchars($_SESSION['username']);
require_once 'db_connect.php';

// Fetch categories
$categories = [];
$error_message = '';

try {
    $cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    if ($cat_result) {
        $categories = $cat_result->fetch_all(MYSQLI_ASSOC);
    } else {
        $error_message = "Failed to fetch categories: " . $conn->error;
    }
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Fetch last invoice number
$max_num = 1400; // Default starting number
try {
    $last_inv_result = $conn->query("SELECT MAX(CAST(invoice_number AS UNSIGNED)) as max_num FROM invoices");
    if ($last_inv_result) {
        $row = $last_inv_result->fetch_assoc();
        $max_num = $row['max_num'] ? intval($row['max_num']) + 1 : 1400;
    }
} catch (Exception $e) {
    $error_message .= "\nFailed to fetch last invoice number: " . $e->getMessage();
}

$conn->close();

// Define LKR symbol
$lkr_symbol = 'LKR';

$invoice_number = str_pad($max_num, 4, '0', STR_PAD_LEFT);
$next_invoice_num = $max_num + 1;

// End output buffering and send content
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Create Invoice</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4338ca;
            --primary-light: #a5b4fc;
            --primary-ultra-light: #eef2ff;
            --secondary: #06b6d4;
            --accent: #ec4899;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            
            --bg-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-secondary: #ffffff;
            --bg-tertiary: #f8fafc;
            --bg-glass: rgba(255, 255, 255, 0.95);
            
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            --text-white: #ffffff;
            
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --border-focus: var(--primary);
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
        }

        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Enhanced Header */
        .header {
            background: var(--bg-glass);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-lg);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: var(--transition);
        }

        .brand:hover {
            transform: translateY(-1px);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 1.3rem;
            box-shadow: var(--shadow);
        }

        .brand-text {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--primary-ultra-light);
            padding: 10px 18px;
            border-radius: 50px;
            border: 1px solid var(--primary-light);
            transition: var(--transition);
        }

        .user-info:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .user-icon {
            width: 24px;
            height: 24px;
            color: var(--primary);
        }

        /* Page Header */
        .page-header {
            padding: 40px 32px;
            text-align: center;
            background: transparent;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--text-white) 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
            letter-spacing: -0.025em;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.15rem;
            font-weight: 400;
        }

        /* Main Layout */
        .main-content {
            flex: 1;
            padding: 0 32px 32px;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        .panel-container {
            display: grid;
            grid-template-columns: 420px 1fr 400px;
            gap: 28px;
            min-height: calc(100vh - 280px);
        }

        /* Enhanced Cards */
        .card {
            background: var(--bg-secondary);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            border: 1px solid var(--border-light);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .card-header {
            padding: 28px 28px 20px 28px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, var(--primary-ultra-light) 0%, rgba(255, 255, 255, 0.5) 100%);
        }

        .card-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 28px;
            flex: 1;
            overflow-y: auto;
        }

        /* Enhanced Form Elements */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
            letter-spacing: -0.01em;
        }

        .form-select, .form-input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: var(--transition);
            background: var(--bg-secondary);
            font-family: inherit;
            font-weight: 500;
        }

        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-select:hover:not(:focus), .form-input:hover:not(:focus) {
            border-color: var(--primary-light);
        }

        /* Enhanced Search */
        .search-container {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            width: 22px;
            height: 22px;
            z-index: 10;
        }

        .search-input {
            padding-left: 52px;
            font-weight: 500;
        }

        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--bg-secondary);
            border: 2px solid var(--primary-light);
            border-radius: var(--radius);
            max-height: 320px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: var(--shadow-xl);
            margin-top: 6px;
        }

        .suggestion-item {
            padding: 18px 20px;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .suggestion-item:hover {
            background: var(--primary-ultra-light);
            transform: translateX(4px);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-name {
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-primary);
            font-size: 1rem;
        }

        .suggestion-meta {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .suggestion-price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }

        /* Enhanced Quantity Controls */
        .quantity-group {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-bottom: 24px;
            justify-content: center;
            background: var(--primary-ultra-light);
            padding: 20px;
            border-radius: var(--radius-lg);
            border: 2px solid var(--primary-light);
        }

        .quantity-input {
            width: 90px;
            text-align: center;
            padding: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            border-radius: var(--radius);
        }

        /* Enhanced Buttons */
        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            letter-spacing: -0.01em;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn:not(:disabled):active {
            transform: translateY(0);
        }

        .btn-sm {
            padding: 10px 18px;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--text-white);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #0891b2 100%);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover:not(:disabled) {
            background: #e2e8f0;
            border-color: var(--primary-light);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: var(--text-white);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: var(--text-white);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            color: var(--text-white);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
            color: var(--text-white);
        }

        /* Enhanced Invoice Section */
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .invoice-number {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
        }

        .invoice-date {
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .items-list {
            max-height: 420px;
            overflow-y: auto;
            margin-bottom: 28px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary-light) transparent;
        }

        .items-list::-webkit-scrollbar {
            width: 6px;
        }

        .items-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .items-list::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 3px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 20px;
            opacity: 0.3;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid var(--border-light);
            transition: var(--transition);
        }

        .item-row:hover {
            background: var(--primary-ultra-light);
            margin: 0 -20px;
            padding: 18px 20px;
            border-radius: var(--radius);
            border-bottom-color: transparent;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-size: 1.05rem;
        }

        .item-meta {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .item-price {
            font-weight: 700;
            color: var(--primary);
            margin-right: 14px;
            font-size: 1.15rem;
        }

        .item-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Enhanced Total Section */
        .total-section {
            margin-top: 24px;
            padding: 24px;
            background: linear-gradient(135deg, var(--primary-ultra-light) 0%, rgba(255, 255, 255, 0.5) 100%);
            border-radius: var(--radius-lg);
            border: 2px solid var(--primary-light);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 6px 0;
        }

        .total-label {
            font-weight: 500;
            color: var(--text-secondary);
        }

        .total-amount {
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary);
            border-top: 2px solid var(--primary-light);
            padding-top: 16px;
            margin-top: 16px;
        }

        /* Enhanced Options Section */
        .options-section {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid var(--border);
        }

        .options-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Enhanced Table Options */
        .table-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }

        .table-option-box {
            position: relative;
            padding: 16px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            font-weight: 600;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .table-option-box:hover {
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .table-option-box.selected {
            border-color: var(--primary);
            background: var(--primary-ultra-light);
            color: var(--primary);
        }

        .selection-box {
            width: 20px;
            height: 20px;
            border: 2px solid currentColor;
            border-radius: 4px;
            flex-shrink: 0;
            position: relative;
        }

        .table-option-box.selected .selection-box::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 2px;
        }

        .hold-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: var(--text-white);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: var(--shadow);
        }

        /* Enhanced Payment Options */
        .payment-options {
            margin-bottom: 24px;
        }

        .option-box:not(.table-option-box) {
            display: flex;
            align-items: center;
            padding: 16px 18px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 12px;
            background: var(--bg-secondary);
        }

        .option-box:not(.table-option-box):hover {
            border-color: var(--primary-light);
            transform: translateX(4px);
            box-shadow: var(--shadow);
        }

        .option-box.selected:not(.table-option-box) {
            border-color: var(--primary);
            background: var(--primary-ultra-light);
            color: var(--primary);
        }

        .option-label {
            font-weight: 600;
            margin-left: 4px;
        }

        /* Enhanced Extra Fields */
        .extra-field {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-light);
            animation: slideDown 0.3s ease-out;
        }

        .extra-field.hidden {
            display: none;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced Loading */
        .loading {
            display: none;
            border: 3px solid var(--border-light);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            width: 28px;
            height: 28px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Toast */
        .toast {
            position: fixed;
            bottom: 28px;
            right: 28px;
            background: var(--success);
            color: var(--text-white);
            padding: 18px 28px;
            border-radius: var(--radius);
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            box-shadow: var(--shadow-xl);
            font-weight: 600;
            max-width: 400px;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background: var(--danger);
        }

        /* Error Messages */
        .error-card {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .action-buttons .btn {
            flex: 1;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .panel-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .main-content {
                padding: 0 20px 20px;
            }
            
            .header {
                padding: 12px 20px;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .quantity-group {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            
            .quantity-input {
                width: 100%;
            }
            
            .table-options {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        /* Print Styles */
        #printArea, #kotArea {
            display: none;
        }

        @media print {
            body > *:not(#printArea):not(#kotArea) {
                display: none !important;
            }
            
            #printArea, #kotArea {
                display: block !important;
                width: 80mm;
                margin: 0;
                padding: 5mm;
                background: #fff;
                font-family: 'Arial', sans-serif;
                font-size: 12pt;
                line-height: 1.5;
                color: #000;
            }
        }

        /* Utility Classes */
        .hidden { display: none !important; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-4 { margin-bottom: 24px; }
    </style>
    <!-- Include Print.js library from CDN -->
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
       <!-- Header -->
<div class="header">
    <div style="display: flex; align-items: center; gap: 20px;">
        <a href="dashboard.php" class="brand">
            <div class="brand-icon">G</div>
            <span class="brand-text">Guardia POS</span>
        </a>
        <a href="dashboard.php" class="btn btn-secondary" style="text-decoration: none; padding: 10px 16px; display: flex; align-items: center; gap: 6px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            Home
        </a>
    </div>
    <div style="display: flex; align-items: center; gap: 16px;">
        <div class="user-info">
            <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>User: <?php echo $username; ?></span>
        </div>
        <a href="logout.php" class="btn btn-danger" style="text-decoration: none; padding: 10px 16px; display: flex; align-items: center; gap: 6px;" onclick="return confirm('Are you sure you want to logout?')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Logout
        </a>
    </div>
</div>

        <!-- Page Header -->
        <div class="main-content">
            <?php if ($error_message): ?>
                <div class="error-card">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="panel-container">
                <!-- Left Column: Search & Add Items -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                Add Items
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <select id="categorySelect" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Search by Name</label>
                                <div class="search-container">
                                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                    <input type="text" id="itemNameInput" class="form-input search-input" placeholder="Enter item name..." autocomplete="off">
                                    <div id="nameSuggestions" class="suggestions"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Search by Code</label>
                                <div class="search-container">
                                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                    <input type="text" id="itemCodeInput" class="form-input search-input" placeholder="Enter item code..." autocomplete="off">
                                    <div id="codeSuggestions" class="suggestions"></div>
                                </div>
                            </div>
                            
                            <div class="loading" id="loadingSpinner"></div>
                            
                            <div id="quantityGroup" class="hidden">
                                <div class="form-group">
                                    <label class="form-label">Quantity</label>
                                    <div class="quantity-group">
                                        <button id="decreaseQty" class="btn btn-secondary btn-sm">−</button>
                                        <input type="number" id="quantityInput" class="form-input quantity-input" value="1" min="1">
                                        <button id="increaseQty" class="btn btn-secondary btn-sm">+</button>
                                    </div>
                                </div>
                                
                                <button id="addToInvoice" class="btn btn-primary" disabled style="width: 100%;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="16"></line>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg>
                                    Add to Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Middle Column: Invoice Preview -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <div class="invoice-meta">
                                <div>
                                    <h2 class="card-title">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path>
                                            <polyline points="14,2 14,8 20,8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                        Invoice
                                    </h2>
                                    <div class="invoice-number"><span id="invoiceNumber"><?php echo $invoice_number; ?></span></div>
                                </div>
                                <div class="invoice-date">
                                    <span id="invoiceDate"><?php echo date('Y-m-d H:i:s'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="items-list" id="itemsList">
                                <div class="empty-state">
                                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p>No items added yet</p>
                                </div>
                            </div>
                            
                            <div class="total-section">
                                <div class="total-row">
                                    <span class="total-label">Items:</span>
                                    <span id="itemCount">0</span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Subtotal:</span>
                                    <span id="subtotal"><?php echo $lkr_symbol; ?> 0.00</span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Discount:</span>
                                    <span id="discountAmount"><?php echo $lkr_symbol; ?> 0.00</span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Service Charge (10%):</span>
                                    <span id="serviceCharge"><?php echo $lkr_symbol; ?> 0.00</span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Delivery Charge:</span>
                                    <span id="deliveryCharge"><?php echo $lkr_symbol; ?> 0.00</span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Grand Total:</span>
                                    <span id="grandTotal" class="total-amount"><?php echo $lkr_symbol; ?> 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Management -->
                <div>
                    <div class="card">
                        <div class="options-section">
                            <div class="card-header">
                                <h2 class="card-title">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="9" cy="9" r="2"></circle>
                                        <path d="M21 15.5c-1.5-1.5-4-1.5-5.5 0"></path>
                                    </svg>
                                    Table Holds
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="table-options">
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <div class="option-box table-option-box" data-table="<?php echo $i; ?>">
                                            <div class="selection-box"></div>
                                            <span class="option-label"><?php echo $i; ?> Table</span>
                                            <span class="hold-badge <?php echo isset($_SESSION['held_invoices'][$i]) && $_SESSION['held_invoices'][$i] ? '' : 'hidden'; ?>" id="table<?php echo $i; ?>-badge">
                                                <?php
                                                if (isset($_SESSION['held_invoices'][$i]) && $_SESSION['held_invoices'][$i]) {
                                                    $totalItems = array_sum(array_column($_SESSION['held_invoices'][$i]['items'], 'quantity'));
                                                    echo $totalItems;
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                
                                <button id="holdInvoice" class="btn btn-warning" disabled style="width: 100%; margin-bottom: 20px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 6v6l4 2"></path>
                                    </svg>
                                    Hold Invoice
                                </button>
                            </div>
                        </div>
                        
                        <div class="options-section">
                            <h3 class="section-title">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                                Payment Type
                            </h3>
                            
                            <div class="payment-options">
                                <div class="option-box" data-type="cash_customer">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Cash Customer</span>
                                </div>
                                
                                <div class="option-box" data-type="cash_staff">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Cash Staff</span>
                                </div>
                                
                                <div class="option-box" data-type="card_customer">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Card Customer</span>
                                </div>
                                
                                <div class="option-box" data-type="card_staff">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Card Staff</span>
                                </div>
                                
                                <div class="option-box" data-type="credit">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Credit</span>
                                    <div class="extra-field hidden" id="creditorField">
                                        <label class="form-label">Creditor Name</label>
                                        <input type="text" class="form-input" id="creditorName" placeholder="Enter creditor name...">
                                    </div>
                                </div>
                                
                                <div class="option-box" data-type="other_credit">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Other Credit</span>
                                    <div class="extra-field hidden" id="otherCreditorField">
                                        <label class="form-label">Other Creditor Name</label>
                                        <input type="text" class="form-input" id="otherCreditorName" placeholder="Enter other creditor name...">
                                    </div>
                                </div>
                                
                                <div class="option-box" data-type="foc">
                                    <div class="selection-box"></div>
                                    <span class="option-label">FOC</span>
                                    <div class="extra-field hidden" id="focField">
                                        <label class="form-label">FOC Responsible Person</label>
                                        <input type="text" class="form-input" id="focResponsible" placeholder="Enter responsible person name...">
                                    </div>
                                </div>
                                
                                <div class="option-box" data-type="take_away">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Take Away</span>
                                </div>
                                
                                <div class="option-box" data-type="delivery">
                                    <div class="selection-box"></div>
                                    <span class="option-label">Delivery</span>
                                    <div class="extra-field hidden" id="deliveryField">
                                        <label class="form-label">Delivery Place</label>
                                        <input type="text" class="form-input" id="deliveryPlace" placeholder="Enter delivery address...">
                                        <label class="form-label" style="margin-top: 12px;">Delivery Charge</label>
                                        <input type="number" class="form-input" id="deliveryChargeInput" placeholder="Enter delivery charge..." min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <h3 class="section-title">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2v20M5 10H2v4h3v-4zm4.5 0H6v4h3.5v-4zm4 0H10v4h3.5v-4zm4 0H14v4h3.5v-4zm4 0H18v4h3v-4z"></path>
                                </svg>
                                Discount
                            </h3>
                            <div class="form-group">
                                <label class="form-label">Discount Amount</label>
                                <input type="number" id="discountInput" class="form-input" min="0" value="0" placeholder="Enter discount amount...">
                            </div>
                            
                            <div class="action-buttons">
                                <button id="printKOT" class="btn btn-info" disabled>
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4zm2 2H5V5h14v14z"></path>
                                    </svg>
                                    Print KOT
                                </button>
                                <button id="saveInvoice" class="btn btn-success" disabled>
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                                        <polyline points="17,21 17,13 7,13 7,21"></polyline>
                                        <polyline points="7,3 7,8 15,8"></polyline>
                                    </svg>
                                    Save & Print Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Area - Optimized for Thermal Printer with Creditor Signature -->
    <div id="printArea" style="width: 80mm; font-family: 'Arial', sans-serif; font-size: 12pt; line-height: 1.5; color: #000; background: #fff; margin: 0; padding: 5mm;">
        <div style="text-align: center; margin: 0; padding: 0;">
            <img src="../images/hg logo.png" alt="Guardia POS Logo" style="width: 100%; max-width: 150mm; height: auto; max-height: 30mm; margin: 0 auto; object-fit: contain;">
            <div style="font-size: 13pt; font-weight: bold; margin-top: 2mm;">Guardian Bar & Restaurant</div>
            <div style="font-size: 11pt; font-weight: bold; margin-bottom: 1mm;">UN MARKETING (PVT) LTD</div>
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 1mm;">No.18, Bandarawatta, Edandawala, Kuruwita</div>
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 1mm;">VAT REG No: 114733180-7000</div>
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 2mm;">Tel: 071 87 22 305 | 045 22 61 600</div>
            <div style="border-top: 1px dotted #000; margin-bottom: 2mm;"></div>
            <div style="display: flex; justify-content: space-between; font-size: 10pt; font-weight: bold; margin-bottom: 2mm;">
                <span>Invoice: <span id="printInvoiceNumber"></span></span>
                <span id="printInvoiceDate"></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 10pt; font-weight: bold; margin-bottom: 2mm; border-bottom: 1px solid #000; padding-bottom: 2mm;">
                <span id="printTableNumber"></span>
                <span id="printCashier"></span>
            </div>
            <div id="paymentContainer" style="display: flex; justify-content: space-between; font-size: 10pt; font-weight: bold; margin-bottom: 3mm; border-bottom: 2px solid #000; padding-bottom: 3mm;">
                <span id="printPaymentType"></span>
                <span id="printExtraInfo"></span>
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 10pt; margin-bottom: 5mm;">
                <thead>
                    <tr style="border-top: 2px solid #000; border-bottom: 1px solid #000;">
                        <th style="width: 45%; text-align: left; padding: 2mm 1mm; font-weight: bold;">Item</th>
                        <th style="width: 15%; text-align: center; padding: 2mm 1mm; font-weight: bold;">Qty</th>
                        <th style="width: 20%; text-align: right; padding: 2mm 1mm; font-weight: bold;">Rate</th>
                        <th style="width: 20%; text-align: right; padding: 2mm 1mm; font-weight: bold;">Total</th>
                    </tr>
                </thead>
                <tbody id="printItems"></tbody>
            </table>
            <div style="font-size: 10pt; font-weight: bold; margin-bottom: 5mm; border-top: 1px dashed #000; padding-top: 3mm;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 2mm;">
                    <span>Items:</span>
                    <span id="printItemCount"></span>
                </div>
                <div id="printSubtotalRow" style="display: flex; justify-content: space-between; margin-bottom: 2mm;">
                    <span>Subtotal:</span>
                    <span id="printSubtotal"></span>
                </div>
                <div id="printDiscountRow" style="display: none; justify-content: space-between; margin-bottom: 2mm;">
                    <span>Discount:</span>
                    <span id="printDiscount"></span>
                </div>
                <div id="printServiceChargeRow" style="display: none; justify-content: space-between; margin-bottom: 2mm;">
                    <span>Service Charge:</span>
                    <span id="printServiceCharge"></span>
                </div>
                <div id="printDeliveryChargeRow" style="display: none; justify-content: space-between; margin-bottom: 2mm;">
                    <span>Delivery Charge:</span>
                    <span id="printDeliveryCharge"></span>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 3mm;">
                    <span style="font-size: 11pt;">Grand Total:</span>
                    <span id="printGrandTotal" style="font-size: 11pt;"></span>
                </div>
            </div>
            
            <!-- Creditor Signature Section - Only shown for credit payments -->
            <div id="printSignatureSection" style="display: none; margin-top: 8mm; padding-top: 5mm; border-top: 1px dashed #000;">
                <div style="font-size: 10pt; font-weight: bold; margin-bottom: 8mm; text-align: left;">
                    Creditor Name: <span id="printCreditorName" style="text-decoration: underline;"></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15mm;">
                    <div style="text-align: left;">
                        <div style="border-bottom: 1px solid #000; width: 30mm; margin-bottom: 2mm;"></div>
                        <div style="font-size: 9pt; font-weight: bold;">Date</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="border-bottom: 1px solid #000; width: 35mm; margin-bottom: 2mm;"></div>
                        <div style="font-size: 9pt; font-weight: bold;">Creditor Signature</div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; font-size: 9pt; font-weight: bold; margin-bottom: 3mm; margin-top: 5mm;">
                * All Taxes Included
            </div>
            <div style="text-align: center; font-size: 12pt; font-weight: bold; font-family: 'Georgia', serif; letter-spacing: 1px; margin-top: 3mm; border-top: 2px solid #000; padding-top: 3mm;">
                Thank You Come Again
            </div>
        </div>
    </div>

<div id="kotArea" style="width: 80mm; font-family: 'Arial', sans-serif; font-size: 14pt; line-height: 1.5; color: #000; background: #fff; margin: 0; padding: 5mm;">
        <div style="text-align: center; margin: 0; padding: 0;">
            <div style="font-size: 16pt; font-weight: bold; margin-bottom: 2mm; border: 2px solid #000; padding: 3mm; background: #ffffcc;">
                KITCHEN ORDER TICKET
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12pt; font-weight: bold; margin-bottom: 3mm; border-bottom: 2px solid #000; padding-bottom: 2mm;">
                <span>Invoice #: <span id="kotNumber"></span></span>
                <span id="kotTime"></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 12pt; font-weight: bold; margin-bottom: 3mm;">
                <span>Cashier: <span id="kotCashier"></span></span>
            </div>
            <table style="width: 100%; border-collapse: collapse; font-size: 12pt; margin-bottom: 5mm;">
                <thead>
                    <tr style="border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <th style="width: 70%; text-align: left; padding: 2mm 1mm; font-weight: bold;">ITEM</th>
                        <th style="width: 30%; text-align: center; padding: 2mm 1mm; font-weight: bold;">QTY</th>
                    </tr>
                </thead>
                <tbody id="kotItems"></tbody>
            </table>
            <div style="font-size: 11pt; font-weight: bold; margin-bottom: 3mm; border-top: 1px dashed #000; padding-top: 3mm;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 2mm;">
                    <span>Total Items:</span>
                    <span id="kotTotalItems"></span>
                </div>
            </div>
            <div style="text-align: center; font-size: 10pt; font-weight: bold; margin-bottom: 3mm;">
                *** KITCHEN COPY ***
            </div>
            <div style="text-align: center; font-size: 11pt; font-weight: bold; font-family: 'Georgia', serif; letter-spacing: 1px; margin-top: 3mm; border-top: 2px solid #000; padding-top: 3mm;">
                PREPARE WITH CARE
            </div>
            <div style="text-align: center; font-size: 9pt; margin-top: 2mm; color: #666;">
                Printed: <span id="kotPrintTime"></span>
            </div>
        </div>
    </div>


    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
    let selectedItem = null;
    let invoiceItems = [];
    let nextInvoiceNumber = <?php echo $next_invoice_num; ?>;
    let selectedTable = null;
    let heldInvoices = <?php echo json_encode($_SESSION['held_invoices']); ?>;
    const lkrSymbol = '<?php echo $lkr_symbol; ?>';
    let kotCounter = 1;

    // DOM Elements
    const categorySelect = document.getElementById('categorySelect');
    const itemNameInput = document.getElementById('itemNameInput');
    const itemCodeInput = document.getElementById('itemCodeInput');
    const nameSuggestions = document.getElementById('nameSuggestions');
    const codeSuggestions = document.getElementById('codeSuggestions');
    const quantityInput = document.getElementById('quantityInput');
    const decreaseBtn = document.getElementById('decreaseQty');
    const increaseBtn = document.getElementById('increaseQty');
    const addBtn = document.getElementById('addToInvoice');
    const itemsList = document.getElementById('itemsList');
    const itemCount = document.getElementById('itemCount');
    const subtotalEl = document.getElementById('subtotal');
    const discountAmountEl = document.getElementById('discountAmount');
    const serviceChargeEl = document.getElementById('serviceCharge');
    const deliveryChargeEl = document.getElementById('deliveryCharge');
    const grandTotalEl = document.getElementById('grandTotal');
    const saveBtn = document.getElementById('saveInvoice');
    const holdBtn = document.getElementById('holdInvoice');
    const printKOTBtn = document.getElementById('printKOT');
    const quantityGroup = document.getElementById('quantityGroup');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const toast = document.getElementById('toast');
    const discountInput = document.getElementById('discountInput');

    // Update Buttons
    function updateButtons() {
        holdBtn.disabled = !(selectedTable && invoiceItems.length > 0);
        saveBtn.disabled = invoiceItems.length === 0;
        printKOTBtn.disabled = invoiceItems.length === 0;
        addBtn.disabled = !selectedItem || !quantityInput.value || parseInt(quantityInput.value) <= 0;
        console.log(`updateButtons: addBtn.disabled = ${addBtn.disabled}, selectedItem = ${!!selectedItem}, quantityInput.value = ${quantityInput.value}`);
    }

    // Table Selection
    document.querySelectorAll('.table-option-box').forEach(box => {
        box.addEventListener('click', () => {
            const newTable = parseInt(box.dataset.table);
            if (selectedTable === newTable) return;

            if (selectedTable && invoiceItems.length > 0) {
                heldInvoices[selectedTable] = {
                    items: invoiceItems.map(item => ({...item})),
                    timestamp: new Date().toISOString()
                };
                fetch('update_held_invoices.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ heldInvoices })
                });
                const totalItems = invoiceItems.reduce((sum, item) => sum + item.quantity, 0);
                const badge = document.getElementById(`table${selectedTable}-badge`);
                badge.textContent = totalItems;
                badge.classList.remove('hidden');
                invoiceItems = [];
                showToast(`Auto-held order for Table ${selectedTable} and switched to Table ${newTable}`);
            }

            document.querySelectorAll('.table-option-box').forEach(b => b.classList.remove('selected'));
            box.classList.add('selected');
            selectedTable = newTable;
            const held = heldInvoices[selectedTable];
            invoiceItems = held ? held.items.map(item => ({...item})) : [];
            const totalItems = invoiceItems.reduce((sum, item) => sum + item.quantity, 0);
            renderInvoice();
            showToast(held ? `Resumed order for Table ${selectedTable} (${totalItems} items)` : `New order for Table ${selectedTable}`);
            updateButtons();
        });
    });

    // Payment Options
    document.querySelectorAll('.option-box:not(.table-option-box)').forEach(box => {
        box.addEventListener('click', () => {
            document.querySelectorAll('.option-box:not(.table-option-box)').forEach(b => b.classList.remove('selected'));
            box.classList.add('selected');
            const type = box.dataset.type;
            document.querySelectorAll('.extra-field').forEach(f => f.classList.add('hidden'));
            if (type === 'credit') {
                document.getElementById('creditorField').classList.remove('hidden');
            } else if (type === 'other_credit') {
                document.getElementById('otherCreditorField').classList.remove('hidden');
            } else if (type === 'foc') {
                document.getElementById('focField').classList.remove('hidden');
            } else if (type === 'delivery') {
                document.getElementById('deliveryField').classList.remove('hidden');
            }
            renderInvoice(); // Update service charge and delivery charge based on payment type
        });
    });

    // Show Toast
    function showToast(message, type = 'success') {
        toast.textContent = message;
        toast.className = type === 'success' ? 'toast show' : 'toast error show';
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Search Suggestions
    function fetchSuggestions(query, inputElement, suggestionsElement, searchType) {
        if (query.length < 2 && searchType === 'name') {
            suggestionsElement.innerHTML = '';
            suggestionsElement.style.display = 'none';
            return;
        }

        loadingSpinner.style.display = 'block';

        const categoryId = categorySelect.value;
        const url = `search_items.php?q=${encodeURIComponent(query)}` + 
                    (categoryId ? `&category_id=${categoryId}` : '');

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                loadingSpinner.style.display = 'none';
                suggestionsElement.innerHTML = '';
                suggestionsElement.style.display = 'none';

                if (data.error) {
                    showToast('Error: ' + data.error, 'error');
                    return;
                }

                if (data.length === 0) return;

                if (searchType === 'code') {
                    const exactMatch = data.find(item => item.item_code.toLowerCase() === query.toLowerCase());
                    if (exactMatch) {
                        selectItem(exactMatch);
                        return;
                    }
                }

                suggestionsElement.style.display = 'block';
                data.forEach((item, index) => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.innerHTML = `
                        <div class="suggestion-details">
                            <div class="suggestion-name">${item.item_name}</div>
                            <div class="suggestion-meta">Code: ${item.item_code} | Stock: ${item.stock || 'N/A'}</div>
                        </div>
                        <div class="suggestion-price">${lkrSymbol} ${item.price.toFixed(2)}</div>
                    `;
                    div.addEventListener('click', () => {
                        selectItem(item);
                    });
                    suggestionsElement.appendChild(div);
                });
            })
            .catch(error => {
                loadingSpinner.style.display = 'none';
                console.error('Fetch error:', error);
                showToast('Failed to load suggestions. Please try again.', 'error');
            });
    }

    // Input Handlers
    itemNameInput.addEventListener('input', function() {
        fetchSuggestions(this.value, itemNameInput, nameSuggestions, 'name');
        itemCodeInput.value = '';
        codeSuggestions.style.display = 'none';
    });

    itemCodeInput.addEventListener('input', function() {
        fetchSuggestions(this.value, itemCodeInput, codeSuggestions, 'code');
        itemNameInput.value = '';
        nameSuggestions.style.display = 'none';
    });

    // Category Change Handler
    categorySelect.addEventListener('change', () => {
        itemNameInput.value = '';
        itemCodeInput.value = '';
        nameSuggestions.innerHTML = '';
        codeSuggestions.innerHTML = '';
        nameSuggestions.style.display = 'none';
        codeSuggestions.style.display = 'none';
        selectedItem = null;
        quantityGroup.classList.add('hidden');
        addBtn.disabled = true;
        console.log('Category changed, reset search');
        updateButtons();
    });

    // Select Item
    function selectItem(item) {
        selectedItem = item;
        itemNameInput.value = item.item_name;
        itemCodeInput.value = item.item_code;
        nameSuggestions.style.display = 'none';
        codeSuggestions.style.display = 'none';
        quantityGroup.classList.remove('hidden');
        quantityInput.value = 1; // Reset quantity to 1
        updateButtons();
        console.log(`Selected item: ${item.item_name}, code: ${item.item_code}, price: ${item.price}`);
        quantityInput.focus();
    }

    // Quantity Controls
    decreaseBtn.addEventListener('click', () => {
        let qty = parseInt(quantityInput.value) || 1;
        if (qty > 1) {
            quantityInput.value = qty - 1;
            updateButtons();
            console.log(`Quantity decreased to: ${quantityInput.value}`);
        }
    });

    increaseBtn.addEventListener('click', () => {
        let qty = parseInt(quantityInput.value) || 1;
        quantityInput.value = qty + 1;
        updateButtons();
        console.log(`Quantity increased to: ${quantityInput.value}`);
    });

    // Delivery Charge Input Handler
    document.getElementById('deliveryChargeInput')?.addEventListener('input', () => {
        renderInvoice();
    });

    // Discount Input Handler
    discountInput.addEventListener('input', () => {
        renderInvoice();
    });

    // Add to Invoice
    addBtn.addEventListener('click', () => {
        console.log('Add to Invoice button clicked');
        console.log(`selectedItem: ${JSON.stringify(selectedItem)}`);
        console.log(`quantityInput.value: ${quantityInput.value}`);

        if (!selectedItem) {
            console.error('No item selected');
            showToast('Please select an item first.', 'error');
            return;
        }

        const qty = parseInt(quantityInput.value);
        if (!qty || qty <= 0) {
            console.error('Invalid quantity:', quantityInput.value);
            showToast('Please enter a valid quantity.', 'error');
            return;
        }

        const price = parseFloat(selectedItem.price);
        if (isNaN(price)) {
            console.error('Invalid price:', selectedItem.price);
            showToast('Invalid item price.', 'error');
            return;
        }

        const itemTotal = price * qty;

        const existingIndex = invoiceItems.findIndex(invItem => invItem.id === selectedItem.id);
        if (existingIndex > -1) {
            invoiceItems[existingIndex].quantity += qty;
            invoiceItems[existingIndex].total = invoiceItems[existingIndex].quantity * price;
            console.log(`Updated existing item: ${invoiceItems[existingIndex].name}, new quantity: ${invoiceItems[existingIndex].quantity}`);
        } else {
            const newItem = {
                id: selectedItem.id,
                name: selectedItem.item_name,
                code: selectedItem.item_code,
                price: price,
                quantity: qty,
                total: itemTotal
            };
            invoiceItems.push(newItem);
            console.log(`Added new item: ${newItem.name}, quantity: ${newItem.quantity}`);
        }

        renderInvoice();
        resetSearch();
        showToast('Item added successfully!');
    });

    // Render Invoice - Updated to handle FOC zero total
    function renderInvoice() {
        console.log('Rendering invoice, items:', invoiceItems);
        if (invoiceItems.length === 0) {
            itemsList.innerHTML = `
                <div class="empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>No items added yet</p>
                </div>
            `;
        } else {
            itemsList.innerHTML = '';
            invoiceItems.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'item-row';
                div.innerHTML = `
                    <div class="item-details">
                        <div class="item-name">${item.name}</div>
                        <div class="item-meta">${item.code} | Qty: ${item.quantity} | Unit: ${lkrSymbol} ${item.price.toFixed(2)}</div>
                    </div>
                    <div class="item-actions">
                        <span class="item-price">${lkrSymbol} ${item.total.toFixed(2)}</span>
                        <button class="btn btn-danger btn-sm" onclick="removeItem(${index})">Remove</button>
                    </div>
                `;
                itemsList.appendChild(div);
            });
        }

        const itemsCount = invoiceItems.reduce((sum, item) => sum + item.quantity, 0) || 0;
        const subtotal = invoiceItems.reduce((sum, item) => sum + item.total, 0);
        const selectedPayment = document.querySelector('.option-box:not(.table-option-box).selected');
        const paymentType = selectedPayment ? selectedPayment.dataset.type : null;
        const discount = parseFloat(discountInput.value) || 0;
        const netSubtotal = subtotal - discount;
        const serviceCharge = (paymentType === 'cash_customer' || paymentType === 'card_customer' || paymentType === 'other_credit') ? netSubtotal * 0.1 : 0;
        const deliveryCharge = paymentType === 'delivery' ? parseFloat(document.getElementById('deliveryChargeInput')?.value || 0) : 0;
        
        // FOC bills should have grand total 0
        let grandTotal;
        if (paymentType === 'foc') {
            grandTotal = 0;
        } else {
            grandTotal = netSubtotal + serviceCharge + deliveryCharge;
        }

        itemCount.textContent = itemsCount;
        subtotalEl.textContent = `${lkrSymbol} ${subtotal.toFixed(2)}`;
        discountAmountEl.textContent = `${lkrSymbol} ${discount.toFixed(2)}`;
        serviceChargeEl.textContent = `${lkrSymbol} ${serviceCharge.toFixed(2)}`;
        deliveryChargeEl.textContent = `${lkrSymbol} ${deliveryCharge.toFixed(2)}`;
        grandTotalEl.textContent = `${lkrSymbol} ${grandTotal.toFixed(2)}`;
        updateButtons();
    }

    // Remove Item
    window.removeItem = function(index) {
        console.log(`Removing item at index ${index}`);
        invoiceItems.splice(index, 1);
        renderInvoice();
        showToast('Item removed!');
    };

    // Reset Search
    function resetSearch() {
        console.log('Resetting search');
        selectedItem = null;
        itemNameInput.value = '';
        itemCodeInput.value = '';
        quantityInput.value = 1;
        quantityGroup.classList.add('hidden');
        addBtn.disabled = true;
        nameSuggestions.style.display = 'none';
        codeSuggestions.style.display = 'none';
        updateButtons();
    }

    // Hold Invoice
    holdBtn.addEventListener('click', () => {
        if (!selectedTable || invoiceItems.length === 0) return;
        heldInvoices[selectedTable] = {
            items: invoiceItems.map(item => ({...item})),
            timestamp: new Date().toISOString()
        };
        fetch('update_held_invoices.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ heldInvoices })
        });
        const totalItems = invoiceItems.reduce((sum, item) => sum + item.quantity, 0);
        const badge = document.getElementById(`table${selectedTable}-badge`);
        badge.textContent = totalItems;
        badge.classList.remove('hidden');
        invoiceItems = [];
        renderInvoice();
        showToast(`Order held for Table ${selectedTable}`);
    });

    // Print KOT (Kitchen Order Ticket)
    printKOTBtn.addEventListener('click', () => {
        if (invoiceItems.length === 0) return;

        const now = new Date();
        const timeOptions = {
            timeZone: 'Asia/Colombo',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        const kotDateTime = now.toLocaleString('en-US', timeOptions);
        const kotPrintDateTime = now.toLocaleString('en-US', timeOptions);

        document.getElementById('kotNumber').textContent = document.getElementById('invoiceNumber').textContent;
        document.getElementById('kotTime').textContent = kotDateTime;
        document.getElementById('kotCashier').textContent = '<?php echo $username; ?>';
        document.getElementById('kotPrintTime').textContent = kotPrintDateTime;

        const kotItems = document.getElementById('kotItems');
        kotItems.innerHTML = '';
        invoiceItems.forEach(item => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #ddd';
            tr.innerHTML = `
                <td style="padding: 3mm 1mm; text-align: left; font-weight: bold; font-size: 12pt;">${item.name}</td>
                <td style="padding: 3mm 1mm; text-align: center; font-weight: bold; font-size: 12pt;">${item.quantity}</td>
            `;
            kotItems.appendChild(tr);
        });

        const totalItems = invoiceItems.reduce((sum, item) => sum + item.quantity, 0);
        document.getElementById('kotTotalItems').textContent = totalItems;

        setTimeout(() => {
            try {
                console.log("🍳 Attempting to print KOT with Print.js...");
                printJS({
                    printable: 'kotArea',
                    type: 'html',
                    style: `
                        @media print {
                            @page {
                                size: 80mm auto;
                                margin: 0;
                            }
                            body {
                                margin: 0;
                                padding: 0;
                                width: 80mm;
                                font-family: 'Arial', sans-serif;
                                font-size: 12pt;
                                line-height: 1.5;
                                color: #000;
                            }
                            #kotArea {
                                display: block !important;
                            }
                        }
                    `,
                    onError: (error) => {
                        console.error("❌ KOT Print.js error:", error);
                        showToast('Failed to print KOT. Please check printer settings.', 'error');
                    },
                    onPrintDialogClose: () => {
                        console.log("✅ KOT print dialog closed");
                        kotCounter++;
                        showToast('KOT printed successfully!');
                    }
                });
                console.log("✅ KOT print job initiated with Print.js");
            } catch (error) {
                console.error("❌ KOT Print.js error:", error);
                showToast('Failed to print KOT. Please check printer settings.', 'error');
            }
        }, 500);
    });

    // Save Invoice with Print - Updated with conditional display for charges
// Save Invoice with Print - Updated to auto-save and refresh
saveBtn.addEventListener('click', () => {
    if (invoiceItems.length === 0) return;

    const selectedOption = document.querySelector('.option-box:not(.table-option-box).selected');
    let paymentInfo = 'cash_customer';
    let extra = '';
    if (selectedOption) {
        paymentInfo = selectedOption.dataset.type;
        if (paymentInfo === 'credit') {
            extra = document.querySelector('#creditorName').value || '';
            if (!extra) {
                showToast('Please enter creditor name for credit payment.', 'error');
                return;
            }
        } else if (paymentInfo === 'other_credit') {
            extra = document.querySelector('#otherCreditorName').value || '';
            if (!extra) {
                showToast('Please enter other creditor name for other credit payment.', 'error');
                return;
            }
        } else if (paymentInfo === 'foc') {
            extra = document.querySelector('#focResponsible').value || '';
            if (!extra) {
                showToast('Please enter responsible person for FOC payment.', 'error');
                return;
            }
        } else if (paymentInfo === 'delivery') {
            extra = document.querySelector('#deliveryPlace').value || '';
            if (!extra) {
                showToast('Please enter delivery place for delivery payment.', 'error');
                return;
            }
        }
    }

    const invoiceNumber = document.getElementById('invoiceNumber').textContent;
    const invoiceDate = document.getElementById('invoiceDate').textContent;
    const subtotal = invoiceItems.reduce((sum, item) => sum + item.total, 0);
    const discount = parseFloat(discountInput.value) || 0;
    const netSubtotal = subtotal - discount;
    const service = (paymentInfo === 'cash_customer' || paymentInfo === 'card_customer' || paymentInfo === 'other_credit') ? netSubtotal * 0.1 : 0;
    const delivery = paymentInfo === 'delivery' ? parseFloat(document.getElementById('deliveryChargeInput')?.value || 0) : 0;
    
    // FOC bills should have total 0
    let total;
    if (paymentInfo === 'foc') {
        total = 0;
    } else {
        total = netSubtotal + service + delivery;
    }
    
    const itemCountVal = invoiceItems.reduce((sum, item) => sum + item.quantity, 0);

    // Populate print area for Print.js
    document.getElementById('printInvoiceNumber').textContent = `${invoiceNumber}`;
    document.getElementById('printInvoiceDate').textContent = `${invoiceDate}`;
    document.getElementById('printTableNumber').textContent = selectedTable ? `Table: ${selectedTable}` : '';
    document.getElementById('printCashier').textContent = `Cashier: <?php echo $username; ?>`;
    
    const paymentContainer = document.getElementById('paymentContainer');
    const paymentTypeEl = document.getElementById('printPaymentType');
    const extraInfoEl = document.getElementById('printExtraInfo');
    const paymentText = `Payment: ${paymentInfo.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())}`;
    let extraText = '';
    if (extra) {
        if (paymentInfo === 'credit' || paymentInfo === 'other_credit') {
            extraText = `Creditor: ${extra}`;
        } else if (paymentInfo === 'foc') {
            extraText = `Responsible: ${extra}`;
        } else {
            extraText = `Delivery Place: ${extra}`;
        }
        paymentContainer.style.flexDirection = 'column';
        paymentContainer.style.alignItems = 'flex-start';
        paymentTypeEl.textContent = paymentText;
        extraInfoEl.textContent = extraText;
    } else {
        paymentContainer.style.flexDirection = 'row';
        paymentTypeEl.textContent = paymentText;
        extraInfoEl.textContent = '';
    }

    const printItems = document.getElementById('printItems');
    printItems.innerHTML = '';
    invoiceItems.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding: 2mm 1mm; border-bottom: 1px solid #000; text-align: left; font-size: 10pt; font-weight: bold;">${item.name}</td>
            <td style="padding: 2mm 1mm; border-bottom: 1px solid #000; text-align: center; font-size: 10pt; font-weight: bold;">${item.quantity}</td>
            <td style="padding: 2mm 1mm; border-bottom: 1px solid #000; text-align: right; font-size: 10pt; font-weight: bold;">${item.price.toFixed(2)}</td>
            <td style="padding: 2mm 1mm; border-bottom: 1px solid #000; text-align: right; font-size: 10pt; font-weight: bold;">${item.total.toFixed(2)}</td>
        `;
        printItems.appendChild(tr);
    });
    
    // Update the print section totals with conditional display
    document.getElementById('printItemCount').textContent = `${itemCountVal}`;
    document.getElementById('printSubtotal').textContent = `${subtotal.toFixed(2)}`;

    // Show discount only if greater than 0
    if (discount > 0) {
        document.getElementById('printDiscountRow').style.display = 'flex';
        document.getElementById('printDiscount').textContent = `${discount.toFixed(2)}`;
    } else {
        document.getElementById('printDiscountRow').style.display = 'none';
    }

    // Show service charge only if greater than 0
    if (service > 0) {
        document.getElementById('printServiceChargeRow').style.display = 'flex';
        document.getElementById('printServiceCharge').textContent = `${service.toFixed(2)}`;
    } else {
        document.getElementById('printServiceChargeRow').style.display = 'none';
    }

    // Show delivery charge only if greater than 0
    if (delivery > 0) {
        document.getElementById('printDeliveryChargeRow').style.display = 'flex';
        document.getElementById('printDeliveryCharge').textContent = `${delivery.toFixed(2)}`;
    } else {
        document.getElementById('printDeliveryChargeRow').style.display = 'none';
    }

    document.getElementById('printGrandTotal').textContent = `${total.toFixed(2)}`;

    // Show/hide signature section for credit payments
    const signatureSection = document.getElementById('printSignatureSection');
    if (paymentInfo === 'credit' || paymentInfo === 'other_credit') {
        signatureSection.style.display = 'block';
        document.getElementById('printCreditorName').textContent = extra;
    } else {
        signatureSection.style.display = 'none';
    }

    // Save to database FIRST, then print
    saveToDatabase(invoiceNumber, paymentInfo, extra, total, delivery, discount, () => {
        // After successful save, initiate print
        setTimeout(() => {
            try {
                console.log("📄 Attempting to print with Print.js...");
                printJS({
                    printable: 'printArea',
                    type: 'html',
                    style: `
                        @media print {
                            @page {
                                size: 80mm auto;
                                margin: 0;
                            }
                            body {
                                margin: 0;
                                padding: 0;
                                width: 80mm;
                                font-family: 'Arial', sans-serif;
                                font-size: 12pt;
                                line-height: 1.5;
                                color: #000;
                            }
                            #printArea {
                                display: block !important;
                            }
                        }
                    `,
                    onError: (error) => {
                        console.error("❌ Print.js error:", error);
                        showToast('Invoice saved but failed to print. Please check printer settings.', 'error');
                    },
                    onPrintDialogClose: () => {
                        console.log("✅ Print dialog closed");
                        showToast('Invoice saved and printed successfully!');
                    }
                });
                console.log("✅ Print job initiated with Print.js");
            } catch (error) {
                console.error("❌ Print.js error:", error);
                showToast('Invoice saved but failed to print. Please check printer settings.', 'error');
            }
        }, 500);
    });
});

// Updated Save to Database function with callback and auto-refresh
function saveToDatabase(invoiceNumber, paymentType, extra, total, deliveryCharge, discount, callback) {
    const invoiceData = {
        invoiceNumber: invoiceNumber,
        paymentType: paymentType,
        creditorName: (paymentType === 'credit' || paymentType === 'other_credit') ? extra : null,
        focResponsible: paymentType === 'foc' ? extra : null,
        deliveryPlace: paymentType === 'delivery' ? extra : null,
        deliveryCharge: paymentType === 'delivery' ? deliveryCharge : null,
        discount: discount,
        tableNumber: selectedTable,
        total: total,
        user: '<?php echo $username; ?>',
        items: invoiceItems
    };

    console.log('Sending invoiceData:', JSON.stringify(invoiceData, null, 2));

    // Disable save button to prevent double-clicking
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    fetch('save_invoice.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(invoiceData)
    })
    .then(res => {
        if (!res.ok) throw new Error('Network error');
        return res.json();
    })
    .then(data => {
        if (data.success) {
            let toastMessage = `Invoice ${invoiceData.invoiceNumber} saved as ${paymentType.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())}`;
            if (extra) {
                toastMessage += (paymentType === 'credit' || paymentType === 'other_credit') ? ` for ${extra}` : paymentType === 'foc' ? ` - Responsible: ${extra}` : ` to ${extra}`;
            }
            if (paymentType === 'delivery') {
                toastMessage += ` (Delivery Charge: ${lkrSymbol} ${deliveryCharge.toFixed(2)})`;
            }
            toastMessage += `! Total: ${lkrSymbol} ${total.toFixed(2)}`;
            
            // Clear held invoice if exists
            if (selectedTable && heldInvoices[selectedTable]) {
                heldInvoices[selectedTable] = null;
                const badge = document.getElementById(`table${selectedTable}-badge`);
                badge.classList.add('hidden');
                fetch('update_held_invoices.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ heldInvoices })
                });
            }
            
            // Clear current invoice
            invoiceItems = [];
            discountInput.value = 0;
            
            // Clear payment selection
            document.querySelectorAll('.option-box:not(.table-option-box)').forEach(b => b.classList.remove('selected'));
            document.querySelectorAll('.extra-field').forEach(f => f.classList.add('hidden'));
            
            // Clear extra fields
            document.getElementById('creditorName').value = '';
            document.getElementById('otherCreditorName').value = '';
            document.getElementById('focResponsible').value = '';
            document.getElementById('deliveryPlace').value = '';
            document.getElementById('deliveryChargeInput').value = '0';
            
            // Increment and update invoice number
            document.getElementById('invoiceNumber').textContent = `${String(nextInvoiceNumber).padStart(4, '0')}`;
            nextInvoiceNumber++;
            
            // Update invoice date to current time
            document.getElementById('invoiceDate').textContent = new Date().toLocaleString('en-US', {
                timeZone: 'Asia/Colombo',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            
            renderInvoice();
            
            // Re-enable save button
            saveBtn.disabled = false;
            saveBtn.innerHTML = `
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                    <polyline points="17,21 17,13 7,13 7,21"></polyline>
                    <polyline points="7,3 7,8 15,8"></polyline>
                </svg>
                Save & Print Invoice
            `;
            
            // Execute callback (print function)
            if (callback) callback();
            
            console.log('Invoice saved successfully, ready for next order');
        } else {
            showToast('Failed to save invoice: ' + (data.error || 'Unknown error'), 'error');
            // Re-enable save button on error
            saveBtn.disabled = false;
            saveBtn.innerHTML = `
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                    <polyline points="17,21 17,13 7,13 7,21"></polyline>
                    <polyline points="7,3 7,8 15,8"></polyline>
                </svg>
                Save & Print Invoice
            `;
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        showToast('Failed to save invoice. Please try again.', 'error');
        // Re-enable save button on error
        saveBtn.disabled = false;
        saveBtn.innerHTML = `
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"></path>
                <polyline points="17,21 17,13 7,13 7,21"></polyline>
                <polyline points="7,3 7,8 15,8"></polyline>
            </svg>
            Save & Print Invoice
        `;
    });
}

    // Close suggestions on outside click
    document.addEventListener('click', (e) => {
        if (!itemNameInput.contains(e.target) && !nameSuggestions.contains(e.target)) {
            nameSuggestions.style.display = 'none';
        }
        if (!itemCodeInput.contains(e.target) && !codeSuggestions.contains(e.target)) {
            codeSuggestions.style.display = 'none';
        }
    });

    // Initial render
    renderInvoice();
    </script>
</body>
</html>
