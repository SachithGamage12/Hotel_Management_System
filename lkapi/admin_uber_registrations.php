<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$dbname = 'hotelgrandguardi_lakway_delivery';
$username = 'hotelgrandguardi_root';
$password = 'Sun123flower@';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// GET: Fetch all registrations or a specific one
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get specific registration
            $stmt = $pdo->prepare("SELECT * FROM uber_store_registrations WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $registration = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($registration) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'registration' => $registration
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Registration not found']);
            }
        } else {
            // Get all registrations with optional filtering
            $status = $_GET['status'] ?? null;
            
            if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
                $stmt = $pdo->prepare("SELECT * FROM uber_store_registrations WHERE status = ? ORDER BY created_at DESC");
                $stmt->execute([$status]);
            } else {
                $stmt = $pdo->query("SELECT * FROM uber_store_registrations ORDER BY created_at DESC");
            }
            
            $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'count' => count($registrations),
                'registrations' => $registrations
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch registrations']);
    }
    exit;
}

// PUT: Update registration status
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !isset($input['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Registration ID and status are required']);
        exit;
    }
    
    if (!in_array($input['status'], ['pending', 'approved', 'rejected'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status value']);
        exit;
    }
    
    try {
        // Get current status for history
        $stmt = $pdo->prepare("SELECT status FROM uber_store_registrations WHERE id = ?");
        $stmt->execute([$input['id']]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$current) {
            http_response_code(404);
            echo json_encode(['error' => 'Registration not found']);
            exit;
        }
        
        // Update status
        $sql = "UPDATE uber_store_registrations SET status = ?, admin_notes = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['status'],
            $input['admin_notes'] ?? null,
            $input['id']
        ]);
        
        // Log status change to history table
        $history_sql = "INSERT INTO uber_registration_status_history 
                        (registration_id, old_status, new_status, changed_by, notes) 
                        VALUES (?, ?, ?, ?, ?)";
        $history_stmt = $pdo->prepare($history_sql);
        $history_stmt->execute([
            $input['id'],
            $current['status'],
            $input['status'],
            $input['admin_id'] ?? 'admin',
            $input['admin_notes'] ?? null
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Registration status updated successfully'
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update registration status']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>