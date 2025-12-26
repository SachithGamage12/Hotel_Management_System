<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if room_data is provided
    if (!isset($_GET['room_data']) || empty($_GET['room_data'])) {
        error_log("No room_data provided in request");
        echo json_encode(['success' => false, 'message' => 'No room data provided']);
        exit;
    }

    $room_entries = explode('/', $_GET['room_data']); // Split into room_type:room_number:ac_type
    $rates = [];
    $rate_counts = []; // To group identical rates with room type and A/C type
    $room_types = []; // To store room type names
    $individual_rates = []; // To store individual rate strings
    
    // Fetch room type names
    $stmt = $conn->query("SELECT id, name FROM room_types");
    $room_types_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($room_types_result as $row) {
        $room_types[$row['id']] = $row['name'];
    }
    if (empty($room_types)) {
        error_log("No room types found in database");
        echo json_encode(['success' => false, 'message' => 'No room types found in database']);
        exit;
    }
    
    foreach ($room_entries as $index => $entry) {
        if (empty($entry)) {
            $individual_rates[$index] = 'Empty entry';
            $rates[$index] = null;
            error_log("Empty entry at index $index");
            continue;
        }

        // Split and validate entry
        $parts = explode(':', $entry);
        if (count($parts) !== 3) {
            $individual_rates[$index] = 'Invalid entry format';
            $rates[$index] = null;
            error_log("Invalid entry format at index $index: $entry");
            continue;
        }

        [$room_type_id, $room_number, $ac_type] = array_map('trim', $parts);
        
        // Validate inputs
        if (!is_numeric($room_type_id) || !isset($room_types[$room_type_id])) {
            $individual_rates[$index] = 'Invalid room type';
            $rates[$index] = null;
            error_log("Invalid room type ID at index $index: $room_type_id");
            continue;
        }
        if (!preg_match('/^[0-9]+$/', $room_number)) {
            $individual_rates[$index] = 'Invalid room number';
            $rates[$index] = null;
            error_log("Invalid room number at index $index: $room_number");
            continue;
        }
        if (!in_array($ac_type, ['AC', 'Non-AC'])) {
            $individual_rates[$index] = 'Invalid A/C type';
            $rates[$index] = null;
            error_log("Invalid A/C type at index $index: $ac_type");
            continue;
        }
        
        // Query rate
        $stmt = $conn->prepare("SELECT rate FROM lodgeroom_rates WHERE room_type_id = :room_type_id AND room_number = :room_number AND ac_type = :ac_type");
        $stmt->bindParam(':room_type_id', $room_type_id, PDO::PARAM_INT);
        $stmt->bindParam(':room_number', $room_number, PDO::PARAM_STR);
        $stmt->bindParam(':ac_type', $ac_type, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $rates[$index] = [
                'room_type_id' => $room_type_id,
                'room_number' => $room_number,
                'ac_type' => $ac_type,
                'rate' => $result['rate']
            ];
            $key = $result['rate'] . '|' . $room_type_id . '|' . $ac_type;
            $rate_counts[$key] = ($rate_counts[$key] ?? 0) + 1;
            $individual_rates[$index] = "Rs. {$result['rate']} ";
        } else {
            $rates[$index] = null;
            $individual_rates[$index] = 'Rate not found';
            error_log("Rate not found for room_type_id: $room_type_id, room_number: $room_number, ac_type: $ac_type");
        }
    }
    
    // Format the total rate output
    $formatted_rates = [];
    $processed_keys = [];
    
    foreach ($rates as $index => $data) {
        if ($data !== null) {
            $rate = $data['rate'];
            $room_type_id = $data['room_type_id'];
            $room_number = $data['room_number'];
            $ac_type = $data['ac_type'];
            $room_type_name = $room_types[$room_type_id];
            $key = $rate . '|' . $room_type_id . '|' . $ac_type;
            
            if ($rate_counts[$key] > 1 && !in_array($key, $processed_keys)) {
                $formatted_rates[] = "Rs. {$rate} x {$rate_counts[$key]} ";
                $processed_keys[] = $key;
            } elseif ($rate_counts[$key] === 1) {
                $formatted_rates[] = "Room {$room_number} ({$room_type_name}, {$ac_type}): Rs. {$rate}";
            }
        } else {
            $parts = explode(':', $room_entries[$index]);
            $room_number = $parts[1] ?? 'Unknown';
            $room_type_id = $parts[0] ?? 'Unknown';
            $ac_type = $parts[2] ?? 'Unknown';
            $room_type_name = $room_types[$room_type_id] ?? 'Unknown';
            $formatted_rates[] = "Room {$room_number} ({$room_type_name}, {$ac_type}): Rate not found";
        }
    }
    
    $total_rate = !empty($formatted_rates) ? implode(' + ', $formatted_rates) : 'No valid rates';
    
    echo json_encode([
        'success' => true,
        'individual_rates' => $individual_rates,
        'total_rate' => $total_rate
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>