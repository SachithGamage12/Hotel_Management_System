<?php




$username = htmlspecialchars($_SESSION['username']);
require_once '../skypos/db_connect.php';

// Initialize form variables with defaults
$success_message = '';
$error_message = '';
$item_code = '';
$item_name = '';
$category_id = 0;
$type = '';
$price = 0;
$discount_price = null;
$discount_start_date = null;
$discount_end_date = null;
$status = 'active';

// Fetch the last item code and generate the next one
$next_item_code = '';
$last_code_query = $conn->query("SELECT item_code FROM hggitems ORDER BY id DESC LIMIT 1");
if ($last_code_query && $last_code_query->num_rows > 0) {
    $last_code = $last_code_query->fetch_assoc()['item_code'];
    // Extract numeric part (e.g., '007' from 'ITEM007')
    if (preg_match('/ITEM(\d+)/', $last_code, $matches)) {
        $last_number = intval($matches[1]);
        $next_number = $last_number + 1;
        $next_item_code = 'ITEM' . str_pad($next_number, 3, '0', STR_PAD_LEFT); // e.g., ITEM008
    } else {
        $next_item_code = 'ITEM001'; // Fallback if format is unexpected
    }
} else {
    $next_item_code = 'ITEM001'; // Start with ITEM001 if no records exist
}
$item_code = $next_item_code; // Set the default item code to the generated one

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_code = trim($_POST['item_code'] ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $type = trim($_POST['type'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $discount_start_date = !empty($_POST['discount_start_date']) ? $_POST['discount_start_date'] : null;
    $discount_end_date = !empty($_POST['discount_end_date']) ? $_POST['discount_end_date'] : null;
    $status = isset($_POST['status']) ? 'active' : 'disabled';

    // Advanced validation with sanitization
    $errors = [];
    if (empty($item_code) || !preg_match('/^ITEM\d{3}$/', $item_code)) {
        $errors[] = 'Item code must follow the format ITEMXXX (e.g., ITEM008).';
    }
    if (empty($item_name) || strlen($item_name) < 2) {
        $errors[] = 'Item name is required and must be at least 2 characters long.';
    }
    if ($category_id <= 0) {
        $errors[] = 'Please select a valid category.';
    }
    if (!in_array($type, ['KOT', 'BOT'])) {
        $errors[] = 'Please select a valid item type (KOT or BOT).';
    }
    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }

    if (!empty($discount_price)) {
        if ($discount_price <= 0 || $discount_price >= $price) {
            $errors[] = 'Discount price must be greater than 0 and less than regular price.';
        }
        if (!empty($discount_start_date) && !empty($discount_end_date)) {
            if (strtotime($discount_start_date) > strtotime($discount_end_date)) {
                $errors[] = 'Start date must be before end date.';
            }
            $max_end_date = date('Y-m-d', strtotime('+1 year'));
            if (strtotime($discount_end_date) > strtotime($max_end_date)) {
                $errors[] = 'Discount end date cannot exceed 1 year from today.';
            }
        } else {
            $errors[] = 'Both start and end dates are required when discount price is set.';
        }
    }

    // Check for duplicate item code
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT id FROM hggitems WHERE item_code = ?");
        $check_stmt->bind_param("s", $item_code);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = 'Item code already exists. Please use a unique code.';
        }
        $check_stmt->close();
    }

    if (empty($errors)) {
        // Insert item with prepared statement
        $stmt = $conn->prepare("
            INSERT INTO hggitems (item_code, item_name, category_id, type, price, discount_price, discount_start_date, discount_end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssisddsss", $item_code, $item_name, $category_id, $type, $price, $discount_price, $discount_start_date, $discount_end_date, $status);

        if ($stmt->execute()) {
            $success_message = 'Item added successfully! You can now add another item or return to dashboard.';
            // Generate the next item code for the form reset
            $last_number = intval(substr($item_code, 4)) + 1;
            $item_code = 'ITEM' . str_pad($last_number, 3, '0', STR_PAD_LEFT);
            $item_name = $type = '';
            $category_id = 0;
            $price = $discount_price = 0;
            $discount_start_date = $discount_end_date = null;
            $status = 'active';
        } else {
            $error_message = 'Error adding item: ' . $conn->error . '. Please try again.';
        }
        $stmt->close();
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Fetch categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result) {
    $categories = $cat_result->fetch_all(MYSQLI_ASSOC);
} else {
    $error_message .= '<br>Unable to fetch categories. Please ensure categories table is populated.';
}

// Close connection after use
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Add Item</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #f59e0b;
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
            --navbar-bg: #1e40af;
            --purple: #a855f7;
            --purple-dark: #7e22ce;
            --lkr-symbol: 'LKR';
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            overflow-x: hidden;
            position: relative;
            color: var(--text-primary);
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(45deg, #0f0f23, #1a1a2e, #16213e);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(245, 158, 11, 0.2) 0%, transparent 50%);
            animation: floatingBg 20s ease-in-out infinite;
        }

        @keyframes floatingBg {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
            33% { transform: translate(-45%, -55%) rotate(120deg) scale(1.1); }
            66% { transform: translate(-55%, -45%) rotate(240deg) scale(0.9); }
        }

        /* Navbar */
        .navbar {
            background: var(--navbar-bg);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-brand {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .navbar-brand:hover {
            color: var(--secondary);
        }

        .navbar-user {
            color: var(--text-primary);
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .user-icon {
            width: 24px;
            height: 24px;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        /* Main container with glass effect */
        .panel-container {
            margin-top: 80px;
            padding: 40px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .panel-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .panel-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #ffffff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textShine 2s ease-in-out infinite;
        }

        @keyframes textShine {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.2); }
        }

        /* Messages */
        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid;
            animation: messageSlide 0.5s ease-out;
            text-align: center;
            transition: all 0.3s ease;
        }

        @keyframes messageSlide {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        /* Form layout */
        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }
        .form-group:nth-child(7) { animation-delay: 0.7s; }
        .form-group:nth-child(8) { animation-delay: 0.8s; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 14px 18px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .form-select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23a1a1aa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        .form-select option {
            background: var(--bg-secondary);
            color: var(--text-primary);
            padding: 8px;
        }

        /* Date inputs group */
        .date-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Status toggle */
        .status-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 26px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* Submit button */
        .submit-btn {
            grid-column: span 2;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        /* Loading state */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .navbar { padding: 12px 16px; }
            .panel-container { 
                padding: 24px; 
                margin: 16px; 
                margin-top: 60px; 
                border-radius: 16px; 
            }
            .panel-title { font-size: 2rem; }
            .form-container { 
                grid-template-columns: 1fr; 
                gap: 16px; 
            }
            .submit-btn { grid-column: span 1; }
            .form-input, .form-select { padding: 12px 16px; font-size: 0.9rem; }
            .date-group { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .panel-container { padding: 16px; margin: 12px; }
            .form-group { margin-bottom: 16px; }
            .panel-title { font-size: 1.75rem; }
        }
    </style>
</head>
<body>
    <!-- Animated background -->
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

    <!-- Panel Content -->
    <div class="panel-container">
        <div class="panel-header">
            <h1 class="panel-title">Add New Item</h1>
            <p style="color: var(--text-secondary); margin-top: 8px; font-size: 1rem;">Enter item details below. Prices are in Sri Lankan Rupees (LKR).</p>
        </div>

        <?php if ($success_message): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" id="addItemForm">
            <div class="form-container">
                <!-- Left Column -->
                <div class="form-column">
                   <div class="form-group">
    <label for="item_code" class="form-label">Item Code</label>
    <input type="text" id="item_code" name="item_code" class="form-input" 
           placeholder="e.g., ITEM008" value="<?php echo htmlspecialchars($item_code); ?>" 
           pattern="ITEM[0-9]{3}" title="Item code must be in the format ITEMXXX (e.g., ITEM008)" required>
    <small style="color: var(--text-secondary); display: block; margin-top: 4px;">
        Suggested code: <?php echo htmlspecialchars($next_item_code); ?>. You can edit if needed.
    </small>
</div>

                    <div class="form-group">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" id="item_name" name="item_name" class="form-input" placeholder="Enter item name" value="<?php echo htmlspecialchars($item_name ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label">Item Category</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type" class="form-label">Item Type</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="">Select KOT or BOT</option>
                            <option value="KOT" <?php echo ($type == 'KOT') ? 'selected' : ''; ?>>KOT (Kitchen Order Ticket)</option>
                            <option value="BOT" <?php echo ($type == 'BOT') ? 'selected' : ''; ?>>BOT (Bar Order Ticket)</option>
                        </select>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <div class="form-group">
                        <label for="price" class="form-label">Price (LKR)</label>
                        <input type="number" id="price" name="price" class="form-input" placeholder="0.00" step="0.01" min="0.01" value="<?php echo ($price > 0) ? $price : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="discount_price" class="form-label">Discount Price (LKR)</label>
                        <input type="number" id="discount_price" name="discount_price" class="form-input" placeholder="0.00" step="0.01" min="0.01" value="<?php echo ($discount_price > 0) ? $discount_price : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Discount Validity Dates</label>
                        <div class="date-group">
                            <input type="date" id="discount_start_date" name="discount_start_date" class="form-input" value="<?php echo $discount_start_date ?? ''; ?>">
                            <input type="date" id="discount_end_date" name="discount_end_date" class="form-input" value="<?php echo $discount_end_date ?? ''; ?>">
                        </div>
                        <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Leave empty if no discount applies.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="status-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" id="status" name="status" <?php echo ($status === 'active') ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <span id="status-text"><?php echo ($status === 'active') ? 'Active' : 'Disabled'; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn" id="submitBtn">Save Item</button>
            </div>
        </form>
    </div>

    <script>
        // Advanced form validation and real-time feedback
        const form = document.getElementById('addItemForm');
        const submitBtn = document.getElementById('submitBtn');
        const statusCheckbox = document.getElementById('status');
        const statusText = document.getElementById('status-text');
        const discountPriceInput = document.getElementById('discount_price');
        const priceInput = document.getElementById('price');
        const startDateInput = document.getElementById('discount_start_date');
        const endDateInput = document.getElementById('discount_end_date');
        const typeSelect = document.getElementById('type');

        // Status toggle text update
        statusCheckbox.addEventListener('change', function() {
            statusText.textContent = this.checked ? 'Active' : 'Disabled';
        });

        // Real-time discount validation
        function validateDiscount() {
            const discountPrice = parseFloat(discountPriceInput.value);
            const price = parseFloat(priceInput.value);
            const dateGroup = document.querySelector('.date-group');
            const submitBtn = document.getElementById('submitBtn');

            if (discountPrice > 0) {
                if (discountPrice >= price) {
                    discountPriceInput.style.borderColor = 'rgba(239, 68, 68, 0.5)';
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Invalid Discount';
                } else {
                    discountPriceInput.style.borderColor = 'var(--primary)';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Item';
                }
                // Enable dates
                startDateInput.required = true;
                endDateInput.required = true;
                dateGroup.style.opacity = '1';
                dateGroup.style.display = 'grid';
            } else {
                discountPriceInput.style.borderColor = 'var(--glass-border)';
                startDateInput.required = false;
                endDateInput.required = false;
                dateGroup.style.opacity = '0.5';
                dateGroup.style.display = 'grid';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Item';
            }
        }

        discountPriceInput.addEventListener('input', validateDiscount);
        priceInput.addEventListener('input', validateDiscount);

        // Date validation on change
        [startDateInput, endDateInput].forEach(input => {
            input.addEventListener('change', function() {
                if (startDateInput.value && endDateInput.value) {
                    if (new Date(startDateInput.value) > new Date(endDateInput.value)) {
                        alert('Start date must be before end date.');
                        this.value = '';
                    }
                }
            });
        });

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            if (!typeSelect.value) {
                e.preventDefault();
                alert('Please select KOT or BOT.');
                typeSelect.focus();
                return;
            }

            const discountPrice = parseFloat(discountPriceInput.value);
            const price = parseFloat(priceInput.value);
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (discountPrice > 0 && (discountPrice >= price)) {
                e.preventDefault();
                alert('Discount price must be less than regular price.');
                return;
            }

            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('Start date must be before end date.');
                return;
            }

            // Add loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            document.body.classList.add('loading');
        });

        // Preserve form values on page load if errors
        window.addEventListener('load', validateDiscount);
    </script>
</body>
</html>