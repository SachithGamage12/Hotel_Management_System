<?php
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

$guest = null;
$room_details = [];
$total_rate = 0;
$stay_days = 0;
$error = '';
$success = '';
$bill_number = null;
$invoice_number = 'TBD';

// Only process if POST request is explicitly made with bill_number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bill_number'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $bill_number = filter_var($_POST['bill_number'], FILTER_SANITIZE_NUMBER_INT);

        // Fetch invoice details from room_payments using last_invoice_number from room_invoice_counter
        $stmt = $conn->prepare("
            SELECT rp.*, g.*, mp.name AS meal_plan_name, ric.last_invoice_number
            FROM room_payments rp
            LEFT JOIN guests g ON rp.booking_reference = g.grc_number
            LEFT JOIN meal_plans mp ON g.meal_plan_id = mp.id
            LEFT JOIN room_invoice_counter ric ON rp.invoice_number = CONCAT('INV-', ric.last_invoice_number)
            WHERE ric.last_invoice_number = :bill_number
        ");
        $stmt->bindParam(':bill_number', $bill_number, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            $error = "No invoice found with bill number $bill_number.";
        } else {
            $guest = $result; // Contains both room_payments and guests data
            $invoice_number = $result['invoice_number'];
            $total_rate = floatval($result['total_amount']);
            $advance_payment = floatval($result['advance_payment']);
            $pending_amount = floatval($result['pending_amount']);

            // Calculate stay days
            $check_in_date = new DateTime($guest['check_in_date']);
            $check_out_date = new DateTime($guest['check_out_date']);
            $interval = $check_in_date->diff($check_out_date);
            $stay_days = max(1, $interval->days);

            // Decode rooms JSON
            $rooms = json_decode($guest['rooms'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = "Invalid rooms JSON data.";
            } else {
                // Fetch room type names
                $stmt = $conn->query("SELECT id, name FROM room_types");
                $room_types_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $room_types = [];
                foreach ($room_types_result as $row) {
                    $room_types[$row['id']] = $row['name'];
                }

                // Fetch rates for each room
                foreach ($rooms as &$room) {
                    $stmt = $conn->prepare("
                        SELECT rate
                        FROM room_rates
                        WHERE room_type_id = :room_type_id
                        AND room_number = :room_number
                        AND ac_type = :ac_type
                    ");
                    $stmt->bindParam(':room_type_id', $room['room_type'], PDO::PARAM_INT);
                    $stmt->bindParam(':room_number', $room['room_number'], PDO::PARAM_STR);
                    $stmt->bindParam(':ac_type', $room['ac_type'], PDO::PARAM_STR);
                    $stmt->execute();
                    $rate_result = $stmt->fetch(PDO::FETCH_ASSOC);

                    $room['room_type_name'] = isset($room_types[$room['room_type']]) ? $room_types[$room['room_type']] : 'Unknown';
                    $room['rate'] = $rate_result ? $rate_result['rate'] : 'N/A';
                    if (is_numeric($room['rate'])) {
                        $room['adjusted_rate'] = floatval($room['rate']) * $stay_days;
                    } else {
                        $room['adjusted_rate'] = 'N/A';
                    }
                    $room_details[] = $room;
                }
                unset($room);
                $success = "Invoice details loaded successfully.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Server error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Invoice</title>
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
            font-size: 0.75rem;
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
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            flex: 1;
            text-align: center;
        }

        .billing-date, .invoice-number {
            font-size: 0.75rem;
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
            font-size: 0.75rem;
        }

        .details-grid strong {
            font-weight: 600;
            color: #1e293b;
        }

        .room-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .room-table th, .room-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            font-size: 0.75rem;
            text-align: left;
        }

        .room-table th {
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
            font-size: 0.75rem;
        }

        .payment-row.total {
            font-weight: 600;
            border-top: 1px dashed #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            margin: 8px 0;
        }

        .payment-row.pending {
            font-weight: 600;
            border-bottom: 2px double #1e293b;
            padding-bottom: 8px;
        }

        .payment-row span:last-child {
            text-align: right;
            min-width: 100px;
        }

        .advance-payment-row, .pending-amount-row {
            display: flex;
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
        }

        .signature-line {
            margin-top: auto;
            border-top: 1px dashed #64748b;
            padding-top: 4px;
            font-size: 0.7rem;
            color: #64748b;
            text-align: center;
        }

        .footer {
            text-align: center;
            color: #64748b;
            font-size: 0.7rem;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            page-break-before: auto;
        }

        .thank-you {
            font-family: 'Dancing Script', cursive;
            font-size: 1.2rem;
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
            display: none;
        }

        #billNumberGroup {
            display: block;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: #1e293b;
            font-size: 0.75rem;
        }

        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .payment-inputs {
            display: flex;
            gap: 8px;
        }

        .payment-inputs select,
        .payment-inputs input {
            flex: 1;
        }

        .readonly-input {
            background-color: #f1f5f9;
            cursor: not-allowed;
        }

        .button-group {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        button {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
        }

        button:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        #searchButton {
            background-color: #6366f1;
            color: white;
        }

        #searchButton:hover:not(:disabled) {
            background-color: #4f46e5;
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
            font-size: 0.75rem;
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

            .room-table th, .room-table td {
                border: 1px solid #1e293b;
                padding: 6px;
                font-size: 0.75rem;
            }

            .room-table th {
                background-color: #e2e8f0;
                font-weight: 600;
            }

            .advance-payment-row, .pending-amount-row {
                display: flex !important;
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
        </div>

        <div class="receipt-title-section">
            <span class="billing-date" id="billingDate"><?php echo $guest ? htmlspecialchars($guest['payment_date']) : date('Y/m/d'); ?></span>
            <h2 class="receipt-title">Room Invoice</h2>
            <span class="invoice-number" id="invoiceNumber"><?php echo htmlspecialchars($invoice_number); ?></span>
        </div>

        <div id="details" class="details-grid<?php echo $guest ? ' active' : ''; ?>">
            <?php if ($guest): ?>
                <p><strong>Guest Name:</strong></p>
                <p><?php echo htmlspecialchars($guest['guest_name'] ?? 'Not provided'); ?></p>
                <p><strong>Contact Number:</strong></p>
                <p><?php echo htmlspecialchars($guest['contact_number'] ?? 'Not provided'); ?></p>
                <p><strong>Email:</strong></p>
                <p><?php echo htmlspecialchars($guest['email'] ?? 'Not provided'); ?></p>
                <p><strong>Address:</strong></p>
                <p><?php echo htmlspecialchars($guest['address'] ?? 'Not provided'); ?></p>
                <p><strong><?php echo $guest['id_type'] === 'Passport' ? 'Passport No:' : 'NIC'; ?>:</strong></p>
                <p><?php echo htmlspecialchars($guest['id_number'] ?? 'Not provided'); ?></p>
                <p><strong>Check-In:</strong></p>
                <p><?php echo htmlspecialchars($guest['check_in_date']); ?></p>
                <p><strong>Check-Out:</strong></p>
                <p><?php echo htmlspecialchars($guest['check_out_date']); ?></p>
                <p><strong>Stay Duration:</strong></p>
                <p><?php echo $stay_days; ?> day<?php echo $stay_days > 1 ? 's' : ''; ?></p>
            <?php else: ?>
                <p><strong>Guest Name:</strong></p>
                <p>Not provided</p>
                <p><strong>Contact Number:</strong></p>
                <p>Not provided</p>
                <p><strong>Email:</strong></p>
                <p>Not provided</p>
                < periferals>
                <p><strong>Address:</strong></p>
                <p>Not provided</p>
            <?php endif; ?>
        </div>

        <div id="room Aria-label="roomDetails" class="<?php echo $room_details ? 'active' : ''; ?>">
            <?php if ($room_details): ?>
                <table class="room-table">
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>A/C Type</th>
                            <th>Rate per Day (Rs.)</th>
                            <th>Total Rate (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($room_details as $room): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td><?php echo htmlspecialchars($room['room_type_name']); ?></td>
                                <td><?php echo htmlspecialchars($room['ac_type']); ?></td>
                                <td><?php echo is_numeric($room['rate']) ? 'Rs. ' . number_format($room['rate'], 2) : htmlspecialchars($room['rate']); ?></td>
                                <td><?php echo is_numeric($room['adjusted_rate']) ? 'Rs. ' . number_format($room['adjusted_rate'], 2) : htmlspecialchars($room['adjusted_rate']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div id="paymentSection" class="<?php echo $guest ? 'active' : ''; ?>">
            <?php if ($guest): ?>
                <div class="payment-summary">
                    <div class="payment-row total">
                        <span>Total:</span>
                        <span><?php echo is_numeric($total_rate) ? 'Rs. ' . number_format($total_rate, 2) : 'N/A'; ?></span>
                    </div>
                    <div class="payment-row advance-payment-row">
                        <span>Advance Payment:</span>
                        <span id="advancePaymentDisplay"><?php echo is_numeric($advance_payment) ? 'Rs. ' . number_format($advance_payment, 2) : 'Rs. 0.00'; ?></span>
                    </div>
                    <div class="payment-row pending pending-amount-row">
                        <span>Pending Amount:</span>
                        <span id="pendingAmountDisplay"><?php echo is_numeric($pending_amount) ? 'Rs. ' . number_format($pending_amount, 2) : 'N/A'; ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer<?php echo $guest ? ' active' : ''; ?>">
            <div class="signature-section">
                <div class="signature-box">
                    <p>Bill Issued by: <span id="issuedBy"><?php echo htmlspecialchars($guest['issued_by'] ?? 'Admin'); ?></span></p>
                    <div class="signature-line">Receptionist Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Guest Signature</div>
                </div>
            </div>
            <div class="thank-you">Thank you for choosing Wedding Bliss</div>
        </div>

        <div class="no-print">
            <div id="error" class="error-message <?php echo $error ? 'error' : ($success ? 'success' : ''); ?>" style="<?php echo $error || $success ? 'display: block;' : ''; ?>">
                <?php echo htmlspecialchars($error ?: $success); ?>
            </div>

            <div class="input-group" id="billNumberGroup">
                <label for="billNumberInput">Bill Number</label>
                <input type="number" id="billNumberInput" placeholder="Enter Bill Number" value="">
            </div>

            <div class="button-group">
                <button id="searchButton">Search</button>
                <button id="printButton" <?php echo !$guest ? 'disabled' : ''; ?>>Print Receipt</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const billNumberInput = document.getElementById('billNumberInput');
        const searchButton = document.getElementById('searchButton');
        const printButton = document.getElementById('printButton');
        const backButton = document.getElementById('backButton');
        const errorDiv = document.getElementById('error');
        const advancePaymentDisplay = document.getElementById('advancePaymentDisplay');
        const pendingAmountDisplay = document.getElementById('pendingAmountDisplay');
        const issuedBySpan = document.getElementById('issuedBy');
        const billingDateDiv = document.getElementById('billingDate');
        const invoiceNumberSpan = document.getElementById('invoiceNumber');
        const detailsSection = document.getElementById('details');
        const roomDetailsSection = document.getElementById('roomDetails');
        const paymentSection = document.getElementById('paymentSection');
        const footerSection = document.querySelector('.footer');

        // Set billing date
        billingDateDiv.textContent = '<?php echo $guest ? htmlspecialchars($guest['payment_date']) : date('Y/m/d'); ?>';

        // Initialize
        document.querySelectorAll('.input-group').forEach(group => {
            if (group.id !== 'billNumberGroup') {
                group.style.display = 'none';
            }
        });

        // Back button
        backButton.addEventListener('click', () => {
            window.location.href = '../audit.php';
        });

        // Search button
        searchButton.addEventListener('click', function() {
            const billNumber = billNumberInput.value.trim();
            if (!billNumber || isNaN(billNumber)) {
                showError('Please enter a valid bill number');
                return;
            }
            // Submit form to reload with bill number
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'bill_number';
            input.value = billNumber;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });

        // Print button
        printButton.addEventListener('click', function() {
            if (!detailsSection.classList.contains('active')) {
                showError('Please search for a valid bill number before printing');
                return;
            }
            window.print();
        });

        // Reset page state after print dialog closes
        window.onafterprint = function() {
            // Clear bill number input
            billNumberInput.value = '';

            // Hide sections
            detailsSection.classList.remove('active');
            roomDetailsSection.classList.remove('active');
            paymentSection.classList.remove('active');
            footerSection.classList.remove('active');

            // Disable print button
            printButton.disabled = true;

            // Clear error/success message
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';

            // Reset invoice number
            invoiceNumberSpan.textContent = 'TBD';

            // Reset payment fields
            advancePaymentDisplay.textContent = 'Rs. 0.00';
            pendingAmountDisplay.textContent = '<?php echo is_numeric($total_rate) ? 'Rs. ' . number_format($total_rate, 2) : 'N/A'; ?>';

            // Redirect to clear server-side state
            window.location.href = window.location.pathname;
        };

        function showError(message) {
            errorDiv.textContent = message;
            errorDiv.className = 'error-message error';
            errorDiv.style.display = 'block';
            setTimeout(() => errorDiv.style.display = 'none', 5000);
        }

        function showSuccess(message) {
            errorDiv.textContent = message;
            errorDiv.className = 'error-message success';
            errorDiv.style.display = 'block';
            setTimeout(() => errorDiv.style.display = 'none', 3000);
        }

        // Show sections only if invoice is found
        <?php if ($guest): ?>
            showSuccess('Invoice details loaded successfully');
            printButton.disabled = false;
        <?php else: ?>
            printButton.disabled = true;
        <?php endif; ?>

        // Fetch current user
        issuedBySpan.textContent = '<?php echo $guest['issued_by'] ?? 'Admin'; ?>';
    });
    </script>
</body>
</html>