<?php
// Start session
session_start();

// Set timezone to Asia/Colombo
date_default_timezone_set('Asia/Colombo');

// Database Connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die('<div class="alert alert-danger">Connection failed: ' . htmlspecialchars($conn->connect_error) . '</div>');
}
$conn->set_charset("utf8mb4");

// Function to normalize unit names
function normalizeUnit($unit) {
    $unit = strtolower(trim($unit));
    // Map 'liter' to 'L' for consistency
    if ($unit === 'liter' || $unit === 'litre') {
        return 'L';
    }
    if ($unit === 'ml' || $unit === 'milliliter' || $unit === 'millilitre' || $unit === 'meleleter') {
        return 'ml';
    }
    return $unit;
}

// Function to convert quantity between units
function convertQuantity($quantity, $fromUnit, $toUnit, $conn) {
    if ($fromUnit === $toUnit) {
        return $quantity;
    }

    $quantity = floatval($quantity);
    if ($quantity < 0) {
        error_log("Invalid quantity: $quantity (negative)");
        throw new Exception("Quantity cannot be negative.");
    }

    // Static conversion table for unit conversions
    $conversionTable = [
        // Weight (base: g)
        'mg' => ['base' => 'g', 'factor' => 0.001],
        'g' => ['base' => 'g', 'factor' => 1],
        'kg' => ['base' => 'g', 'factor' => 1000],
        't' => ['base' => 'g', 'factor' => 1000000],
        // Volume (base: L)
        'ml' => ['base' => 'L', 'factor' => 0.001],
        'mL' => ['base' => 'L', 'factor' => 0.001],
        'L' => ['base' => 'L', 'factor' => 1],
        'l' => ['base' => 'L', 'factor' => 1],
        'm³' => ['base' => 'L', 'factor' => 1000],
        // Length (base: m)
        'mm' => ['base' => 'm', 'factor' => 0.001],
        'cm' => ['base' => 'm', 'factor' => 0.01],
        'm' => ['base' => 'm', 'factor' => 1],
        'km' => ['base' => 'm', 'factor' => 1000],
        // Count (base: pcs)
        'pcs' => ['base' => 'pcs', 'factor' => 1],
        'doz' => ['base' => 'pcs', 'factor' => 12],
        'unit' => ['base' => 'pcs', 'factor' => 1]
    ];

    // Normalize units
    $fromUnit = normalizeUnit($fromUnit);
    $toUnit = normalizeUnit($toUnit);

    // Verify units exist in items table
    $stmt = $conn->prepare("SELECT DISTINCT unit_type FROM items WHERE unit_type IN (?, ?)");
    $stmt->bind_param("ss", $fromUnit, $toUnit);
    $stmt->execute();
    $result = $stmt->get_result();
    $validUnits = [];
    while ($row = $result->fetch_assoc()) {
        $validUnits[] = normalizeUnit($row['unit_type']);
    }
    $stmt->close();

    if (!in_array($fromUnit, $validUnits) || !in_array($toUnit, $validUnits)) {
        error_log("Invalid unit conversion: from $fromUnit to $toUnit (not found in items table)");
        throw new Exception("Invalid unit: '$fromUnit' or '$toUnit' not found in items table.");
    }

    if (!isset($conversionTable[$fromUnit]) || !isset($conversionTable[$toUnit])) {
        error_log("Invalid unit conversion: from $fromUnit to $toUnit (not in conversion table)");
        throw new Exception("Unit conversion not supported: '$fromUnit' to '$toUnit'.");
    }

    if ($conversionTable[$fromUnit]['base'] !== $conversionTable[$toUnit]['base']) {
        error_log("Incompatible unit types: $fromUnit and $toUnit");
        throw new Exception("Cannot convert between incompatible units: '$fromUnit' and '$toUnit'");
    }

    // Convert to base unit, then to target unit
    $baseQuantity = $quantity * $conversionTable[$fromUnit]['factor'];
    $result = $baseQuantity / $conversionTable[$toUnit]['factor'];
    error_log("Converted $quantity $fromUnit to $result $toUnit (base: {$conversionTable[$fromUnit]['base']})");
    return $result;
}

