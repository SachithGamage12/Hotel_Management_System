<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: frontoffice_login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']); // Sanitize username for display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front Office Admin Panel</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb; /* Vibrant blue */
            --secondary: #f472b6; /* Soft pink */
            --dark: #1e293b; /* Deep slate */
            --light: #f1f5f9; /* Light gray */
            --success: #22c55e; /* Green */
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            --background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            --panel-bg: #ffffff;
            --text-color: #0f172a;
            --border: #e2e8f0;
            --accent: #93c5fd; /* Light blue accent */
            --booked-out-guest: #10b981; /* Emerald for Out Guest Room */
            --booked-out-honeymoon: #f43f5e; /* Rose for Out Guest HoneyMoon Room */
            --booked-out-anniversary: #f59e0b; /* Amber for Out Guest Anniversary Room */
            --booked-day-use: #3b82f6; /* Blue for Day Use Room */
            --booked-group: #8b5cf6; /* Purple for Group Booking Room */
            --booked-foreign: #ec4899; /* Pink for Foreign Room */
            --booked-default: #6b7280; /* Gray for old or unspecified types */
        }

        [data-theme="dark"] {
            --background: linear-gradient(145deg, #0f172a, #1e293b);
            --panel-bg: #1e293b;
            --text-color: #f1f5f9;
            --border: #475569;
            --light: #334155;
            --accent: #1e40af;
            --booked-out-guest: #059669;
            --booked-out-honeymoon: #e11d48;
            --booked-out-anniversary: #d97706;
            --booked-day-use: #2563eb;
            --booked-group: #7c3aed;
            --booked-foreign: #db2777;
            --booked-default: #4b5563;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: var(--background);
            color: var(--text-color);
            min-height: 100vh;
            transition: all 0.3s ease;
            overflow-x: hidden;
        }

        .admin-panel {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: var(--panel-bg);
            padding: 32px 24px;
            color: var(--text-color);
            transition: transform 0.4s ease;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }

        .sidebar.collapsed {
            transform: translateX(-280px);
        }

        .user-greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 32px;
            color: var(--primary);
            letter-spacing: 0.5px;
        }

.logo-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 120px; /* Adjust size as needed */
    height: 120px; /* Equal to width for a perfect circle */
    background-color: #000000; /* Black background inside the circle */
    border-radius: 50%; /* Circular shape */
    border: 3px solid #000000; /* Black border around the circle */
    margin: 0 auto 10px auto; /* Center and add spacing */
}

