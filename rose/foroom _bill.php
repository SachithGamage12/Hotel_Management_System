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
$grc_number = null;
$invoice_number = 'TBD';
$additional_hours = 0;
$hourly_rate = 0.00;

// Only process if POST request is explicitly made with grc_number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['grc_number'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $grc_number = filter_var($_POST['grc_number'], FILTER_SANITIZE_NUMBER_INT);

        // Fetch guest details
        $stmt = $conn->prepare("
            SELECT g.*, mp.name AS meal_plan_name
            FROM roseguests g
            LEFT JOIN meal_plans mp ON g.meal_plan_id = mp.id
            WHERE g.grc_number = :grc_number
        ");
        $stmt->bindParam(':grc_number', $grc_number, PDO::PARAM_INT);
        $stmt->execute();
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$guest) {
            $error = "No guest found with GRC number $grc_number.";
        } else {
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

                // Fetch rates for each room and adjust by stay days
                foreach ($rooms as &$room) {
                    $stmt = $conn->prepare("
                        SELECT rate
                        FROM roseroom_rates
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
                        $total_rate += $room['adjusted_rate'];
                    } else {
                        $room['adjusted_rate'] = 'N/A';
                    }
                    $room_details[] = $room;
                }
                unset($room);
                $success = "Guest details loaded successfully.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        error_log("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        $error = "Server error: " . $e->getMessage();
        error_log("Server error: " . $e->getMessage());
    }
}