// Fetch valid unit types from items table
function getValidUnits($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT unit_type FROM items ORDER BY unit_type");
    if (!$stmt) {
        error_log("Error fetching unit types: " . $conn->error);
        return [];
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $units = [];
    while ($row = $result->fetch_assoc()) {
        $units[] = normalizeUnit($row['unit_type']);
    }
    $stmt->close();
    $result->free();
    // Remove duplicates and sort
    $units = array_unique($units);
    sort($units);
    return $units;
}

// Handle AJAX request to mark request as printed
if (isset($_POST['mark_printed'])) {
    $request_id = (int)$_POST['request_id'];
    try {
        // First check if the request exists and is in a state that can be marked as printed
        $stmt_check = $conn->prepare("SELECT status, approver_id, issued_date FROM item_requests WHERE id = ?");
        if (!$stmt_check) {
            throw new Exception("Database error: Unable to prepare check query. " . $conn->error);
        }
        $stmt_check->bind_param("i", $request_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            throw new Exception("Request ID $request_id does not exist.");
        }
        
        $request_data = $result_check->fetch_assoc();
        $current_status = $request_data['status'];
        $approver_id = $request_data['approver_id'];
        $issued_date = $request_data['issued_date'];
        $stmt_check->close();
        
        // Check if request can be marked as printed
        if ($current_status === 'printed') {
            throw new Exception("Request ID $request_id is already marked as printed.");
        }
        
        if (empty($approver_id) || empty($issued_date)) {
            throw new Exception("Request ID $request_id cannot be marked as printed until items are issued.");
        }
        
        // Update status to printed
        $stmt = $conn->prepare("UPDATE item_requests SET status = 'printed' WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database error: Unable to prepare update query. " . $conn->error);
        }
        $stmt->bind_param("i", $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Database error during update: " . $stmt->error);
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows affected. Request may not exist or already be printed.");
        }
        
        $stmt->close();
        error_log("Successfully marked request ID $request_id as printed");
        echo json_encode(['success' => true, 'message' => 'Request marked as printed successfully']);
    } catch (Exception $e) {
        error_log("Error marking request ID $request_id as printed: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle manual issuing
if (isset($_POST['issue_request'])) {
    error_log("POST data: " . print_r($_POST, true));
    $request_id = (int)$_POST['request_id'];
    $accepter_username = trim($_POST['accepter_username']);
    $accepter_password = $_POST['accepter_password'];
    $items_to_issue = isset($_POST['items']) ? $_POST['items'] : [];

    error_log("Parsed inputs: request_id=$request_id, username=$accepter_username, password=" . (empty($accepter_password) ? 'empty' : 'provided'));

    if ($request_id <= 0) {
        $error = "Invalid Request ID. Please select a valid request.";
        error_log($error);
    } elseif (empty($accepter_username)) {
        $error = "Username is required.";
        error_log($error);
    } elseif (empty($accepter_password)) {
        $error = "Password is required.";
        error_log($error);
    } elseif (empty($items_to_issue)) {
        $error = "No items selected to issue.";
        error_log($error);
    } else {
        $conn->begin_transaction();
        try {
            // Check if request exists and is pending
            $stmt_check = $conn->prepare("SELECT status FROM item_requests WHERE id = ? FOR UPDATE");
            if (!$stmt_check) {
                throw new Exception("Database error: Unable to prepare request verification query. " . $conn->error);
            }
            $stmt_check->bind_param("i", $request_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows === 0) {
                throw new Exception("Request ID $request_id does not exist.");
            }
            $status = $result_check->fetch_assoc()['status'];
            if ($status !== 'pending') {
                throw new Exception("Request ID $request_id is not in pending status. Current status: $status");
            }
            $stmt_check->close();
            $result_check->free();

            // Validate accepter credentials and get ID
            $stmt = $conn->prepare("SELECT id, password FROM approvers WHERE username = ?");
            if (!$stmt) {
                throw new Exception("Database error: Unable to prepare approver query. " . $conn->error);
            }
            $stmt->bind_param("s", $accepter_username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $approver = $result->fetch_assoc();
                if (password_verify($accepter_password, $approver['password'])) {
                    $approver_id = $approver['id'];
                    $issued_something = false;
                    $no_stock_items = [];

                    // Fetch request items for validation
                    $stmt_items = $conn->prepare("
                        SELECT ri.id AS ri_id, ri.item_id, ri.quantity, ri.issued_quantity, ri.unit_type AS request_unit, 
                               i.name, i.unit_type AS base_unit 
                        FROM request_items ri 
                        JOIN items i ON ri.item_id = i.id 
                        WHERE ri.request_id = ? FOR UPDATE
                    ");
                    if (!$stmt_items) {
                        throw new Exception("Database error: Unable to prepare request items query. " . $conn->error);
                    }
                    $stmt_items->bind_param("i", $request_id);
                    $stmt_items->execute();
                    $items_result = $stmt_items->get_result();
                    $request_items = [];
                    while ($row = $items_result->fetch_assoc()) {
                        $row['base_unit'] = normalizeUnit($row['base_unit']);
                        $row['request_unit'] = normalizeUnit($row['request_unit']);
                        $request_items[$row['ri_id']] = $row;
                    }
                    $stmt_items->close();
                    $items_result->free();

                    // Process each submitted item
                    foreach ($items_to_issue as $item_data) {
                        $ri_id = (int)$item_data['ri_id'];
                        $issue_quantity = floatval($item_data['quantity']);
                        $issue_unit = normalizeUnit(trim($item_data['unit_type']));

                        if (!isset($request_items[$ri_id])) {
                            throw new Exception("Invalid request item ID: $ri_id");
                        }

                        $item = $request_items[$ri_id];
                        $item_id = $item['item_id'];
                        $item_name = $item['name'];
                        $quantity = floatval($item['quantity']);
                        $issued_quantity = floatval($item['issued_quantity']);
                        $request_unit = $item['request_unit'];
                        $base_unit = $item['base_unit'];

                        // Convert quantities to base unit
                        $quantity_in_base = convertQuantity($quantity, $request_unit, $base_unit, $conn);
                        $issued_in_base = convertQuantity($issued_quantity, $request_unit, $base_unit, $conn);
                        $remaining_in_base = $quantity_in_base - $issued_in_base;

                        // Validate issue quantity and unit
                        if ($issue_quantity <= 0) {
                            continue; // Skip zero or negative quantities
                        }

                        $issue_quantity_base = convertQuantity($issue_quantity, $issue_unit, $base_unit, $conn);
                        if ($issue_quantity_base > $remaining_in_base) {
                            throw new Exception("Cannot issue $issue_quantity $issue_unit for $item_name. Only $remaining_in_base $base_unit remaining.");
                        }

                        $location = 'Main Warehouse';

                        // Check stock
                        $stmt_stock = $conn->prepare("
                            SELECT available_quantity, last_added_quantity, total_quantity 
                            FROM item_stock 
                            WHERE item_id = ? AND location = ? FOR UPDATE
                        ");
                        if (!$stmt_stock) {
                            throw new Exception("Database error: Unable to prepare stock query for item ID $item_id. " . $conn->error);
                        }
                        $stmt_stock->bind_param("is", $item_id, $location);
                        $stmt_stock->execute();
                        $stock_result = $stmt_stock->get_result();

                        if ($stock_result->num_rows === 0) {
                            error_log("No stock found for item ID $item_id at location $location");
                            $no_stock_items[] = $item_name;
                            $stmt_stock->close();
                            $stock_result->free();
                            continue;
                        }
                        $stock = $stock_result->fetch_assoc();
                        $available_qty = floatval($stock['available_quantity']);
                        $last_added_qty = floatval($stock['last_added_quantity']);
                        $total_qty = floatval($stock['total_quantity']);
                        $stmt_stock->close();
                        $stock_result->free();

                        if ($issue_quantity_base > $total_qty) {
                            throw new Exception("Insufficient stock for $item_name: Requested $issue_quantity_base $base_unit, Available $total_qty $base_unit.");
                        }

                        // Update stock
                        $new_available_qty = $available_qty - $issue_quantity_base;
                        $new_last_added_qty = $last_added_qty;
                        if ($new_available_qty < 0) {
                            $new_last_added_qty += $new_available_qty;
                            $new_available_qty = 0;
                        }
                        $new_total_qty = $new_available_qty + $new_last_added_qty;

                        error_log("Updating stock for $item_name: Deducting $issue_quantity_base $base_unit, New Available: $new_available_qty $base_unit, New Last Added: $new_last_added_qty $base_unit, New Total: $new_total_qty $base_unit");

                        $stmt_update_stock = $conn->prepare("
                            UPDATE item_stock 
                            SET available_quantity = ?, last_added_quantity = ?, total_quantity = ?
                            WHERE item_id = ? AND location = ?
                        ");
                        if (!$stmt_update_stock) {
                            throw new Exception("Database error: Unable to prepare stock update query for item ID $item_id. " . $conn->error);
                        }
                        $stmt_update_stock->bind_param("dddis", $new_available_qty, $new_last_added_qty, $new_total_qty, $item_id, $location);
                        if (!$stmt_update_stock->execute()) {
                            throw new Exception("Database error: Failed to update stock for item '$item_name'. " . $stmt_update_stock->error);
                        }
                        $stmt_update_stock->close();

                        // Update request_item
                        $new_issued = $issue_unit === $request_unit ? $issued_quantity + $issue_quantity : convertQuantity($issued_in_base + $issue_quantity_base, $base_unit, $request_unit, $conn);
                        error_log("Updating request item $ri_id: Issued $issue_quantity $issue_unit, New Issued: $new_issued $request_unit");
                        $stmt_update_ri = $conn->prepare("UPDATE request_items SET issued_quantity = ? WHERE id = ?");
                        if (!$stmt_update_ri) {
                            throw new Exception("Database error: Unable to prepare request item update query. " . $conn->error);
                        }
                        $stmt_update_ri->bind_param("di", $new_issued, $ri_id);
                        if (!$stmt_update_ri->execute()) {
                            throw new Exception("Database error: Failed to update issued quantity for request item ID $ri_id. " . $stmt_update_ri->error);
                        }
                        $stmt_update_ri->close();

                        $issued_something = true;
                    }

                    // Update item_requests with approver_id and issued_date
                    if ($issued_something) {
                        $issued_date = date('Y-m-d H:i:s');
                        $stmt_update_request = $conn->prepare("UPDATE item_requests SET approver_id = ?, issued_date = ? WHERE id = ?");
                        if (!$stmt_update_request) {
                            throw new Exception("Database error: Unable to prepare request update query. " . $conn->error);
                        }
                        $stmt_update_request->bind_param("isi", $approver_id, $issued_date, $request_id);
                        if (!$stmt_update_request->execute()) {
                            throw new Exception("Database error: Failed to update approver_id and issued_date for request ID $request_id. " . $stmt_update_request->error);
                        }
                        $stmt_update_request->close();
                    }

                    // Check if fully fulfilled
                    $stmt_check_fulfilled = $conn->prepare("SELECT COUNT(*) as unfinished FROM request_items WHERE request_id = ? AND issued_quantity < quantity");
                    if (!$stmt_check_fulfilled) {
                        throw new Exception("Database error: Unable to prepare fulfilled check query. " . $conn->error);
                    }
                    $stmt_check_fulfilled->bind_param("i", $request_id);
                    $stmt_check_fulfilled->execute();
                    $fulfilled_result = $stmt_check_fulfilled->get_result();
                    $unfinished = $fulfilled_result->fetch_assoc()['unfinished'];
                    $stmt_check_fulfilled->close();
                    $fulfilled_result->free();

                    if ($unfinished == 0) {
                        $success = "Request ID $request_id fully fulfilled. Issued on $issued_date. Please print the request sheet to finalize.";
                    } else {
                        if ($issued_something) {
                            $success = "Quantities issued for request ID $request_id on $issued_date. Remaining items still pending.";
                            if (!empty($no_stock_items)) {
                                $success .= " No stock available for: " . implode(', ', $no_stock_items) . ".";
                            }
                        } else {
                            $success = "No quantities issued for request ID $request_id. " . (!empty($no_stock_items) ? "No stock available for: " . implode(', ', $no_stock_items) . "." : "");
                        }
                    }

                    $conn->commit();
                } else {
                    throw new Exception("Invalid password.");
                }
            } else {
                throw new Exception("Username not found.");
            }
            $stmt->close();
            $result->free();
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
            error_log("Error issuing request ID $request_id: " . $e->getMessage());
        }
    }
}

// Fetch all pending requests
$pending_requests = [];
$stmt = $conn->prepare("SELECT id, request_date, requester_name, section, reason, last_request_date, manager_id, approver_id, issued_date FROM item_requests WHERE status = 'pending' ORDER BY request_date DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        error_log("Request ID fetched: " . $row['id']);
        $pending_requests[] = $row;
    }
    $stmt->close();
    $result->free();
} else {
    $error = "Error fetching pending requests: " . htmlspecialchars($conn->error);
    error_log("Prepare failed for pending requests query: " . $conn->error);
}

// Fetch all approver usernames
$approver_usernames = [];
$stmt_approvers = $conn->prepare("SELECT username FROM approvers ORDER BY username");
if ($stmt_approvers) {
    $stmt_approvers->execute();
    $result_approvers = $stmt_approvers->get_result();
    while ($row = $result_approvers->fetch_assoc()) {
        $approver_usernames[] = $row['username'];
    }
    $stmt_approvers->close();
    $result_approvers->free();
} else {
    $error = "Error fetching approver usernames: " . htmlspecialchars($conn->error);
    error_log("Prepare failed for approvers query: " . $conn->error);
}

// Fetch all unit types from items table
$valid_units = getValidUnits($conn);

// Fetch items and manager/approver names for each pending request
$managers = [];
$stmt_managers = $conn->prepare("SELECT id, username FROM managers");
if ($stmt_managers) {
    $stmt_managers->execute();
    $result_managers = $stmt_managers->get_result();
    while ($row = $result_managers->fetch_assoc()) {
        $managers[$row['id']] = $row['username'];
    }
    $stmt_managers->close();
    $result_managers->free();
}

$approvers = [];
$stmt_approvers = $conn->prepare("SELECT id, username FROM approvers");
if ($stmt_approvers) {
    $stmt_approvers->execute();
    $result_approvers = $stmt_approvers->get_result();
    while ($row = $result_approvers->fetch_assoc()) {
        $approvers[$row['id']] = $row['username'];
    }
    $stmt_approvers->close();
    $result_approvers->free();
}

foreach ($pending_requests as &$request) {
    $request['manager_name'] = isset($managers[$request['manager_id']]) ? $managers[$request['manager_id']] : 'Unknown';
    $request['approver_name'] = !empty($request['approver_id']) && isset($approvers[$request['approver_id']]) ? $approvers[$request['approver_id']] : 'N/A';

    // Fetch items
    $request['items'] = [];
    $stmt_items = $conn->prepare("
        SELECT ri.id AS ri_id, i.name, ri.quantity, ri.issued_quantity, ri.unit_type AS request_unit, i.unit_type AS base_unit 
        FROM request_items ri 
        JOIN items i ON ri.item_id = i.id 
        WHERE ri.request_id = ?
    ");
    if ($stmt_items) {
        $stmt_items->bind_param("i", $request['id']);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        while ($item_row = $items_result->fetch_assoc()) {
            $item_row['request_unit'] = normalizeUnit($item_row['request_unit']);
            $item_row['base_unit'] = normalizeUnit($item_row['base_unit']);
            $item_row['remaining'] = $item_row['quantity'] - $item_row['issued_quantity'];
            $item_row['valid_units'] = $valid_units; // Use normalized units from items table
            $request['items'][] = $item_row;
        }
        $stmt_items->close();
        $items_result->free();
    } else {
        error_log("Prepare failed for request_items query: " . $conn->error);
    }
}
unset($request);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Item Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
        }
        .request-sheet {
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .request-sheet h4 {
            border-bottom: 1px solid #ced4da;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .request-sheet p {
            margin: 10px 0;
            font-size: 1rem;
        }
        .request-issued-date {
            margin: 10px 0;
            font-size: 1rem;
        }
        .request-sheet .table {
            margin-top: 20px;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .print-button, .confirm-button {
            display: inline-block;
            margin: 20px 10px 0;
            padding: 10px 20px;
            border-radius: 5px;
        }
        #print-error {
            display: none;
        }
        .modal-body .table input[type="number"] {
            width: 100px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: #fff;
                font-family: 'Arial', sans-serif;
                font-size: 9pt;
                line-height: 1.6;
                color: #000;
                width: 80mm;
            }
            .container {
                margin: 0;
                padding: 5mm;
                width: 80mm;
            }
            .request-sheet {
                border: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
                page-break-after: always;
            }
            .request-sheet h4 {
                font-size: 12pt;
                text-align: center;
                margin: 0 0 5mm;
                border-bottom: 1.5px solid #000;
                padding-bottom: 3mm;
                text-transform: uppercase;
            }
            .request-sheet p, .request-issued-date {
                font-size: 9pt;
                font-weight: bold;
                margin: 3mm 0;
                display: flex;
                align-items: center;
            }
            .request-sheet p strong, .request-issued-date strong {
                display: inline-block;
                min-width: 20mm;
                text-align: left;
            }
            .request-sheet p .colon, .request-issued-date .colon {
                display: inline-block;
                width: 2mm;
                text-align: center;
            }
            .request-sheet p span, .request-issued-date span {
                display: inline-block;
                margin-left: 1mm;
            }
            .request-sheet .table {
                width: 80mm;
                border-collapse: collapse;
                margin: 5mm 0;
                table-layout: fixed;
                font-size: 9pt;
            }
            .request-sheet .table th, .request-sheet .table td {
                border: 0.5px solid #000;
                padding: 2mm;
                text-align: left;
            }
            .request-sheet .table th:first-child, .request-sheet .table td:first-child {
                width: 50%;
                word-wrap: break-word;
                white-space: normal;
            }
            .request-sheet .table th:nth-child(2), .request-sheet .table td:nth-child(2),
            .request-sheet .table th:nth-child(3), .request-sheet .table td:nth-child(3) {
                width: 25%;
                text-align: center;
            }
            .request-sheet .table th {
                border-top: 1.5px solid #000;
                border-bottom: 1.5px solid #000;
                font-weight: bold;
            }
            .request-sheet .table tr:last-child td {
                border-bottom: 1.5px solid #000;
            }
            .signatures {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                margin: 5mm 0;
                font-size: 9pt;
                text-align: left;
            }
            .signature-box {
                width: 32%;
                min-width: 25mm;
                text-align: center;
                padding: 3mm;
            }
            .signature-box div {
                font-weight: bold;
                font-size: 9pt;
                white-space: nowrap;
                margin-bottom: 3mm;
            }
            .signature-line {
                border-top: 1.5px solid #000;
                margin-top: 6mm;
                padding-top: 2mm;
            }
            .footer {
                margin: 5mm 0;
                padding: 3mm 0 0 0;
                border-top: 1px dashed #000;
                font-size: 9pt;
                text-align: center;
                font-weight: bold;
            }
            .print-button, .confirm-button, .alert, h2 {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <style>
@media print {
  .no-print {
    display: none !important;
  }
}
</style>

<button onclick="window.location.href='../logistic.php'" 
        class="no-print"
        style="background-color: #f09424ff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>

        <div id="print-error" class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
            <span id="print-error-message">Failed to print. Check QZ Tray and printer settings. Status updated.</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h2 class="text-center mb-4">Pending Item Requests</h2>

        <?php if (empty($pending_requests)): ?>
            <p class="text-center">No pending requests found.</p>
        <?php else: ?>
            <?php foreach ($pending_requests as $request): ?>
                <div class="request-sheet" id="request-sheet-<?php echo $request['id']; ?>">
                    <h4>Item Request Sheet</h4>
                    <p class="request-id"><strong>Request ID</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['id']); ?></span></p>
                    <p class="request-date"><strong>Request Date</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['request_date']); ?></span></p>
                    <p class="request-requester"><strong>Requester Name</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['requester_name']); ?></span></p>
                    <p class="request-section"><strong>Section</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['section']); ?></span></p>
                    <p class="request-reason"><strong>Reason</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['reason']); ?></span></p>
                    <p class="request-last-date"><strong>Last Request Date</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['last_request_date'] ?: 'N/A'); ?></span></p>
                    <p class="request-manager"><strong>Confirmed by Manager</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['manager_name']); ?></span></p>
                    <p class="request-approver"><strong>Confirmed by Approver</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['approver_name']); ?></span></p>
                    <p class="request-issued-date"><strong>Issued Date</strong><span class="colon">: </span><span><?php echo htmlspecialchars($request['issued_date'] ?: 'N/A'); ?></span></p>
                    <h5 class="mt-3">Items Requested</h5>
                    <table class="table table-bordered" id="items-<?php echo $request['id']; ?>">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Requested Qty</th>
                                <th>Issued Qty</th>
                                <th>Remaining Qty</th>
                                <th>Request Unit</th>
                                <th>Base Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($request['items'])): ?>
                                <tr><td colspan="6">No items found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($request['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo number_format($item['quantity'], 2) . ' ' . htmlspecialchars($item['request_unit']); ?></td>
                                        <td><?php echo number_format($item['issued_quantity'], 2) . ' ' . htmlspecialchars($item['request_unit']); ?></td>
                                        <td><?php echo number_format($item['remaining'], 2) . ' ' . htmlspecialchars($item['request_unit']); ?></td>
                                        <td><?php echo htmlspecialchars($item['request_unit']); ?></td>
                                        <td><?php echo htmlspecialchars($item['base_unit']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <button class="btn btn-primary print-button" data-request-id="<?php echo $request['id']; ?>">Print Request Sheet</button>
                    <button class="btn btn-success confirm-button" data-bs-toggle="modal" data-bs-target="#confirmModal" data-request-id="<?php echo $request['id']; ?>" data-items='<?php echo json_encode($request['items']); ?>'>Issue Items</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Issue Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" id="issueForm">
                        <input type="hidden" name="request_id" id="requestId">
                        <div class="mb-3">
                            <label for="accepter_username" class="form-label">Username</label>
                            <select class="form-select" id="accepter_username" name="accepter_username" required>
                                <option value="">Select Approver</option>
                                <?php foreach ($approver_usernames as $username): ?>
                                    <option value="<?php echo htmlspecialchars($username); ?>"><?php echo htmlspecialchars($username); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="accepter_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="accepter_password" name="accepter_password" required>
                        </div>
                        <h5>Items to Issue</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Requested Qty</th>
                                    <th>Issued Qty</th>
                                    <th>Remaining Qty</th>
                                    <th>Request Unit</th>
                                    <th>Issue Quantity</th>
                                    <th>Issue Unit</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                        <button type="submit" name="issue_request" class="btn btn-primary">Issue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Set request_id and items in modal
const confirmModal = document.getElementById('confirmModal');
confirmModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const requestId = button.getAttribute('data-request-id');
    const items = JSON.parse(button.getAttribute('data-items') || '[]');
    const modalRequestId = confirmModal.querySelector('#requestId');
    const itemsTableBody = confirmModal.querySelector('#itemsTableBody');

    modalRequestId.value = requestId;
    itemsTableBody.innerHTML = '';

    items.forEach(item => {
        const validUnits = item.valid_units || [];
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${parseFloat(item.quantity).toFixed(2)} ${item.request_unit}</td>
            <td>${parseFloat(item.issued_quantity).toFixed(2)} ${item.request_unit}</td>
            <td>${parseFloat(item.remaining).toFixed(2)} ${item.request_unit}</td>
            <td>${item.request_unit}</td>
            <td><input type="number" name="items[${item.ri_id}][quantity]" min="0" step="0.01" class="form-control" placeholder="Enter quantity"></td>
            <td>
                <select name="items[${item.ri_id}][unit_type]" class="form-select" required>
                    <option value="">Select Unit</option>
                    ${validUnits.map(unit => `<option value="${unit}" ${unit === item.request_unit ? 'selected' : ''}>${unit}</option>`).join('')}
                </select>
                <input type="hidden" name="items[${item.ri_id}][ri_id]" value="${item.ri_id}">
            </td>
        `;
        itemsTableBody.appendChild(row);
    });

    console.log('Modal opened for Request ID:', requestId, 'Items:', items);
});

// Validate form submission
document.getElementById('issueForm').addEventListener('submit', function(event) {
    const requestId = document.getElementById('requestId').value;
    if (!requestId || parseInt(requestId) <= 0) {
        event.preventDefault();
        alert('Invalid Request ID. Please select a valid request.');
    }
    const items = document.querySelectorAll('#itemsTableBody tr');
    let hasValidInput = false;
    items.forEach(item => {
        const quantityInput = item.querySelector('input[type="number"]');
        const unitSelect = item.querySelector('select');
        if (quantityInput.value && parseFloat(quantityInput.value) > 0 && unitSelect.value) {
            hasValidInput = true;
        }
    });
    if (!hasValidInput) {
        event.preventDefault();
        alert('Please specify at least one valid quantity and unit to issue.');
    }
});

// Handle print button click
document.querySelectorAll('.print-button').forEach(button => {
    button.addEventListener('click', function() {
        const requestId = this.getAttribute('data-request-id');
        console.log('Starting print for Request ID:', requestId);

        const printError = document.getElementById('print-error');
        const printErrorMessage = document.getElementById('print-error-message');
        const requestSheet = document.getElementById('request-sheet-' + requestId);
        const table = document.getElementById('items-' + requestId);

        if (!requestSheet || !table) {
            console.error('Request sheet or table not found for Request ID:', requestId);
            alert('Error: Request data not found.');
            return;
        }

        let requestIdText, date, requester, section, reason, lastRequestDate, manager, approver, issuedDate;
        try {
            requestIdText = requestSheet.querySelector('.request-id span:last-child')?.textContent || '';
            date = requestSheet.querySelector('.request-date span:last-child')?.textContent || '';
            requester = requestSheet.querySelector('.request-requester span:last-child')?.textContent || '';
            section = requestSheet.querySelector('.request-section span:last-child')?.textContent || '';
            reason = requestSheet.querySelector('.request-reason span:last-child')?.textContent || '';
            lastRequestDate = requestSheet.querySelector('.request-last-date span:last-child')?.textContent || 'N/A';
            manager = requestSheet.querySelector('.request-manager span:last-child')?.textContent || 'Unknown';
            approver = requestSheet.querySelector('.request-approver span:last-child')?.textContent || 'N/A';
            issuedDate = requestSheet.querySelector('.request-issued-date span:last-child')?.textContent || 'N/A';

            if (!requestIdText || !date || !requester || !section || !reason || !manager) {
                throw new Error('Missing required fields.');
            }
        } catch (e) {
            console.error('Error extracting request data:', e.message);
            alert('Error: Failed to extract request data.');
            return;
        }

        const loggedInUser = '<?php echo isset($_SESSION['username']) ? addslashes(htmlspecialchars($_SESSION['username'])) : ''; ?>';
        if (!loggedInUser) {
            console.error('Logged-in user not found in session.');
            alert('Error: User session not found. Please log in.');
            return;
        }

        const tableClone = table.cloneNode(true);
        const rows = tableClone.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            const headerCells = rows[i].getElementsByTagName('th');
            if (cells.length >= 6) {
                cells[5].remove(); // Remove Base Unit
                cells[4].remove(); // Remove Request Unit
                cells[3].remove(); // Remove Remaining Qty
            }
            if (headerCells.length >= 6) {
                headerCells[5].remove(); // Remove Base Unit
                headerCells[4].remove(); // Remove Request Unit
                headerCells[3].remove(); // Remove Remaining Qty
            }
        }
        const itemsHtml = tableClone.querySelector('tbody').innerHTML;

        if (!requestIdText) {
            console.error('Invalid Request ID.');
            alert('Error: Invalid Request ID.');
            return;
        }

        if (!itemsHtml || itemsHtml.includes('No items found')) {
            console.error('No items to print.');
            alert('Error: No items in request.');
            return;
        }

        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                        font-family: 'Arial', sans-serif;
                    }
                    body {
                        font-size: 9pt;
                        line-height: 1.6;
                        color: #000;
                        background: #fff;
                        width: 80mm;
                        margin: 0;
                        padding: 0;
                    }
                    .request-container {
                        width: 80mm;
                        margin: 0 auto;
                        padding: 0;
                        text-align: left;
                    }
                    .request-header {
                        margin: 0 0 5mm 0;
                        padding: 0 0 3mm 0;
                        text-align: left;
                    }
                    .request-header h2 {
                        font-size: 12pt;
                        font-weight: bold;
                        margin-bottom: 4mm;
                        text-transform: uppercase;
                        text-align: center;
                        border-bottom: 1.5px solid #000;
                    }
                    .request-header div {
                        font-size: 9pt;
                        font-weight: bold;
                        display: flex;
                        align-items: center;
                        margin-bottom: 3mm;
                    }
                    .request-header div strong {
                        display: inline-block;
                        min-width: 20mm;
                        text-align: left;
                    }
                    .request-header div .colon {
                        display: inline-block;
                        width: 2mm;
                        text-align: center;
                    }
                    .request-header div span {
                        display: inline-block;
                        margin-left: 1mm;
                    }
                    .request-table {
                        width: 80mm;
                        border-collapse: collapse;
                        margin: 0;
                        padding: 0;
                        font-size: 9pt;
                        table-layout: fixed;
                        text-align: left;
                    }
                    .request-table tr {
                        margin-bottom: 3mm;
                    }
                    .request-table th {
                        border-top: 1.5px solid #000;
                        border-bottom: 1.5px solid #000;
                        padding: 2mm;
                        text-align: left;
                        font-weight: bold;
                    }
                    .request-table td {
                        padding: 2mm;
                        border-bottom: 0.5px solid #000;
                        text-align: left;
                        font-weight: bold;
                    }
                    .request-table th:first-child, .request-table td:first-child {
                        width: 50%;
                        word-wrap: break-word;
                        white-space: normal;
                    }
                    .request-table th:nth-child(2), .request-table td:nth-child(2) {
                        width: 25%;
                        text-align: center;
                    }
                    .request-table th:nth-child(3), .request-table td:nth-child(3) {
                        width: 25%;
                        text-align: center;
                    }
                    .request-table tr:last-child td {
                        border-bottom: 1.5px solid #000;
                    }
                    .signatures {
                        display: flex;
                        flex-wrap: wrap;
                        justify-content: space-between;
                        margin: 0 0 5mm 0;
                        font-size: 9pt;
                        text-align: left;
                    }
                    .signature-box {
                        width: 32%;
                        min-width: 25mm;
                        text-align: center;
                        padding: 3mm;
                    }
                    .signature-box div {
                        font-weight: bold;
                        font-size: 9pt;
                        white-space: nowrap;
                        margin-bottom: 3mm;
                    }
                    .signature-line {
                        border-top: 1.5px solid #000;
                        margin-top: 6mm;
                        padding-top: 2mm;
                    }
                    .footer {
                        margin: 0 0 5mm 0;
                        padding: 3mm 0 0 0;
                        border-top: 1px dashed #000;
                        font-size: 9pt;
                        text-align: center;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class='request-container'>
                    <div class='request-header'>
                        <h2>Item Request Sheet</h2>
                        <div><strong>Request ID</strong><span class='colon'>: </span><span>${requestIdText}</span></div>
                        <div><strong>Date</strong><span class='colon'>: </span><span>${date}</span></div>
                        <div><strong>Requester</strong><span class='colon'>: </span><span>${requester}</span></div>
                        <div><strong>Section</strong><span class='colon'>: </span><span>${section}</span></div>
                        <div><strong>Reason</strong><span class='colon'>: </span><span>${reason}</span></div>
                        <div><strong>Last Req. Date</strong><span class='colon'>: </span><span>${lastRequestDate}</span></div>
                        <div><strong>Manager</strong><span class='colon'>: </span><span>${manager}</span></div>
                        <div><strong>Confirmed By</strong><span class='colon'>: </span><span>${approver}</span></div>
                        <div><strong>Issued Date</strong><span class='colon'>: </span><span>${issuedDate}</span></div>
                    </div>
                    <table class='request-table'>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Req Qty</th>
                                <th>Iss Qty</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                    <div class='signatures'>
                        <div class='signature-box'>
                            <div>Issued By:</div>
                            <div>${loggedInUser}</div>
                            <div class='signature-line'></div>
                        </div>
                        <div class='signature-box'>
                            <div>Received By:</div>
                            <div>${requester}</div>
                            <div class='signature-line'></div>
                        </div>
                        <div class='signature-box'>
                            <div>Confirmed By:</div>
                            <div>${approver}</div>
                            <div class='signature-line'></div>
                        </div>
                    </div>
                    <div class='footer'>System Generated!</div>
                </div>
            </body>
            </html>
        `;

        console.log('Print content prepared:', printContent.substring(0, 200) + '...');

        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            console.error('Failed to open print window.');
            alert('Error: Unable to open print window. Please allow pop-ups.');
            return;
        }
        printWindow.document.write(printContent);
        printWindow.document.close();

        // Trigger print and handle the afterprint event
        printWindow.onload = function() {
            printWindow.print();
            
            // Use both onafterprint and a fallback timer
            let printHandled = false;
            
            const handleAfterPrint = function() {
                if (!printHandled) {
                    printHandled = true;
                    printWindow.close();
                    askToMarkAsPrinted(requestId);
                }
            };
            
            // Modern browsers support onafterprint
            printWindow.onafterprint = handleAfterPrint;
            
            // Fallback for browsers that don't support onafterprint
            setTimeout(() => {
                if (!printHandled) {
                    console.log('Fallback: Assuming print completed after timeout');
                    printWindow.close();
                    askToMarkAsPrinted(requestId);
                }
            }, 3000); // 3 second fallback
        };
    });
});