.grand-guardian-logo {
    max-width: 90%; /* Slightly smaller than container to fit inside */
    max-height: 90%; /* Maintain aspect ratio */
    object-fit: contain; /* Ensure logo fits without distortion */
}

        .menu {
            margin-top: 24px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            margin-bottom: 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, transparent, var(--light) 100%);
        }

        .menu-item:hover {
            background: linear-gradient(90deg, var(--accent), var(--light) 100%);
            transform: translateX(4px);
        }

        .menu-item.active {
            background: linear-gradient(90deg, var(--primary), var(--secondary) 100%);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
        }

        .menu-item i {
            font-size: 20px;
            margin-right: 16px;
        }

        .menu-item span {
            font-size: 15px;
            font-weight: 500;
        }

        .main-content {
            flex: 1;
            padding: 32px;
            margin-left: 280px;
            transition: all 0.4s ease;
            overflow-x: auto;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .toggle-sidebar {
            background: var(--primary);
            color: #ffffff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            position: fixed;
            top: 20px;
            left: 300px;
            z-index: 9;
        }

        .sidebar.collapsed ~ .main-content .toggle-sidebar {
            left: 20px;
        }

        .toggle-sidebar:hover {
            background: #1d4ed8;
            transform: rotate(90deg) scale(1.1);
        }

        .theme-toggle {
            background: var(--secondary);
            color: #ffffff;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            margin-left: 12px;
        }

        .theme-toggle:hover {
            background: #db2777;
            transform: rotate(90deg) scale(1.1);
        }

        .dashboard {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            min-height: calc(100vh - 96px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .dashboard-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            letter-spacing: 0.5px;
        }

        .calendar-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .calendar-controls .date {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-color);
            padding: 8px 16px;
            background: var(--light);
            border-radius: 8px;
        }

        .calendar-controls select {
            padding: 10px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--panel-bg);
            color: var(--text-color);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .calendar-controls select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .calendar-controls select:hover {
            background: var(--light);
        }

        .calendar-container {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--shadow);
            -webkit-overflow-scrolling: touch;
        }

        .calendar-table-wrapper {
            min-width: 1000px;
        }

        .calendar-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
            background: var(--panel-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .calendar-table th, .calendar-table td {
            padding: 14px;
            text-align: center;
            border: 1px solid var(--border);
            color: var(--text-color);
            min-width: 100px;
            transition: all 0.2s ease;
        }

        .calendar-table th {
            background: linear-gradient(180deg, var(--light), var(--panel-bg));
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 2;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .calendar-table th:last-child, .calendar-table tfoot th {
            background: linear-gradient(180deg, var(--light), var(--panel-bg));
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .calendar-table td {
            background: var(--panel-bg);
            position: relative;
        }

        .calendar-table td.booked {
            color: #ffffff;
            font-weight: 500;
            cursor: pointer;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .calendar-table td.booked:hover {
            filter: brightness(0.9);
            transform: scale(1.02);
        }

        .calendar-table td:not(.booked):hover {
            background: var(--accent);
            transform: scale(1.02);
        }

        .calendar-table td.date-cell {
            font-weight: 500;
            background: var(--light);
        }

        .calendar-table td.date-repeat-cell {
            font-weight: 500;
            background: var(--light);
            text-align: center;
            min-width: 80px;
            border-left: 2px solid var(--border);
        }

        .calendar-table tfoot td {
            background: linear-gradient(180deg, var(--light), var(--panel-bg));
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 2px solid var(--border);
        }

        .booked-tag {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
            overflow-y: auto;
        }

        .modal-content {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 32px;
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            color: var(--text-color);
            animation: slideUp 0.4s ease;
        }

        .modal-content h3 {
            margin-bottom: 24px;
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 600px;
        }

        .booking-table th, .booking-table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            color: var(--text-color);
        }

        .booking-table th {
            background: var(--light);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .booking-table tr:hover {
            background: var(--accent);
        }

        .no-bookings {
            text-align: center;
            color: var(--text-color);
            font-size: 15px;
            margin: 24px 0;
            font-weight: 500;
        }

        .close-modal {
            position: absolute;
            top: 16px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: var(--primary);
            transform: rotate(90deg);
        }

        .error-message {
            color: #dc2626;
            text-align: center;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
            font-weight: 500;
        }

        .booking-form, .room-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .booking-form label, .room-form label {
            font-weight: 500;
            color: var(--text-color);
            font-size: 14px;
        }

        .booking-form input, .booking-form select, .booking-form textarea, .room-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--text-color);
            background: var(--panel-bg);
            transition: all 0.3s ease;
        }

        .booking-form input:focus, .booking-form select:focus, .booking-form textarea:focus, .room-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .booking-form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .booking-form button, .room-form button {
            background: var(--success);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .booking-form button:hover, .room-form button:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .delete-room-btn, .delete-booking-btn {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .delete-room-btn:hover, .delete-booking-btn:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .add-booking-btn, .add-group-booking-btn {
            background: var(--success);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-booking-btn:hover, .add-group-booking-btn:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .room-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            max-height: 150px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--panel-bg);
        }

        .room-checkboxes label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            width: 100px;
        }

        .select-all-container {
            margin-bottom: 10px;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-280px);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .toggle-sidebar {
                left: 20px;
            }

            .sidebar.collapsed ~ .main-content .toggle-sidebar {
                left: 20px;
            }

            .calendar-table th, .calendar-table td {
                min-width: 70px;
                font-size: 12px;
                padding: 10px;
            }

            .calendar-table .booked-tag {
                font-size: 9px;
                padding: 1px 4px;
            }

            .user-greeting {
                font-size: 16px;
            }

            .booking-table th, .booking-table td {
                font-size: 12px;
                padding: 10px;
            }

            .calendar-controls {
                flex-direction: column;
                gap: 12px;
            }

            .calendar-controls select, .calendar-controls .date {
                width: 100%;
                text-align: center;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .calendar-container {
                max-height: calc(100vh - 250px);
            }

            .booking-table {
                min-width: 100%;
            }

            .room-checkboxes label {
                width: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="sidebar">
            <div class="user-greeting">Welcome, <?php echo $username; ?>!</div>
            <!-- Inside the <div class="header"> section -->
<div class="header">
    <div class="logo-container">
        <img src="../image/rose logo png.png" alt="Grand Guardian Logo" class="grand-guardian-logo">
    </div>
</div>
            <div class="menu">
                <div class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item" onclick="window.location.href='Grc.php'">
                    <i class="fas fa-address-book"></i>
                    <span>GRC Form</span>
                </div>
                <div class="menu-item" onclick="window.location.href='foroom _bill.php'">
                    <i class="fas fa-calculator"></i>
                    <span>Room Billing</span>
                </div>
                <div class="menu-item" onclick="window.location.href='view_foroom_bill.php'">
                    <i class="fas fa-credit-card-alt"></i>
                    <span>Invoice View</span>
                </div>
                 <div class="menu-item" onclick="window.location.href='frontoffice_logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
                <div></div>
                <div>
                    <button class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button class="theme-toggle" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
            
            <div class="dashboard">
                <div class="dashboard-header">
                    <h2>Room Booking Calendar</h2>
                    <div class="calendar-controls">
                        <span class="date">Today: <span id="current-date"></span></span>
                        <select id="month-select">
                            <option value="0">January</option>
                            <option value="1">February</option>
                            <option value="2">March</option>
                            <option value="3">April</option>
                            <option value="4">May</option>
                            <option value="5">June</option>
                            <option value="6">July</option>
                            <option value="7">August</option>
                            <option value="8">September</option>
                            <option value="9">October</option>
                            <option value="10">November</option>
                            <option value="11">December</option>
                        </select>
                        <select id="year-select"></select>
                        <button class="add-booking-btn" onclick="openAddBookingModal()">Add Booking</button>
                        <button class="add-booking-btn" onclick="openAddRoomModal()">Manage Rooms</button>
                        <button class="add-booking-btn" onclick="exportToPDF()">Export to PDF</button>
                    </div>
                </div>
                
                <div class="error-message" id="calendar-error"></div>
                <div class="calendar-container">
                    <div class="calendar-table-wrapper">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
            
            <div class="modal" id="booking-modal">
                <div class="modal-content">
                    <span class="close-modal">×</span>
                    <h3>Bookings for <span id="modal-date"></span>, Room <span id="modal-room"></span></h3>
                    <div id="booking-details">
                        <div class="booking-table-wrapper">
                            <table class="booking-table">
                                <thead>
                                    <tr>
                                        <th>Guest Name</th>
                                        <th>Telephone</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Pax</th>
                                        <th>Remarks</th>
                                        <th>Function Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="booking-table-body"></tbody>
                            </table>
                        </div>
                        <button class="add-group-booking-btn" id="add-group-booking-btn" style="margin-top: 16px; display: none;">Add Room to Group</button>
                        <div class="no-bookings" id="no-bookings-message" style="display: none;">
                            No bookings found for this date and room.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal" id="add-booking-modal">
                <div class="modal-content">
                    <span class="close-modal">×</span>
                    <h3 id="booking-modal-title">Add New Room Booking</h3>
                    <form class="booking-form" id="add-booking-form">
                        <label for="guest-name">Guest Name</label>
                        <input type="text" id="guest-name" required>
                        
                        <label for="guest-telephone">Telephone Number</label>
                        <input type="tel" id="guest-telephone" required pattern="[0-9]{10}">
                        
                        <label for="check-in">Check-in Date</label>
                        <input type="date" id="check-in" required>
                        
                        <label for="check-out">Check-out Date</label>
                        <input type="date" id="check-out" required>
                        
                        <label>Room Numbers</label>
                        <div class="select-all-container">
                            <label><input type="checkbox" id="select-all-rooms"> Select All</label>
                        </div>
                        <div class="room-checkboxes" id="room-checkboxes"></div>
                        
                        <label for="pax">Number of Pax</label>
                        <input type="number" id="pax" required min="1">
                        
                        <label for="remarks">Remarks</label>
                        <textarea id="remarks"></textarea>
                        
                        <label for="function-type">Function Type</label>
                        <select id="function-type">
                            <option value="Out Guest Room">Out Guest Room</option>
                            <option value="Out Guest HoneyMoon Room">Out Guest HoneyMoon Room</option>
                            <option value="Out Guest Anniversary Room">Out Guest Anniversary Room</option>
                            <option value="Day Use Room">Day Use Room</option>
                            <option value="Group Booking Room">Group Booking Room</option>
                            <option value="Foreign Room">Foreign Room</option>
                        </select>
                        
                        <button type="submit">Save Booking</button>
                    </form>
                    <div class="error-message" id="form-error"></div>
                </div>
            </div>
            
            <div class="modal" id="add-room-modal">
                <div class="modal-content">
                    <span class="close-modal">×</span>
                    <h3>Manage Room Numbers</h3>
                    <form class="room-form" id="add-room-form">
                        <label for="new-room">Add New Room Number</label>
                        <input type="text" id="new-room" placeholder="e.g., 201" pattern="[0-9]+" required>
                        <button type="submit">Add Room</button>
                    </form>
                    <div class="error-message" id="room-error"></div>
                    <h4 style="margin-top: 24px; color: var(--primary);">Existing Rooms</h4>
                    <div class="booking-table-wrapper">
                        <table class="booking-table">
                            <thead>
                                <tr>
                                    <th>Room Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="room-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Scroll to top on page load
        window.addEventListener('load', function() {
            window.scrollTo(0, 0);
        });

        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Theme toggle
        const themeToggle = document.querySelector('.theme-toggle');
        themeToggle.addEventListener('click', function() {
            document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            this.innerHTML = `<i class="fas fa-${document.body.dataset.theme === 'dark' ? 'sun' : 'moon'}"></i>`;
        });

        // Set current date and min date for inputs
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' });
        document.getElementById('check-in').setAttribute('min', today);
        document.getElementById('check-out').setAttribute('min', today);

        // Populate month and year selectors
        const monthSelect = document.getElementById('month-select');
        const yearSelect = document.getElementById('year-select');
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth();
        const currentYear = currentDate.getFullYear();

        monthSelect.value = currentMonth;
        for (let year = currentYear - 5; year <= currentYear + 5; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            if (year === currentYear) option.selected = true;
            yearSelect.appendChild(option);
        }

        // For mobile view
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar').classList.add('collapsed');
        }

        // Calendar rendering
        function renderCalendar(bookings, rooms, year, month) {
            const calendarEl = document.getElementById('calendar');
            const errorEl = document.getElementById('calendar-error');
            const validFunctionTypes = [
                'Out Guest Room',
                'Out Guest HoneyMoon Room',
                'Out Guest Anniversary Room',
                'Day Use Room',
                'Group Booking Room',
                'Foreign Room'
            ];
            const functionColors = {
                'Out Guest Room': 'var(--booked-out-guest)',
                'Out Guest HoneyMoon Room': 'var(--booked-out-honeymoon)',
                'Out Guest Anniversary Room': 'var(--booked-out-anniversary)',
                'Day Use Room': 'var(--booked-day-use)',
                'Group Booking Room': 'var(--booked-group)',
                'Foreign Room': 'var(--booked-foreign)',
                '': 'var(--booked-default)',
                'Honeymoon Room': 'var(--booked-default)',
                'Standard Room': 'var(--booked-default)',
                'Family Suite': 'var(--booked-default)'
            };

            // Calculate days in month
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Create table
            let tableHTML = '<table class="calendar-table"><thead><tr><th>Date</th>';
            rooms.forEach(room => {
                tableHTML += `<th>${room.room_number}</th>`;
            });
            tableHTML += '<th>Date</th></tr></thead><tbody>';

            // Generate rows for each day
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const formattedDate = date.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
                tableHTML += `<tr><td class="date-cell">${formattedDate}</td>`;
                rooms.forEach(room => {
                    const matchingBookings = bookings.filter(booking => 
                        booking.room_number === room.room_number &&
                        dateStr >= booking.check_in &&
                        dateStr <= booking.check_out
                    );
                    if (matchingBookings.length > 0) {
                        const functionType = matchingBookings[0].function_type || '';
                        const color = functionColors[functionType] || functionColors[''];
                        tableHTML += `<td class="booked" style="background-color: ${color}" data-date="${dateStr}" data-room="${room.room_number}"><span class="booked-tag">${functionType || 'Booked'}</span></td>`;
                    } else {
                        tableHTML += `<td data-date="${dateStr}" data-room="${room.room_number}"></td>`;
                    }
                });
                tableHTML += `<td class="date-repeat-cell">${formattedDate}</td>`;
                tableHTML += '</tr>';
            }

            // Add footer row with room numbers
            tableHTML += '<tfoot><tr><th>Date</th>';
            rooms.forEach(room => {
                tableHTML += `<th>${room.room_number}</th>`;
            });
            tableHTML += '<th>Date</th></tr></tfoot>';
            tableHTML += '</table>';

            calendarEl.innerHTML = tableHTML;

            // Add click listeners for booked cells
            document.querySelectorAll('.calendar-table td.booked').forEach(cell => {
                cell.addEventListener('click', function() {
                    fetchBookingDetails(this.dataset.date, this.dataset.room);
                });
            });
        }

        // Fetch rooms and bookings
        function fetchCalendarData(year = currentYear, month = currentMonth) {
            const start = `${year}-${String(month + 1).padStart(2, '0')}-01`;
            const end = `${year}-${String(month + 2).padStart(2, '0')}-01`;

            Promise.all([
                fetch(`new_api.php?action=get_rooms`),
                fetch(`new_api.php?action=get_bookings&start=${start}&end=${end}`)
            ])
            .then(([roomsResponse, bookingsResponse]) => {
                if (!roomsResponse.ok || !bookingsResponse.ok) {
                    throw new Error('Network response was not ok');
                }
                return Promise.all([roomsResponse.json(), bookingsResponse.json()]);
            })
            .then(([rooms, bookings]) => {
                if (rooms.error || bookings.error) {
                    document.getElementById('calendar-error').textContent = rooms.error || bookings.error;
                    document.getElementById('calendar-error').style.display = 'block';
                    return;
                }
                renderCalendar(bookings, rooms, year, month);
                populateRoomCheckboxes(rooms);
            })
            .then(() => {
                document.querySelector('.calendar-container').scrollTop = 0;
            })
            .catch(error => {
                document.getElementById('calendar-error').textContent = 'Failed to load calendar data. Please try again.';
                document.getElementById('calendar-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Update calendar on month/year change
        monthSelect.addEventListener('change', () => {
            fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });

        yearSelect.addEventListener('change', () => {
            fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
        });

        // Populate room checkboxes in booking form
        function populateRoomCheckboxes(rooms) {
            const container = document.getElementById('room-checkboxes');
            container.innerHTML = '';
            rooms.forEach(room => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" name="room_numbers" value="${room.room_number}"> Room ${room.room_number}`;
                container.appendChild(label);
            });
            // Add select all functionality
            document.getElementById('select-all-rooms').addEventListener('change', function() {
                document.querySelectorAll('input[name="room_numbers"]').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        // Open add booking modal
        function openAddBookingModal(booking = null) {
            const modal = document.getElementById('add-booking-modal');
            const form = document.getElementById('add-booking-form');
            const title = document.getElementById('booking-modal-title');
            modal.style.display = 'flex';
            form.reset();
            document.getElementById('form-error').style.display = 'none';
            document.getElementById('select-all-rooms').checked = false;
            document.querySelectorAll('input[name="room_numbers"]').forEach(checkbox => checkbox.checked = false);

            if (booking) {
                title.textContent = 'Add Room to Group Booking';
                document.getElementById('guest-name').value = booking.guest_name || '';
                document.getElementById('guest-telephone').value = booking.telephone || '';
                document.getElementById('check-in').value = booking.check_in || '';
                document.getElementById('check-out').value = booking.check_out || '';
                document.getElementById('pax').value = booking.pax || 1;
                document.getElementById('remarks').value = booking.remarks || '';
                document.getElementById('function-type').value = booking.function_type || 'Out Guest Room';
                // Disable fields to maintain group consistency
                document.getElementById('guest-name').disabled = true;
                document.getElementById('guest-telephone').disabled = true;
                document.getElementById('check-in').disabled = true;
                document.getElementById('check-out').disabled = true;
                document.getElementById('pax').disabled = true;
                document.getElementById('remarks').disabled = true;
                document.getElementById('function-type').disabled = true;
            } else {
                title.textContent = 'Add New Room Booking';
                document.getElementById('guest-name').disabled = false;
                document.getElementById('guest-telephone').disabled = false;
                document.getElementById('check-in').disabled = false;
                document.getElementById('check-out').disabled = false;
                document.getElementById('pax').disabled = false;
                document.getElementById('remarks').disabled = false;
                document.getElementById('function-type').disabled = false;
            }
            modal.scrollTop = 0;
        }

        // Open manage rooms modal
        function openAddRoomModal() {
            const modal = document.getElementById('add-room-modal');
            modal.style.display = 'flex';
            document.getElementById('add-room-form').reset();
            document.getElementById('room-error').style.display = 'none';
            fetchRoomsForManagement();
            modal.scrollTop = 0;
        }

        // Fetch rooms for management
        function fetchRoomsForManagement() {
            fetch('new_api.php?action=get_rooms')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(rooms => {
                    const tableBody = document.getElementById('room-table-body');
                    tableBody.innerHTML = '';
                    rooms.forEach(room => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${room.room_number}</td>
                            <td><button class="delete-room-btn" data-room="${room.room_number}">Delete</button></td>
                        `;
                        tableBody.appendChild(row);
                    });
                    document.querySelectorAll('.delete-room-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            deleteRoom(this.dataset.room);
                        });
                    });
                })
                .catch(error => {
                    document.getElementById('room-error').textContent = 'Failed to load rooms. Please try again.';
                    document.getElementById('room-error').style.display = 'block';
                    console.error('Error:', error);
                });
        }

        // Delete room
        function deleteRoom(roomNumber) {
            fetch('new_api.php?action=delete_room', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ room_number: roomNumber })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    fetchRoomsForManagement();
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                } else {
                    document.getElementById('room-error').textContent = data.error || 'Failed to delete room.';
                    document.getElementById('room-error').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('room-error').textContent = 'Failed to delete room. Please try again.';
                document.getElementById('room-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Handle add room form submission
        document.getElementById('add-room-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const roomNumber = document.getElementById('new-room').value;
            const roomError = document.getElementById('room-error');

            fetch('new_api.php?action=add_room', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ room_number: roomNumber })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('add-room-modal').style.display = 'none';
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                    fetchRoomsForManagement();
                } else {
                    roomError.textContent = data.error || 'Failed to add room.';
                    roomError.style.display = 'block';
                }
            })
            .catch(error => {
                roomError.textContent = 'Failed to add room. Please try again.';
                roomError.style.display = 'block';
                console.error('Error:', error);
            });
        });

        // Handle booking form submission
        document.getElementById('add-booking-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formError = document.getElementById('form-error');
            const guestName = document.getElementById('guest-name').value;
            const telephone = document.getElementById('guest-telephone').value;
            const checkIn = document.getElementById('check-in').value;
            const checkOut = document.getElementById('check-out').value;
            const roomNumbers = Array.from(document.querySelectorAll('input[name="room_numbers"]:checked')).map(input => input.value);
            const pax = document.getElementById('pax').value;
            const remarks = document.getElementById('remarks').value;
            const functionType = document.getElementById('function-type').value;

            // Validate inputs
            if (roomNumbers.length === 0) {
                formError.textContent = 'Please select at least one room.';
                formError.style.display = 'block';
                return;
            }
            if (new Date(checkOut) <= new Date(checkIn)) {
                formError.textContent = 'Check-out date must be after check-in date.';
                formError.style.display = 'block';
                return;
            }
            if (new Date(checkIn) < new Date(today)) {
                formError.textContent = 'Check-in date cannot be in the past.';
                formError.style.display = 'block';
                return;
            }

            fetch('new_api.php?action=add_booking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    guest_name: guestName,
                    telephone: telephone,
                    check_in: checkIn,
                    check_out: checkOut,
                    room_numbers: roomNumbers,
                    pax: pax,
                    remarks: remarks,
                    function_type: functionType
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('add-booking-modal').style.display = 'none';
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                } else {
                    formError.textContent = data.error || 'Failed to save booking.';
                    formError.style.display = 'block';
                }
            })
            .catch(error => {
                formError.textContent = 'Failed to save booking. Please try again.';
                formError.style.display = 'block';
                console.error('Error:', error);
            });
        });

        // Delete booking
        function deleteBooking(bookingId) {
            fetch('new_api.php?action=delete_booking', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: bookingId })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('booking-modal').style.display = 'none';
                    fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
                } else {
                    document.getElementById('calendar-error').textContent = data.error || 'Failed to delete booking.';
                    document.getElementById('calendar-error').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('calendar-error').textContent = 'Failed to delete booking. Please try again.';
                document.getElementById('calendar-error').style.display = 'block';
                console.error('Error:', error);
            });
        }

        // Export to PDF
        function exportToPDF() {
            const year = parseInt(document.getElementById('year-select').value);
            const month = parseInt(document.getElementById('month-select').value);
            const url = `export_bookings_pdf.php?year=${year}&month=${month}`;
            window.location.href = url;
        }

        // Fetch booking details for modal
        function fetchBookingDetails(dateStr, roomNumber) {
            const modal = document.getElementById('booking-modal');
            const tableBody = document.getElementById('booking-table-body');
            const noBookingsMessage = document.getElementById('no-bookings-message');
            const errorEl = document.getElementById('calendar-error');
            const modalDate = document.getElementById('modal-date');
            const modalRoom = document.getElementById('modal-room');
            const addGroupBtn = document.getElementById('add-group-booking-btn');
            const validFunctionTypes = [
                'Out Guest Room',
                'Out Guest HoneyMoon Room',
                'Out Guest Anniversary Room',
                'Day Use Room',
                'Group Booking Room',
                'Foreign Room'
            ];

            const date = new Date(dateStr);
            const formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            modalDate.textContent = formattedDate;
            modalRoom.textContent = roomNumber;

            fetch(`new_api.php?action=get_booking_details&date=${dateStr}&room_number=${roomNumber}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(bookings => {
                    tableBody.innerHTML = '';
                    if (bookings && bookings.length > 0) {
                        bookings.forEach(booking => {
                            const isValidFunctionType = validFunctionTypes.includes(booking.function_type);
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${booking.guest_name || 'N/A'}</td>
                                <td>${booking.telephone || 'N/A'}</td>
                                <td>${booking.check_in || 'N/A'}</td>
                                <td>${booking.check_out || 'N/A'}</td>
                                <td>${booking.pax || '0'}</td>
                                <td>${booking.remarks || 'N/A'}</td>
                                <td>${booking.function_type || 'N/A'}</td>
                                <td>${isValidFunctionType ? `<button class="delete-booking-btn" data-booking-id="${booking.id}">Delete</button>` : ''}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                        // Add delete listeners
                        document.querySelectorAll('.delete-booking-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                deleteBooking(this.dataset.bookingId);
                            });
                        });
                        // Enable group booking button only for valid function types
                        const firstBooking = bookings[0];
                        const isValidFunctionType = validFunctionTypes.includes(firstBooking.function_type);
                        addGroupBtn.style.display = isValidFunctionType ? 'block' : 'none';
                        if (isValidFunctionType) {
                            addGroupBtn.onclick = () => openAddBookingModal(firstBooking);
                        }
                        noBookingsMessage.style.display = 'none';
                        errorEl.style.display = 'none';
                        modal.style.display = 'flex';
                    } else {
                        noBookingsMessage.style.display = 'block';
                        addGroupBtn.style.display = 'none';
                        errorEl.textContent = 'No bookings found for this date and room.';
                        errorEl.style.display = 'block';
                        modal.style.display = 'flex';
                    }
                })
                .catch(error => {
                    errorEl.textContent = 'Failed to load booking details. Please try again.';
                    errorEl.style.display = 'block';
                    console.error('Error:', error);
                    modal.style.display = 'none';
                });
        }

        // Close modals
        document.querySelectorAll('.close-modal').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });

        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    this.style.display = 'none';
                }
            });
        });

        // Initialize calendar
        document.addEventListener('DOMContentLoaded', () => {
            fetchCalendarData();
        });
    </script>
</body>
</html>