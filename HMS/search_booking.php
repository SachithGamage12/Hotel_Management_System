<?php
header('Content-Type: application/json');

// Disable error display to prevent breaking JSON output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Log errors, but don't display them

// Database connection parameters
$host = 'localhost';
$dbname = 'hotelgrandguardi_wedding_bliss';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get and validate booking code
    $booking_code = isset($_POST['booking_code']) ? trim($_POST['booking_code']) : '';

    if (strlen($booking_code) !== 4 || !ctype_alnum($booking_code)) {
        echo json_encode(['error' => 'Invalid booking code. Must be 4 alphanumeric characters.']);
        exit;
    }

    // 1. Get booking details, prioritizing the latest record from wedding_bookings_history
    $sql = "
        SELECT 
            COALESCE(eb.full_name, wb.full_name) AS Name,
            COALESCE(eb.contact_no1, wb.contact_no1) AS Contact_no1,
            COALESCE(eb.contact_no2, wb.contact_no2) AS Contact_no2,
            COALESCE(eb.bride_address, wb.bride_address) AS Bride_address,
            COALESCE(eb.groom_address, wb.groom_address) AS Groom_address,
            COALESCE(eb.booking_date, wb.booking_date) AS Booking_date,
            ft.name AS Details_of_event,
            v.name AS Hall,
            COALESCE(eb.no_of_pax, wb.no_of_pax) AS Number_of_pax
        FROM 
            wedding_bookings wb
        LEFT JOIN (
            SELECT eb_inner.*
            FROM wedding_bookings_history eb_inner
            INNER JOIN (
                SELECT booking_reference, MAX(updated_at) AS latest_update
                FROM wedding_bookings_history
                GROUP BY booking_reference
            ) eb_max ON eb_inner.booking_reference = eb_max.booking_reference 
                AND eb_inner.updated_at = eb_max.latest_update
        ) eb ON wb.booking_reference = eb.booking_reference
        LEFT JOIN 
            venues v ON COALESCE(eb.venue_id, wb.venue_id) = v.id
        LEFT JOIN 
            function_types ft ON COALESCE(eb.function_type_id, wb.function_type_id) = ft.id
        WHERE 
            wb.booking_reference = :booking_code
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['booking_code' => $booking_code]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['error' => 'No booking found for the provided code.']);
        exit;
    }

    // 2. Get payment history for this booking with rate per plate
    $sql = "
        SELECT 
            pi.invoice_number,
            pi.total_value,
            pi.rate_per_plate,
            ph.payment_type,
            ph.payment_amount,
            ph.payment_date
        FROM 
            payment_invoices pi
        JOIN 
            payment_history ph ON pi.id = ph.invoice_id
        WHERE 
            pi.booking_reference = :booking_code
        ORDER BY 
            pi.invoice_date DESC, ph.payment_date DESC
    ";
    
    $stmt = $pdo->prepare($sql); // Fixed: Changed $query to $sql
    $stmt->execute(['booking_code' => $booking_code]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Calculate total paid and pending amount
    $total_paid = 0;
    $total_value = 0;
    $rate_per_plate = 0;
    
    if (!empty($payments)) {
        $total_value = (float)$payments[0]['total_value'];
        $rate_per_plate = (float)$payments[0]['rate_per_plate'];
        $total_paid = array_reduce($payments, function($sum, $payment) {
            return $sum + (float)$payment['payment_amount'];
        }, 0);
    }
    
    $pending_amount = $total_value - $total_paid;

    // Return all data
    echo json_encode([
        'booking' => $booking,
        'payments' => $payments,
        'total_paid' => $total_paid,
        'pending_amount' => $pending_amount,
        'total_value' => $total_value,
        'rate_per_plate' => $rate_per_plate
    ], JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // Log the error to a file or error monitoring system
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'A database error occurred. Please try again later.']);
} catch (Exception $e) {
    // Log the general error
    error_log('General error: ' . $e->getMessage());
    echo json_encode(['error' => 'An error occurred. Please try again later.']);
}
?>