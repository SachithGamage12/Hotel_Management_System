<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root"; // Replace with your database username
$password = "Sun123flower@"; // Replace with your database password
$dbname = "hotelgrandguardi_wedding_bliss"; // Adjust based on your database name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

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
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roserooms WHERE room_number = :room_number");
        $overlapStmt = $conn->prepare("
            SELECT COUNT(*) FROM roseroom_bookings
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
            INSERT INTO roseroom_bookings (guest_name, telephone, check_in, check_out, room_number, pax, remarks, function_type, created_at)
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
        $stmt = $conn->prepare("DELETE FROM roseroom_bookings WHERE id = :booking_id");
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
            FROM roseroom_bookings
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
            FROM roseroom_bookings
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
        $stmt = $conn->prepare("SELECT room_number FROM roserooms ORDER BY room_number");
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
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roserooms WHERE room_number = :room_number");
        $stmt->execute(['room_number' => $room_number]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Room number already exists']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO roserooms (room_number) VALUES (:room_number)");
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
        $stmt = $conn->prepare("SELECT COUNT(*) FROM roseroom_bookings WHERE room_number = :room_number");
        $stmt->execute(['room_number' => $room_number]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['error' => 'Cannot delete room with existing bookings']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM roserooms WHERE room_number = :room_number");
        $stmt->execute(['room_number' => $room_number]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to delete room: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
?>