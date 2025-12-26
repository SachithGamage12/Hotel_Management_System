<?php
session_start();

// ---------------------------------------------------------------------
// 1. SECURITY – must be logged in
// ---------------------------------------------------------------------
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
$username = htmlspecialchars($_SESSION['username']);

require_once '../hggpos/db_connect.php';

// ---------------------------------------------------------------------
// 2. INITIALISE variables
// ---------------------------------------------------------------------
$search_query   = trim($_GET['q'] ?? '');
$found_items    = [];
$item           = null;               // the item that will be edited
$editing        = false;
$success_msg    = '';
$error_msg      = '';

// ---------------------------------------------------------------------
// 3. SEARCH – when a query is supplied
// ---------------------------------------------------------------------
if ($search_query !== '') {
    $like = "%$search_query%";
    $stmt = $conn->prepare("
        SELECT i.*, c.name AS category_name
        FROM hggitems i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.item_code LIKE ? OR i.item_name LIKE ?
        ORDER BY i.item_code
        LIMIT 20
    ");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $found_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ---------------------------------------------------------------------
// 4. EDIT MODE – when an item id is selected
// ---------------------------------------------------------------------
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $editing = true;
    $edit_id = (int)$_GET['edit'];

    $stmt = $conn->prepare("
        SELECT i.*, c.name AS category_name
        FROM hggitems i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.id = ?
    ");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$item) {
        $error_msg = 'Item not found.';
        $editing = false;
    }
}

// ---------------------------------------------------------------------
// 5. UPDATE – POST handling
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $editing) {
    $item_id            = (int)$_POST['item_id'];
    $item_code          = trim($_POST['item_code'] ?? '');
    $item_name          = trim($_POST['item_name'] ?? '');
    $category_id        = (int)($_POST['category_id'] ?? 0);
    $type               = trim($_POST['type'] ?? '');
    $price              = floatval($_POST['price'] ?? 0);
    $discount_price     = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $discount_start_date= !empty($_POST['discount_start_date']) ? $_POST['discount_start_date'] : null;
    $discount_end_date  = !empty($_POST['discount_end_date']) ? $_POST['discount_end_date'] : null;
    $status             = isset($_POST['status']) ? 'active' : 'disabled';

    // ------------------- validation -------------------
    $errors = [];

    if (!preg_match('/^ITEM\d{3}$/', $item_code)) {
        $errors[] = 'Item code must be ITEMXXX (e.g., ITEM008).';
    }
    if (strlen($item_name) < 2) {
        $errors[] = 'Item name must be at least 2 characters.';
    }
    if ($category_id <= 0) {
        $errors[] = 'Please select a category.';
    }
    if (!in_array($type, ['KOT', 'BOT'])) {
        $errors[] = 'Invalid type (KOT / BOT).';
    }
    if ($price <= 0) {
        $errors[] = 'Price must be > 0.';
    }

    if ($discount_price !== null) {
        if ($discount_price <= 0 || $discount_price >= $price) {
            $errors[] = 'Discount price must be >0 and < regular price.';
        }
        if ($discount_start_date && $discount_end_date) {
            if (strtotime($discount_start_date) > strtotime($discount_end_date)) {
                $errors[] = 'Start date must be before end date.';
            }
            $max = date('Y-m-d', strtotime('+1 year'));
            if (strtotime($discount_end_date) > strtotime($max)) {
                $errors[] = 'Discount end date cannot be more than 1 year ahead.';
            }
        } else {
            $errors[] = 'Both discount dates are required when a discount price is set.';
        }
    }

    // ---- check duplicate code (except for the current item) ----
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM hggitems WHERE item_code = ? AND id <> ?");
        $stmt->bind_param('si', $item_code, $item_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Item code already used by another item.';
        }
        $stmt->close();
    }

    // ------------------- SAVE -------------------
    if (empty($errors)) {
        $stmt = $conn->prepare("
            UPDATE hggitems SET
                item_code = ?, item_name = ?, category_id = ?, type = ?,
                price = ?, discount_price = ?, discount_start_date = ?,
                discount_end_date = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            'ssisddsssi',
            $item_code, $item_name, $category_id, $type,
            $price, $discount_price, $discount_start_date,
            $discount_end_date, $status, $item_id
        );

        if ($stmt->execute()) {
            $success_msg = 'Item updated successfully!';
            // refresh the $item array so the form shows the fresh data
            $stmt2 = $conn->prepare("SELECT i.*, c.name AS category_name FROM hggitems i LEFT JOIN categories c ON i.category_id = c.id WHERE i.id = ?");
            $stmt2->bind_param('i', $item_id);
            $stmt2->execute();
            $item = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $error_msg = 'Database error: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $error_msg = implode('<br>', $errors);
    }
}

