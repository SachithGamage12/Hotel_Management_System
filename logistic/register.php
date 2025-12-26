<?php
// =========================
// DB CONNECTION
// =========================
$host = "localhost"; 
$db   = "hotelgrandguardi_wedding_bliss";
$user = "hotelgrandguardi_root";
$pass = "Sun123flower@";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit("Database connection failed.");
}

session_start();

// Generate CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF check
    if (!isset($_POST["csrf"]) || !hash_equals($_SESSION["csrf"], $_POST["csrf"])) {
        $errors[] = "Invalid form token. Please refresh and try again.";
    } else {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $confirm  = $_POST["confirm_password"] ?? "";

        // Validation
        if ($username === "" || $password === "" || $confirm === "") {
            $errors[] = "All fields are required.";
        }
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = "Username must be between 3 and 50 characters.";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }

        if (!$errors) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO logestic_users (username, password) VALUES (:u, :p)");
                $stmt->execute([":u" => $username, ":p" => $hash]);

                $success = "User created successfully.";
                $_POST = []; // clear form
                $_SESSION['csrf'] = bin2hex(random_bytes(32)); // new token
            } catch (PDOException $e) {
                if ($e->getCode() === "23000") {
                    $errors[] = "Username already exists.";
                } else {
                    $errors[] = "Database error. Please try again.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create User</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root {
  --bg: #0b1220;
  --card: #111a2b;
  --muted: #93a4c1;
  --text: #eaf0ff;
  --accent: #4f8cff;
  --success-bg: #12351d;
  --success: #8ee0a1;
  --error-bg: #34161a;
  --error: #ff9cab;
  --border: #223154;
}
* { box-sizing: border-box; }
body {
  margin: 0; font-family: system-ui, sans-serif;
  background: radial-gradient(1200px 600px at 20% -10%, #1b2640 0%, var(--bg) 60%);
  color: var(--text); min-height: 100vh; display: grid; place-items: center; padding: 24px;
}
.container { width: 100%; max-width: 520px; }
h1 { font-size: 28px; margin: 0 0 18px; letter-spacing: 0.2px; }
.card {
  background: linear-gradient(180deg, var(--card), #0d1626 120%);
  border: 1px solid var(--border); border-radius: 16px;
  padding: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.form-group { display: grid; gap: 8px; margin-bottom: 16px; }
label { font-size: 14px; color: var(--muted); }
input[type="text"], input[type="password"] {
  width: 100%; padding: 12px 14px; border-radius: 12px;
  border: 1px solid var(--border); background: #0a1322; color: var(--text);
  outline: none; transition: border-color 0.15s, box-shadow 0.15s;
}
input[type="text"]:focus, input[type="password"]:focus {
  border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,140,255,0.15);
}
.btn {
  width: 100%; padding: 12px 16px; border: 0; border-radius: 12px;
  background: linear-gradient(135deg, var(--accent), #6aa7ff);
  color: #fff; font-weight: 600; letter-spacing: 0.3px;
  cursor: pointer; transition: transform 0.05s ease-in-out, filter 0.15s;
}
.btn:hover { filter: brightness(1.05); }
.btn:active { transform: translateY(1px); }
.alert {
  border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; border: 1px solid var(--border);
}
.alert.success { background: var(--success-bg); color: var(--success); }
.alert.error { background: var(--error-bg); color: var(--error); }
ul { margin: 0 0 0 16px; }
</style>
</head>
<body>
<div class="container">
    <h1>Create User</h1>

    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert error"><ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>

    <form method="post" class="card">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" maxlength="50"
                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" minlength="6" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" name="confirm_password" type="password" minlength="6" required>
        </div>
        <button type="submit" class="btn">Create User</button>
    </form>
</div>
</body>
</html>
