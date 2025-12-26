<?php

require_once '../skypos/db_connect.php';

// Initialize messages
$success_msg = $error_msg = '';

// ------------------- ADD CATEGORY -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);

    if (empty($name)) {
        $error_msg = 'Category name is required.';
    } elseif (strlen($name) < 2) {
        $error_msg = 'Category name must be at least 2 characters.';
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $success_msg = "Category '$name' added successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
            if ($conn->errno === 1062) {
                $error_msg = "Category '$name' already exists.";
            }
        }
        $stmt->close();
    }
}

// ------------------- EDIT CATEGORY -------------------
if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['cat_id'];
    $name = trim($_POST['category_name']);

    if (empty($name) || strlen($name) < 2) {
        $error_msg = 'Category name must be at least 2 characters.';
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $id);
        if ($stmt->execute()) {
            $success_msg = "Category updated successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
            if ($conn->errno === 1062) {
                $error_msg = "Category '$name' already exists.";
            }
        }
        $stmt->close();
    }
}

// ------------------- DELETE CATEGORY -------------------
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Prevent deleting if items use this category
    $check = $conn->prepare("SELECT COUNT(*) FROM hggitems WHERE category_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $count = $check->get_result()->fetch_row()[0];
    $check->close();

    if ($count > 0) {
        $error_msg = "Cannot delete: $count item(s) use this category.";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success_msg = "Category deleted successfully!";
        } else {
            $error_msg = "Error deleting category.";
        }
        $stmt->close();
    }
}

