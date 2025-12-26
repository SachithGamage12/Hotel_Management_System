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

$guest_name = '';
$address = '';
$email = '';
$id_number = '';
$check_in_date = '';
$check_out_date = '';
$room_details = [];
$total_rate = 0;
$stay_days = 1; // Default, will be calculated dynamically
$error = '';
$success = '';
$invoice_number = 'TBD';
$advance_payment = 0;

// Initialize room types and room numbers
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch room types
    $stmt = $conn->query("SELECT id, name FROM room_types");
    $room_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch available room numbers
    $stmt = $conn->query("SELECT DISTINCT room_number FROM rooms");
    $room_numbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    error_log("Database error: " . $e->getMessage());
}

// Handle AJAX request to fetch room rate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fetch_rate') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $room_type_id = filter_var($_POST['room_type_id'], FILTER_SANITIZE_NUMBER_INT);
        $room_number = filter_var($_POST['room_number'], FILTER_SANITIZE_STRING);
        $ac_type = filter_var($_POST['ac_type'], FILTER_SANITIZE_STRING);
        
        // Query to fetch rate, ensuring room_number exists in rooms table
        $stmt = $conn->prepare("
            SELECT rr.rate, rt.name AS room_type_name
            FROM room_rates rr
            JOIN rooms r ON rr.room_number = r.room_number
            JOIN room_types rt ON rr.room_type_id = rt.id
            WHERE rr.room_type_id = :room_type_id
            AND rr.room_number = :room_number
            AND rr.ac_type = :ac_type
        ");
        $stmt->bindParam(':room_type_id', $room_type_id, PDO::PARAM_INT);
        $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
        $stmt->bindParam(':ac_type', $ac_type, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Ensure rate is formatted as a float with two decimal places
            $rate = floatval($result['rate']);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'rate' => number_format($rate, 2, '.', ''),
                'room_type_name' => $result['room_type_name']
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No rate found for the selected room type, number, and A/C type'
            ]);
        }
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Handle form submission to save advance payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_advance_payment') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Ensure advance_invoice_counter has a record
        $stmt = $conn->query("SELECT COUNT(*) FROM advance_invoice_counter WHERE id = 1");
        if ($stmt->fetchColumn() == 0) {
            $conn->exec("INSERT INTO advance_invoice_counter (id, last_invoice_number) VALUES (1, 1000)");
        }
        
        // Get and lock the last invoice number
        $stmt = $conn->query("SELECT last_invoice_number FROM advance_invoice_counter WHERE id = 1 LIMIT 1 FOR UPDATE");
        $last_invoice = $stmt->fetchColumn();
        if ($last_invoice === false) {
            throw new Exception("Invoice counter not initialized.");
        }
        $new_invoice_number = sprintf('INV-%04d', $last_invoice + 1);
        
        // Check if invoice number exceeds 9999
        if ($last_invoice + 1 > 9999) {
            throw new Exception('Invoice number limit reached (9999). Please reset or modify the counter.');
        }
        
        // Update invoice counter
        $stmt = $conn->prepare("UPDATE advance_invoice_counter SET last_invoice_number = ? WHERE id = 1");
        $stmt->execute([$last_invoice + 1]);
        
        // Sanitize and validate inputs
        $guest_name = filter_var($_POST['guest_name'], FILTER_SANITIZE_STRING);
        $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING) ?: null;
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) ?: null;
        $id_number = filter_var($_POST['id_number'], FILTER_SANITIZE_STRING) ?: null;
        $check_in_date = filter_var($_POST['check_in_date'], FILTER_SANITIZE_STRING);
        $check_out_date = filter_var($_POST['check_out_date'], FILTER_SANITIZE_STRING);
        $advance_payment = floatval(preg_replace('/[^0-9.]/', '', $_POST['advance_payment'] ?? 0));
        $total_rate = floatval(preg_replace('/[^0-9.]/', '', $_POST['total_rate'] ?? 0));
        $stay_days = filter_var($_POST['stay_days'], FILTER_SANITIZE_NUMBER_INT);
        
        // Validate required inputs
        if (empty($guest_name) || empty($check_in_date) || empty($check_out_date)) {
            throw new Exception("Guest name, check-in, and check-out dates are required.");
        }
        if ($advance_payment <= 0) {
            throw new Exception("Advance payment must be greater than zero.");
        }
        if ($advance_payment > $total_rate) {
            throw new Exception("Advance payment cannot exceed total amount.");
        }
        if ($stay_days < 1) {
            throw new Exception("Stay duration must be at least one day.");
        }
        
        // Process room details
        $room_details = json_decode($_POST['room_details'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid room details JSON.");
        }
        
        // Save to advance_payments
        $stmt = $conn->prepare("
            INSERT INTO advance_payments (
                invoice_number, guest_name, address, email, id_number,
                check_in_date, check_out_date, rooms, total_amount,
                advance_payment, issued_by, payment_date
            ) VALUES (
                :invoice_number, :guest_name, :address, :email, :id_number,
                :check_in_date, :check_out_date, :rooms, :total_amount,
                :advance_payment, :issued_by, NOW()
            )
        ");
        // In the save_advance_payment block
$issued_by = $logged_in_username; // Use logged-in username
$stmt->bindParam(':issued_by', $issued_by);
        $total_amount = $total_rate;
        $rooms_json = json_encode($room_details);
        
        $stmt->bindParam(':invoice_number', $new_invoice_number);
        $stmt->bindParam(':guest_name', $guest_name);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id_number', $id_number);
        $stmt->bindParam(':check_in_date', $check_in_date);
        $stmt->bindParam(':check_out_date', $check_out_date);
        $stmt->bindParam(':rooms', $rooms_json);
        $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
        $stmt->bindParam(':advance_payment', $advance_payment, PDO::PARAM_STR);
        $stmt->bindParam(':issued_by', $issued_by);
        $stmt->execute();
        
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'invoice_number' => $new_invoice_number,
            'message' => 'Advance payment saved successfully'
        ]);
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database error in save_advance_payment: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Validation error in save_advance_payment: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Validation error: ' . $e->getMessage()
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Payment Invoice</title>
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
        .room-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .room-table th, .room-table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            font-size: 0.875rem;
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
            font-size: 0.875rem;
        }
        .payment-row.advance-payment-row {
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
        .payment-inputs {
            display: flex;
            gap: 8px;
        }
        .payment-inputs select,
        .payment-inputs input {
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
        #addRoomButton {
            background-color: #10b981;
            color: white;
        }
        #addRoomButton:hover:not(:disabled) {
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
            .room-table th, .room-table td {
                border: 1px solid #1e293b;
                padding: 8px;
                font-size: 0.875rem;
            }
            .room-table th {
                background-color: #e2e8f0;
                font-weight: 600;
            }
            .payment-row.advance-payment-row {
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
            <span class="billing-date" id="billingDate"><?php echo date('Y/m/d'); ?></span>
            <h2 class="receipt-title">Advance Payment Invoice</h2>
            <span class="invoice-number" id="invoiceNumber"><?php echo htmlspecialchars($invoice_number); ?></span>
        </div>
        <div id="details" class="details-grid">
            <p><strong>Guest Name:</strong></p>
            <p id="guestNameDisplay"><?php echo htmlspecialchars($guest_name ?: 'Not provided'); ?></p>
            <p><strong>Address:</strong></p>
            <p id="addressDisplay"><?php echo htmlspecialchars($address ?: 'Not provided'); ?></p>
            <p><strong>Email:</strong></p>
            <p id="emailDisplay"><?php echo htmlspecialchars($email ?: 'Not provided'); ?></p>
            <p><strong>NIC/Passport:</strong></p>
            <p id="idNumberDisplay"><?php echo htmlspecialchars($id_number ?: 'Not provided'); ?></p>
            <p><strong>Check-In:</strong></p>
            <p id="checkInDateDisplay"><?php echo htmlspecialchars($check_in_date ?: 'Not provided'); ?></p>
            <p><strong>Check-Out:</strong></p>
            <p id="checkOutDateDisplay"><?php echo htmlspecialchars($check_out_date ?: 'Not provided'); ?></p>
            <p><strong>Stay Duration:</strong></p>
            <p id="stayDurationDisplay"><?php echo $stay_days; ?> day<?php echo $stay_days == 1 ? '' : 's'; ?></p>
        </div>
        <div id="roomDetails">
            <table class="room-table" id="roomTable">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>A/C Type</th>
                        <th>Rate per Day (Rs.)</th>
                        <th>Total Rate (Rs.)</th>
                    </tr>
                </thead>
                <tbody id="roomTableBody">
                    <?php foreach ($room_details as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($room['room_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($room['ac_type']); ?></td>
                            <td>Rs. <?php echo number_format($room['rate'], 2); ?></td>
                            <td>Rs. <?php echo number_format($room['adjusted_rate'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div id="paymentSection">
            <div class="payment-summary">
                <div class="payment-row">
                    <span>Total Amount:</span>
                    <span id="totalAmountDisplay">Rs. <?php echo number_format($total_rate, 2); ?></span>
                </div>
                <div class="payment-row advance-payment-row">
                    <span>Advance Payment:</span>
                    <span id="advancePaymentDisplay">Rs. 0.00</span>
                </div>
            </div>
        </div>
        <div class="footer">
            <div class="signature-section">
               <div class="signature-box">
    <p>Bill Issued by: <span id="issuedBy"><?php echo htmlspecialchars($logged_in_username); ?></span></p>
    <div class="signature-line">Receptionist Signature</div>
</div>
                <div class="signature-box">
                    <div class="signature-line">Guest Signature</div>
                </div>
            </div>
            <div class="thank-you">Thank you for choosing Hotel Grand Guardian</div>
        </div>
        <div class="no-print">
            <div id="error" class="error-message <?php echo $error ? 'error' : ($success ? 'success' : ''); ?>" style="<?php echo $error || $success ? 'display: block;' : ''; ?>">
                <?php echo htmlspecialchars($error ?: $success); ?>
            </div>
            <div class="input-group">
                <label for="guestName">Guest Name *</label>
                <input type="text" id="guestName" placeholder="Enter guest name">
            </div>
            <div class="input-group">
                <label for="address">Address</label>
                <input type="text" id="address" placeholder="Enter address (optional)">
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Enter email (optional)">
            </div>
            <div class="input-group">
                <label for="idNumber">NIC/Passport</label>
                <input type="text" id="idNumber" placeholder="Enter NIC or Passport (optional)">
            </div>
            <div class="input-group">
                <label for="checkInDate">Check-In Date *</label>
                <input type="date" id="checkInDate" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="input-group">
                <label for="checkOutDate">Check-Out Date *</label>
                <input type="date" id="checkOutDate" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="input-group">
                <label>Add Room *</label>
                <div class="payment-inputs">
                    <select id="roomTypeSelect">
                        <option value="">Select Room Type</option>
                        <?php foreach ($room_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" data-name="<?php echo htmlspecialchars($type['name']); ?>">
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="roomNumberSelect">
                        <option value="">Select Room Number</option>
                        <?php foreach ($room_numbers as $number): ?>
                            <option value="<?php echo htmlspecialchars($number); ?>">
                                <?php echo htmlspecialchars($number); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="acTypeSelect">
                        <option value="">Select A/C Type</option>
                        <option value="AC">AC</option>
                        <option value="Non-AC">Non-AC</option>
                    </select>
                    <button id="addRoomButton">Add Room</button>
                </div>
            </div>
            <div class="input-group">
                <label for="advancePayment">Advance Payment *</label>
                <input type="number" id="advancePayment" placeholder="Enter advance payment" min="0" step="0.01" value="">
            </div>
            <div class="button-group">
                <button id="printButton" disabled>Print Receipt</button>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const guestNameInput = document.getElementById('guestName');
        const addressInput = document.getElementById('address');
        const emailInput = document.getElementById('email');
        const idNumberInput = document.getElementById('idNumber');
        const checkInDateInput = document.getElementById('checkInDate');
        const checkOutDateInput = document.getElementById('checkOutDate');
        const roomTypeSelect = document.getElementById('roomTypeSelect');
        const roomNumberSelect = document.getElementById('roomNumberSelect');
        const acTypeSelect = document.getElementById('acTypeSelect');
        const addRoomButton = document.getElementById('addRoomButton');
        const advancePaymentInput = document.getElementById('advancePayment');
        const printButton = document.getElementById('printButton');
        const backButton = document.getElementById('backButton');
        const errorDiv = document.getElementById('error');
        const guestNameDisplay = document.getElementById('guestNameDisplay');
        const addressDisplay = document.getElementById('addressDisplay');
        const emailDisplay = document.getElementById('emailDisplay');
        const idNumberDisplay = document.getElementById('idNumberDisplay');
        const checkInDateDisplay = document.getElementById('checkInDateDisplay');
        const checkOutDateDisplay = document.getElementById('checkOutDateDisplay');
        const stayDurationDisplay = document.getElementById('stayDurationDisplay');
        const roomTableBody = document.getElementById('roomTableBody');
        const totalAmountDisplay = document.getElementById('totalAmountDisplay');
        const advancePaymentDisplay = document.getElementById('advancePaymentDisplay');
        const issuedBySpan = document.getElementById('issuedBy');
        const billingDateDiv = document.getElementById('billingDate');
        const invoiceNumberSpan = document.getElementById('invoiceNumber');
        const detailsSection = document.getElementById('details');
        const roomDetailsSection = document.getElementById('roomDetails');
        const paymentSection = document.getElementById('paymentSection');
        const footerSection = document.querySelector('.footer');

        let roomDetails = [];
        let totalRate = 0;
        let stayDays = 1;

        // Set current date as billing date
        const today = new Date();
        billingDateDiv.textContent = formatDate(today);

        // Back button
        backButton.addEventListener('click', () => {
            window.location.href = 'Frontoffice.php';
        });

        // Function to calculate stay duration
        function calculateStayDays() {
            if (checkInDateInput.value && checkOutDateInput.value) {
                const checkIn = new Date(checkInDateInput.value);
                const checkOut = new Date(checkOutDateInput.value);
                const timeDiff = checkOut - checkIn;
                stayDays = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                if (stayDays < 1) {
                    stayDays = 1;
                    showError('Check-out date must be after check-in date');
                    checkOutDateInput.value = '';
                    checkOutDateDisplay.textContent = 'Not provided';
                }
                stayDurationDisplay.textContent = `${stayDays} day${stayDays === 1 ? '' : 's'}`;
            } else {
                stayDays = 1;
                stayDurationDisplay.textContent = '1 day';
            }
            updateRoomRates();
            updateCalculations();
        }

        // Function to update room rates based on stay duration
        function updateRoomRates() {
            totalRate = 0;
            roomDetails = roomDetails.map(room => {
                room.adjusted_rate = room.rate * stayDays;
                totalRate += room.adjusted_rate;
                return room;
            });
            updateRoomTable();
        }

        // Function to update room table
        function updateRoomTable() {
            roomTableBody.innerHTML = '';
            roomDetails.forEach(room => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${room.room_number}</td>
                    <td>${room.room_type_name}</td>
                    <td>${room.ac_type}</td>
                    <td>Rs. ${room.rate.toFixed(2)}</td>
                    <td>Rs. ${room.adjusted_rate.toFixed(2)}</td>
                `;
                roomTableBody.appendChild(row);
            });
        }

        // Function to update calculations and displays
        function updateCalculations() {
            totalAmountDisplay.textContent = `Rs. ${totalRate.toFixed(2)}`;
            advancePaymentDisplay.textContent = `Rs. ${(parseFloat(advancePaymentInput.value) || 0).toFixed(2)}`;

            // Enable print button if all required fields are filled
            printButton.disabled = !(guestNameInput.value.trim() && checkInDateInput.value && checkOutDateInput.value && roomDetails.length > 0 && parseFloat(advancePaymentInput.value) > 0 && stayDays >= 1);
        }

        // Input event listeners
        guestNameInput.addEventListener('input', function() {
            guestNameDisplay.textContent = this.value || 'Not provided';
            updateCalculations();
        });

        addressInput.addEventListener('input', function() {
            addressDisplay.textContent = this.value || 'Not provided';
        });

        emailInput.addEventListener('input', function() {
            emailDisplay.textContent = this.value || 'Not provided';
        });

        idNumberInput.addEventListener('input', function() {
            idNumberDisplay.textContent = this.value || 'Not provided';
        });

        checkInDateInput.addEventListener('input', function() {
            checkInDateDisplay.textContent = this.value || 'Not provided';
            // Set check-out min date to check-in date
            checkOutDateInput.min = this.value;
            calculateStayDays();
        });

        checkOutDateInput.addEventListener('input', function() {
            checkOutDateDisplay.textContent = this.value || 'Not provided';
            calculateStayDays();
        });

        advancePaymentInput.addEventListener('input', function() {
            let pay = parseFloat(this.value) || 0;
            if (pay < 0) pay = 0;
            if (pay > totalRate) pay = totalRate;
            this.value = pay;
            updateCalculations();
        });

        // Add room button
        addRoomButton.addEventListener('click', async function() {
            const roomTypeId = roomTypeSelect.value;
            const roomTypeName = roomTypeSelect.options[roomTypeSelect.selectedIndex]?.dataset.name || '';
            const roomNumber = roomNumberSelect.value;
            const acType = acTypeSelect.value;

            if (!roomTypeId || !roomNumber || !acType) {
                showError('Please select room type, room number, and A/C type');
                return;
            }

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'fetch_rate',
                        'room_type_id': roomTypeId,
                        'room_number': roomNumber,
                        'ac_type': acType
                    })
                });
                const result = await response.json();
                if (result.success) {
                    const rate = parseFloat(result.rate) || 0;
                    const adjustedRate = rate * stayDays;
                    const room = {
                        room_type: roomTypeId,
                        room_type_name: result.room_type_name,
                        room_number: roomNumber,
                        ac_type: acType,
                        rate: rate,
                        adjusted_rate: adjustedRate
                    };

                    // Check for duplicate room
                    if (roomDetails.some(r => r.room_number === roomNumber && r.ac_type === acType)) {
                        showError('This room number and A/C type combination is already added');
                        return;
                    }

                    // Add to room details
                    roomDetails.push(room);
                    totalRate += adjustedRate;

                    // Update table
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${roomNumber}</td>
                        <td>${result.room_type_name}</td>
                        <td>${acType}</td>
                        <td>Rs. ${rate.toFixed(2)}</td>
                        <td>Rs. ${adjustedRate.toFixed(2)}</td>
                    `;
                    roomTableBody.appendChild(row);

                    // Clear selections
                    roomTypeSelect.value = '';
                    roomNumberSelect.value = '';
                    acTypeSelect.value = '';

                    // Show sections
                    detailsSection.classList.add('active');
                    roomDetailsSection.classList.add('active');
                    paymentSection.classList.add('active');
                    footerSection.classList.add('active');

                    updateCalculations();
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Failed to fetch room rate: ' + error.message);
            }
        });

        // Print button
        printButton.addEventListener('click', async function() {
            if (!guestNameInput.value.trim() || !checkInDateInput.value || !checkOutDateInput.value || roomDetails.length === 0 || parseFloat(advancePaymentInput.value) <= 0 || stayDays < 1) {
                showError('Please fill in all required fields (*), add at least one room, enter a valid advance payment, and ensure check-out is after check-in');
                return;
            }

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'save_advance_payment',
                        'guest_name': guestNameInput.value.trim(),
                        'address': addressInput.value.trim(),
                        'email': emailInput.value.trim(),
                        'id_number': idNumberInput.value.trim(),
                        'check_in_date': checkInDateInput.value,
                        'check_out_date': checkOutDateInput.value,
                        'room_details': JSON.stringify(roomDetails),
                        'total_rate': totalRate.toFixed(2),
                        'advance_payment': parseFloat(advancePaymentInput.value).toFixed(2),
                        'stay_days': stayDays
                    })
                });
                const result = await response.json();
                if (result.success) {
                    invoiceNumberSpan.textContent = result.invoice_number;
                    showSuccess(result.message);
                    setTimeout(() => window.print(), 500);
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Failed to save advance payment: ' + error.message);
            }
        });

        // Reset page state after print dialog closes
        window.onafterprint = function() {
            // Clear inputs
            guestNameInput.value = '';
            addressInput.value = '';
            emailInput.value = '';
            idNumberInput.value = '';
            checkInDateInput.value = '';
            checkOutDateInput.value = '';
            advancePaymentInput.value = '';

            // Clear room table and details
            roomTableBody.innerHTML = '';
            roomDetails = [];
            totalRate = 0;
            stayDays = 1;

            // Reset displays
            guestNameDisplay.textContent = 'Not provided';
            addressDisplay.textContent = 'Not provided';
            emailDisplay.textContent = 'Not provided';
            idNumberDisplay.textContent = 'Not provided';
            checkInDateDisplay.textContent = 'Not provided';
            checkOutDateDisplay.textContent = 'Not provided';
            stayDurationDisplay.textContent = '1 day';
            totalAmountDisplay.textContent = 'Rs. 0.00';
            advancePaymentDisplay.textContent = 'Rs. 0.00';
            invoiceNumberSpan.textContent = 'TBD';

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

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}/${month}/${day}`;
        }

        // Initialize
        // Initialize
issuedBySpan.textContent = '<?php echo htmlspecialchars($logged_in_username); ?>';
calculateStayDays();
updateCalculations();
        calculateStayDays();
        updateCalculations();
    });
    </script>
</body>
</html>