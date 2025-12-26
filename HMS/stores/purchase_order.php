<?php
include 'config.php';

function generatePONumber() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT po_number FROM purchase_orders ORDER BY id DESC LIMIT 1");
        $lastPO = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastPO) {
            $parts = explode('-', $lastPO['po_number']);
            $lastNumber = (int)end($parts);
            $nextNumber = max($lastNumber + 1, 1500);
        } else {
            $nextNumber = 1500;
        }
        return 'PO-' . $nextNumber;
    } catch(PDOException $e) {
        return 'PO-1500';
    }
}

// Fetch data
$suppliers = [];
$responsibilities = [];
try {
    $stmt = $conn->query("SELECT id, name, contact_number FROM suppliers ORDER BY name");
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT id, name FROM responsibilities ORDER BY name");
    $responsibilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Handle form submission
if(isset($_POST['create_po'])) {
    $po_number = $_POST['po_number'];
    $supplier_id = $_POST['supplier_id'];
    $received_by = $_SESSION['username'] ?? 'Unknown';
    $confirmed_by = $_POST['confirmed_by'];
    $password = $_POST['password'];
    
    try {
        // Verify password
        $stmt = $conn->prepare("SELECT * FROM responsibilities WHERE id = :id");
        $stmt->bindParam(':id', $confirmed_by);
        $stmt->execute();
        $responsible = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$responsible || !password_verify($password, $responsible['password'])) {
            throw new Exception("Invalid password for confirmation");
        }
        
        $conn->beginTransaction();
        
        // Insert PO header
        $stmt = $conn->prepare("INSERT INTO purchase_orders 
                              (po_number, supplier_id, received_by, confirmed_by) 
                              VALUES (:po_number, :supplier_id, :received_by, :confirmed_by)");
        $stmt->execute([
            ':po_number' => $po_number,
            ':supplier_id' => $supplier_id,
            ':received_by' => $received_by,
            ':confirmed_by' => $confirmed_by
        ]);
        
        $po_id = $conn->lastInsertId();
        
        // Insert PO items
        $stmt = $conn->prepare("INSERT INTO po_items 
                              (po_id, item_id, quantity, mass_unit) 
                              VALUES (:po_id, :item_id, :quantity, :mass_unit)");
        
        foreach($_POST['item_id'] as $index => $item_id) {
            if(empty($item_id)) continue;
            
            // Validate item_id
            $item_stmt = $conn->prepare("SELECT id, item_name FROM inventory WHERE id = :item_id");
            $item_stmt->bindParam(':item_id', $item_id);
            $item_stmt->execute();
            $item = $item_stmt->fetch(PDO::FETCH_ASSOC);
            if(!$item) {
                throw new Exception("Invalid item ID: $item_id");
            }
            
            // Validate mass_unit
            $valid_units = ['kg', 'g', 'unit', 'liter', 'liter Can', 'Tin', 'Bottle', 'Packet'];
            $mass_unit = $_POST['mass_unit'][$index];
            if (!in_array($mass_unit, $valid_units)) {
                throw new Exception("Invalid unit for item ID $item_id: $mass_unit");
            }
            
            $stmt->execute([
                ':po_id' => $po_id,
                ':item_id' => $item_id,
                ':quantity' => $_POST['quantity'][$index],
                ':mass_unit' => $mass_unit
            ]);
        }
        
        $conn->commit();
        
        $_SESSION['print_po'] = $po_id;
        $_SESSION['message'] = "Purchase Order created successfully!";
        header("Location: print_po.php");
        exit();
    } catch(Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: purchase_order.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Purchase Order</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .message { padding: 10px; margin: 15px 0; border-radius: 4px; }
        .success { background: #dff0d8; color: #3c763d; }
        .error { background: #f2dede; color: #a94442; }
        .search-box { position: relative; }
        .search-results { position: absolute; width: 100%; max-height: 200px; overflow-y: auto; 
                         border: 1px solid #ddd; background: white; z-index: 1000; display: none; }
        .search-results div { padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; }
        .search-results div:hover { background: #f5f5f5; }
        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }
        .item-row { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .remove-item { color: red; cursor: pointer; float: right; }
        #items-container { margin-bottom: 20px; }
        .add-item-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <button onclick="window.location.href='../stores.php'" style="background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
    Back
</button>

    <div class="container">
        <h1>Create Purchase Order</h1>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <form method="post" id="poForm">
            <div class="row">
                <div class="form-group">
                    <label for="po_number">PO Number:</label>
                    <input type="text" id="po_number" name="po_number" value="<?= generatePONumber() ?>" readonly>
                </div>
                <div class="form-group">
                    <label>PO Date:</label>
                    <input type="text" value="<?= date('Y-m-d H:i:s') ?>" readonly>
                </div>
            </div>
            
            <div class="form-group search-box">
                <label for="supplier_search">Search Supplier:</label>
                <input type="text" id="supplier_search" placeholder="Type supplier name...">
                <div class="search-results" id="supplier_results"></div>
                <input type="hidden" id="supplier_id" name="supplier_id" required>
            </div>
            
            <div id="items-container">
                <div class="item-row" data-index="0">
                    <span class="remove-item" onclick="removeItem(this)">✕ Remove</span>
                    <div class="row">
                        <div class="form-group search-box">
                            <label for="item_search_0">Search Item:</label>
                            <input type="text" id="item_search_0" class="item-search" placeholder="Type item name..." required>
                            <div class="search-results" id="item_results_0"></div>
                            <input type="hidden" name="item_id[]" id="item_id_0" required>
                        </div>
                        <div class="form-group">
                            <label for="mass_unit_0">Unit:</label>
                            <select name="mass_unit[]" id="mass_unit_0" required>
                                <option value="unit">Unit</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="liter">Liter</option>
                                <option value="liter Can">Liter Can</option>
                                <option value="Tin">Tin</option>
                                <option value="Bottle">Bottle</option>
                                <option value="Packet">Packet</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label>Quantity:</label>
                            <input type="number" name="quantity[]" step="0.01" min="0.01" value="1">
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="button" class="add-item-btn" onclick="addItem()">+ Add Another Item</button>
            
            <div class="form-group">
                <label for="received_by">Received By:</label>
                <input type="text" id="received_by" name="received_by" value="<?= $_SESSION['username'] ?? 'Unknown' ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="confirmed_by">Confirmed By:</label>
                <select id="confirmed_by" name="confirmed_by" required>
                    <option value="">Select responsible person</option>
                    <?php foreach($responsibilities as $resp): ?>
                        <option value="<?= $resp['id'] ?>"><?= htmlspecialchars($resp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Confirm Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="create_po">Create Purchase Order</button>
        </form>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    let itemCount = 1;
    
    function addItem() {
        const newItem = `
        <div class="item-row" data-index="${itemCount}">
            <span class="remove-item" onclick="removeItem(this)">✕ Remove</span>
            <div class="row">
                <div class="form-group search-box">
                    <label for="item_search_${itemCount}">Search Item:</label>
                    <input type="text" id="item_search_${itemCount}" class="item-search" placeholder="Type item name..." required>
                    <div class="search-results" id="item_results_${itemCount}"></div>
                    <input type="hidden" name="item_id[]" id="item_id_${itemCount}" required>
                </div>
                <div class="form-group">
                    <label for="mass_unit_${itemCount}">Unit:</label>
                    <select name="mass_unit[]" id="mass_unit_${itemCount}" required>
                        <option value="unit">Unit</option>
                        <option value="kg">Kilogram (kg)</option>
                        <option value="g">Gram (g)</option>
                        <option value="liter">Liter</option>
                        <option value="liter Can">Liter Can</option>
                        <option value="Tin">Tin</option>
                        <option value="Bottle">Bottle</option>
                        <option value="Packet">Packet</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Quantity:</label>
                    <input type="number" name="quantity[]" step="0.01" min="0.01" value="1">
                </div>
            </div>
        </div>`;
        
        $('#items-container').append(newItem);
        itemCount++;
        setupItemEvents();
    }
    
    function removeItem(element) {
        if($('.item-row').length > 1) {
            $(element).closest('.item-row').remove();
        } else {
            alert("You must have at least one item in the purchase order.");
        }
    }
    
    function setupItemEvents() {
        $('.item-row').each(function() {
            const $row = $(this);
            const index = $row.data('index');
            const $qty = $row.find('input[name="quantity[]"]');
            const $unit = $row.find(`select[name="mass_unit[]"]`);
            const $searchInput = $row.find(`#item_search_${index}`);
            const $results = $row.find(`#item_results_${index}`);

            // Adjust quantity input based on unit
            $unit.change(function() {
                const unit = $(this).val();
                if(unit === 'unit' || unit === 'Tin' || unit === 'Bottle' || unit === 'Packet') {
                    $qty.val('1').attr('step', '1').attr('min', '1');
                } else {
                    $qty.attr('step', '0.01').attr('min', '0.01');
                }
            });

            // Item search
            $searchInput.on('input', function() {
                const query = $(this).val();
                if(query.length > 2) {
                    $.get('search_item.php', {query: query}, function(data) {
                        $results.empty();
                        if(data.length > 0) {
                            data.forEach(function(item) {
                                $results.append(
                                    `<div data-id="${item.id}" data-unit="${item.unit}">
                                        ${item.item_name}
                                    </div>`
                                );
                            });
                            $results.show();
                        } else {
                            $results.hide();
                        }
                    }, 'json');
                } else {
                    $results.empty().hide();
                }
            });

            // Item selection
            $row.on('click', `#item_results_${index} div`, function() {
                const id = $(this).data('id');
                const unit = $(this).data('unit');
                const text = $(this).text().trim();
                $row.find(`#item_search_${index}`).val(text).data('selected', true);
                $row.find(`#item_id_${index}`).val(id);
                $row.find(`#mass_unit_${index}`).val(unit);
                $results.empty().hide();

                // Adjust quantity input based on selected unit
                if(unit === 'unit' || unit === 'Tin' || unit === 'Bottle' || unit === 'Packet') {
                    $qty.val('1').attr('step', '1').attr('min', '1');
                } else {
                    $qty.val('1').attr('step', '0.01').attr('min', '0.01');
                }
            });

            // Prevent suggestions from reappearing unless new input
            $searchInput.on('focus keydown', function() {
                if($(this).data('selected') && $(this).val().length > 0) {
                    $results.empty().hide();
                }
            });
        });
    }
    
    $(document).ready(function() {
        // Supplier search
        $('#supplier_search').on('input', function() {
            const query = $(this).val();
            if(query.length > 2) {
                $.get('search_supplier.php', {query: query}, function(data) {
                    const results = $('#supplier_results');
                    results.empty();
                    if(data.length > 0) {
                        data.forEach(function(supplier) {
                            results.append(
                                `<div data-id="${supplier.id}">
                                    ${supplier.name} - ${supplier.contact_number}
                                </div>`
                            );
                        });
                        results.show();
                    } else {
                        results.hide();
                    }
                }, 'json');
            } else {
                $('#supplier_results').hide();
            }
        });1
        
        $(document).on('click', '#supplier_results div', function() {
            const id = $(this).data('id');
            const text = $(this).text().trim();
            $('#supplier_id').val(id);
            $('#supplier_search').val(text);
            $('#supplier_results').hide();
        });
        
        setupItemEvents();
    });
    </script>
</body>
</html>