// ------------------- FETCH CATEGORIES -------------------
$categories = [];
$result = $conn->query("SELECT id, name, created_at FROM categories ORDER BY name ASC");
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Manage Categories</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        *{margin:0;padding:0;box-sizing:border-box;}
        :root{
            --primary:#6366f1;--primary-dark:#4f46e5;--secondary:#ec4899;--accent:#f59e0b;
            --bg-primary:#0f0f23;--bg-secondary:#1a1a2e;--glass:rgba(255,255,255,0.05);
            --glass-border:rgba(255,255,255,0.1);--text-primary:#fff;--text-secondary:#a1a1aa;
            --navbar-bg:#1e40af;--success:#22c55e;--danger:#ef4444;
        }
        body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;}
        .bg-animation{position:fixed;top:0;left:0;width:100%;height:100%;z-index:-2;
            background:linear-gradient(45deg,#0f0f23,#1a1a2e,#16213e);}
        .bg-animation::before{content:'';position:absolute;width:200%;height:200%;
            background:radial-gradient(circle at 20% 20%,rgba(99,102,241,.3)0%,transparent 50%),
                       radial-gradient(circle at 80% 80%,rgba(236,72,153,.3)0%,transparent 50%),
                       radial-gradient(circle at 40% 60%,rgba(245,158,11,.2)0%,transparent 50%);
            animation:float 20s ease-in-out infinite;}
        @keyframes float{0%,100%{transform:translate(-50%,-50%) rotate(0deg) scale(1);}
            33%{transform:translate(-45%,-55%) rotate(120deg) scale(1.1);}
            66%{transform:translate(-55%,-45%) rotate(240deg) scale(.9);}}

        .navbar{background:var(--navbar-bg);padding:16px 32px;display:flex;justify-content:space-between;
                align-items:center;position:fixed;top:0;left:0;right:0;z-index:1000;
                box-shadow:0 4px 6px -1px rgba(0,0,0,.1);}
        .navbar-brand{color:#fff;font-size:1.5rem;font-weight:700;text-decoration:none;
            background:linear-gradient(45deg,#fff,#a855f7);-webkit-background-clip:text;
            -webkit-text-fill-color:transparent;}
        .navbar-user{display:flex;align-items:center;gap:8px;font-weight:500;}
        .user-icon{width:24px;height:24px;fill:currentColor;}

        .sidebar{position:fixed;left:0;top:70px;bottom:0;width:260px;
                 background:rgba(26,26,46,.7);backdrop-filter:blur(20px);
                 border-right:1px solid var(--glass-border);padding:24px 16px;z-index:900;}
        .nav-link{display:flex;align-items:center;gap:12px;padding:12px 16px;
                  color:var(--text-secondary);text-decoration:none;border-radius:10px;
                  margin-bottom:8px;transition:all .3s;font-weight:500;}
        .nav-link:hover,.nav-link.active{background:var(--glass);color:var(--text-primary);
                  box-shadow:0 4px 12px rgba(99,102,241,.2);}
        .nav-link svg{width:20px;height:20px;}

        .main-content{margin-left:260px;margin-top:70px;padding:32px;transition:margin-left .3s;}
        .page-header{margin-bottom:32px;}
        .page-title{font-size:2.2rem;font-weight:700;
            background:linear-gradient(45deg,#fff,#c4b5fd);-webkit-background-clip:text;
            -webkit-text-fill-color:transparent;animation:shine 2s ease-in-out infinite;}
        @keyframes shine{0%,100%{filter:brightness(1);}50%{filter:brightness(1.3);}}

        .panel{background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--glass-border);
               border-radius:16px;padding:24px;margin-bottom:24px;}
        .form-group{margin-bottom:16px;}
        .form-label{display:block;margin-bottom:8px;font-weight:500;color:var(--text-primary);}
        .form-input{width:100%;padding:14px 18px;background:var(--glass);border:1px solid var(--glass-border);
                    border-radius:10px;color:var(--text-primary);font-size:.95rem;transition:all .3s;}
        .form-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(99,102,241,.1);}
        .btn{padding:12px 20px;border:none;border-radius:10px;font-weight:600;cursor:pointer;
             transition:all .3s;}
        .btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(99,102,241,.3);}
        .btn-danger{background:#ef4444;color:#fff;}
        .btn-danger:hover{background:#dc2626;}
        .btn-sm{font-size:.8rem;padding:6px 12px;}

        .table{width:100%;border-collapse:collapse;margin-top:16px;}
        .table th,.table td{padding:12px 16px;text-align:left;border-bottom:1px solid var(--glass-border);}
        .table th{background:var(--glass);font-weight:600;}
        .table tr:hover{background:rgba(255,255,255,.05);}
        .actions{display:flex;gap:8px;}
        .badge{padding:4px 10px;border-radius:20px;font-size:.7rem;font-weight:600;}
        .badge-success{background:rgba(34,197,94,.2);color:#86efac;}
        .badge-danger{background:rgba(239,68,68,.2);color:#fca5a5;}

        .message{padding:16px 20px;border-radius:12px;margin-bottom:24px;font-weight:500;
                 backdrop-filter:blur(10px);border:1px solid;animation:slide .5s ease-out;text-align:center;}
        @keyframes slide{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
        .success-message{background:rgba(34,197,94,.1);border-color:rgba(34,197,94,.3);color:#86efac;}
        .error-message{background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.3);color:#fca5a5;}

        @media (max-width:992px){
            .sidebar{transform:translateX(-100%);}
            .main-content{margin-left:0;}
            .mobile-toggle{display:block;}
            .sidebar.open{transform:translateX(0);}
        }
        .mobile-toggle{display:none;background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer;}
    </style>
</head>
<body>
    <div class="bg-animation"></div>

    <!-- Navbar -->
    <nav class="navbar">
        <button class="mobile-toggle" id="sidebarToggle">Menu</button>
        <a href="admin_panel.php" class="navbar-brand">Guardia POS</a>
        <div class="navbar-user">
            <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>Admin: <?php echo $username; ?></span>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <nav>
            <a href="admin_panel.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                Dashboard
            </a>
            <a href="add_item.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                Add Item
            </a>
            <a href="edit_item.php" class="nav-link">
                <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 0 0 0-1.41l-2.34-2.34a.996.996 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                Edit Items
            </a>
            <a href="categories.php" class="nav-link active">
                <svg viewBox="0 0 24 24"><path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 6h-6c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1z"/></svg>
                Categories
            </a>
            <a href="logout.php" class="nav-link" style="margin-top:auto;color:#fca5a5;">
                <svg viewBox="0 0 24 24"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5a2 2 0 0 0-2 2v4h2V5h14v14H5v-4H3v4a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
                Logout
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-header">
            <h1 class="page-title">Manage Categories</h1>
            <p style="color:var(--text-secondary);">Add, edit, or remove item categories.</p>
        </div>

        <?php if ($success_msg): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Add Category Form -->
        <div class="panel">
            <h3 style="margin-bottom:16px;color:var(--text-primary);">Add New Category</h3>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-input" placeholder="e.g., Beverages" required minlength="2">
                </div>
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="panel">
            <h3 style="margin-bottom:16px;color:var(--text-primary);">All Categories (<?php echo count($categories); ?>)</h3>
            <?php if (!empty($categories)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                                        <input type="text" name="category_name" value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                               style="background:transparent;border:none;color:inherit;font:inherit;width:120px;" required>
                                        <button type="submit" name="edit_category" class="btn btn-sm btn-primary">Save</button>
                                    </form>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($cat['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="?delete=<?php echo $cat['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Delete category \"<?php echo htmlspecialchars($cat['name']); \")\"?');">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:var(--text-secondary);text-align:center;padding:20px;">No categories found. Add one above!</p>
            <?php endif; ?>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('sidebarToggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>