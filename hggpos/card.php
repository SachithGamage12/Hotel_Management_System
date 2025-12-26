<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
require_once 'db_connect.php';

$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'day';
$filter_value = isset($_GET['filter_value']) ? $_GET['filter_value'] : date('Y-m-d');
$payment_filter = isset($_GET['payment_filter']) ? $_GET['payment_filter'] : 'all';
$invoices = [];
$error_message = '';
$summary = [
    'total_invoices' => 0,
    'total_amount' => 0,
    'card_customer_count' => 0,
    'card_staff_count' => 0
];

try {
    $valid_filter_types = ['day', 'month', 'year'];
    if (!in_array($filter_type, $valid_filter_types)) {
        $filter_type = 'day';
    }

    // Build base query
    $query = "
        SELECT id, invoice_number, created_at, payment_type, cashier,
               subtotal, discount, service_charge, COALESCE(delivery_charge, 0) AS delivery_charge, grand_total
        FROM invoices
        WHERE status != 'canceled'
    ";

    $params = [];
    $param_types = '';

    // Payment type filter
    if ($payment_filter === 'card_customer') {
        $query .= " AND payment_type = 'card_customer'";
    } elseif ($payment_filter === 'card_staff') {
        $query .= " AND payment_type = 'card_staff'";
    } else {
        $query .= " AND payment_type IN ('card_customer', 'card_staff')";
    }

    // Date filter
    if ($filter_type === 'day' && !empty($filter_value)) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_value)) {
            $query .= " AND DATE(created_at) = ?";
            $params[] = $filter_value;
            $param_types .= 's';
        } else {
            $error_message = "Invalid date format.";
        }
    } elseif ($filter_type === 'month' && !empty($filter_value)) {
        if (preg_match('/^\d{4}-\d{2}$/', $filter_value)) {
            $query .= " AND DATE_FORMAT(created_at, '%Y-%m') = ?";
            $params[] = $filter_value;
            $param_types .= 's';
        } else {
            $error_message = "Invalid month format.";
        }
    } elseif ($filter_type === 'year' && !empty($filter_value)) {
        if (is_numeric($filter_value) && $filter_value >= 2000 && $filter_value <= date('Y')) {
            $query .= " AND YEAR(created_at) = ?";
            $params[] = $filter_value;
            $param_types .= 'i';
        } else {
            $error_message = "Invalid year.";
        }
    }

    $query .= " ORDER BY created_at DESC LIMIT 500";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate summary
    $summary['total_invoices'] = count($invoices);
    foreach ($invoices as $invoice) {
        $summary['total_amount'] += $invoice['grand_total'];
        if ($invoice['payment_type'] === 'card_customer') {
            $summary['card_customer_count']++;
        } else {
            $summary['card_staff_count']++;
        }
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Card Invoices</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --navbar-bg: #1e40af;
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --green: #10b981;
            --green-dark: #059669;
            --red: #ef4444;
            --blue: #3b82f6;
            --orange: #f59e0b;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            color: var(--text-primary);
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-icon {
            width: 24px;
            height: 24px;
            color: var(--text-primary);
        }

        .main-content {
            margin-top: 80px;
            padding: 40px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        .card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            animation: fadeInUp 0.6s ease-out;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .summary-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--green);
        }

        .filter-section {
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        select, input[type="date"], input[type="month"], input[type="number"] {
            padding: 10px 12px;
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        button {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            border: none;
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.5);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid var(--glass-border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-primary);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }

        th {
            background: var(--bg-secondary);
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            font-weight: 400;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: rgba(99, 102, 241, 0.1);
            cursor: pointer;
            transform: scale(1.01);
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .badge-card-customer {
            background: rgba(16, 185, 129, 0.2);
            color: var(--green);
        }

        .badge-card-staff {
            background: rgba(59, 130, 246, 0.2);
            color: var(--blue);
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--blue), #2563eb);
            border: none;
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.5);
        }

        .error-message {
            color: var(--red);
            font-weight: 500;
            margin-bottom: 16px;
            padding: 12px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--red);
            border-radius: 8px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 24px;
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--glass-border);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .close-btn {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-secondary);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: var(--red);
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
                margin-top: 60px;
            }

            .navbar {
                padding: 12px 16px;
            }

            .card {
                padding: 16px;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: 1fr 1fr;
            }

            th, td {
                padding: 8px;
                font-size: 0.85rem;
            }

            .modal-content {
                width: 95%;
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Guardia POS</a>
        <div class="navbar-user">
            <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>User: <?php echo $username; ?></span>
        </div>
    </nav>

    <div class="main-content">
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Invoices</div>
                <div class="summary-value"><?php echo $summary['total_invoices']; ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Amount</div>
                <div class="summary-value">LKR <?php echo number_format($summary['total_amount'], 2); ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Card Customer Invoices</div>
                <div class="summary-value" style="color: var(--green);"><?php echo $summary['card_customer_count']; ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Card Staff Invoices</div>
                <div class="summary-value" style="color: var(--blue);"><?php echo $summary['card_staff_count']; ?></div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form" id="filterForm">
                <div class="form-group">
                    <label class="form-label">Payment Type</label>
                    <select name="payment_filter" id="paymentFilter">
                        <option value="all" <?php echo $payment_filter === 'all' ? 'selected' : ''; ?>>All Card</option>
                        <option value="card_customer" <?php echo $payment_filter === 'card_customer' ? 'selected' : ''; ?>>Card Customer</option>
                        <option value="card_staff" <?php echo $payment_filter === 'card_staff' ? 'selected' : ''; ?>>Card Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Filter Type</label>
                    <select name="filter_type" id="filterType" onchange="updateFilterInput()">
                        <option value="day" <?php echo $filter_type === 'day' ? 'selected' : ''; ?>>Daily</option>
                        <option value="month" <?php echo $filter_type === 'month' ? 'selected' : ''; ?>>Monthly</option>
                        <option value="year" <?php echo $filter_type === 'year' ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
                <div class="form-group" id="dayGroup">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="filter_value" id="filterValue" value="<?php echo $filter_type === 'day' ? htmlspecialchars($filter_value) : date('Y-m-d'); ?>">
                </div>
                <div class="form-group" id="monthGroup" style="display: none;">
                    <label class="form-label">Select Month</label>
                    <input type="month" name="filter_value_month" id="filterValueMonth" value="<?php echo $filter_type === 'month' ? htmlspecialchars($filter_value) : date('Y-m'); ?>">
                </div>
                <div class="form-group" id="yearGroup" style="display: none;">
                    <label class="form-label">Select Year</label>
                    <input type="number" name="filter_value_year" id="filterValueYear" value="<?php echo $filter_type === 'year' ? htmlspecialchars($filter_value) : date('Y'); ?>" min="2000" max="<?php echo date('Y'); ?>">
                </div>
                <div class="form-group">
                    <button type="submit">Apply Filter</button>
                </div>
            </form>
        </div>

        <!-- Invoices Table -->
        <div class="card">
            <h2 class="card-title">Card Invoices - <?php 
                $display_filter = ucfirst($filter_type);
                if ($payment_filter !== 'all') {
                    $display_filter .= ' | ' . ($payment_filter === 'card_customer' ? 'Card Customer' : 'Card Staff');
                }
                echo $display_filter . ': ' . htmlspecialchars($filter_value); 
            ?></h2>
            <?php if (empty($invoices)): ?>
                <div class="no-data">
                    <p>No card invoices found for the selected criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table id="invoicesTable">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date & Time</th>
                                <th>Payment Type</th>
                                <th>Cashier</th>
                                <th>Subtotal</th>
                                <th>Discount</th>
                                <th>Service Charge</th>
                                <th>Delivery</th>
                                <th>Grand Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr data-invoice-id="<?php echo htmlspecialchars((int)$invoice['id']); ?>">
                                    <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($invoice['created_at']))); ?></td>
                                    <td>
                                        <span class="badge <?php echo $invoice['payment_type'] === 'card_customer' ? 'badge-card-customer' : 'badge-card-staff'; ?>">
                                            <?php echo htmlspecialchars($invoice['payment_type'] === 'card_customer' ? 'Card Customer' : 'Card Staff'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($invoice['cashier']); ?></td>
                                    <td>LKR <?php echo number_format($invoice['subtotal'], 2); ?></td>
                                    <td>LKR <?php echo number_format($invoice['discount'], 2); ?></td>
                                    <td>LKR <?php echo number_format($invoice['service_charge'], 2); ?></td>
                                    <td>LKR <?php echo number_format($invoice['delivery_charge'], 2); ?></td>
                                    <td><strong>LKR <?php echo number_format($invoice['grand_total'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <a href="dashboard.php" class="action-btn">
            <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <!-- Invoice Items Modal -->
    <div class="modal" id="invoiceItemsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Invoice Items</h3>
                <span class="close-btn" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalError" class="error-message" style="display: none;"></div>
            <div class="table-container">
                <table id="itemsTable">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr><td colspan="4" style="text-align: center;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Filter Type Update Function
        function updateFilterInput() {
            const filterType = document.getElementById('filterType').value;

            // Show/hide appropriate input fields
            document.getElementById('dayGroup').style.display = filterType === 'day' ? 'flex' : 'none';
            document.getElementById('monthGroup').style.display = filterType === 'month' ? 'flex' : 'none';
            document.getElementById('yearGroup').style.display = filterType === 'year' ? 'flex' : 'none';

            // Set the correct name attribute for form submission
            document.getElementById('filterValue').name = filterType === 'day' ? 'filter_value' : '';
            document.getElementById('filterValueMonth').name = filterType === 'month' ? 'filter_value' : '';
            document.getElementById('filterValueYear').name = filterType === 'year' ? 'filter_value' : '';
        }

        // Initialize filter input on page load
        updateFilterInput();

        // Filter Form Submit Handler
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const filterType = document.getElementById('filterType').value;
            const paymentFilter = document.getElementById('paymentFilter').value;
            let filterValue;

            // Get the appropriate filter value based on filter type
            if (filterType === 'day') {
                filterValue = document.getElementById('filterValue').value;
            } else if (filterType === 'month') {
                filterValue = document.getElementById('filterValueMonth').value;
            } else {
                filterValue = document.getElementById('filterValueYear').value;
            }

            // Redirect with query parameters
            window.location.href = `card.php?filter_type=${filterType}&filter_value=${encodeURIComponent(filterValue)}&payment_filter=${paymentFilter}`;
        });

        // Open Modal Function
        function openModal(invoiceId) {
            console.log('Opening modal for invoice ID:', invoiceId);

            // Validate invoice ID
            if (!invoiceId || isNaN(invoiceId) || invoiceId <= 0) {
                console.error('Invalid invoiceId:', invoiceId);
                document.getElementById('modalError').textContent = 'Invalid invoice ID';
                document.getElementById('modalError').style.display = 'block';
                return;
            }

            const modal = document.getElementById('invoiceItemsModal');
            const itemsTableBody = document.getElementById('itemsTableBody');
            const modalError = document.getElementById('modalError');

            // Reset modal state
            modalError.style.display = 'none';
            itemsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Loading...</td></tr>';
            modal.style.display = 'flex';

            // Fetch invoice items from server
            fetch('get_invoice_items.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json' 
                },
                body: JSON.stringify({ 
                    invoice_id: parseInt(invoiceId) 
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not OK: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                itemsTableBody.innerHTML = '';

                if (data.success) {
                    if (data.items.length === 0) {
                        itemsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No items found for this invoice.</td></tr>';
                    } else {
                        // Populate table with invoice items
                        data.items.forEach(item => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <strong>${escapeHtml(item.item_code || 'N/A')}</strong><br>
                                    <small style="color: var(--text-secondary);">${escapeHtml(item.item_name || 'Unknown Item')}</small>
                                </td>
                                <td>${escapeHtml(item.quantity)}</td>
                                <td>LKR ${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td><strong>LKR ${parseFloat(item.total_price).toFixed(2)}</strong></td>
                            `;
                            itemsTableBody.appendChild(row);
                        });
                    }
                } else {
                    // Handle error response
                    itemsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--red);">Error loading items</td></tr>';
                    modalError.textContent = data.error || 'Failed to load invoice items';
                    modalError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                itemsTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center; color: var(--red);">Error loading items</td></tr>';
                modalError.textContent = 'Error fetching items: ' + error.message;
                modalError.style.display = 'block';
            });
        }

        // Close Modal Function
        function closeModal() {
            const modal = document.getElementById('invoiceItemsModal');
            modal.style.display = 'none';
        }

        // HTML Escape Function for Security
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Add Click Event to Invoice Table Rows
        const invoicesTable = document.getElementById('invoicesTable');
        if (invoicesTable) {
            invoicesTable.addEventListener('click', (event) => {
                const row = event.target.closest('tr');

                // Make sure we clicked on a tbody row (not thead)
                if (row && row.parentElement.tagName === 'TBODY') {
                    const invoiceId = row.getAttribute('data-invoice-id');
                    console.log('Clicked row with invoice ID:', invoiceId);

                    if (invoiceId) {
                        openModal(invoiceId);
                    } else {
                        console.error('No invoice ID found on row');
                        document.getElementById('modalError').textContent = 'No invoice ID found';
                        document.getElementById('modalError').style.display = 'block';
                    }
                }
            });
        }

        // Close Modal When Clicking Outside
        window.addEventListener('click', (event) => {
            const modal = document.getElementById('invoiceItemsModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Close Modal with ESC Key
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Optional: Add loading indicator for filter form
        document.getElementById('filterForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Loading...';
            }
        });
    </script>
</body>
</html>