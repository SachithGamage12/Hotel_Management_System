<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-800 text-white flex flex-col">
            <div class="p-4 text-2xl font-bold border-b border-indigo-700">
                Superadmin Panel
            </div>
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="superadmin/responsibel.php" class="flex items-center p-2 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-user-tie mr-2"></i> Add Senior Managers
                        </a>
                    </li>
                    <li>
                        <a href="superadmin/manager_register.php" class="flex items-center p-2 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-user-plus mr-2"></i> Request Sheet Managers
                        </a>
                    </li>
                    <li>
                        <a href="superadmin/logistic_approve.php" class="flex items-center p-2 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-truck mr-2"></i> Logistic Approve
                        </a>
                    </li>
                    <li>
                        <a href="superadmin/Kitchen_responsible_add.php" class="flex items-center p-2 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-utensils mr-2"></i> Kitchen Responsible
                        </a>
                    </li>
                    <li class="mt-auto">
                        <a href="index.php" class="flex items-center p-2 rounded-lg hover:bg-red-600 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome, Superadmin</h1>
                <p class="text-gray-600">Select an option from the sidebar to manage the system.</p>
            </div>
        </div>
    </div>
</body>
</html>