// ---------------------------------------------------------------------
// 6. FETCH CATEGORIES (for dropdown)
// ---------------------------------------------------------------------
$categories = [];
$cat_res = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    $categories = $cat_res->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS – Search & Edit Item</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        /* ---- SAME CSS as add_item.php (copy-paste) ---- */
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --primary:#6366f1;--primary-dark:#4f46e5;--secondary:#ec4899;--accent:#f59e0b;
            --bg-primary:#0f0f23;--bg-secondary:#1a1a2e;--glass:rgba(255,255,255,0.05);
            --glass-border:rgba(255,255,255,0.1);--text-primary:#fff;--text-secondary:#a1a1aa;
            --navbar-bg:#1e40af;
        }
        body{font-family:'Inter',sans-serif;min-height:100vh;background:var(--bg-primary);color:var(--text-primary);}
        .bg-animation{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-2;
            background:linear-gradient(45deg,#0f0f23,#1a1a2e,#16213e);}
        .bg-animation::before{content:'';position:absolute;width:200%;height:200%;
            background:radial-gradient(circle at 20% 20%,rgba(99,102,241,.3)0%,transparent 50%),
                       radial-gradient(circle at 80% 80%,rgba(236,72,153,.3)0%,transparent 50%),
                       radial-gradient(circle at 40% 60%,rgba(245,158,11,.2)0%,transparent 50%);
            animation:floatingBg 20s ease-in-out infinite;}
        @keyframes floatingBg{
            0%,100%{transform:translate(-50%,-50%) rotate(0deg) scale(1);}
            33%{transform:translate(-45%,-55%) rotate(120deg) scale(1.1);}
            66%{transform:translate(-55%,-45%) rotate(240deg) scale(.9);}
        }
        .navbar{background:var(--navbar-bg);padding:16px 32px;display:flex;justify-content:space-between;
                align-items:center;position:fixed;top:0;left:0;right:0;z-index:1000;box-shadow:0 4px 6px -1px rgba(0,0,0,.1);}
        .navbar-brand{color:var(--text-primary);font-size:1.5rem;font-weight:700;text-decoration:none;}
        .navbar-brand:hover{color:var(--secondary);}
        .navbar-user{color:var(--text-primary);display:flex;align-items:center;gap:8px;font-weight:500;}
        .user-icon{width:24px;height:24px;}
        .panel-container{margin-top:80px;padding:40px;max-width:1000px;margin-left:auto;margin-right:auto;
                         background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--glass-border);
                         border-radius:24px;box-shadow:0 25px 50px -12px rgba(0,0,0,.5);}
        .panel-header{text-align:center;margin-bottom:40px;}
        .panel-title{font-size:2.5rem;font-weight:700;background:linear-gradient(45deg,#fff,#f0f9ff);
                     -webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:textShine 2s ease-in-out infinite;}
        @keyframes textShine{0%,100%{filter:brightness(1);}50%{filter:brightness(1.2);}}
        .message{padding:16px 20px;border-radius:12px;margin-bottom:24px;font-weight:500;
                 backdrop-filter:blur(10px);border:1px solid;animation:messageSlide .5s ease-out;text-align:center;}
        @keyframes messageSlide{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
        .error-message{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#fca5a5;}
        .success-message{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:#86efac;}
        .search-bar{margin-bottom:24px;display:flex;gap:12px;}
        .search-bar input{width:100%;max-width:400px;padding:14px 18px;background:var(--glass);border:1px solid var(--glass-border);
                          border-radius:10px;color:var(--text-primary);font-size:.95rem;}
        .search-bar button{padding:14px 24px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));
                           border:none;border-radius:10px;color:#fff;font-weight:600;cursor:pointer;}
        .results-table{width:100%;border-collapse:collapse;margin-top:16px;}
        .results-table th,.results-table td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--glass-border);}
        .results-table th{background:var(--glass);font-weight:600;}
        .results-table tr:hover{background:rgba(255,255,255,.05);}
        .btn-edit{display:inline-block;padding:6px 12px;background:var(--accent);color:#fff;border-radius:6px;text-decoration:none;font-size:.85rem;}
        .btn-edit:hover{background:#d97706;}
        .form-container{display:grid;grid-template-columns:1fr 1fr;gap:24px;}
        .form-group{margin-bottom:24px;position:relative;}
        .form-label{display:block;margin-bottom:8px;color:var(--text-primary);font-weight:500;font-size:.9rem;}
        .form-input,.form-select{width:100%;padding:14px 18px;background:var(--glass);border:1px solid var(--glass-border);
                                 border-radius:10px;color:var(--text-primary);font-size:.95rem;transition:all .3s cubic-bezier(.4,0,.2,1);}
        .form-input:focus,.form-select:focus{outline:none;border-color:var(--primary);background:rgba(255,255,255,.08);
                                            box-shadow:0 0 0 3px rgba(99,102,241,.1);}
        .form-select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
                     background-position:right 12px center;background-repeat:no-repeat;background-size:16px;padding-right:40px;}
        .date-group{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .status-toggle{display:flex;align-items:center;gap:12px;}
        .toggle-switch{position:relative;width:50px;height:26px;}
        .toggle-switch input{opacity:0;width:0;height:0;}
        .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#ccc;
                transition:.3s;border-radius:26px;}
        .slider:before{position:absolute;content:"";height:20px;width:20px;left:3px;bottom:3px;background:#fff;
                       transition:.3s;border-radius:50%;}
        input:checked + .slider{background:var(--primary);}
        input:checked + .slider:before{transform:translateX(24px);}
        .submit-btn{grid-column:span 2;padding:16px;background:linear-gradient(135deg,var(--primary),var(--primary-dark));
                    border:none;border-radius:10px;color:#fff;font-weight:600;cursor:pointer;letter-spacing:1px;text-transform:uppercase;}
        .submit-btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(99,102,241,.3);}
        @media(max-width:768px){
            .navbar{padding:12px 16px;}
            .panel-container{padding:24px;margin:16px;margin-top:60px;border-radius:16px;}
            .form-container{grid-template-columns:1fr;gap:16px;}
            .submit-btn{grid-column:span 1;}
            .date-group{grid-template-columns:1fr;}
        }
    </style>
</head>
<body>
<div class="bg-animation"></div>

<!-- Navbar -->
<nav class="navbar">
    <a href="dashboard.php" class="navbar-brand">Guardia POS</a>
    <div class="navbar-user">
        <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
        <span>User: <?php echo $username; ?></span>
    </div>
</nav>

<div class="panel-container">
    <div class="panel-header">
        <h1 class="panel-title">Search & Edit Item</h1>
        <p style="color:var(--text-secondary);margin-top:8px;">Type Item Code or Name to find an item, then click <strong>Edit</strong>.</p>
    </div>

    <?php if ($success_msg): ?>
        <div class="message success-message"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="message error-message"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- ====================== SEARCH FORM ====================== -->
    <form method="GET" class="search-bar">
        <input type="text" name="q" placeholder="Enter Item Code or Name…" value="<?php echo htmlspecialchars($search_query); ?>" autofocus>
        <button type="submit">Search</button>
    </form>

    <!-- ====================== SEARCH RESULTS ====================== -->
    <?php if ($search_query !== '' && !empty($found_items)): ?>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Price (LKR)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($found_items as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['item_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name'] ?? '—'); ?></td>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['status'] === 'active' ? 'Active' : 'Disabled'; ?></td>
                        <td><a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($search_query !== '' && empty($found_items)): ?>
        <p style="color:var(--text-secondary);text-align:center;">No items found for “<?php echo htmlspecialchars($search_query); ?>”.</p>
    <?php endif; ?>

    <!-- ====================== EDIT FORM ====================== -->
    <?php if ($editing && $item): ?>
        <hr style="border-color:var(--glass-border);margin:32px 0;">
        <form method="POST" id="editItemForm">
            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
            <div class="form-container">

                <!-- LEFT COLUMN -->
                <div class="form-column">
                    <div class="form-group">
                        <label class="form-label">Item Code</label>
                        <input type="text" name="item_code" class="form-input"
                               value="<?php echo htmlspecialchars($item['item_code']); ?>" required
                               pattern="ITEM[0-9]{3}" title="ITEMXXX (e.g., ITEM008)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="item_name" class="form-input"
                               value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($item['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="KOT" <?php echo ($item['type'] === 'KOT') ? 'selected' : ''; ?>>KOT</option>
                            <option value="BOT" <?php echo ($item['type'] === 'BOT') ? 'selected' : ''; ?>>BOT</option>
                        </select>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="form-column">
                    <div class="form-group">
                        <label class="form-label">Price (LKR)</label>
                        <input type="number" name="price" class="form-input" step="0.01" min="0.01"
                               value="<?php echo $item['price']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Discount Price (LKR)</label>
                        <input type="number" name="discount_price" class="form-input" step="0.01" min="0"
                               value="<?php echo $item['discount_price'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Discount Dates</label>
                        <div class="date-group">
                            <input type="date" name="discount_start_date" class="form-input"
                                   value="<?php echo $item['discount_start_date'] ?? ''; ?>">
                            <input type="date" name="discount_end_date" class="form-input"
                                   value="<?php echo $item['discount_end_date'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="status-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status" <?php echo ($item['status'] === 'active') ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span id="statusText"><?php echo ($item['status'] === 'active') ? 'Active' : 'Disabled'; ?></span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="saveBtn">Update Item</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    // Toggle status text
    const statusChk = document.querySelector('input[name="status"]');
    const statusTxt = document.getElementById('statusText');
    if (statusChk) {
        statusChk.addEventListener('change', () => {
            statusTxt.textContent = statusChk.checked ? 'Active' : 'Disabled';
        });
    }

    // Real-time discount validation (same as add_item)
    const discountInput = document.querySelector('input[name="discount_price"]');
    const priceInput    = document.querySelector('input[name="price"]');
    const startInput    = document.querySelector('input[name="discount_start_date"]');
    const endInput      = document.querySelector('input[name="discount_end_date"]');
    const saveBtn       = document.getElementById('saveBtn');

    function validateDiscount() {
        if (!discountInput || !priceInput) return;
        const disc = parseFloat(discountInput.value) || 0;
        const pri  = parseFloat(priceInput.value) || 0;

        if (disc > 0) {
            if (disc >= pri) {
                discountInput.style.borderColor = 'rgba(239,68,68,.5)';
                if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Invalid Discount'; }
            } else {
                discountInput.style.borderColor = 'var(--primary)';
                if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Update Item'; }
            }
            startInput.required = true;
            endInput.required   = true;
        } else {
            discountInput.style.borderColor = 'var(--glass-border)';
            startInput.required = false;
            endInput.required   = false;
            if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Update Item'; }
        }
    }
    if (discountInput) discountInput.addEventListener('input', validateDiscount);
    if (priceInput)    priceInput.addEventListener('input', validateDiscount);
    window.addEventListener('load', validateDiscount);
</script>
</body>
</html>