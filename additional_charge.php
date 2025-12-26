<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: frontoffice_login.php");
    exit();
}

$logged_in_username = $_SESSION['username'];

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

$nic = '';
$contact_no = '';
$issued_by = '';
$payment_date = '';
$invoice_number = '';
$additional_charges = [];
$total_amount = 0;
$error = '';
$success = '';
$search_performed = false;

// Database connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "Database connection failed: " . $e->getMessage();
    error_log("Database connection failed: " . $e->getMessage());
}

// Handle search request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search_invoice') {
    $input_invoice_number = filter_var($_POST['invoice_number'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($input_invoice_number) || !is_numeric($input_invoice_number)) {
        $error = "Please enter a valid invoice number (digits only).";
    } else {
        // Prepend 'INV-' to the numeric input
        $invoice_number = 'INV-' . sprintf('%04d', $input_invoice_number);
        try {
            $stmt = $conn->prepare("
                SELECT nic, contact_no, issued_by, payment_date
                FROM room_payments 
                WHERE invoice_number = :invoice_number
            ");
            $stmt->bindParam(':invoice_number', $invoice_number, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $nic = $result['nic'] ?: '';
                $contact_no = $result['contact_no'] ?: '';
                $issued_by = $result['issued_by'] ?: '';
                $payment_date = $result['payment_date'] ? date('Y/m/d', strtotime($result['payment_date'])) : date('Y/m/d');
                $success = "Invoice details retrieved successfully.";
                $search_performed = true;

                // Fetch additional charges
                $stmt = $conn->prepare("
                    SELECT charge_name, room_number, amount 
                    FROM additional_charges 
                    WHERE invoice_number = :invoice_number
                ");
                $stmt->bindParam(':invoice_number', $invoice_number, PDO::PARAM_STR);
                $stmt->execute();
                $additional_charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calculate total amount
                $total_amount = array_sum(array_column($additional_charges, 'amount'));
            } else {
                $error = "No invoice found with number: INV-" . htmlspecialchars($input_invoice_number);
                $invoice_number = '';
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Database error in search_invoice: " . $e->getMessage());
        }
    }
}

// Handle adding additional charge
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_charge') {
    $invoice_number = filter_var($_POST['invoice_number'], FILTER_SANITIZE_STRING);
    $charge_name = filter_var($_POST['charge_name'], FILTER_SANITIZE_STRING);
    $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
    $amount = floatval(preg_replace('/[^0-9.]/', '', $_POST['amount'] ?? 0));

    if (empty($charge_name) || $amount <= 0) {
        $error = "Please provide a valid charge name and amount.";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO additional_charges (invoice_number, charge_name, room_number, amount)
                VALUES (:invoice_number, :charge_name, :room_number, :amount)
            ");
            $stmt->bindParam(':invoice_number', $invoice_number, PDO::PARAM_STR);
            $stmt->bindParam(':charge_name', $charge_name, PDO::PARAM_STR);
            $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->execute();
            
            $success = "Additional charge added successfully.";
            
            // Refresh invoice details
            $stmt = $conn->prepare("
                SELECT nic, contact_no, issued_by, payment_date
                FROM room_payments 
                WHERE invoice_number = :invoice_number
            ");
            $stmt->bindParam(':invoice_number', $invoice_number, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $nic = $result['nic'] ?: '';
                $contact_no = $result['contact_no'] ?: '';
                $issued_by = $result['issued_by'] ?: '';
                $payment_date = $result['payment_date'] ? date('Y/m/d', strtotime($result['payment_date'])) : date('Y/m/d');
                $search_performed = true;

                $stmt = $conn->prepare("
                    SELECT charge_name, room_number, amount 
                    FROM additional_charges 
                    WHERE invoice_number = :invoice_number
                ");
                $stmt->bindParam(':invoice_number', $invoice_number, PDO::PARAM_STR);
                $stmt->execute();
                $additional_charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $total_amount = array_sum(array_column($additional_charges, 'amount'));
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            error_log("Database error in add_charge: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Additional Charges Invoice</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        @media screen {
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8fafc;
                color: #1e293b;
                line-height: 1.4;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                min-height: 100vh;
                padding: 20px;
            }
        }
        .no-print-top {
            width: 160mm;
            margin: 0 auto 20px auto;
            text-align: left;
        }
        .receipt-container {
            width: 160mm;
            min-height: 290mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            font-size: 1rem;
        }
        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-bottom: 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            page-break-after: avoid;
            min-height: 100px;
        }
        .receipt-title-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            width: 100%;
        }
        .receipt-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            flex: 1;
            text-align: center;
        }
        .billing-date, .invoice-number {
            font-size: 0.875rem;
            color: #64748b;
        }
        .invoice-number {
            text-align: right;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 8px;
            margin-bottom: 10px;
            page-break-inside: auto;
        }
        .details-grid p {
            padding: 6px 0;
            font-size: 0.875rem;
        }
        .details-grid strong {
            font-weight: 600;
            color: #1e293b;
        }
        .charges-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .charges-table th, .charges-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 0.875rem;
            text-align: left;
        }
        .charges-table th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        .payment-summary {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.875rem;
        }
        .payment-row.total-amount-row {
            font-weight: 600;
            border-top: 1px dashed #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            margin: 8px 0;
        }
        .payment-row span:last-child {
            text-align: right;
            min-width: 100px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            align-items: flex-end;
            page-break-before: avoid;
        }
        .signature-box {
            width: 48%;
            display: flex;
            flex-direction: column;
            height: 80px;
        }
        .signature-box p {
            margin-bottom: 20px;
            font-size: 0.875rem;
        }
        .signature-line {
            margin-top: auto;
            border-top: 1px dashed #64748b;
            padding-top: 4px;
            font-size: 0.75rem;
            color: #64748b;
            text-align: center;
        }
        .footer {
            text-align: center;
            color: #64748b;
            font-size: 0.75rem;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            page-break-before: auto;
        }
        .thank-you {
            font-family: 'Dancing Script', cursive;
            font-size: 1.5rem;
            color: #000000;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            margin: 10px 0;
        }
        .no-print {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        .input-group {
            margin-bottom: 10px;
            display: block;
        }
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: #1e293b;
            font-size: 0.875rem;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        .charge-inputs {
            display: flex;
            gap: 8px;
        }
        .charge-inputs input {
            flex: 1;
        }
        .button-group {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        button {
            padding: 10px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.875rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
        }
        button:hover:not(:disabled) {
            transform: translateY(-1px);
        }
        #searchButton {
            background-color: #10b981;
            color: white;
        }
        #searchButton:hover:not(:disabled) {
            background-color: #059669;
        }
        #addChargeButton {
            background-color: #10b981;
            color: white;
        }
        #addChargeButton:hover:not(:disabled) {
            background-color: #059669;
        }
        #printButton {
            background-color: #3b82f6;
            color: white;
        }
        #printButton:hover:not(:disabled) {
            background-color: #2563eb;
        }
        #backButton {
            background-color: #6b7280;
            color: white;
        }
        #backButton:hover:not(:disabled) {
            background-color: #4b5563;
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .error-message {
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-size: 0.875rem;
            display: none;
        }
        .error-message.error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #f5a5a5;
        }
        .error-message.success {
            background-color: #dcfce7;
            color: #16a34a;
            border: 1px solid #86efac;
        }
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
                display: block;
            }
            .no-print, .no-print-top {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none;
                border: none;
                width: 160mm;
                height: auto;
                min-height: 290mm;
                padding: 10mm;
                margin: 0;
                overflow: visible;
                page-break-after: auto;
                font-size: 0.875rem;
            }
            .details-grid, .payment-summary, .footer {
                page-break-inside: auto;
            }
            .header, .signature-section {
                page-break-inside: avoid;
            }
            .receipt-container:last-child {
                page-break-after: avoid;
            }
            .charges-table th, .charges-table td {
                border: 1px solid #1e293b;
                padding: 8px;
                font-size: 0.875rem;
            }
            .charges-table th {
                background-color: #e2e8f0;
                font-weight: 600;
            }
            .payment-row.total-amount-row {
                font-weight: 600;
                border-top: 1px dashed #1e293b;
                border-bottom: 1px solid #1e293b;
                margin: 8px 0;
                display: flex !important;
            }
            .receipt-title {
                font-size: 1.125rem;
            }
            .billing-date, .invoice-number {
                font-size: 0.75rem;
            }
            .details-grid p {
                font-size: 0.75rem;
            }
            .payment-row {
                font-size: 0.75rem;
            }
            .signature-box p {
                font-size: 0.75rem;
            }
            .signature-line {
                font-size: 0.675rem;
            }
            .footer {
                font-size: 0.675rem;
            }
            .thank-you {
                font-size: 1.25rem;
            }
            @page {
                size: 160mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-top">
        <button id="backButton">Back</button>
    </div>
    <div class="receipt-container">
        <div class="header">
            <!-- Add logo or header content if needed -->
        </div>
        <div class="receipt-title-section">
            <span class="billing-date"><?php echo htmlspecialchars($payment_date ?: date('Y/m/d')); ?></span>
            <h2 class="receipt-title">Additional Charges Invoice</h2>
            <span class="invoice-number"><?php echo htmlspecialchars($invoice_number ?: '-'); ?></span>
        </div>
        <div class="details-grid">
            <p><strong>NIC/Passport:</strong></p>
            <p><?php echo htmlspecialchars($nic ?: '-'); ?></p>
            <p><strong>Contact No:</strong></p>
            <p><?php echo htmlspecialchars($contact_no ?: '-'); ?></p>
        </div>
        <div>
            <table class="charges-table">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Charge Name</th>
                        <th>Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody id="chargesTableBody">
                    <?php if ($search_performed && empty($additional_charges)): ?>
                        <tr>
                            <td colspan="3">No additional charges added</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($additional_charges as $charge): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($charge['room_number'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($charge['charge_name'] ?? '-'); ?></td>
                                <td>Rs. <?php echo number_format($charge['amount'] ?? 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div>
            <div class="payment-summary">
                <div class="payment-row total-amount-row">
                    <span>Total Amount:</span>
                    <span>Rs. <?php echo number_format($total_amount, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <p>Bill Issued by: <?php echo htmlspecialchars($issued_by ?: $logged_in_username); ?></p>
                    <div class="signature-line">Receptionist Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Guest Signature</div>
                </div>
            </div>
            <div class="thank-you">Thank you for choosing Hotel Grand Guardian</div>
        </div>
        <div class="no-print">
            <div class="error-message <?php echo $error ? 'error' : ($success ? 'success' : ''); ?>" style="<?php echo $error || $success ? 'display: block;' : ''; ?>">
                <?php echo htmlspecialchars($error ?: $success); ?>
            </div>
            <form id="searchForm" method="POST">
                <div class="input-group">
                    <label for="invoiceNumberSearch">Invoice Number (Digits Only) *</label>
                    <input type="number" id="invoiceNumberSearch" name="invoice_number" placeholder="Enter invoice number (e.g., 1001)" value="<?php echo htmlspecialchars($input_invoice_number ?? ''); ?>">
                    <input type="hidden" name="action" value="search_invoice">
                </div>
                <div class="button-group">
                    <button type="submit" id="searchButton">Search Invoice</button>
                    <?php if ($search_performed && !empty($invoice_number)): ?>
                        <button type="button" id="printButton">Print Invoice</button>
                    <?php endif; ?>
                </div>
            </form>
            <?php if ($search_performed && !empty($invoice_number)): ?>
                <form id="addChargeForm" method="POST">
                    <div class="input-group">
                        <label>Add Additional Charge</label>
                        <div class="charge-inputs">
                            <input type="text" id="roomNumber" name="room_number" placeholder="Enter room number (e.g., 101)">
                            <input type="text" id="chargeName" name="charge_name" placeholder="Enter charge name (e.g., Room Service)">
                            <input type="number" id="chargeAmount" name="amount" placeholder="Enter amount" min="0" step="0.01">
                            <button type="submit" id="addChargeButton">Add Charge</button>
                        </div>
                        <input type="hidden" name="action" value="add_charge">
                        <input type="hidden" name="invoice_number" value="<?php echo htmlspecialchars($invoice_number); ?>">
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backButton = document.getElementById('backButton');
            const printButton = document.getElementById('printButton');
            const errorDiv = document.querySelector('.error-message');

            // Back button
            backButton.addEventListener('click', () => {
                window.location.href = 'Frontoffice.php';
            });

            // Print button (only available after successful search)
            if (printButton) {
                printButton.addEventListener('click', () => {
                    window.print();
                });
            }

            // Clear error/success message after 5 seconds
            if (errorDiv && errorDiv.style.display === 'block') {
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>