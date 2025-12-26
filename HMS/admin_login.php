<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #6b7280, #1e3a8a);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        input:focus {
            outline: none;
            ring: 2px solid #1e40af;
        }
        button {
            transition: transform 0.2s ease, background-color 0.3s ease;
        }
        button:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="login-container max-w-md w-full mx-4 p-8 rounded-2xl shadow-2xl">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Admin Login</h2>
        <form method="POST" action="">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Username" required 
                       class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-transparent transition duration-200 text-gray-700 bg-gray-50">
            </div>
            <div class="mb-6">
                <input type="password" name="password" placeholder="Password" required 
                       class="w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-600 focus:border-transparent transition duration-200 text-gray-700 bg-gray-50">
            </div>
            <button type="submit" 
                    class="w-full p-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                Login
            </button>
        </form>
        <?php
        session_start(); // Start the session
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $valid_username = "Admin";
            $valid_password = "Admin123@";

            if ($username === $valid_username && $password === $valid_password) {
                $_SESSION['loggedin'] = true; // Set session variable
                header("Location: admin.php"); // Redirect to admin.php
                exit();
            } else {
                echo "<p class='text-red-500 text-center mt-4 font-medium'>Invalid username or password.</p>";
            }
        }
        ?>
    </div>
</body>
</html>