// Function to ask user to confirm print success and mark as printed
function askToMarkAsPrinted(requestId) {
    if (confirm('Was the print successful? Click OK to mark the request as printed.')) {
        console.log('User confirmed print. Marking request as printed:', requestId);
        markRequestAsPrinted(requestId);
    } else {
        console.log('User canceled print confirmation. Status not updated.');
        alert('Print confirmation canceled. Request status not updated.');
    }
}

// Function to send AJAX request to mark request as printed
function markRequestAsPrinted(requestId) {
    const printError = document.getElementById('print-error');
    const printErrorMessage = document.getElementById('print-error-message');
    
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'mark_printed=1&request_id=' + encodeURIComponent(requestId)
    })
    .then(response => {
        console.log('AJAX response received:', response);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Request marked as printed successfully. Reloading page.');
            alert('Request marked as printed successfully!');
            window.location.reload();
        } else {
            console.error('Error marking request as printed:', data.error);
            printErrorMessage.textContent = 'Error marking request as printed: ' + (data.error || 'Unknown error');
            printError.style.display = 'block';
            setTimeout(() => {
                printError.style.display = 'none';
            }, 5000);
        }
    })
    .catch(error => {
        console.error('AJAX error:', error);
        printErrorMessage.textContent = 'Error marking request as printed: ' + error;
        printError.style.display = 'block';
        setTimeout(() => {
            printError.style.display = 'none';
        }, 5000);
    });
}
</script>
</body>
</html>

<?php
$conn->close();
?>