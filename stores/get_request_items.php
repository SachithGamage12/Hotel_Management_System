<?php
// Database Connection
$conn = new mysqli('localhost', 'hotelgrandguardi_root', 'Sun123flower@', 'hotelgrandguardi_wedding_bliss');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['request_id'])) {
    $request_id = (int)$_GET['request_id'];
    
    $sql = "SELECT ri.item_id, i.item_name, ri.quantity as requested_qty, i.stock as available_stock
            FROM request_items ri
            JOIN inventory i ON ri.item_id = i.id
            WHERE ri.request_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$row['item_name']}</td>
            <td>{$row['requested_qty']}</td>
            <td>{$row['available_stock']}</td>
            <td>
                <input type='number' class='form-control issue-quantity' 
                       name='items[{$row['item_id']}]' 
                       min='0' max='{$row['available_stock']}' 
                       data-requested='{$row['requested_qty']}' 
                       data-stock='{$row['available_stock']}'
                       value='{$row['requested_qty']}'>
            </td>
        </tr>";
    }
    
    $stmt->close();
}
$conn->close();
?>