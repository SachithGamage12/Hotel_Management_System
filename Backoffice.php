<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Backoffice_login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']); // Sanitize username for display
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BackOffice Admin Panel</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #e84393;
            --dark: #2d3436;
            --light: #f5f6fa;
            --success: #00b894;
            --info: #0984e3;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --background: #f9f9f9;
            --panel-bg: white;
            --text-color: #2d3436;
        }
        
        [data-theme="dark"] {
            --background: #1a1a1a;
            --panel-bg: #2d2d2d;
            --text-color: #ffffff;
        }
        
        [data-theme="light"] {
            --text-color: #000000;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--background);
            color: var(--text-color);
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }
        
        .admin-panel {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            animation: gradientShift 10s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px 20px;
            color: white;
            transform: translateX(0);
            transition: all 0.4s ease;
            box-shadow: var(--shadow);
            z-index: 10;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar.collapsed {
            transform: translateX(-260px);
        }
        
        .user-greeting {
            font-size: 18px;
            font-weight: 500;
            color: white;
            margin-bottom: 20px;
            padding-left: 10px;
            animation: slideInLeft 0.5s ease;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            padding-left: 10px;
        }
        
        .grand-guardian-logo {
            width: 120px;
            height: auto;
            transition: transform 0.3s ease, opacity 0.3s ease;
            animation: logoPulse 2s ease infinite;
        }
        
        .grand-guardian-logo:hover {
            transform: scale(1.1);
            opacity: 0.9;
        }
        
        @keyframes logoPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .menu {
            margin-top: 40px;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
        }
        
        .menu-item:hover::before {
            left: 0;
        }
        
        .menu-item.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .menu-item i {
            font-size: 20px;
            margin-right: 15px;
        }
        
        .menu-item span {
            font-size: 16px;
            font-weight: 500;
            white-space: nowrap;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            transition: all 0.4s ease;
            margin-left: 280px;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 20px;
        }
        
        .header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .toggle-sidebar {
            background: var(--primary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.3);
            position: fixed;
            top: 20px;
            left: 300px;
            z-index: 9;
        }
        
        .sidebar.collapsed ~ .main-content .toggle-sidebar {
            left: 40px;
        }
        
        .toggle-sidebar:hover {
            transform: rotate(180deg);
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.5);
        }
        
        .theme-toggle {
            background: var(--secondary);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(232, 67, 147, 0.3);
            margin-left: 10px;
        }
        
        .theme-toggle:hover {
            transform: rotate(90deg);
            box-shadow: 0 4px 20px rgba(232, 67, 147, 0.5);
        }
        
        .dashboard {
            background: var(--panel-bg);
            border-radius: 16px;
            padding: 30px;
            box-shadow: var(--shadow);
            animation: zoomIn 0.6s ease;
            color: var(--text-color);
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .dashboard-header h2 {
            font-size: 24px;
            color: var(--text-color);
            animation: slideInLeft 0.5s ease;
        }
        
        .calendar-container {
            margin-bottom: 40px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            animation: zoomIn 0.5s ease;
        }
        
        .fc {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .fc .fc-toolbar {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .fc .fc-button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 6px;
        }
        
        .fc .fc-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .fc-daygrid-day {
            height: 80px;
            width: 80px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .fc-daygrid-day-number {
            color: var(--text-color);
            font-weight: 500;
            text-align: center;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .fc-daygrid-day.has-bookings {
            animation: borderPulse 1.5s infinite;
            border: 2px solid var(--secondary);
            border-radius: 4px;
            background: rgba(232, 67, 147, 0.05);
        }
        
        .fc-daygrid-day.has-bookings .fc-daygrid-day-number::after {
            content: attr(data-booking-count);
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--secondary);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }
        
        @keyframes borderPulse {
            0% { border-color: var(--secondary); box-shadow: 0 0 5px var(--secondary); }
            50% { border-color: transparent; box-shadow: 0 0 10px var(--secondary); }
            100% { border-color: var(--secondary); box-shadow: 0 0 5px var(--secondary); }
        }
        
        .fc-daygrid-day:hover {
            background: rgba(108, 92, 231, 0.1);
            transform: scale(1.02);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            background: var(--panel-bg);
            border-radius: 12px;
            padding: 20px;
            width: 90%;
            max-width: 1000px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: var(--shadow);
            position: relative;
            animation: slideUp 0.4s ease;
            color: var(--text-color);
        }
        
        .modal-content h3 {
            margin-bottom: 20px;
            color: var(--text-color);
            font-size: 20px;
            font-weight: 600;
        }
        
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .booking-table th, .booking-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            color: var(--text-color);
        }
        
        .booking-table th {
            background: var(--light);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            color: var(--dark);
        }
        
        .booking-table td {
            font-size: 14px;
        }
        
        .booking-table tr:hover {
            background: rgba(108, 92, 231, 0.1);
            transform: translateX(5px);
            transition: all 0.3s ease;
        }
        
        .no-bookings {
            text-align: center;
            color: var(--text-color);
            font-size: 16px;
            margin: 20px 0;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
            animation-delay: 0.2s;
        }
        
        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            transform: rotate(90deg);
            color: var(--secondary);
        }
        
        .error-message {
            color: #d63031;
            text-align: center;
            margin-bottom: 20px;
            display: none;
            animation: shake 0.5s ease;
        }
        
        .delete-button {
            background: #d63031;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .delete-button:hover {
            background: #b71c1c;
            transform: scale(1.05);
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInLeft {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
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
            }
            
            .toggle-sidebar {
                left: 20px;
            }
            
            .sidebar.collapsed ~ .main-content .toggle-sidebar {
                left: 20px;
            }
            
            .booking-table th, .booking-table td {
                font-size: 12px;
                padding: 8px;
            }
            
            .fc-daygrid-day {
                height: 60px;
                width: 60px;
            }
            
            .user-greeting {
                font-size: 16px;
            }
            
            .fc-daygrid-day-number {
                font-size: 12px;
            }
            
            .fc-daygrid-day.has-bookings .fc-daygrid-day-number::after {
                font-size: 8px;
                padding: 1px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="sidebar">
            <div class="user-greeting">Hey, <?php echo $username; ?>!</div>
            <div class="logo">
                <img src="images/logo.avif" alt="Grand Guardian Logo" class="grand-guardian-logo">
            </div>
            <div class="menu">
                <div class="menu-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="menu-item" onclick="window.location.href='wedding.php'">
                    <i class="fas fa-user-plus"></i>
                    <span>Wedding Form</span>
                </div>
                <div class="menu-item" onclick="window.location.href='edit_booking.html'">
                    <i class="fas fa-edit"></i>
                    <span>Edit Wedding Form</span>
                </div>
                <div class="menu-item" onclick="window.location.href='wedding_menu_upload.php'">
                    <i class="fa-regular fa-file"></i>
                    <span>Upload Wedding Menu</span>
                </div>
                <div class="menu-item" onclick="window.location.href='wedding_bill.html'">
                    <i class="fas fa-calculator"></i>
                    <span>Function Billing</span>
                </div>
                <div class="menu-item" onclick="window.location.href='supplier_payment.php'">
                    <i class="fas fa-receipt"></i>
                    <span>Supplier Payments</span>
                </div>
                <div class="menu-item" onclick="window.location.href='invoice.html'">
                    <i class="fas fa-credit-card-alt"></i>
                    <span>Invoice View</span>
                </div>
                <div class="menu-item" onclick="window.location.href='backoffice_room_detail.php'">
                    <i class="fa fa-calendar"></i>
                    <span>Room Availability</span>
                </div>
                <div class="menu-item" onclick="window.location.href='Boroom_bill.html'">
                    <i class="fas fa-university"></i>
                    <span>Wedding Room Billing</span>
                </div>
                <div class="menu-item" onclick="window.location.href='invoice_lookup.html'">
                    <i class="fas fa-credit-card-alt"></i>
                    <span>View Room Bill Invoice</span>
                </div>
                <div class="menu-item" onclick="window.location.href='functions_update.php'">
                    <i class="fas fa fa-check"></i>
                    <span>Update Function Invoices</span>
                </div>
                 <div class="menu-item" onclick="window.location.href='room_report.php'">
                    <i class="fas fa fa-check"></i>
                    <span>Update Room Invoices</span>
                </div>
                 <div class="menu-item" onclick="window.location.href='cancel_function_bill.php'">
                    <i class="fa-solid fa-ban"></i>
                    <span>Cancel Function Bill</span>
                </div>
                <div class="menu-item" onclick="window.location.href='cancel_bill.php'">
                    <i class="fa-solid fa-ban"></i>
                    <span>Cancel Room Bill</span>
                </div>
                <div class="menu-item" onclick="window.location.href='Backoffice_logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="header">
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
                    <h2>Booking Details</h2>
                    <div class="date">Today: <span id="current-date"></span></div>
                </div>
                
                <div class="error-message" id="calendar-error"></div>
                <div class="calendar-container">
                    <div id="calendar"></div>
                </div>
            </div>
            
            <div class="modal" id="booking-modal">
                <div class="modal-content">
                    <span class="close-modal">×</span>
                    <h3>Bookings for <span id="modal-date"></span></h3>
                    <div id="booking-details">
                        <table class="booking-table">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>Couple Name</th>
                                    <th>Full Name</th>
                                    <th>Contact No 1</th>
                                    <th>Contact No 2</th>
                                    <th>Pax</th>
                                    <th>Venue</th>
                                    <th>Time</th>
                                    <th>Groom Address</th>
                                    <th>Bride Address</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="booking-table-body"></tbody>
                        </table>
                        <div class="no-bookings" id="no-bookings-message" style="display: none;">
                            No bookings found for this date.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
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
        
        // Ripple effect for buttons
        function createRipple(event) {
            const button = event.currentTarget;
            const circle = document.createElement('span');
            const diameter = Math.max(button.clientWidth, button.clientHeight);
            const radius = diameter / 2;
            
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${event.clientX - button.getBoundingClientRect().left - radius}px`;
            circle.style.top = `${event.clientY - button.getBoundingClientRect().top - radius}px`;
            circle.classList.add('ripple');
            
            const ripple = button.getElementsByClassName('ripple')[0];
            if (ripple) {
                ripple.remove();
            }
            
            button.appendChild(circle);
        }
        
        const buttons = document.querySelectorAll('.toggle-sidebar, .theme-toggle, .delete-button');
        buttons.forEach(button => {
            button.addEventListener('click', createRipple);
        });
        
        // Set current date
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('en-US', options);
        
        // For mobile view
        if (window.innerWidth <= 768) {
            document.querySelector('.sidebar').classList.add('collapsed');
        }

        // Calendar initialization
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const errorEl = document.getElementById('calendar-error');

            // Create year and month dropdowns
            function createYearDropdown() {
                const currentYear = new Date().getFullYear();
                const yearRange = 10; // Show years from currentYear-5 to currentYear+5
                const select = document.createElement('select');
                select.id = 'year-select';
                select.style.padding = '5px';
                select.style.margin = '0 5px';
                select.style.borderRadius = '4px';
                select.style.background = 'rgba(255, 255, 255, 0.9)';
                select.style.color = 'black';
                select.style.border = 'none';
                
                for (let year = currentYear - 5; year <= currentYear + 5; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    if (year === currentYear) option.selected = true;
                    select.appendChild(option);
                }
                return select;
            }

            function createMonthDropdown() {
                const months = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];
                const currentMonth = new Date().getMonth();
                const select = document.createElement('select');
                select.id = 'month-select';
                select.style.padding = '5px';
                select.style.margin = '0 5px';
                select.style.borderRadius = '4px';
                select.style.background = 'rgba(255, 255, 255, 0.9)';
                select.style.color = 'black';
                select.style.border = 'none';
                
                months.forEach((month, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = month;
                    if (index === currentMonth) option.selected = true;
                    select.appendChild(option);
                });
                return select;
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'yearSelect,monthSelect'
                },
                customButtons: {
                    yearSelect: {
                        text: '',
                        click: function() {
                            // Handled by dropdown change event
                        }
                    },
                    monthSelect: {
                        text: '',
                        click: function() {
                            // Handled by dropdown change event
                        }
                    }
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const start = new Date(fetchInfo.startStr);
                    const end = new Date(fetchInfo.endStr);
                    const currentMonthStart = new Date(start.getFullYear(), start.getMonth(), 1);
                    const currentMonthEnd = new Date(end.getFullYear(), end.getMonth() + 1, 0);
                    
                    fetch(`api.php?action=get_bookings&start=${currentMonthStart.toISOString()}&end=${currentMonthEnd.toISOString()}`)
                        .then(response => {
                            if (!response.ok) {
                                console.error('Fetch error:', response.status, response.statusText);
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                errorEl.textContent = `Error: ${data.error}`;
                                errorEl.style.display = 'block';
                                successCallback([]);
                            } else {
                                errorEl.style.display = 'none';
                                successCallback(data.map(event => ({
                                    title: event.booking_count > 0 ? `${event.booking_count} Bookings` : new Date(event.start).getDate(),
                                    start: event.start,
                                    booking_count: event.booking_count,
                                    classNames: event.booking_count > 0 ? ['has-bookings'] : [],
                                    extendedProps: { booking_count: event.booking_count }
                                })));
                            }
                        })
                        .catch(error => {
                            errorEl.textContent = 'Failed to load bookings. Please try again.';
                            errorEl.style.display = 'block';
                            console.error('Error fetching bookings:', error);
                            failureCallback(error);
                        });
                },
                eventDidMount: function(info) {
                    if (info.event.extendedProps.booking_count > 0) {
                        const dayNumberEl = info.el.querySelector('.fc-daygrid-day-number');
                        if (dayNumberEl) {
                            dayNumberEl.setAttribute('data-booking-count', info.event.extendedProps.booking_count);
                        }
                    }
                },
                eventContent: function(arg) {
                    return {
                        html: `<div class="fc-daygrid-day-number">${arg.event.title}</div>`
                    };
                },
                dateClick: function(info) {
                    fetchBookingDetails(info.dateStr);
                },
                datesSet: function(info) {
                    // Update dropdowns when calendar view changes
                    const yearSelect = document.getElementById('year-select');
                    const monthSelect = document.getElementById('month-select');
                    if (yearSelect && monthSelect) {
                        yearSelect.value = info.view.currentStart.getFullYear();
                        monthSelect.value = info.view.currentStart.getMonth();
                    }
                }
            });

            // Render custom dropdowns after calendar is initialized
            calendar.render();

            // Replace custom button text with dropdowns
            const yearButton = document.querySelector('.fc-yearSelect-button');
            const monthButton = document.querySelector('.fc-monthSelect-button');
            if (yearButton && monthButton) {
                const yearDropdown = createYearDropdown();
                const monthDropdown = createMonthDropdown();
                yearButton.innerHTML = '';
                monthButton.innerHTML = '';
                yearButton.appendChild(yearDropdown);
                monthButton.appendChild(monthDropdown);

                // Handle year and month selection
                yearDropdown.addEventListener('change', function() {
                    const selectedYear = parseInt(this.value);
                    const selectedMonth = parseInt(document.getElementById('month-select').value);
                    calendar.gotoDate(new Date(selectedYear, selectedMonth, 1));
                });

                monthDropdown.addEventListener('change', function() {
                    const selectedMonth = parseInt(this.value);
                    const selectedYear = parseInt(document.getElementById('year-select').value);
                    calendar.gotoDate(new Date(selectedYear, selectedMonth, 1));
                });
            }
        });

        // Fetch booking details for modal
        function fetchBookingDetails(dateStr) {
            const modal = document.getElementById('booking-modal');
            const tableBody = document.getElementById('booking-table-body');
            const noBookingsMessage = document.getElementById('no-bookings-message');
            const errorEl = document.getElementById('calendar-error');
            const modalDate = document.getElementById('modal-date');
            
            const date = new Date(dateStr);
            const formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            modalDate.textContent = formattedDate;
            
            fetch(`api.php?action=get_booking_details&date=${dateStr}`)
                .then(response => {
                    if (!response.ok) {
                        console.error('Fetch booking details error:', response.status, response.statusText);
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(bookings => {
                    tableBody.innerHTML = '';
                    if (bookings && bookings.length > 0) {
                        bookings.forEach(booking => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${booking.booking_reference || 'N/A'}</td>
                                <td>${booking.couple_name || 'Unknown'}</td>
                                <td>${booking.full_name || 'N/A'}</td>
                                <td>${booking.contact_no1 || 'N/A'}</td>
                                <td>${booking.contact_no2 || 'N/A'}</td>
                                <td>${booking.no_of_pax || '0'}</td>
                                <td>${booking.venue_name || 'Unknown'}</td>
                                <td>${booking.time || 'N/A'}</td>
                                <td>${booking.groom_address || 'N/A'}</td>
                                <td>${booking.bride_address || 'N/A'}</td>
                                <td><button class="delete-button" data-booking-reference="${booking.booking_reference}" data-date="${dateStr}" aria-label="Delete booking ${booking.booking_reference}">Delete</button></td>
                            `;
                            tableBody.appendChild(row);
                        });
                        noBookingsMessage.style.display = 'none';
                        errorEl.style.display = 'none';
                        modal.style.display = 'flex';

                        // Add event listeners for delete buttons
                        document.querySelectorAll('.delete-button').forEach(button => {
                            button.addEventListener('click', function() {
                                const bookingReference = this.getAttribute('data-booking-reference');
                                const dateStr = this.getAttribute('data-date');
                                console.log('Attempting to delete booking:', bookingReference, 'for date:', dateStr);
                                deleteBooking(bookingReference, dateStr, calendar);
                            });
                        });
                    } else {
                        noBookingsMessage.style.display = 'block';
                        errorEl.textContent = 'No bookings found for this date.';
                        errorEl.style.display = 'block';
                        modal.style.display = 'flex';
                    }
                })
                .catch(error => {
                    errorEl.textContent = 'Failed to load booking details. Please try again.';
                    errorEl.style.display = 'block';
                    console.error('Error fetching booking details:', error);
                    modal.style.display = 'none';
                });
        }

        // Delete booking function
        function deleteBooking(bookingReference, dateStr, calendar) {
            const errorEl = document.getElementById('calendar-error');
            const modal = document.getElementById('booking-modal');
            if (!bookingReference) {
                errorEl.textContent = 'Error: Booking reference is missing.';
                errorEl.style.display = 'block';
                return;
            }
            const confirmDelete = confirm(`Are you sure you want to delete booking ${bookingReference}?`);
            if (!confirmDelete) return;

            fetch(`api.php?action=delete_booking&booking_reference=${bookingReference}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    console.log('Delete response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`Network response was not ok: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Delete response data:', data);
                    if (data.success) {
                        errorEl.textContent = `Booking ${bookingReference} deleted successfully.`;
                        errorEl.style.color = 'var(--success)';
                        errorEl.style.display = 'block';
                        setTimeout(() => { errorEl.style.display = 'none'; }, 3000);
                        // Refresh the modal and calendar
                        fetchBookingDetails(dateStr);
                        calendar.refetchEvents();
                    } else {
                        errorEl.textContent = `Error: ${data.error || 'Failed to delete booking.'}`;
                        errorEl.style.display = 'block';
                    }
                })
                .catch(error => {
                    errorEl.textContent = 'Failed to delete booking. Please try again.';
                    errorEl.style.display = 'block';
                    console.error('Error deleting booking:', error);
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
    </script>
</body>
</html>