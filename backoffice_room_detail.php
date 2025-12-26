<?php
// backoffice_room_detail.php
// This file handles the room bookings dashboard with calendar, controls, and modals
session_start();
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Backoffice_login.php");
    exit();
}
// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root";
$password = "Sun123flower@";
$dbname = "hotelgrandguardi_wedding_bliss";
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Alter table to add booking_type column (if not exists)
    try {
        $conn->exec("ALTER TABLE room_bookings ADD COLUMN booking_type VARCHAR(10) NULL");
    } catch (PDOException $e) {
        if ($e->getCode() != '42S21') { // Ignore if column already exists
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to alter table: ' . $e->getMessage()]);
            exit;
        }
    }
    // Alter check_in and check_out to DATETIME (if not already)
    try {
        $conn->exec("ALTER TABLE room_bookings MODIFY check_in DATETIME NULL");
        $conn->exec("ALTER TABLE room_bookings MODIFY check_out DATETIME NULL");
    } catch (PDOException $e) {
        // Ignore if already DATETIME
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Bookings Details</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #f472b6;
            --dark: #1e293b;
            --light: #f1f5f9;
            --success: #22c55e;
            --shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            --background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            --panel-bg: #ffffff;
            --text-color: #0f172a;
            --border: #e2e8f0;
            --accent: #93c5fd;
            --booked-1: #f43f5e; /* Red for Honeymoon Room */
            --booked-2: #8b5cf6; /* Purple for Other */
            --booked-3: #f59e0b; /* Yellow for Changing Room */
            --booked-4: #3b82f6; /* Blue for default/unknown */
            --booked-out-guest: #10b981; /* Emerald for Out Guest Room */
            --booked-out-honeymoon: #e11d48; /* Rose for Out Guest HoneyMoon Room */
            --booked-out-anniversary: #d97706; /* Amber for Out Guest Anniversary Room */
            --booked-day-use: #2563eb; /* Blue for Day Use Room */
            --booked-group: #7c3aed; /* Purple for Group Booking Room */
            --booked-foreign: #ec4899; /* Pink for Foreign Room */
            --booked-night: #6b7280; /* Gray for Night Bookings */
        }
        [data-theme="dark"] {
            --background: linear-gradient(145deg, #0f172a, #1e293b);
            --panel-bg: #1e293b;
            --text-color: #f1f5f9;
            --border: #475569;
            --light: #334155;
            --accent: #1e40af;
            --booked-1: #e11d48;
            --booked-2: #7c3aed;
            --booked-3: #d97706;
            --booked-4: #2563eb;
            --booked-out-guest: #059669;
            --booked-out-honeymoon: #e11d48;
            --booked-out-anniversary: #d97706;
            --booked-day-use: #2563eb;
            --booked-group: #7c3aed;
            --booked-foreign: #db2777;
            --booked-night: #4b5563;
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
            padding: 32px;
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
        .booking-form .room-checkbox-container {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px;
            background: var(--panel-bg);
        }
        .booking-form .room-checkbox-container label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        .booking-form .room-checkbox-container input[type="checkbox"] {
            width: auto;
            cursor: pointer;
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
        .delete-room-btn, .delete-booking-btn, .edit-booking-btn, .postpone-booking-btn {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 8px;
        }
        .edit-booking-btn {
            background: #2563eb;
        }
        .postpone-booking-btn {
            background: #f59e0b;
        }
        .delete-room-btn:hover, .delete-booking-btn:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }
        .edit-booking-btn:hover {
            background: #1e40af;
            transform: translateY(-1px);
        }
        .postpone-booking-btn:hover {
            background: #d97706;
            transform: translateY(-1px);
        }
        .add-room-btn {
            background: var(--success);
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .add-room-btn:hover {
            background: #16a34a;
            transform: translateY(-1px);
        }
        .add-booking-btn {
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
        .add-booking-btn:hover {
            background: #16a34a;
            transform: translateY(-2px);
            box-shadow: var(--shadow);
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
        }
        .theme-toggle:hover {
            background: #db2777;
            transform: rotate(90deg) scale(1.1);
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
            body {
                padding: 20px;
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
            .booking-form .room-checkbox-container {
                max-height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-top" style="text-align: left; margin: 10px;">
        <button id="backButton" aria-label="Go back to previous page" style="padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Back
        </button>
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
                <button class="theme-toggle" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
       
        <div class="error-message" id="calendar-error"></div>
        <div class="calendar-container">
            <div class="calendar-table-wrapper">
                <div id="calendar"></div>
            </div>
        </div>
       
        <div class="modal" id="booking-modal">
            <div class="modal-content">
                <span class="close-modal">×</span>
                <h3>Bookings for <span id="modal-date"></span>, Room <span id="modal-room"></span>, <span id="modal-booking-type"></span></h3>
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
                                    <th>Booking Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="booking-table-body"></tbody>
                        </table>
                    </div>
                    <div class="no-bookings" id="no-bookings-message" style="display: none;">
                        No bookings found for this date, room, and time slot.
                    </div>
                    <div style="margin-top: 16px; text-align: right;">
                        <button class="add-room-btn" id="add-room-to-group" style="display: none;">Add Room to Group</button>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="modal" id="add-booking-modal">
            <div class="modal-content">
                <span class="close-modal">×</span>
                <h3 id="booking-modal-title">Add New Room Booking</h3>
                <form class="booking-form" id="add-booking-form">
                    <input type="hidden" id="booking-id">
                    <label for="guest-name">Guest Name</label>
                    <input type="text" id="guest-name" required>
                   
                    <label for="guest-telephone">Telephone Number</label>
                    <input type="tel" id="guest-telephone" required pattern="[0-9]{10}">
                   
                    <label for="check-in">Check-in Date and Time</label>
                    <input type="datetime-local" id="check-in" required>
                   
                    <label for="check-out">Check-out Date and Time</label>
                    <input type="datetime-local" id="check-out" required>
                   
                    <label>Room Numbers</label>
                    <div class="room-checkbox-container" id="room-checkboxes">
                        <label><input type="checkbox" id="select-all-rooms"> Select All</label>
                    </div>
                   
                    <label for="pax">Number of Pax</label>
                    <input type="number" id="pax" required min="1">
                   
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" rows="4" placeholder="Enter any additional remarks"></textarea>
                   
                    <label for="function-type">Function Type</label>
                    <select id="function-type">
                        <option value="">Select Function Type</option>
                        <option value="Changing Room">Changing Room</option>
                        <option value="Honeymoon Room">Honeymoon Room</option>
                        <option value="Other">Other</option>
                    </select>
                   
                    <label for="booking-type">Booking Type</label>
                    <select id="booking-type" required>
                        <option value="day">Day</option>
                        <option value="night">Night</option>
                    </select>
                   
                    <button type="submit" id="save-booking-btn">Save Booking</button>
                </form>
                <div class="error-message" id="form-error"></div>
            </div>
        </div>
       
        <div class="modal" id="postpone-booking-modal">
            <div class="modal-content">
                <span class="close-modal">×</span>
                <h3>Postpone Booking</h3>
                <form class="booking-form" id="postpone-booking-form">
                    <input type="hidden" id="postpone-booking-id">
                    <label for="postpone-check-in">New Check-in Date and Time</label>
                    <input type="datetime-local" id="postpone-check-in" required>
                   
                    <label for="postpone-check-out">New Check-out Date and Time</label>
                    <input type="datetime-local" id="postpone-check-out" required>
                   
                    <label for="postpone-booking-type">Booking Type</label>
                    <select id="postpone-booking-type" required>
                        <option value="day">Day</option>
                        <option value="night">Night</option>
                    </select>
                   
                    <button type="submit">Postpone Booking</button>
                </form>
                <div class="error-message" id="postpone-error"></div>
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
<script>
    // Scroll to top on page load
    window.addEventListener('load', function() {
        window.scrollTo(0, 0);
    });

    // Theme toggle
    const themeToggle = document.querySelector('.theme-toggle');
    themeToggle.addEventListener('click', function() {
        document.body.dataset.theme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        this.innerHTML = `<i class="fas fa-${document.body.dataset.theme === 'dark' ? 'sun' : 'moon'}"></i>`;
    });

    // Set current date
    document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        weekday: 'long' 
    });

    // Initialize month and year selectors
    const monthSelect = document.getElementById('month-select');
    const yearSelect = document.getElementById('year-select');
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth();

    // Set current month
    monthSelect.value = currentMonth;

    // Populate year selector (5 years back, 5 years forward)
    for (let year = currentYear - 5; year <= currentYear + 5; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === currentYear) option.selected = true;
        yearSelect.appendChild(option);
    }

    // Valid function types for back office management
    const validBackOfficeFunctionTypes = ['Honeymoon Room', 'Changing Room', 'Other'];

    // Color mapping for different function types
    const colorMap = {
        'Honeymoon Room': 'var(--booked-1)',
        'Changing Room': 'var(--booked-3)',
        'Other': 'var(--booked-2)',
        'Out Guest Room': 'var(--booked-out-guest)',
        'Out Guest HoneyMoon Room': 'var(--booked-out-honeymoon)',
        'Out Guest Anniversary Room': 'var(--booked-out-anniversary)',
        'Day Use Room': 'var(--booked-day-use)',
        'Group Booking Room': 'var(--booked-group)',
        'Foreign Room': 'var(--booked-foreign)',
        '': 'var(--booked-4)',
        'night': 'var(--booked-night)'
    };

    // Main function to render calendar
    function renderCalendar(bookings, rooms, year, month) {
        const calendarEl = document.getElementById('calendar');
        const errorEl = document.getElementById('calendar-error');
        
        // Clear any previous errors
        errorEl.style.display = 'none';
        
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let tableHTML = `
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Date</th>
        `;
        
        // Add room headers
        rooms.forEach(room => {
            tableHTML += `<th colspan="2">Room ${room.room_number}</th>`;
        });
        
        tableHTML += `
                        <th>Date</th>
                    </tr>
                    <tr>
                        <th></th>
        `;
        
        // Add day/night headers for each room
        rooms.forEach(() => {
            tableHTML += `<th>Day</th><th>Night</th>`;
        });
        
        tableHTML += `
                        <th></th>
                    </tr>
                </thead>
                <tbody>
        `;

        // Generate calendar rows for each day
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const date = new Date(dateStr);
            const formattedDate = date.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
            
            tableHTML += `<tr><td class="date-cell">${formattedDate}</td>`;
            
            // Generate cells for each room and booking type
            rooms.forEach(room => {
                ['day', 'night'].forEach(bookingType => {
                    const cellBookings = getBookingsForCell(bookings, room.room_number, dateStr, bookingType);
                    
                    if (cellBookings.length > 0) {
                        const primaryBooking = cellBookings[0];
                        const functionType = primaryBooking.function_type || '';
                        const color = getCellColor(functionType, bookingType);
                        const displayText = functionType || bookingType.charAt(0).toUpperCase() + bookingType.slice(1);
                        
                        tableHTML += `
                            <td class="booked" 
                                style="background-color: ${color}" 
                                data-date="${dateStr}" 
                                data-room="${room.room_number}" 
                                data-booking-type="${bookingType}">
                                <span class="booked-tag">${displayText}</span>
                            </td>
                        `;
                    } else {
                        tableHTML += `
                            <td data-date="${dateStr}" 
                                data-room="${room.room_number}" 
                                data-booking-type="${bookingType}">
                            </td>
                        `;
                    }
                });
            });
            
            tableHTML += `<td class="date-repeat-cell">${formattedDate}</td></tr>`;
        }

        // Table footer
        tableHTML += `
                </tbody>
                <tfoot>
                    <tr>
                        <th>Date</th>
        `;
        
        rooms.forEach(() => {
            tableHTML += `<th>Day</th><th>Night</th>`;
        });
        
        tableHTML += `
                        <th>Date</th>
                    </tr>
                </tfoot>
            </table>
        `;
        
        calendarEl.innerHTML = tableHTML;

        // Add click event listeners to booked cells
        document.querySelectorAll('.calendar-table td.booked').forEach(cell => {
            cell.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                const room = this.getAttribute('data-room');
                const bookingType = this.getAttribute('data-booking-type');
                fetchBookingDetails(date, room, bookingType);
            });
        });
    }

    // Helper function to get bookings for a specific cell
    function getBookingsForCell(bookings, roomNumber, dateStr, bookingType) {
        return bookings.filter(booking => {
            // Skip if booking doesn't match room and type
            if (booking.room_number !== roomNumber || booking.booking_type !== bookingType) {
                return false;
            }
            
            // Parse dates
            const checkIn = new Date(booking.check_in);
            const checkOut = new Date(booking.check_out);
            const currentDate = new Date(dateStr);
            
            // Reset times to compare only dates
            checkIn.setHours(0, 0, 0, 0);
            checkOut.setHours(0, 0, 0, 0);
            currentDate.setHours(0, 0, 0, 0);
            
            // Check if current date is within booking range
            return currentDate >= checkIn && currentDate < checkOut;
        });
    }

    // Helper function to get cell color
    function getCellColor(functionType, bookingType) {
        if (bookingType === 'night') {
            return colorMap['night'];
        }
        return colorMap[functionType] || colorMap[''];
    }

    // Fetch calendar data from API
    function fetchCalendarData(year = currentYear, month = currentMonth) {
        // Calculate date range for the entire month
        const startDate = new Date(year, month, 1);
        const endDate = new Date(year, month + 1, 0);
        
        const start = formatDateForAPI(startDate);
        const end = formatDateForAPI(endDate);

        // Show loading state
        document.getElementById('calendar-error').style.display = 'none';

        Promise.all([
            fetch(`new_api.php?action=get_rooms`).then(handleResponse),
            fetch(`new_api.php?action=get_bookings&start=${start}&end=${end}`).then(handleResponse)
        ])
        .then(([roomsData, bookingsData]) => {
            if (roomsData.error) throw new Error(roomsData.error);
            if (bookingsData.error) throw new Error(bookingsData.error);
            
            renderCalendar(bookingsData, roomsData, year, month);
            populateRoomCheckboxes(roomsData);
        })
        .catch(error => {
            showError('Failed to load calendar data: ' + error.message);
        });
    }

    // Helper function to handle API responses
    function handleResponse(response) {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    }

    // Helper function to format date for API
    function formatDateForAPI(date) {
        return date.toISOString().split('T')[0];
    }

    // Helper function to show errors
    function showError(message) {
        const errorEl = document.getElementById('calendar-error');
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    // Populate room checkboxes in booking form
    function populateRoomCheckboxes(rooms) {
        const container = document.getElementById('room-checkboxes');
        container.innerHTML = '<label><input type="checkbox" id="select-all-rooms"> Select All</label>';
        
        rooms.forEach(room => {
            const label = document.createElement('label');
            label.innerHTML = `
                <input type="checkbox" name="room-numbers" value="${room.room_number}"> 
                Room ${room.room_number}
            `;
            container.appendChild(label);
        });

        // Handle Select All checkbox
        const selectAll = document.getElementById('select-all-rooms');
        selectAll.addEventListener('change', function() {
            const checkboxes = container.querySelectorAll('input[name="room-numbers"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Fetch booking details for modal
    function fetchBookingDetails(dateStr, roomNumber, bookingType) {
        const modal = document.getElementById('booking-modal');
        const tableBody = document.getElementById('booking-table-body');
        const noBookingsMessage = document.getElementById('no-bookings-message');
        const modalDate = document.getElementById('modal-date');
        const modalRoom = document.getElementById('modal-room');
        const modalBookingType = document.getElementById('modal-booking-type');
        const addRoomBtn = document.getElementById('add-room-to-group');

        // Update modal header
        const date = new Date(dateStr);
        const formattedDate = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        modalDate.textContent = formattedDate;
        modalRoom.textContent = roomNumber;
        modalBookingType.textContent = bookingType.charAt(0).toUpperCase() + bookingType.slice(1);

        // Fetch booking details
        fetch(`new_api.php?action=get_booking_details&date=${dateStr}&room_number=${roomNumber}&booking_type=${bookingType}`)
            .then(handleResponse)
            .then(bookings => {
                tableBody.innerHTML = '';
                
                if (bookings && bookings.length > 0) {
                    displayBookingsInModal(bookings, dateStr, roomNumber, bookingType);
                    noBookingsMessage.style.display = 'none';
                    modal.style.display = 'flex';
                } else {
                    noBookingsMessage.style.display = 'block';
                    addRoomBtn.style.display = 'none';
                    modal.style.display = 'flex';
                }
            })
            .catch(error => {
                showError('Failed to load booking details: ' + error.message);
                modal.style.display = 'none';
            });
    }

    // Display bookings in modal
    function displayBookingsInModal(bookings, dateStr, roomNumber, bookingType) {
        const tableBody = document.getElementById('booking-table-body');
        const addRoomBtn = document.getElementById('add-room-to-group');

        bookings.forEach(booking => {
            const isBackOfficeFunctionType = validBackOfficeFunctionTypes.includes(booking.function_type);
            const row = document.createElement('tr');
            
            // Format dates for display
            const checkIn = booking.check_in ? new Date(booking.check_in).toLocaleString() : 'N/A';
            const checkOut = booking.check_out ? new Date(booking.check_out).toLocaleString() : 'N/A';
            
            row.innerHTML = `
                <td>${booking.guest_name || 'N/A'}</td>
                <td>${booking.telephone || 'N/A'}</td>
                <td>${checkIn}</td>
                <td>${checkOut}</td>
                <td>${booking.pax || '0'}</td>
                <td>${booking.remarks || 'N/A'}</td>
                <td>${booking.function_type || 'N/A'}</td>
                <td>${booking.booking_type || 'N/A'}</td>
                <td>
                    ${isBackOfficeFunctionType ? `
                        <button class="edit-booking-btn" data-booking-id="${booking.id}">Edit</button>
                        <button class="postpone-booking-btn" data-booking-id="${booking.id}">Postpone</button>
                        <button class="delete-booking-btn" data-booking-id="${booking.id}">Delete</button>
                    ` : ''}
                </td>
            `;
            tableBody.appendChild(row);
        });

        // Setup action buttons
        setupActionButtons(bookings, dateStr, roomNumber, bookingType);
        
        // Show/hide add room button
        const firstBooking = bookings[0];
        const isBackOfficeFunctionType = validBackOfficeFunctionTypes.includes(firstBooking.function_type);
        addRoomBtn.style.display = isBackOfficeFunctionType ? 'inline-block' : 'none';
        
        if (isBackOfficeFunctionType) {
            addRoomBtn.onclick = () => openAddBookingModal(firstBooking);
        }
    }

    // Setup action buttons in modal
    function setupActionButtons(bookings, dateStr, roomNumber, bookingType) {
        // Edit buttons
        document.querySelectorAll('.edit-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                const booking = bookings.find(b => b.id == bookingId);
                openAddBookingModal(booking);
            });
        });

        // Postpone buttons
        document.querySelectorAll('.postpone-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                const booking = bookings.find(b => b.id == bookingId);
                openPostponeBookingModal(booking);
            });
        });

        // Delete buttons
        document.querySelectorAll('.delete-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                if (confirm('Are you sure you want to delete this booking?')) {
                    deleteBooking(bookingId, dateStr, roomNumber, bookingType);
                }
            });
        });
    }

    // Open add/edit booking modal
    function openAddBookingModal(booking = null) {
        const modal = document.getElementById('add-booking-modal');
        const modalTitle = document.getElementById('booking-modal-title');
        const saveBtn = document.getElementById('save-booking-btn');
        const form = document.getElementById('add-booking-form');
        
        modal.style.display = 'flex';
        form.reset();
        document.getElementById('form-error').style.display = 'none';

        // Clear room checkboxes
        document.querySelectorAll('#room-checkboxes input[name="room-numbers"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.getElementById('select-all-rooms').checked = false;

        if (booking) {
            // Edit mode
            modalTitle.textContent = 'Edit Booking';
            saveBtn.textContent = 'Update Booking';
            populateFormForEdit(booking);
        } else {
            // Add mode
            modalTitle.textContent = 'Add New Room Booking';
            saveBtn.textContent = 'Save Booking';
            document.getElementById('booking-id').value = '';
        }

        setupDateTimeInputs();
    }

    // Populate form for editing
    function populateFormForEdit(booking) {
        document.getElementById('booking-id').value = booking.id || '';
        document.getElementById('guest-name').value = booking.guest_name || '';
        document.getElementById('guest-telephone').value = booking.telephone || '';
        
        // Format dates for datetime-local input
        const checkIn = booking.check_in ? new Date(booking.check_in).toISOString().slice(0, 16) : '';
        const checkOut = booking.check_out ? new Date(booking.check_out).toISOString().slice(0, 16) : '';
        
        document.getElementById('check-in').value = checkIn;
        document.getElementById('check-out').value = checkOut;
        document.getElementById('pax').value = booking.pax || 1;
        document.getElementById('remarks').value = booking.remarks || '';
        document.getElementById('function-type').value = booking.function_type || '';
        document.getElementById('booking-type').value = booking.booking_type || 'day';
        
        // Check the room checkbox
        if (booking.room_number) {
            const checkbox = document.querySelector(`#room-checkboxes input[value="${booking.room_number}"]`);
            if (checkbox) checkbox.checked = true;
        }
    }

    // Setup date time inputs
    function setupDateTimeInputs() {
        const checkInInput = document.getElementById('check-in');
        const checkOutInput = document.getElementById('check-out');
        
        // Set min date to now
        const now = new Date().toISOString().slice(0, 16);
        checkInInput.setAttribute('min', now);

        // Update check-out min when check-in changes
        checkInInput.addEventListener('change', function() {
            checkOutInput.min = this.value;
        });
    }

    // Open postpone booking modal
    function openPostponeBookingModal(booking) {
        const modal = document.getElementById('postpone-booking-modal');
        modal.style.display = 'flex';
        document.getElementById('postpone-error').style.display = 'none';
        
        document.getElementById('postpone-booking-id').value = booking.id || '';
        
        // Format dates for datetime-local input
        const checkIn = booking.check_in ? new Date(booking.check_in).toISOString().slice(0, 16) : '';
        const checkOut = booking.check_out ? new Date(booking.check_out).toISOString().slice(0, 16) : '';
        
        document.getElementById('postpone-check-in').value = checkIn;
        document.getElementById('postpone-check-out').value = checkOut;
        document.getElementById('postpone-booking-type').value = booking.booking_type || 'day';
        
        setupDateTimeInputs();
    }

    // Open manage rooms modal
    function openAddRoomModal() {
        const modal = document.getElementById('add-room-modal');
        modal.style.display = 'flex';
        document.getElementById('add-room-form').reset();
        document.getElementById('room-error').style.display = 'none';
        fetchRoomsForManagement();
    }

    // Fetch rooms for management modal
    function fetchRoomsForManagement() {
        fetch('new_api.php?action=get_rooms')
            .then(handleResponse)
            .then(rooms => {
                const tableBody = document.getElementById('room-table-body');
                tableBody.innerHTML = '';
                
                rooms.forEach(room => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${room.room_number}</td>
                        <td>
                            <button class="delete-room-btn" data-room="${room.room_number}">
                                Delete
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });

                // Add delete room event listeners
                document.querySelectorAll('.delete-room-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const roomNumber = this.getAttribute('data-room');
                        deleteRoom(roomNumber);
                    });
                });
            })
            .catch(error => {
                document.getElementById('room-error').textContent = 'Failed to load rooms: ' + error.message;
                document.getElementById('room-error').style.display = 'block';
            });
    }

    // Delete room
    function deleteRoom(roomNumber) {
        if (!confirm('Are you sure you want to delete this room?')) return;
        
        fetch('new_api.php?action=delete_room', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ room_number: roomNumber })
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                fetchRoomsForManagement();
                fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
            } else {
                throw new Error(data.error || 'Failed to delete room');
            }
        })
        .catch(error => {
            document.getElementById('room-error').textContent = error.message;
            document.getElementById('room-error').style.display = 'block';
        });
    }

    // Delete booking
    function deleteBooking(bookingId, dateStr, roomNumber, bookingType) {
        fetch('new_api.php?action=delete_booking', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_id: bookingId })
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                // Refresh the modal and calendar
                fetchBookingDetails(dateStr, roomNumber, bookingType);
                fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
            } else {
                throw new Error(data.error || 'Failed to delete booking');
            }
        })
        .catch(error => {
            showError('Failed to delete booking: ' + error.message);
        });
    }

    // Handle add/edit booking form submission
    document.getElementById('add-booking-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formError = document.getElementById('form-error');
        formError.style.display = 'none';

        const formData = getBookingFormData();
        
        if (!validateBookingForm(formData)) {
            return;
        }

        submitBookingForm(formData);
    });

    // Get booking form data
    function getBookingFormData() {
        const bookingId = document.getElementById('booking-id').value;
        const roomCheckboxes = document.querySelectorAll('#room-checkboxes input[name="room-numbers"]:checked');
        const roomNumbers = Array.from(roomCheckboxes).map(checkbox => checkbox.value);
        
        return {
            bookingId: bookingId,
            guestName: document.getElementById('guest-name').value,
            telephone: document.getElementById('guest-telephone').value,
            checkIn: document.getElementById('check-in').value,
            checkOut: document.getElementById('check-out').value,
            roomNumbers: roomNumbers,
            pax: document.getElementById('pax').value,
            remarks: document.getElementById('remarks').value,
            functionType: document.getElementById('function-type').value,
            bookingType: document.getElementById('booking-type').value
        };
    }

    // Validate booking form
    function validateBookingForm(formData) {
        const formError = document.getElementById('form-error');
        
        if (new Date(formData.checkOut) <= new Date(formData.checkIn)) {
            formError.textContent = 'Check-out must be after check-in.';
            formError.style.display = 'block';
            return false;
        }

        if (!formData.bookingId && formData.roomNumbers.length === 0) {
            formError.textContent = 'Please select at least one room.';
            formError.style.display = 'block';
            return false;
        }

        return true;
    }

    // Submit booking form
    function submitBookingForm(formData) {
        const formError = document.getElementById('form-error');
        const action = formData.bookingId ? 'edit_booking' : 'add_booking';
        
        const requestBody = formData.bookingId ? {
            booking_id: formData.bookingId,
            guest_name: formData.guestName,
            telephone: formData.telephone,
            check_in: formData.checkIn,
            check_out: formData.checkOut,
            pax: formData.pax,
            remarks: formData.remarks,
            function_type: formData.functionType,
            booking_type: formData.bookingType
        } : {
            guest_name: formData.guestName,
            telephone: formData.telephone,
            check_in: formData.checkIn,
            check_out: formData.checkOut,
            room_numbers: formData.roomNumbers,
            pax: formData.pax,
            remarks: formData.remarks,
            function_type: formData.functionType,
            booking_type: formData.bookingType
        };

        fetch(`new_api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestBody)
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                document.getElementById('add-booking-modal').style.display = 'none';
                fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
            } else {
                throw new Error(data.error || `Failed to ${formData.bookingId ? 'update' : 'save'} booking`);
            }
        })
        .catch(error => {
            formError.textContent = error.message;
            formError.style.display = 'block';
        });
    }

    // Handle postpone booking form submission
    document.getElementById('postpone-booking-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formError = document.getElementById('postpone-error');
        formError.style.display = 'none';

        const bookingId = document.getElementById('postpone-booking-id').value;
        const checkIn = document.getElementById('postpone-check-in').value;
        const checkOut = document.getElementById('postpone-check-out').value;
        const bookingType = document.getElementById('postpone-booking-type').value;

        if (new Date(checkOut) <= new Date(checkIn)) {
            formError.textContent = 'New check-out must be after new check-in.';
            formError.style.display = 'block';
            return;
        }

        fetch('new_api.php?action=postpone_booking', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                booking_id: bookingId,
                new_check_in: checkIn,
                new_check_out: checkOut,
                booking_type: bookingType
            })
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                document.getElementById('postpone-booking-modal').style.display = 'none';
                fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
            } else {
                throw new Error(data.error || 'Failed to postpone booking');
            }
        })
        .catch(error => {
            formError.textContent = error.message;
            formError.style.display = 'block';
        });
    });

    // Handle add room form submission
    document.getElementById('add-room-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const roomError = document.getElementById('room-error');
        roomError.style.display = 'none';

        const roomNumber = document.getElementById('new-room').value;

        if (!roomNumber || !/^\d+$/.test(roomNumber)) {
            roomError.textContent = 'Please enter a valid room number (numbers only).';
            roomError.style.display = 'block';
            return;
        }

        fetch('new_api.php?action=add_room', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ room_number: roomNumber })
        })
        .then(handleResponse)
        .then(data => {
            if (data.success) {
                document.getElementById('add-room-modal').style.display = 'none';
                fetchCalendarData(parseInt(yearSelect.value), parseInt(monthSelect.value));
            } else {
                throw new Error(data.error || 'Failed to add room');
            }
        })
        .catch(error => {
            roomError.textContent = error.message;
            roomError.style.display = 'block';
        });
    });

    // Export to PDF
    function exportToPDF() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);
        const url = `export_bookings_pdf.php?action=export_bookings&year=${year}&month=${month}`;
        window.location.href = url;
    }

    // Close modals when clicking close button or outside
    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                this.style.display = 'none';
            }
        });
    });

    // Event listeners for month/year changes
    monthSelect.addEventListener('change', updateCalendar);
    yearSelect.addEventListener('change', updateCalendar);

    function updateCalendar() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);
        fetchCalendarData(year, month);
    }

    // Back button functionality
    document.getElementById('backButton').addEventListener('click', function() {
        window.location.href = 'Backoffice.php';
    });

    // Initialize calendar when page loads
    document.addEventListener('DOMContentLoaded', function() {
        fetchCalendarData();
    });
</script>
</body>
</html>