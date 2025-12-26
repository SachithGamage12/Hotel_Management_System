<?php
// search_grc.php - Search for GRC by number and display/print details

// Database connection
$servername = "localhost";
$username = "hotelgrandguardi_root"; // Replace with your DB username
$password = "Sun123flower@"; // Replace with your DB password
$dbname = "hotelgrandguardi_wedding_bliss"; // Replace with your DB name

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle search
$grc_number = isset($_GET['grc_number']) ? trim($_GET['grc_number']) : '';
$guest_data = null;
$rooms = [];
$meal_plan_name = '-';
$room_types = [];

if (!empty($grc_number)) {
    // Fetch guest details from guests table
    $stmt = $conn->prepare("SELECT * FROM guests WHERE grc_number = :grc_number LIMIT 1");
    $stmt->execute([':grc_number' => $grc_number]);
    $guest_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($guest_data) {
        // Fetch room details from guest_rooms table
        $stmt_rooms = $conn->prepare("SELECT gr.room_type_id, gr.room_number, gr.ac_type, gr.room_rate, rt.name AS room_type_name 
                                      FROM guest_rooms gr 
                                      LEFT JOIN room_types rt ON gr.room_type_id = rt.id 
                                      WHERE gr.grc_number = :grc_number");
        $stmt_rooms->execute([':grc_number' => $grc_number]);
        $rooms = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);

        // Fetch meal plan name
        if (!empty($guest_data['meal_plan_id'])) {
            $stmt_meal = $conn->prepare("SELECT name FROM meal_plans WHERE id = :meal_plan_id");
            $stmt_meal->execute([':meal_plan_id' => $guest_data['meal_plan_id']]);
            $meal_plan = $stmt_meal->fetch(PDO::FETCH_ASSOC);
            $meal_plan_name = $meal_plan ? $meal_plan['name'] : '-';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Guest Registration Card</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      padding: 2rem;
    }
    .search-box {
      background: white;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
    }
    .card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
      width: 190mm;
      height: 277mm;
      padding: 0;
      border: none;
      display: <?php echo $guest_data ? 'flex' : 'none'; ?>;
      flex-direction: column;
      overflow: hidden;
      position: relative;
      margin: 0 auto;
    }
    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, #d4af37, #ffd700, #d4af37);
    }
    .header {
      text-align: center;
      padding: 1rem 1.5rem 0.75rem;
      background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
      margin-bottom: 0;
      position: relative;
    }
    .header::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 0;
      border-left: 20px solid transparent;
      border-right: 20px solid transparent;
      border-top: 10px solid #2a5298;
    }
    .logo {
      width: 80px;
      height: 80px;
      margin: 0 auto 0.5rem;
      background: #ffffff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      border: 2px solid #d4af37;
    }
    .logo img {
      width: 55px;
      height: 55px;
      object-fit: contain;
    }
    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      font-weight: 700;
      color: #ffffff;
      margin: 0 0 0.25rem 0;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    .subheader {
      font-size: 0.8rem;
      color: #e8f2ff;
      margin: 0;
      font-weight: 300;
      letter-spacing: 1px;
      text-transform: uppercase;
    }
    .content-area {
      padding: 1rem 1.5rem 1rem;
      flex-grow: 1;
    }
    .welcome-text {
      text-align: center;
      color: #666;
      font-size: 0.75rem;
      margin-bottom: 1rem;
      font-style: italic;
      padding: 0.5rem;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
      border-radius: 6px;
      border-left: 3px solid #d4af37;
    }
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin-top: 1rem;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    tr {
      height: 1.75rem;
    }
    tr:nth-child(even) {
      background: #f8f9fa;
    }
    tr:nth-child(odd) {
      background: #ffffff;
    }
    td {
      border: 1px solid #e9ecef;
      padding: 0.5rem 0.75rem;
      font-size: 0.8rem;
      color: #333;
      border-top: none;
      border-bottom: none;
    }
    tr:first-child td {
      border-top: 1px solid #e9ecef;
    }
    tr:last-child td {
      border-bottom: 1px solid #e9ecef;
    }
    td:first-child {
      font-weight: 600;
      color: #1e3c72;
      width: 35%;
      background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
      border-right: 2px solid #d4af37;
    }
    td:last-child {
      border-right: 1px solid #e9ecef;
    }
    td:first-child:first-of-type {
      border-left: 1px solid #e9ecef;
    }
    td span {
      font-size: 0.8rem;
      font-weight: 500;
    }
    .checkin-checkout {
      display: flex;
      justify-content: space-between;
      padding: 0.75rem;
      background: linear-gradient(135deg, #1e3c72, #2a5298);
      color: #ffffff;
      margin-top: 1rem;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .checkin-checkout div {
      font-size: 0.8rem;
      font-weight: 500;
      text-align: center;
      flex: 1;
    }
    .checkin-checkout div:first-child {
      border-right: 1px solid rgba(255, 255, 255, 0.3);
      padding-right: 0.75rem;
    }
    .checkin-checkout div:last-child {
      padding-left: 0.75rem;
    }
    .signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 1.5rem;
      margin-bottom: 1rem;
      gap: 1.5rem;
    }
    .signature-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 45%;
      padding: 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      border: 1px solid #e9ecef;
    }
    .signature-line {
      border-top: 2px dotted #1e3c72;
      width: 100%;
      margin-bottom: 0.25rem;
      height: 1.5rem;
    }
    .signature-text {
      font-size: 0.85rem;
      color: #1e3c72;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .print-button {
      background: linear-gradient(135deg, #d4af37, #ffd700);
      color: #1e3c72;
      padding: 0.75rem 2.5rem;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.3s ease;
      align-self: center;
      margin-top: 1rem;
      border: none;
      text-transform: uppercase;
      letter-spacing: 1px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    .print-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
      background: linear-gradient(135deg, #ffd700, #d4af37);
    }
    .decorative-border {
      height: 2px;
      background: linear-gradient(90deg, #d4af37, #ffd700, #d4af37);
      margin: 1rem 0;
    }
    .error-message {
      text-align: center;
      color: #dc2626;
      font-size: 1rem;
      padding: 1rem;
      background: #fef2f2;
      border-radius: 8px;
      margin-top: 2rem;
    }
    @media print {
      @page {
        size: A4;
        margin: 10mm;
      }
      body {
        background: #ffffff;
        padding: 0;
        margin: 0;
      }
      .search-box, .error-message {
        display: none;
      }
      .card {
        box-shadow: none;
        border: 1px solid #000;
        border-radius: 0;
        padding: 0;
        width: 190mm;
        height: 277mm;
        margin: 0;
        page-break-inside: avoid;
        display: block;
      }
      .card::before {
        background: #000;
      }
      .header {
        background: none;
        border-bottom: 2px solid #000;
        margin-bottom: 0.5rem;
        padding: 1rem 1.5rem 0.5rem;
      }
      .header::after {
        display: none;
      }
      .logo {
        background: #ffffff;
        border: 2px solid #000;
        box-shadow: none;
      }
      h1, .subheader {
        color: #000;
        text-shadow: none;
      }
      .welcome-text {
        background: #f5f5f5;
        border-left: 4px solid #000;
      }
      table {
        box-shadow: none;
        border: 1px solid #000;
      }
      table td {
        border: 1px solid #000;
        color: #000;
        font-size: 9pt;
        padding: 0.4rem 0.6rem;
      }
      tr {
        height: 1.5rem;
      }
      td:first-child {
        color: #000;
        background: #f5f5f5;
        border-right: 2px solid #000;
      }
      .checkin-checkout {
        border: 2px solid #000;
        background: #f5f5f5;
        color: #000;
        border-radius: 0;
        box-shadow: none;
      }
      .signature-box {
        background: #ffffff;
        border: 1px solid #000;
        border-radius: 0;
      }
      .signature-line {
        border-top: 2px dotted #000;
      }
      .signature-text {
        color: #000;
      }
      .print-button {
        display: none;
      }
      .decorative-border {
        background: #000;
      }
    }
    @media (max-width: 640px) {
      .card {
        width: 100%;
        min-height: auto;
        border-radius: 12px;
      }
      .header {
        padding: 1.5rem 1rem 1rem;
      }
      .logo {
        width: 80px;
        height: 80px;
      }
      .logo img {
        width: 60px;
        height: 60px;
      }
      h1 {
        font-size: 1.5rem;
      }
      .subheader {
        font-size: 0.8rem;
      }
      .content-area {
        padding: 1.5rem 1rem 1rem;
      }
      table td {
        font-size: 0.8rem;
        padding: 0.75rem;
      }
      tr {
        height: 2rem;
      }
      .checkin-checkout {
        margin-top: 1.5rem;
        flex-direction: column;
        gap: 0.5rem;
      }
      .checkin-checkout div:first-child {
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0 0 0.5rem 0;
      }
      .checkin-checkout div:last-child {
        padding: 0.5rem 0 0 0;
      }
      .signature-box {
        width: 48%;
        padding: 0.75rem;
      }
      .signature-text {
        font-size: 0.75rem;
      }
      .signatures {
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        gap: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="text-3xl font-bold text-center mb-8 text-white">Search Guest Registration Card</h1>
    
    <!-- Search Form -->
    <div class="search-box">
      <form method="GET">
        <div class="flex gap-4">
          <input type="text" name="grc_number" value="<?php echo htmlspecialchars($grc_number); ?>" placeholder="Enter GRC Number" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
          <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Search</button>
        </div>
      </form>
    </div>

    <?php if ($guest_data): ?>
    <!-- Guest Details Card -->
    <div class="card">
      <div class="header">
        <div class="logo">
          <img src="images/logo.avif" alt="Hotel Logo">
        </div>
        <h1>Guest Registration</h1>
        <div class="subheader">Hospitality Excellence</div>
      </div>
      
      <div class="content-area">
        <div class="welcome-text">
          Thank you for choosing our hotel. We are delighted to have you with us.
        </div>
        
        <div class="decorative-border"></div>
        
        <table>
          <tr><td>GRC Number</td><td><span><?php echo htmlspecialchars($guest_data['grc_number'] ?? '-'); ?></span></td></tr>
          <tr><td>Guest Name</td><td><span><?php echo htmlspecialchars($guest_data['guest_name'] ?? '-'); ?></span></td></tr>
          <tr><td>Contact Number</td><td><span><?php echo htmlspecialchars($guest_data['contact_number'] ?? '-'); ?></span></td></tr>
          <tr><td>Email Address</td><td><span><?php echo htmlspecialchars($guest_data['email'] ?? '-'); ?></span></td></tr>
          <tr><td>Address</td><td><span><?php echo htmlspecialchars($guest_data['address'] ?? '-'); ?></span></td></tr>
          <tr><td>ID Type</td><td><span><?php echo htmlspecialchars($guest_data['id_type'] ?? '-'); ?></span></td></tr>
          <tr><td>ID Number</td><td><span><?php echo htmlspecialchars($guest_data['id_number'] ?? '-'); ?></span></td></tr>
          <tr><td>Other Guest 1 Name</td><td><span><?php echo htmlspecialchars($guest_data['other_guest_name_1'] ?? '-'); ?></span></td></tr>
          <tr><td>Other Guest 1 NIC</td><td><span><?php echo htmlspecialchars($guest_data['other_guest_nic_1'] ?? '-'); ?></span></td></tr>
          <tr><td>Other Guest 2 Name</td><td><span><?php echo htmlspecialchars($guest_data['other_guest_name_2'] ?? '-'); ?></span></td></tr>
          <tr><td>Other Guest 2 NIC</td><td><span><?php echo htmlspecialchars($guest_data['other_guest_nic_2'] ?? '-'); ?></span></td></tr>
          <tr><td>Other Guest 3 Name</td><td><span><?php echo htmlspecialchars($guest_data['other_guest_name_3'] ?? '-'); ?></span></td></tr>
          <tr><td>Other Guest 3 NIC</td><td><span><?php echo htmlspecialchars($guest_data['other_guest_nic_3'] ?? '-'); ?></span></td></tr>
          <tr><td>Check-In</td><td><span><?php echo htmlspecialchars(($guest_data['check_in_date'] ? $guest_data['check_in_date'] . ' ' . $guest_data['check_in_time'] . ' ' . $guest_data['check_in_time_am_pm'] : '-')); ?></span></td></tr>
          <tr><td>Check-Out</td><td><span><?php echo htmlspecialchars(($guest_data['check_out_date'] ? $guest_data['check_out_date'] . ' ' . $guest_data['check_out_time'] . ' ' . $guest_data['check_out_time_am_pm'] : '-')); ?></span></td></tr>
          <tr><td>Room Details</td><td><span><?php 
            echo $rooms ? implode('<br>', array_map(function($room) {
              return 'Room No. ' . htmlspecialchars($room['room_number']) . ', Type: ' . htmlspecialchars($room['room_type_name'] ?? '-') . ', Rate: ' . htmlspecialchars($room['room_rate'] ?? '-');
            }, $rooms)) : '-';
          ?></span></td></tr>
          <tr><td>Meal Plan</td><td><span><?php echo htmlspecialchars($meal_plan_name); ?></span></td></tr>
          <tr><td>Number of Guests</td><td><span><?php echo htmlspecialchars($guest_data['number_of_pax'] ?? '-'); ?></span></td></tr>
          <tr><td>Special Requests</td><td><span><?php echo htmlspecialchars($guest_data['remarks'] ?? '-'); ?></span></td></tr>
        </table>
        
        <div class="decorative-border"></div>
        
        <div class="checkin-checkout">
          <div>
            <div style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 0.25rem;">CHECK-IN TIME</div>
            <div style="font-size: 1.1rem; font-weight: 600;">2:00 PM</div>
          </div>
          <div>
            <div style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 0.25rem;">CHECK-OUT TIME</div>
            <div style="font-size: 1.1rem; font-weight: 600;">12:00 PM</div>
          </div>
        </div>

        <div class="signatures">
          <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">Receptionist Signature</div>
          </div>
          <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">Guest Signature</div>
          </div>
        </div>
        
        <button class="print-button" onclick="window.print()">Print Registration Card</button>
      </div>
    </div>
    <?php else: ?>
    <?php if (!empty($grc_number)): ?>
    <div class="error-message">
      <p>No GRC found with number: <?php echo htmlspecialchars($grc_number); ?></p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <script>
    // Auto-focus search input
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.querySelector('input[name="grc_number"]');
      if (searchInput) {
        searchInput.focus();
      }
    });
  </script>
</body>
</html>