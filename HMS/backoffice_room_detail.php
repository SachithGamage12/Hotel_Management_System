<?php
// backoffice_room_detail.php
// This file handles the room bookings dashboard with calendar, controls, and modals
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Backoffice_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root"; // Replace with your database username
$password = "Sun123flower@"; // Replace with your database password
$dbname = "hotelgrandguardi_wedding_bliss"; // Adjust based on your database name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Alter table to add remarks, function_type, and id columns (if not exists)
    try {
        $conn->exec("ALTER TABLE room_bookings ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
        $conn->exec("ALTER TABLE room_bookings ADD COLUMN remarks TEXT NULL");
        $conn->exec("ALTER TABLE room_bookings ADD COLUMN function_type VARCHAR(100) NULL");
    } catch (PDOException $e) {
        if ($e->getCode() != '42S21') { // Ignore if columns already exist
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to alter table: ' . $e->getMessage()]);
            exit;
        }
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Handle API requests
$action = isset($_GET['action']) ? $_GET['action'] : '';
if ($action) {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_booking') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['guest_name'], $input['telephone'], $input['check_in'], $input['check_out'], $input['room_numbers'], $input['pax'])) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $guest_name = filter_var($input['guest_name'], FILTER_SANITIZE_STRING);
        $telephone = filter_var($input['telephone'], FILTER_SANITIZE_STRING);
        $check_in = filter_var($input['check_in'], FILTER_SANITIZE_STRING);
        $check_out = filter_var($input['check_out'], FILTER_SANITIZE_STRING);
        $room_numbers = array_map('filter_var', $input['room_numbers'], array_fill(0, count($input['room_numbers']), FILTER_SANITIZE_STRING));
        $pax = filter_var($input['pax'], FILTER_VALIDATE_INT);
        $remarks = isset($input['remarks']) ? filter_var($input['remarks'], FILTER_SANITIZE_STRING) : null;
        $function_type = isset($input['function_type']) ? filter_var($input['function_type'], FILTER_SANITIZE_STRING) : null;

        // Validate inputs
        if (!$guest_name || !$telephone || !$check_in || !$check_out || empty($room_numbers) || $pax === false || $pax < 1) {
            echo json_encode(['error' => 'Invalid input data']);
            exit;
        }

        // Validate dates
        if (strtotime($check_out) <= strtotime($check_in)) {
            echo json_encode(['error' => 'Check-out date must be after check-in date']);
            exit;
        }

        try {
            $conn->beginTransaction();

            // Check if rooms exist and validate for overlaps
            $stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
            $overlapStmt = $conn->prepare("
                SELECT COUNT(*) FROM room_bookings
                WHERE room_number = :room_number
                AND (
                    (:check_in BETWEEN check_in AND check_out)
                    OR (:check_out BETWEEN check_in AND check_out)
                    OR (check_in BETWEEN :check_in AND :check_out)
                )
            ");

            foreach ($room_numbers as $room_number) {
                // Validate room exists
                $stmt->execute(['room_number' => $room_number]);
                if ($stmt->fetchColumn() == 0) {
                    $conn->rollBack();
                    echo json_encode(['error' => "Invalid room number: $room_number"]);
                    exit;
                }

                // Check for overlapping bookings
                $overlapStmt->execute([
                    'room_number' => $room_number,
                    'check_in' => $check_in,
                    'check_out' => $check_out
                ]);
                if ($overlapStmt->fetchColumn() > 0) {
                    $conn->rollBack();
                    echo json_encode(['error' => "Room $room_number is already booked for the selected dates"]);
                    exit;
                }
            }

            // Insert bookings for each room
            $insertStmt = $conn->prepare("
                INSERT INTO room_bookings (guest_name, telephone, check_in, check_out, room_number, pax, remarks, function_type, created_at)
                VALUES (:guest_name, :telephone, :check_in, :check_out, :room_number, :pax, :remarks, :function_type, NOW())
            ");

            foreach ($room_numbers as $room_number) {
                $insertStmt->execute([
                    'guest_name' => $guest_name,
                    'telephone' => $telephone,
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'room_number' => $room_number,
                    'pax' => $pax,
                    'remarks' => $remarks,
                    'function_type' => $function_type
                ]);
            }

            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            $conn->rollBack();
            echo json_encode(['error' => 'Failed to save booking: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_booking') {
        $input = json_decode(file_get_contents('php://input'), true);
        $booking_id = filter_var($input['booking_id'], FILTER_VALIDATE_INT);

        if (!$booking_id) {
            echo json_encode(['error' => 'Invalid booking ID']);
            exit;
        }

        try {
            $stmt = $conn->prepare("DELETE FROM room_bookings WHERE id = :booking_id");
            $stmt->execute(['booking_id' => $booking_id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Booking not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to delete booking: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get_bookings') {
        $start = $_GET['start'];
        $end = $_GET['end'];

        try {
            $stmt = $conn->prepare("
                SELECT 
                    id,
                    check_in,
                    check_out,
                    guest_name,
                    room_number,
                    pax,
                    telephone,
                    remarks,
                    function_type
                FROM room_bookings
                WHERE check_in <= :end AND check_out >= :start
            ");
            $stmt->execute(['start' => $start, 'end' => $end]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($bookings);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to fetch bookings: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get_booking_details') {
        $date = $_GET['date'];
        $room_number = $_GET['room_number'];

        try {
            $stmt = $conn->prepare("
                SELECT 
                    id,
                    guest_name,
                    telephone,
                    check_in,
                    check_out,
                    room_number,
                    pax,
                    remarks,
                    function_type
                FROM room_bookings
                WHERE :date BETWEEN check_in AND check_out
                AND room_number = :room_number
            ");
            $stmt->execute(['date' => $date, 'room_number' => $room_number]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($bookings);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to fetch booking details: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get_rooms') {
        try {
            $stmt = $conn->prepare("SELECT room_number FROM rooms ORDER BY room_number");
            $stmt->execute();
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rooms);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to fetch rooms: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_room') {
        $input = json_decode(file_get_contents('php://input'), true);
        $room_number = filter_var($input['room_number'], FILTER_SANITIZE_STRING);

        if (!$room_number || !preg_match('/^[0-9]+$/', $room_number)) {
            echo json_encode(['error' => 'Invalid room number']);
            exit;
        }

        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = :room_number");
            $stmt->execute(['room_number' => $room_number]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Room number already exists']);
                exit;
            }

            $stmt = $conn->prepare("INSERT INTO rooms (room_number) VALUES (:room_number)");
            $stmt->execute(['room_number' => $room_number]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to add room: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_room') {
        $input = json_decode(file_get_contents('php://input'), true);
        $room_number = filter_var($input['room_number'], FILTER_SANITIZE_STRING);

        try {
            // Check if room has bookings
            $stmt = $conn->prepare("SELECT COUNT(*) FROM room_bookings WHERE room_number = :room_number");
            $stmt->execute(['room_number' => $room_number]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['error' => 'Cannot delete room with existing bookings']);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM rooms WHERE room_number = :room_number");
            $stmt->execute(['room_number' => $room_number]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Failed to delete room: ' . $e->getMessage()]);
        }
        exit;
    }

    echo json_encode(['error' => 'Invalid action']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Bookings Details</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #f472b6;
            --dark: #1e293b;
            --light: #f1f5f9;
            --success: #22c55e;
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            --background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            --panel-bg: #ffffff;
            --text-color: #0f172a;
            --border: #e2e8f0;
            --accent: #93c5fd;
            --booked-1: #f43f5e; /* Red for Honeymoon Room */
            --booked-2: #8b5cf6; /* Purple for Other */
            --booked-3: #f59e0b; /* Yellow for Changing Room */
            --booked-4: #3b82f6; /* Blue for default/unknown */
            --booked-out-guest: #10b981; /* Emerald for Out Guest Room */
            --booked-out-honeymoon: #e11d48; /* Rose for Out Guest HoneyMoon Room */
            --booked-out-anniversary: #d97706; /* Amber for Out Guest Anniversary Room */
            --booked-day-use: #2563eb; /* Blue for Day Use Room */
            --booked-group: #7c3aed; /* Purple for Group Booking Room */
            --booked-foreign: #ec4899; /* Pink for Foreign Room */
        }

        [data-theme="dark"] {
            --background: linear-gradient(145deg, #0f172a, #1e293b);
            --panel-bg: #1e293b;
            --text-color: #f1f5f9;
            --border: #475569;
            --light: #334155;
            --accent: #1e40af;
            --booked-1: #e11d48;
            --booked-2: #7c3aed;
            --booked-3: #d97706;
            --booked-4: #2563eb;
            --booked-out-guest: #059669;
            --booked-out-honeymoon: #e11d48;
            --booked-out-anniversary: #d97706;
            --booked-day-use: #2563eb;
            --booked-group: #7c3aed;
            --booked-foreign: #db2777;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text-color);
            min-height: 100vh;
            transition: all 0.3s ease;
            overflow-x: hidden;
            padding: 32px;
        }

        .dashboard {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            min-height: calc(100vh - 96px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .dashboard-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            letter-spacing: 0.5px;
        }

        .calendar-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .calendar-controls .date {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-color);
            padding: 8px 16px;
            background: var(--light);
            border-radius: 8px;
        }

        .calendar-controls select {
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--panel-bg);
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .calendar-controls select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .calendar-controls select:hover {
            background: var(--light);
        }

        .calendar-container {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            -webkit-overflow-scrolling: touch;
        }

        .calendar-table-wrapper {
            min-width: 1000px;
        }

        .calendar-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
            background: var(--panel-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .calendar-table th, .calendar-table td {
            padding: 14px;
            text-align: center;
            border: 1px solid var(--border);
            color: var(--text-color);
            min-width: 100px;
            transition: all 0.2s ease;
        }

        .calendar-table th {
            background: linear-gradient(180deg, var(--light), var(--panel-bg));
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .calendar-table th:last-child, .calendar-table tfoot th {
            background: linear-gradient(180deg, var(--light), var(--panel-bg));
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .calendar-table td {
            background: var(--panel-bg);
            position: relative;
        }

        .calendar-table td.booked {
            color: #ffffff;
            font-weight: 500;
            cursor: pointer;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-table td.booked:hover {
            filter: brightness(0.9);
            transform: scale(1.02);
        }

        .calendar-table td:not(.booked):hover {
            background: var(--accent);
            transform: scale(1.02);
        }

        .calendar-table td.date-cell {
            font-weight: 500;
            background: var(--light);
        }

        .calendar-table td.date-repeat-cell {
            font-weight: 500;
            background: var(--light);
            text-align: center;
            min-width: 80px;
            border-left: 2px solid var(--border);
        }

        .calendar-table tfoot td {
            background: linear-gradient(180deg, var(--light), var(--panel-bg));
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 2px solid var(--border);
        }

        .booked-tag {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
            overflow-y: auto;
        }

        .modal-content {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 32px;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            color: var(--text-color);
            animation: slideUp 0.4s ease;
        }

        .modal-content h3 {
            margin-bottom: 24px;
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 600px;
        }

        .booking-table th, .booking-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            color: var(--text-color);
        }

        .booking-table th {
            background: var(--light);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .booking-table tr:hover {
            background: var(--accent);
        }

        .no-bookings {
            text-align: center;
            color: var(--text-color);
            font-size: 15px;
            margin: 24px 0;
            font-weight: 500;
        }

        .close-modal {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }

        .error-message {
            color: #dc2626;
            text-align: center;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
            font-weight: 500;
        }

        .booking-form, .room-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .booking-form label, .room-form label {
            font-weight: 500;
            color: var(--text-color);
            font-size: 14px;
        }

        .booking-form input, .booking-form select, .booking-form textarea, .room-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-color);
            background: var(--panel-bg);
            transition: all 0.3s ease;
        }

        .booking-form input:focus, .booking-form select:focus, .booking-form textarea:focus, .room-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .booking-form .room-checkbox-container {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            background: var(--panel-bg);
        }

        .booking-form .room-checkbox-container label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .booking-form .room-checkbox-container input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .booking-form button, .room-form button {
            background: var(--success);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .booking-form button:hover, .room-form button:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .delete-room-btn, .delete-booking-btn {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .delete-room-btn:hover, .delete-booking-btn:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .add-room-btn {
            background: var(--success);
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .add-room-btn:hover {
            background: #16a34a;
            transform: translateY(-1px);
        }

        .add-booking-btn {
            background: var(--success);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-booking-btn:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .theme-toggle {
            background: var(--secondary);
            color: #ffffff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .theme-toggle:hover {
            background: #db2777;
            transform: rotate(90deg) scale(1.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .calendar-table th, .calendar-table td {
                min-width: 70px;
                font-size: 12px;
                padding: 10px;
            }

            .calendar-table .booked-tag {
                font-size: 9px;
                padding: 1px 4px;
            }

            .booking-table th, .booking-table td {
                font-size: 12px;
                padding: 10px;
            }

            .calendar-controls {
                flex-direction: column;
                gap: 12px;
            }

            .calendar-controls select, .calendar-controls .date {
                width: 100%;
                text-align: center;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .calendar-container {
                max-height: calc(100vh - 250px);
            }

            .booking-table {
                min-width: 100%;
            }

            .booking-form .room-checkbox-container {
                max-height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-top" style="text-align: left; margin: 10px;">
        <button id="backButton" aria-label="Go back to previous page" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Back
        </button>
    </div>

    <div class="dashboard">
        <div class="dashboard-header">
            <h2>Room Booking Calendar</h2>
            <div class="calendar-controls">
                <span class="date">Today: <span id="current-date"></span></span>
                <select id="month-select">
                    <option value="0">January</option>
                    <option value="1">February</option>
                    <option value="2">March</option>
                    <option value="3">April</option>
                    <option value="4">May</option>
                    <option value="5">June</option>
                    <option value="6">July</option>
                    <option value="7">August</option>
                    <option value="8">September</option>
                    <option value="9">October</option>
                    <option value="10">November</option>
                    <option value="11">December</option>
                </select>
                <select id="year-select"></select>
                <button class="add-booking-btn" onclick="openAddBookingModal()">Add Booking</button>
                <button class="add-booking-btn" onclick="openAddRoomModal()">Manage Rooms</button>
                <button class="add-booking-btn" onclick="exportToPDF()">Export to PDF</button>
                <button class="theme-toggle" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
        
        <div class="error-message" id="calendar-error"></div>
        <div class="calendar-container">
            <div class="calendar-table-wrapper">
                <div id="calendar"></div>
            </div>
        </div>
        
        <div class="modal" id="booking-modal">
            <div class="modal-content">
                <span class="close-modal">×</span>
                <h3>Bookings for <span id="modal-date"></span>, Room <span id="modal-room"></span></h3>
                <div id="booking-details">
                    <div class="booking-table-wrapper">
                        <table class="booking-table">
                            <thead>
                                <tr>
                                    <th>Guest Name</th>
                                    <th>Telephone</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Pax</th>
                                    <th>Remarks</th>
                                    <th>Function Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="booking-table-body"></tbody>
                        </table>
                    </div>
                    <div class="no-bookings" id="no-bookings-message" style="display: none;">
                        No bookings found for this date and room.
                    </div>
                    <div style="margin-top: 16px; text-align: right;">
                        <button class="add-room-btn" id="add-room-to-group" style="display: none;">Add Room to Group</button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal" id="add-booking-modal">
            <div class="modal-content">
                <span class="close-modal">×</span>
                <h3>Add New Room Booking</h3>
                <form class="booking-form" id="add-booking-form">
                    <label for="guest-name">Guest Name</label>
                    <input type="text" id="guest-name" required>
                    
                    <label for="guest-telephone">Telephone Number</label>
                    <input type="tel" id="guest-telephone" required pattern="[0-9]{10}">
                    
                    <label for="check-in">Check-in Date</label>
                    <input type="date" id="check-in" required>
                    
                    <label for="check-out">Check-out Date</label>
                    <input type="date" id="check-out" required>
                    
                    <label>Room Numbers</label>
                    <div class="room-checkbox-container" id="room-checkboxes">
                        <label><input type="checkbox" id="select-all-rooms"> Select All</label>
                        <!-- Checkboxes populated dynamically -->
                    </div>
                    
                    <label for="pax">Number of Pax</label>
                    <input type="number" id="pax" required min="1">
                    
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" rows="4" placeholder="Enter any additional remarks"></textarea>
                    
                    <label for="function-type">Function Type</label>
                    <select id="function-type">
                        <option value="">Select Function Type</option>
                        <option value="Changing Room">Changing Room</option>
                        <option value="Honeymoon Room">Honeymoon Room</option>
                        <option value="Other">Other</option>
                    </select>
                    
                    <button type="submit">Save Booking</button>
                </form>
                <div class="error-message" id="form-error"></div>
            </div>
        </div>
        
        <div class="modal" id="add-room-modal">
            <div class="modal-content">
                <span class="close-modal">×</span>
                <h3>Manage Room Numbers</h3>
                <form class="room-form" id="add-room-form">
                    <label for="new-room">Add New Room Number</label>
                    <input type="text" id="new-room" placeholder="e.g., 201" pattern="[0-9]+" required>
                    <button type="submit">Add Room</button>
                </form>
                <div class="error-message" id="room-error"></div>
                <h4 style="margin-top: 24px; color: var(--primary);">Existing Rooms</h4>
                <div class="booking-table-wrapper">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="room-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Scroll to top on page load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', function() {
            document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            this.innerHTML = `<i class="fas fa-${document.body.dataset.theme === 'dark' ? 'sun' : 'moon'}"></i>`;
        });

        // Set current date
        const options = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' };
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);

        // Populate month and year selectors
        const monthSelect = document.getElementById('month-select');
        const yearSelect = document.getElementById('year-select');
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();

        monthSelect.value = currentMonth;
        for (let year = currentYear - 5; year <= currentYear + 5; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearSelect.appendChild(option);
        }

        // Valid function types for back office management
        const validBackOfficeFunctionTypes = ['Honeymoon Room', 'Changing Room', 'Other'];

        // Calendar rendering
        function renderCalendar(bookings, rooms, year, month) {
            const calendarEl = document.getElementById('calendar');
            const errorEl = document.getElementById('calendar-error');

            // Calculate days in month
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const colorMap = {
                'Honeymoon Room': 'var(--booked-1)', // Red
                'Changing Room': 'var(--booked-3)', // Yellow
                'Other': 'var(--booked-2)', // Purple
                'Out Guest Room': 'var(--booked-out-guest)', // Emerald
                'Out Guest HoneyMoon Room': 'var(--booked-out-honeymoon)', // Rose
                'Out Guest Anniversary Room': 'var(--booked-out-anniversary)', // Amber
                'Day Use Room': 'var(--booked-day-use)', // Blue
                'Group Booking Room': 'var(--booked-group)', // Purple
                'Foreign Room': 'var(--booked-foreign)', // Pink
                '': 'var(--booked-4)' // Blue for default/unknown
            };

            // Create table
            let tableHTML = '<table class="calendar-table"><thead><tr><th>Date</th>';
            rooms.forEach(room => {
                tableHTML += `<th>${room.room_number}</th>`;
            });
            tableHTML += '<th>Date</th></tr></thead><tbody>';

            // Generate rows for each day
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const formattedDate = date.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
                tableHTML += `<tr><td class="date-cell">${formattedDate}</td>`;
                rooms.forEach(room => {
                    const matchingBookings = bookings.filter(booking => 
                        booking.room_number === room.room_number &&
                        dateStr >= booking.check_in &&
                        dateStr <= booking.check_out
                    );
                    if (matchingBookings.length > 0) {
                        const functionType = matchingBookings[0].function_type || '';
                        const color = colorMap[functionType] || colorMap[''];
                        tableHTML += `<td class="booked" style="background-color: ${color}" data-date="${dateStr}" data-room="${room.room_number}"><span class="booked-tag">${functionType || 'Booked'}</span></td>`;
                    } else {
                        tableHTML += `<td data-date="${dateStr}" data-room="${room.room_number}"></td>`;
                    }
                });
                tableHTML += `<td class="date-repeat-cell">${formattedDate}</td>`;
                tableHTML += '</tr>';
            }

            tableHTML += '<tfoot><tr><th>Date</th>';
            rooms.forEach(room => {
                tableHTML += `<th>${room.room_number}</th>`;
            });
            tableHTML += '<th>Date</th></tr></tfoot>';
            tableHTML += '</table>';

            calendarEl.innerHTML = tableHTML;

            // Add click listeners for booked cells
            document.querySelectorAll('.calendar-table td.booked').forEach(cell => {
                cell.addEventListener('click', function() {
                    fetchBookingDetails(this.dataset.date, this.dataset.room);
                });
            });
        }

        // Fetch rooms and bookings
        function fetchCalendarData(year = currentYear, month = currentMonth) {
            const start = `${year}-${String(month + 1).padStart(2, '0')}-01`;
            const end = `${year}-${String(month + 2).padStart(2, '0')}-01`;

            Promise.all([
                fetch(`new_api.php?action=get_rooms`),
                fetch(`new_api.php?action=get_bookings&start=${start}&end=${end}`)
            ])
            .then(([roomsResponse, bookingsResponse]) => {
                if (!roomsResponse.ok || !bookingsResponse.ok) {
                    throw new Error('Network response was not ok');
                }
                return Promise.all([roomsResponse.json(), bookingsResponse.json()]);
            })
            .then(([rooms, bookings]) => {
                if (rooms.error || bookings.error) {
                    document.getElementById('calendar-error').textContent = rooms.error || bookings.error;
                    document.getElementById('calendar-error').style.display = 'block';
                    return;
                }
                renderCalendar(bookings, rooms, year, month);
                populateRoomCheckboxes(rooms);
            })
            .then(() => {
                document.querySelector('.calendar-container').scrollTop = 0;
            })
            .catch(error => {
                document.getElementById('calendar-error').textContent = 'Failed to load calendar data. Please try again.';
                document.getElementById('calendar-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Update calendar on month/year change
        monthSelect.addEventListener('change', () => {
            fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });

        yearSelect.addEventListener('change', () => {
            fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });

        // Populate room checkboxes in booking form
        function populateRoomCheckboxes(rooms) {
            const container = document.getElementById('room-checkboxes');
            container.innerHTML = '<label><input type="checkbox" id="select-all-rooms"> Select All</label>';
            rooms.forEach(room => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" name="room-numbers" value="${room.room_number}"> Room ${room.room_number}`;
                container.appendChild(label);
            });

            // Handle Select All checkbox
            const selectAll = document.getElementById('select-all-rooms');
            selectAll.addEventListener('change', function() {
                const checkboxes = container.querySelectorAll('input[name="room-numbers"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Open add booking modal and set min dates
        function openAddBookingModal(booking = null) {
            const modal = document.getElementById('add-booking-modal');
            const checkInInput = document.getElementById('check-in');
            const checkOutInput = document.getElementById('check-out');
            const form = document.getElementById('add-booking-form');
            const roomCheckboxes = document.getElementById('room-checkboxes');
            modal.style.display = 'flex';
            form.reset();
            document.getElementById('form-error').style.display = 'none';
            modal.scrollTop = 0;

            // Clear all checkboxes
            roomCheckboxes.querySelectorAll('input[name="room-numbers"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('select-all-rooms').checked = false;

            // Pre-fill form if adding room to group
            if (booking) {
                document.getElementById('guest-name').value = booking.guest_name || '';
                document.getElementById('guest-telephone').value = booking.telephone || '';
                document.getElementById('check-in').value = booking.check_in || '';
                document.getElementById('check-out').value = booking.check_out || '';
                document.getElementById('pax').value = booking.pax || 1;
                document.getElementById('remarks').value = booking.remarks || '';
                document.getElementById('function-type').value = booking.function_type || '';
            }

            // Set min date to today for check-in
            const today = new Date().toISOString().split('T')[0];
            checkInInput.setAttribute('min', today);

            // Update check-out min date based on check-in
            checkInInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const minCheckOutDate = new Date(checkInDate);
                minCheckOutDate.setDate(checkInDate.getDate() + 1);
                checkOutInput.setAttribute('min', minCheckOutDate.toISOString().split('T')[0]);
            }, { once: true });

            // Ensure check-out min date is at least today
            checkOutInput.setAttribute('min', today);
        }

        // Open manage rooms modal
        function openAddRoomModal() {
            const modal = document.getElementById('add-room-modal');
            modal.style.display = 'flex';
            document.getElementById('add-room-form').reset();
            document.getElementById('room-error').style.display = 'none';
            fetchRoomsForManagement();
            modal.scrollTop = 0;
        }

        // Fetch rooms for management
        function fetchRoomsForManagement() {
            fetch('new_api.php?action=get_rooms')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(rooms => {
                    const tableBody = document.getElementById('room-table-body');
                    tableBody.innerHTML = '';
                    rooms.forEach(room => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${room.room_number}</td>
                            <td><button class="delete-room-btn" data-room="${room.room_number}">Delete</button></td>
                        `;
                        tableBody.appendChild(row);
                    });
                    document.querySelectorAll('.delete-room-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            deleteRoom(this.dataset.room);
                        });
                    });
                })
                .catch(error => {
                    document.getElementById('room-error').textContent = 'Failed to load rooms. Please try again.';
                    document.getElementById('room-error').style.display = 'block';
                    console.error('Error:', error);
                });
        }

        // Delete room
        function deleteRoom(roomNumber) {
            fetch('new_api.php?action=delete_room', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ room_number: roomNumber })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    fetchRoomsForManagement();
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                } else {
                    document.getElementById('room-error').textContent = data.error || 'Failed to delete room.';
                    document.getElementById('room-error').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('room-error').textContent = 'Failed to delete room. Please try again.';
                document.getElementById('room-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Delete booking
        function deleteBooking(bookingId, dateStr, roomNumber) {
            if (!confirm('Are you sure you want to cancel this booking?')) return;

            fetch('new_api.php?action=delete_booking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    fetchBookingDetails(dateStr, roomNumber);
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                } else {
                    document.getElementById('calendar-error').textContent = data.error || 'Failed to cancel booking.';
                    document.getElementById('calendar-error').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('calendar-error').textContent = 'Failed to cancel booking. Please try again.';
                document.getElementById('calendar-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Handle add room form submission
        document.getElementById('add-room-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const roomNumber = document.getElementById('new-room').value;
            const roomError = document.getElementById('room-error');

            fetch('new_api.php?action=add_room', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ room_number: roomNumber })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('add-room-modal').style.display = 'none';
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                    fetchRoomsForManagement();
                } else {
                    roomError.textContent = data.error || 'Failed to add room.';
                    roomError.style.display = 'block';
                }
            })
            .catch(error => {
                roomError.textContent = 'Failed to add room. Please try again.';
                roomError.style.display = 'block';
                console.error('Error:', error);
            });
        });

        // Handle booking form submission
        document.getElementById('add-booking-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formError = document.getElementById('form-error');
            const guestName = document.getElementById('guest-name').value;
            const telephone = document.getElementById('guest-telephone').value;
            const checkIn = document.getElementById('check-in').value;
            const checkOut = document.getElementById('check-out').value;
            const roomCheckboxes = document.querySelectorAll('#room-checkboxes input[name="room-numbers"]:checked');
            const roomNumbers = Array.from(roomCheckboxes).map(checkbox => checkbox.value);
            const pax = document.getElementById('pax').value;
            const remarks = document.getElementById('remarks').value;
            const functionType = document.getElementById('function-type').value;

            if (new Date(checkOut) <= new Date(checkIn)) {
                formError.textContent = 'Check-out date must be after check-in date.';
                formError.style.display = 'block';
                return;
            }

            if (roomNumbers.length === 0) {
                formError.textContent = 'Please select at least one room.';
                formError.style.display = 'block';
                return;
            }

            fetch('new_api.php?action=add_booking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    guest_name: guestName,
                    telephone: telephone,
                    check_in: checkIn,
                    check_out: checkOut,
                    room_numbers: roomNumbers,
                    pax: pax,
                    remarks: remarks,
                    function_type: functionType
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('add-booking-modal').style.display = 'none';
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                } else {
                    formError.textContent = data.error || 'Failed to save booking.';
                    formError.style.display = 'block';
                }
            })
            .catch(error => {
                formError.textContent = 'Failed to save booking. Please try again.';
                formError.style.display = 'block';
                console.error('Error:', error);
            });
        });

        // Export to PDF
        function exportToPDF() {
            const year = parseInt(document.getElementById('year-select').value);
            const month = parseInt(document.getElementById('month-select').value);
            const url = `export_bookings_pdf.php?action=export_bookings&year=${year}&month=${month}`;
            window.location.href = url;
        }

        // Fetch booking details for modal
        function fetchBookingDetails(dateStr, roomNumber) {
            const modal = document.getElementById('booking-modal');
            const tableBody = document.getElementById('booking-table-body');
            const noBookingsMessage = document.getElementById('no-bookings-message');
            const errorEl = document.getElementById('calendar-error');
            const modalDate = document.getElementById('modal-date');
            const modalRoom = document.getElementById('modal-room');
            const addRoomBtn = document.getElementById('add-room-to-group');

            const date = new Date(dateStr);
            const formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            modalDate.textContent = formattedDate;
            modalRoom.textContent = roomNumber;

            fetch(`new_api.php?action=get_booking_details&date=${dateStr}&room_number=${roomNumber}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(bookings => {
                    tableBody.innerHTML = '';
                    if (bookings && bookings.length > 0) {
                        bookings.forEach(booking => {
                            const isBackOfficeFunctionType = validBackOfficeFunctionTypes.includes(booking.function_type);
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${booking.guest_name || 'N/A'}</td>
                                <td>${booking.telephone || 'N/A'}</td>
                                <td>${booking.check_in || 'N/A'}</td>
                                <td>${booking.check_out || 'N/A'}</td>
                                <td>${booking.pax || '0'}</td>
                                <td>${booking.remarks || 'N/A'}</td>
                                <td>${booking.function_type || 'N/A'}</td>
                                <td>${isBackOfficeFunctionType ? `<button class="delete-booking-btn" data-booking-id="${booking.id}" data-date="${dateStr}" data-room="${roomNumber}">Delete</button>` : ''}</td>
                            `;
                            tableBody.appendChild(row);
                        });

                        // Show "Add Room to Group" button only for back office function types
                        const firstBooking = bookings[0];
                        const isBackOfficeFunctionType = validBackOfficeFunctionTypes.includes(firstBooking.function_type);
                        addRoomBtn.style.display = isBackOfficeFunctionType ? 'inline-block' : 'none';
                        if (isBackOfficeFunctionType) {
                            addRoomBtn.onclick = () => openAddBookingModal(firstBooking);
                        }

                        noBookingsMessage.style.display = 'none';
                        errorEl.style.display = 'none';
                        modal.style.display = 'flex';

                        // Add event listeners for delete buttons
                        document.querySelectorAll('.delete-booking-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                deleteBooking(this.dataset.bookingId, this.dataset.date, this.dataset.room);
                            });
                        });
                    } else {
                        noBookingsMessage.style.display = 'block';
                        addRoomBtn.style.display = 'none';
                        errorEl.textContent = '';
                        errorEl.style.display = 'block';
                        modal.style.display = 'flex';
                    }
                })
                .catch(error => {
                    errorEl.textContent = 'Failed to load booking details. Please try again.';
                    errorEl.style.display = 'block';
                    console.error('Error:', error);
                    modal.style.display = 'none';
                });
        }

        // Close modals
        document.querySelectorAll('.close-modal').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    this.style.display = 'none';
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            fetchCalendarData();
        });

        const backButton = document.getElementById('backButton');
        backButton.addEventListener('click', () => {
            window.location.href = 'Backoffice.php';
        });
    </script>
</body>
</html>