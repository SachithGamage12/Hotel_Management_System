<?php
session_start();
require_once 'config/db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $conn = getDBConnection();
    
    // Check if this is an admin trying to unlock registration
    if (isset($_POST['unlock_registration'])) {
        // Hardcoded admin credentials
        $admin_username = 'InventoryAdmin';
        $admin_password = '123456';
        
        if ($username === $admin_username && $password === $admin_password) {
            // Update registration access
            $update = $conn->prepare("UPDATE inventoryregistration_access SET is_locked = FALSE");
            if ($update === false) {
                $error = "Failed to prepare unlock query: " . $conn->error;
            } else {
                $update->execute();
                $_SESSION['registration_unlocked'] = true;
                header("Location: Inventory_register.php");
                exit();
            }
        } else {
            $error = "Invalid admin credentials";
        }
    } 
    // Normal login
    else {
        $stmt = $conn->prepare("SELECT id, username, password FROM inventory_users WHERE username = ?");
        if ($stmt === false) {
            $error = "Failed to prepare login query: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: inventory.php");
                    exit();
                } else {
                    $error = "Invalid username or password";
                }
            } else {
                $error = "Invalid username or password";
            }
        }
    }
    
    $conn->close();
}

// Check registration lock status
$conn = getDBConnection();
$lock_result = $conn->query("SELECT is_locked FROM inventoryregistration_access LIMIT 1");
$is_locked = $lock_result->fetch_assoc()['is_locked'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6b21a8, #dc2626);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            padding: 1rem;
        }

        .canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.3;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
        }

        h1 {
            color: #2d1b4e;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: clamp(1.5rem, 5vw, 1.8rem);
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b3b6e;
            font-weight: 500;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            background: #f9fafb;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #6b21a8;
            box-shadow: 0 0 0 3px rgba(107, 33, 168, 0.1);
        }

        button {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(90deg, #6b21a8, #dc2626);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .error {
            color: #dc2626;
            margin-bottom: 1.2rem;
            text-align: center;
            font-size: 0.9rem;
            background: rgba(220, 38, 38, 0.1);
            padding: 0.5rem;
            border-radius: 4px;
        }

        .register-link {
            text-align: center;
            margin-top: 1.2rem;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .register-link a, .register-link button {
            color: #6b21a8;
            text-decoration: none;
            font-weight: 500;
            background: none;
            border: none;
            cursor: pointer;
        }

        .register-link a:hover, .register-link button:hover {
            text-decoration: underline;
        }

        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            width: 100%;
            max-width: 350px;
            position: relative;
            animation: popupFadeIn 0.3s ease-out;
        }

        .popup-content h2 {
            font-size: 1rem;
            color: #4b3b6e;
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .close-popup {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #6b7280;
            cursor: pointer;
        }

        @keyframes popupFadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container {
            animation: fadeIn 0.5s ease-out;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
                max-width: 90%;
            }

            button {
                padding: 0.8rem;
            }

            .popup-content {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <canvas class="canvas" id="bgCanvas"></canvas>
    <div class="login-container">
        <h1>Login to Inventory Panel</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="inventory_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <button type="button" onclick="showPopup()">Register here</button></p>
        </div>
    </div>

    <div class="popup" id="adminPopup">
        <div class="popup-content">
            <button class="close-popup" onclick="closePopup()">×</button>
            <h2>Admin Unlock Registration</h2>
            <form action="inventory_login.php" method="POST">
                <div class="form-group">
                    <label for="admin_username">Admin Username</label>
                    <input type="text" id="admin_username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Admin Password</label>
                    <input type="password" id="admin_password" name="password" required>
                </div>
                
                <input type="hidden" name="unlock_registration" value="1">
                <button type="submit">Unlock Registration</button>
            </form>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('bgCanvas');
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        const particles = [];
        const particleCount = 100;

        class Particle {
            constructor() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.size = Math.random() * 3 + 1;
                this.speedX = Math.random() * 0.5 - 0.25;
                this.speedY = Math.random() * 0.5 - 0.25;
                this.opacity = Math.random() * 0.5 + 0.1;
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
            }

            draw() {
                ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function init() {
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(particle => {
                particle.update();
                particle.draw();
            });
            requestAnimationFrame(animate);
        }

        init();
        animate();

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        function showPopup() {
            document.getElementById('adminPopup').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('adminPopup').style.display = 'none';
        }
    </script>
</body>
</html>