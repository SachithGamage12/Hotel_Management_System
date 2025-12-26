<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Responsible Person</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-5xl bg-white p-8 rounded-2xl shadow-2xl">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-8 text-center">Add Responsible Person</h2>
        
        <?php
        // Database connection
        $servername = "localhost";
        $username = "hotelgrandguardi_root";
        $password = "Sun123flower@";
        $dbname = "hotelgrandguardi_wedding_bliss";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Handle form submission
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_responsible'])) {
                $name = trim($_POST['name']);
                $password = $_POST['password'];
                
                try {
                    // Validate inputs
                    if (empty($name) || empty($password)) {
                        throw new Exception("Name and password are required.");
                    }
                    
                    // Check if name already exists
                    $stmt = $conn->prepare("SELECT id FROM responsible WHERE name = ?");
                    $stmt->execute([$name]);
                    if ($stmt->fetch()) {
                        throw new Exception("A responsible person with this name already exists.");
                    }
                    
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert responsible person
                    $stmt = $conn->prepare("INSERT INTO responsible (name, password) VALUES (?, ?)");
                    $stmt->execute([$name, $hashed_password]);
                    
                    echo "<div class='bg-green-100 text-green-800 p-4 rounded-lg mb-6 text-center'>Responsible person '$name' added successfully.</div>";
                } catch (Exception $e) {
                    echo "<div class='bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center'>Error: " . $e->getMessage() . "</div>";
                }
            }
            
            // Fetch existing responsible persons
            $stmt = $conn->prepare("SELECT id, name FROM responsible ORDER BY name");
            $stmt->execute();
            $responsibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            echo "<div class='bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-center'>Connection failed: " . $e->getMessage() . "</div>";
        }
        ?>

        <form method="post" action="" class="space-y-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" name="name" id="name" required class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter responsible person's name">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" id="password" required class="block w-full max-w-md p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter password">
            </div>
            <div>
                <button type="submit" name="add_responsible" class="w-full max-w-md bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200">Add Responsible Person</button>
            </div>
        </form>

        <h3 class="text-xl font-semibold text-gray-800 mt-8 mb-4">Existing Responsible Persons</h3>
        <?php if (empty($responsibles)): ?>
            <p class="text-gray-600 text-center">No responsible persons found.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead class="bg-indigo-50">
                        <tr>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">ID</th>
                            <th class="py-3 px-4 border-b text-left text-sm font-medium text-gray-700">Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responsibles as $responsible): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4 border-b text-sm"><?php echo $responsible['id']; ?></td>
                                <td class="py-3 px-4 border-b text-sm"><?php echo htmlspecialchars($responsible['name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>