// Handle AJAX request to save invoice and get invoice number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_invoice') {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Log raw POST data for debugging
        error_log("Raw POST data: " . print_r($_POST, true));

        // Begin transaction
        $conn->beginTransaction();

        // Get last invoice number
        $stmt = $conn->query("SELECT last_invoice_number FROM roseroom_invoice_counter ORDER BY id DESC LIMIT 1");
        $last_invoice = $stmt->fetchColumn();
        $new_invoice_number = 'INV-' . ($last_invoice + 1);

        // Update invoice counter
        $stmt = $conn->prepare("INSERT INTO roseroom_invoice_counter (last_invoice_number) VALUES (:last_invoice_number)");
        $stmt->bindParam(':last_invoice_number', $last_invoice_number);
        $last_invoice_number = $last_invoice + 1;
        $stmt->execute();

        // Save to room_payments
        $stmt = $conn->prepare("
            INSERT INTO roseroom_payments (
                booking_reference, invoice_number, ac_type, meal_plan, 
                value_type, amount_type, total_amount, discount, advance_payment, 
                pending_amount, additional_hours, hourly_rate, issued_by, nic, contact_no, payment_date
            ) VALUES (
                :booking_reference, :invoice_number, :ac_type, :meal_plan, 
                :value_type, :amount_type, :total_amount, :discount, :advance_payment, 
                :pending_amount, :additional_hours, :hourly_rate, :issued_by, :nic, :contact_no, NOW()
            )
        ");

        $booking_reference = $grc_number;
        $ac_type = $room_details[0]['ac_type'] ?? null;
        $meal_plan = $guest['meal_plan_name'] ?? null;
        $value_type = 'Room Booking';
        $amount_type = 'Invoice';

        // Sanitize and validate numeric inputs
        $additional_hours = intval($_POST['additional_hours'] ?? 0);
        $hourly_rate = floatval(preg_replace('/[^0-9.]/', '', $_POST['hourly_rate'] ?? 0));
        if ($additional_hours < 0 || $hourly_rate < 0) {
            throw new Exception("Additional hours and hourly rate cannot be negative.");
        }
        $additional_charge = $additional_hours * $hourly_rate;
        $total_amount = floatval($total_rate) + $additional_charge;
        $discount = floatval(preg_replace('/[^0-9.]/', '', $_POST['discount'] ?? 0));
        if ($discount < 0 || $discount > $total_amount) {
            throw new Exception("Invalid discount amount.");
        }
        $final_total = $total_amount - $discount;
        $advance_payment = floatval(preg_replace('/[^0-9.]/', '', $_POST['advance_payment'] ?? 0));
        $pending_amount = floatval(preg_replace('/[^0-9.]/', '', $_POST['pending_amount'] ?? $final_total));

        // Validate inputs
        if ($advance_payment < 0 || $pending_amount < 0) {
            throw new Exception("Advance or pending amount cannot be negative.");
        }
        if ($advance_payment + $pending_amount != $final_total) {
            error_log("Validation error: advance_payment ($advance_payment) + pending_amount ($pending_amount) != final_total ($final_total)");
            throw new Exception("Invalid payment amounts: total does not match.");
        }

        // Log values for debugging
        error_log("Saving invoice: grc_number=$grc_number, total_amount=$total_amount, additional_hours=$additional_hours, hourly_rate=$hourly_rate, discount=$discount, final_total=$final_total, advance_payment=$advance_payment, pending_amount=$pending_amount");

        $issued_by = 'Admin';
        $nic = $guest['id_number'] ?? null;
        $contact_no = $guest['contact_number'] ?? null;

        $stmt->bindParam(':booking_reference', $booking_reference);
        $stmt->bindParam(':invoice_number', $new_invoice_number);
        $stmt->bindParam(':ac_type', $ac_type);
        $stmt->bindParam(':meal_plan', $meal_plan);
        $stmt->bindParam(':value_type', $value_type);
        $stmt->bindParam(':amount_type', $amount_type);
        $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
        $stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
        $stmt->bindParam(':advance_payment', $advance_payment, PDO::PARAM_STR);
        $stmt->bindParam(':pending_amount', $pending_amount, PDO::PARAM_STR);
        $stmt->bindParam(':additional_hours', $additional_hours, PDO::PARAM_INT);
        $stmt->bindParam(':hourly_rate', $hourly_rate, PDO::PARAM_STR);
        $stmt->bindParam(':issued_by', $issued_by);
        $stmt->bindParam(':nic', $nic);
        $stmt->bindParam(':contact_no', $contact_no);
        $stmt->execute();

        $conn->commit();

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'invoice_number' => $new_invoice_number,
            'additional_charge' => number_format($additional_charge, 2),
            'message' => 'Invoice saved successfully'
        ]);
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Database error in save_invoice: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Validation error in save_invoice: " . $e->getMessage());
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

        .hours-count-row, .additional-hour-row, .discount-row, .advance-payment-row, .pending-amount-row {
            display: none;
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

        #grcNumberGroup {
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

            .hours-count-row, .additional-hour-row, .discount-row, .advance-payment-row, .pending-amount-row {
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
            <!-- Add logo or header content if needed -->
        </div>

        <div class="receipt-title-section">
            <span class="billing-date" id="billingDate"><?php echo date('Y/m/d'); ?></span>
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
                <p><strong>Address:</strong></p>
                <p>Not provided</p>
            <?php endif; ?>
        </div>

        <div id="roomDetails" class="<?php echo $room_details ? 'active' : ''; ?>">
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
                    <div class="payment-row">
                        <span>Room Subtotal:</span>
                        <span><?php echo is_numeric($total_rate) ? 'Rs. ' . number_format($total_rate, 2) : 'N/A'; ?></span>
                    </div>
                    <div class="payment-row hours-count-row">
                        <span>Additional Hours:</span>
                        <span id="hoursCountDisplay">0</span>
                    </div>
                    <div class="payment-row additional-hour-row">
                        <span>Additional Hours Charge:</span>
                        <span id="additionalHourChargeDisplay">Rs. 0.00</span>
                    </div>
                    <div class="payment-row">
                        <span>Subtotal:</span>
                        <span id="subtotalDisplay"><?php echo is_numeric($total_rate) ? 'Rs. ' . number_format($total_rate, 2) : 'N/A'; ?></span>
                    </div>
                    <div class="payment-row discount-row">
                        <span>Discount:</span>
                        <span id="discountDisplay">Rs. 0.00</span>
                    </div>
                    <div class="payment-row total">
                        <span>Final Total:</span>
                        <span id="finalTotalDisplay"><?php echo is_numeric($total_rate) ? 'Rs. ' . number_format($total_rate, 2) : 'N/A'; ?></span>
                    </div>
                    <div class="payment-row advance-payment-row">
                        <span>Advance Payment:</span>
                        <span id="advancePaymentDisplay">Rs. 0.00</span>
                    </div>
                    <div class="payment-row pending pending-amount-row">
                        <span>Pending Amount:</span>
                        <span id="pendingAmountDisplay"><?php echo is_numeric($total_rate) ? 'Rs. ' . number_format($total_rate, 2) : 'N/A'; ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer<?php echo $guest ? ' active' : ''; ?>">
            <div class="signature-section">
                <div class="signature-box">
                    <p>Bill Issued by: <span id="issuedBy">Admin</span></p>
                    <div class="signature-line">Receptionist Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">Guest Signature</div>
                </div>
            </div>
            <div class="thank-you">Thank you for choosing Hotel Rose Garden</div>
        </div>

        <div class="no-print">
            <div id="error" class="error-message <?php echo $error ? 'error' : ($success ? 'success' : ''); ?>" style="<?php echo $error || $success ? 'display: block;' : ''; ?>">
                <?php echo htmlspecialchars($error ?: $success); ?>
            </div>

            <div class="input-group" id="grcNumberGroup">
                <label for="grcNumber">GRC Number</label>
                <input type="number" id="grcNumber" placeholder="Enter GRC Number" value="">
            </div>

            <div class="input-group" id="additionalHoursGroup" style="display: <?php echo $guest ? 'block' : 'none'; ?>;">
                <label for="additionalHours">Additional Hours</label>
                <input type="number" id="additionalHours" placeholder="Enter additional hours" min="0" step="1" value="0">
            </div>

            <div class="input-group" id="hourlyRateGroup" style="display: <?php echo $guest ? 'block' : 'none'; ?>;">
                <label for="hourlyRate">Hourly Rate (Rs.)</label>
                <input type="number" id="hourlyRate" placeholder="Enter hourly rate" min="0" step="0.01" value="0">
            </div>

            <div class="input-group" id="discountGroup" style="display: <?php echo $guest ? 'block' : 'none'; ?>;">
                <label for="discount">Discount (Rs.)</label>
                <input type="number" id="discount" placeholder="Enter discount" min="0" step="0.01" value="0">
            </div>

            <div class="input-group" id="advancePaymentGroup" style="display: <?php echo $guest ? 'block' : 'none'; ?>;">
                <label for="advancePayment">Advance Payment</label>
                <input type="number" id="advancePayment" placeholder="Enter advance payment" min="0" step="0.01" value="">
            </div>

            <div class="input-group" id="pendingAmountGroup" style="display: <?php echo $guest ? 'block' : 'none'; ?>;">
                <label for="pendingAmount">Pending Amount</label>
                <input type="text" id="pendingAmount" class="readonly-input" readonly value="<?php echo is_numeric($total_rate) ? number_format($total_rate, 2) : 'N/A'; ?>">
            </div>

            <div class="button-group">
                <button id="searchButton">Search</button>
                <button id="printButton" <?php echo !$guest ? 'disabled' : ''; ?>>Print Receipt</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const grcNumberInput = document.getElementById('grcNumber');
        const additionalHoursInput = document.getElementById('additionalHours');
        const hourlyRateInput = document.getElementById('hourlyRate');
        const discountInput = document.getElementById('discount');
        const advancePaymentInput = document.getElementById('advancePayment');
        const pendingAmountInput = document.getElementById('pendingAmount');
        const searchButton = document.getElementById('searchButton');
        const printButton = document.getElementById('printButton');
        const backButton = document.getElementById('backButton');
        const errorDiv = document.getElementById('error');
        const hoursCountDisplay = document.getElementById('hoursCountDisplay');
        const additionalHourChargeDisplay = document.getElementById('additionalHourChargeDisplay');
        const subtotalDisplay = document.getElementById('subtotalDisplay');
        const discountDisplay = document.getElementById('discountDisplay');
        const advancePaymentDisplay = document.getElementById('advancePaymentDisplay');
        const finalTotalDisplay = document.getElementById('finalTotalDisplay');
        const pendingAmountDisplay = document.getElementById('pendingAmountDisplay');
        const issuedBySpan = document.getElementById('issuedBy');
        const billingDateDiv = document.getElementById('billingDate');
        const invoiceNumberSpan = document.getElementById('invoiceNumber');
        const detailsSection = document.getElementById('details');
        const roomDetailsSection = document.getElementById('roomDetails');
        const paymentSection = document.getElementById('paymentSection');
        const footerSection = document.querySelector('.footer');

        const roomSubtotal = parseFloat('<?php echo $total_rate; ?>') || 0;
        const hasGuest = <?php echo $guest ? 'true' : 'false'; ?>;

        // Set current date as billing date
        const today = new Date();
        billingDateDiv.textContent = formatDate(today);

        // Initialize
        document.querySelectorAll('.input-group').forEach(group => {
            if (group.id !== 'grcNumberGroup') {
                group.style.display = 'none';
            }
        });

        // Back button
        backButton.addEventListener('click', () => {
            window.location.href = 'frontoffice.php';
        });

        // Function to update all calculations and displays
        function updateCalculations() {
            const additionalHours = parseInt(additionalHoursInput.value) || 0;
            const hourlyRate = parseFloat(hourlyRateInput.value) || 0;
            const additionalCharge = additionalHours * hourlyRate;
            const subtotal = roomSubtotal + additionalCharge;
            const discountVal = parseFloat(discountInput.value) || 0;
            const advanceVal = parseFloat(advancePaymentInput.value) || 0;
            const finalTotal = Math.max(0, subtotal - discountVal);
            const pending = Math.max(0, finalTotal - advanceVal);

            hoursCountDisplay.textContent = additionalHours;
            additionalHourChargeDisplay.textContent = `Rs. ${additionalCharge.toFixed(2)}`;
            subtotalDisplay.textContent = `Rs. ${subtotal.toFixed(2)}`;
            discountDisplay.textContent = `Rs. ${discountVal.toFixed(2)}`;
            finalTotalDisplay.textContent = `Rs. ${finalTotal.toFixed(2)}`;
            advancePaymentDisplay.textContent = `Rs. ${advanceVal.toFixed(2)}`;
            pendingAmountDisplay.textContent = `Rs. ${pending.toFixed(2)}`;
            pendingAmountInput.value = pending.toFixed(2);
        }

        // Additional hours input event
        if (additionalHoursInput) {
            additionalHoursInput.addEventListener('input', function() {
                let hours = parseInt(this.value) || 0;
                if (hours < 0) hours = 0;
                this.value = hours;
                updateCalculations();
            });
        }

        // Hourly rate input event
        if (hourlyRateInput) {
            hourlyRateInput.addEventListener('input', function() {
                let rate = parseFloat(this.value) || 0;
                if (rate < 0) rate = 0;
                this.value = rate;
                updateCalculations();
            });
        }

        // Discount input event
        if (discountInput) {
            discountInput.addEventListener('input', function() {
                const additionalHours = parseInt(additionalHoursInput.value) || 0;
                const hourlyRate = parseFloat(hourlyRateInput.value) || 0;
                const additionalCharge = additionalHours * hourlyRate;
                const subtotal = roomSubtotal + additionalCharge;
                let disc = parseFloat(this.value) || 0;
                if (disc < 0) disc = 0;
                if (disc > subtotal) disc = subtotal;
                this.value = disc;
                updateCalculations();
            });
        }

        // Advance payment input event
        if (advancePaymentInput) {
            advancePaymentInput.addEventListener('input', function() {
                const additionalHours = parseInt(additionalHoursInput.value) || 0;
                const hourlyRate = parseFloat(hourlyRateInput.value) || 0;
                const additionalCharge = additionalHours * hourlyRate;
                const subtotal = roomSubtotal + additionalCharge;
                const disc = parseFloat(discountInput.value) || 0;
                const currentFinal = subtotal - disc;
                let pay = parseFloat(this.value) || 0;
                if (pay < 0) pay = 0;
                if (pay > currentFinal) pay = currentFinal;
                this.value = pay;
                updateCalculations();
            });
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}/${month}/${day}`;
        }

        // Search button
        searchButton.addEventListener('click', function() {
            const grcNumber = grcNumberInput.value.trim();
            if (!grcNumber || isNaN(grcNumber)) {
                showError('Please enter a valid GRC number');
                return;
            }
            // Submit form to reload with GRC number
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'grc_number';
            input.value = grcNumber;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });

        // Print button
        printButton.addEventListener('click', async function() {
            if (!detailsSection.classList.contains('active')) {
                showError('Please search for a valid GRC number before printing');
                return;
            }

            const additionalHours = parseInt(additionalHoursInput.value) || 0;
            const hourlyRate = parseFloat(hourlyRateInput.value) || 0;
            const advancePayment = parseFloat(advancePaymentInput.value) || 0;
            const pendingAmount = parseFloat(pendingAmountInput.value) || 0;
            const discount = parseFloat(discountInput.value) || 0;

            // Log values for debugging
            console.log('Sending AJAX request with:', {
                grc_number: '<?php echo $grc_number; ?>',
                additional_hours: additionalHours,
                hourly_rate: hourlyRate.toFixed(2),
                discount: discount.toFixed(2),
                advance_payment: advancePayment.toFixed(2),
                pending_amount: pendingAmount.toFixed(2)
            });

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'save_invoice',
                        'grc_number': '<?php echo $grc_number; ?>',
                        'additional_hours': additionalHours,
                        'hourly_rate': hourlyRate.toFixed(2),
                        'discount': discount.toFixed(2),
                        'advance_payment': advancePayment.toFixed(2),
                        'pending_amount': pendingAmount.toFixed(2)
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    // Update invoice number in UI
                    invoiceNumberSpan.textContent = result.invoice_number;
                    additionalHourChargeDisplay.textContent = `Rs. ${result.additional_charge}`;
                    showSuccess(result.message);
                    // Proceed with printing
                    setTimeout(() => window.print(), 500);
                } else {
                    showError(result.message);
                }
            } catch (error) {
                showError('Failed to save invoice: ' + error.message);
            }
        });

        // Reset page state after print dialog closes
        window.onafterprint = function() {
            // Clear GRC number input
            grcNumberInput.value = '';

            // Hide sections
            detailsSection.classList.remove('active');
            roomDetailsSection.classList.remove('active');
            paymentSection.classList.remove('active');
            footerSection.classList.remove('active');

            // Hide input groups except GRC number
            document.getElementById('additionalHoursGroup').style.display = 'none';
            document.getElementById('hourlyRateGroup').style.display = 'none';
            document.getElementById('discountGroup').style.display = 'none';
            document.getElementById('advancePaymentGroup').style.display = 'none';
            document.getElementById('pendingAmountGroup').style.display = 'none';
            document.getElementById('grcNumberGroup').style.display = 'block';

            // Disable print button
            printButton.disabled = true;

            // Clear error/success message
            errorDiv.style.display = 'none';
            errorDiv.textContent = '';

            // Reset invoice number
            invoiceNumberSpan.textContent = 'TBD';

            // Reset payment fields
            additionalHoursInput.value = '0';
            hourlyRateInput.value = '0';
            discountInput.value = '0';
            advancePaymentInput.value = '';
            pendingAmountInput.value = '<?php echo is_numeric($total_rate) ? number_format($total_rate, 2) : 'N/A'; ?>';
            updateCalculations();

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

        // Show sections only if guest is found
        <?php if ($guest): ?>
            document.querySelectorAll('.hours-count-row, .additional-hour-row, .discount-row, .advance-payment-row, .pending-amount-row').forEach(row => {
                row.style.display = 'flex';
            });
            document.getElementById('additionalHoursGroup').style.display = 'block';
            document.getElementById('hourlyRateGroup').style.display = 'block';
            document.getElementById('discountGroup').style.display = 'block';
            document.getElementById('advancePaymentGroup').style.display = 'block';
            document.getElementById('pendingAmountGroup').style.display = 'block';
            updateCalculations();
            showSuccess('Guest details loaded successfully');
            printButton.disabled = false;
        <?php else: ?>
            printButton.disabled = true;
        <?php endif; ?>

        // Fetch current user
        issuedBySpan.textContent = 'Admin';
    });
    </script>
</body>
</html>