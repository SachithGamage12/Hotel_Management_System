<?php
session_start();
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin');

$servername = "localhost";
$username_db = "hotelgrandguardi_root";
$password_db = "Sun123flower@";
$dbname     = "hotelgrandguardi_wedding_bliss";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    // === 1. All Room Numbers + Location Mapping ===
    $all_rooms = [
        '201' => 'Paragon', '202' => 'Paragon', '203' => 'Paragon', '204' => 'Paragon', '205' => 'Paragon', '206' => 'Paragon',
        '301' => 'Sky',     '302' => 'Sky',     '303' => 'Sky',     '304' => 'Sky',     '305' => 'Sky',     '306' => 'Sky',     '307' => 'Sky'
    ];

    // === 2. Today's Bookings (Check-in / Check-out) ===
    $today_bookings_query = "
        SELECT rb.*,
               CASE 
                   WHEN rb.check_in <= NOW() AND (rb.check_out IS NULL OR rb.check_out >= NOW()) THEN 'Checked In'
                   WHEN rb.check_in > NOW() THEN 'Scheduled'
                   WHEN rb.check_out < NOW() THEN 'Checked Out'
                   ELSE 'Unknown'
               END AS status
        FROM room_bookings rb
        WHERE DATE(rb.check_in) = :today OR DATE(rb.check_out) = :today
        ORDER BY rb.check_in DESC
    ";
    $stmt = $conn->prepare($today_bookings_query);
    $stmt->execute(['today' => $today]);
    $today_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 3. Tomorrow's Arrivals ===
    $tomorrow_bookings_query = "
        SELECT rb.*
        FROM room_bookings rb
        WHERE DATE(rb.check_in) = :tomorrow
        ORDER BY rb.check_in ASC
    ";
    $stmt = $conn->prepare($tomorrow_bookings_query);
    $stmt->execute(['tomorrow' => $tomorrow]);
    $tomorrow_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 4. Upcoming 30 Days Bookings ===
    $future_bookings_query = "
        SELECT rb.*
        FROM room_bookings rb
        WHERE rb.check_in > NOW() AND rb.check_in <= DATE_ADD(NOW(), INTERVAL 30 DAY)
        ORDER BY rb.check_in ASC
    ";
    $stmt = $conn->prepare($future_bookings_query);
    $stmt->execute();
    $future_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 5. Available Rooms Today ===
    $occupied_today = array_column($today_bookings, 'room_number');
    $available_today = array_diff_key($all_rooms, array_flip($occupied_today));

    // === 6. Available Rooms Tomorrow ===
    $occupied_tomorrow = array_column($tomorrow_bookings, 'room_number');
    $available_tomorrow = array_diff_key($all_rooms, array_flip($occupied_tomorrow));

} catch (PDOException $e) {
    $error_message = "DB Error: " . $e->getMessage();
    $today_bookings = $tomorrow_bookings = $future_bookings = $available_today = $available_tomorrow = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Room Bookings - iPad</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root{--p:#6c5ce7;--s:#e84393;--d:#2d3436;--l:#f5f6fa;--bg:#f9f9f9;--pan:#fff;--txt:#2d3436;--warn:#f39c12;--success:#00b894;--danger:#e74c3c;}
        [data-theme="dark"]{--bg:#1a1a1a;--pan:#2d2d2d;--txt:#fff;}
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
        body{background:var(--bg);color:var(--txt);padding:15px 10px;min-height:100vh;}
        .container{max-width:1024px;margin:auto;background:var(--pan);border-radius:12px;padding:20px;
                  box-shadow:0 8px 25px rgba(0,0,0,.1);}
        .header{text-align:center;margin-bottom:20px;}
        .clock{font-family:'Orbitron',sans-serif;font-size:2.2rem;color:var(--p);background:rgba(255,255,255,.9);
                padding:8px 16px;border-radius:8px;display:inline-block;letter-spacing:1px;}
        .date{font-size:1rem;margin-top:4px;color:var(--txt);}
        .section{margin-bottom:25px;}
        .section h3{font-size:1.3rem;margin-bottom:12px;color:var(--p);border-bottom:2px solid var(--p);padding-bottom:5px;}
        table{width:100%;border-collapse:collapse;font-size:.82rem;}
        th{background:var(--p);color:#fff;padding:7px 5px;text-align:left;font-weight:500;}
        td{padding:7px 5px;border-bottom:1px solid rgba(0,0,0,.1);vertical-align:top;}
        tr:hover{background:rgba(0,0,0,.05);}
        .no-data{text-align:center;font-style:italic;color:#777;padding:15px;}
        .badge{padding:2px 6px;border-radius:4px;font-size:.65rem;color:#fff;}
        .badge-in{background:var(--success);}
        .badge-out{background:var(--danger);}
        .badge-scheduled{background:var(--warn);}
        .badge-day{background:#3498db;}
        .badge-night{background:#2c3e50;}
        .available-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:10px;margin-top:10px;}
        .room-card{background:var(--success);color:#fff;padding:10px;border-radius:8px;text-align:center;font-weight:600;font-size:.9rem;}
        .room-card.paragon{background:#9b59b6;}
        .room-card.sky{background:#2980b9;}
        @media (orientation:landscape){
            table{font-size:.85rem;}
            .available-grid{grid-template-columns:repeat(auto-fill,minmax(120px,1fr));}
        }
    </style>
</head>
<body data-theme="light">
<div class="container">

    <!-- Header -->
    <div class="header">
        <div class="clock" id="clock"></div>
        <div class="date" id="today"></div>
    </div>

    <?php if (!empty($error_message)): ?>
        <p style="color:#d63031;text-align:center;font-weight:600;"><?=$error_message?></p>
    <?php endif; ?>

    <!-- Today's Activity -->
    <div class="section">
        <h3>Today's Check-ins / Check-outs</h3>
        <?php if ($today_bookings): ?>
            <table>
                <thead><tr>
                    <th>Room</th><th>Guest</th><th>Pax</th><th>Phone</th>
                    <th>Check-in</th><th>Check-out</th><th>Slot</th><th>Type</th><th>Function</th><th>Status</th>
                </tr></thead>
                <tbody>
                <?php foreach ($today_bookings as $b): ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($b['room_number'])?></strong></td>
                        <td><?=htmlspecialchars($b['guest_name'])?></td>
                        <td><?=$b['pax']?></td>
                        <td><?=htmlspecialchars($b['telephone'])?></td>
                        <td><?=date('g:i A', strtotime($b['check_in']))?></td>
                        <td><?= $b['check_out'] ? date('g:i A', strtotime($b['check_out'])) : '—' ?></td>
                        <td><span class="badge <?= $b['time_slot']=='Day'?'badge-day':'badge-night' ?>"><?= $b['time_slot'] ?></span></td>
                        <td><span class="badge <?= $b['booking_type']=='day'?'badge-day':'badge-night' ?>"><?= ucfirst($b['booking_type']) ?></span></td>
                        <td><?=htmlspecialchars($b['function_type'] ?: '—')?></td>
                        <td>
                            <?php
                            $status = $b['status'];
                            $class = $status === 'Checked In' ? 'badge-in' : ($status === 'Checked Out' ? 'badge-out' : 'badge-scheduled');
                            ?>
                            <span class="badge <?=$class?>"><?=$status?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No check-ins or check-outs today.</p>
        <?php endif; ?>

        <!-- Available Rooms Today -->
        <?php if (!empty($available_today)): ?>
            <div style="margin-top:15px;">
                <strong>Available Rooms Today:</strong>
                <div class="available-grid">
                    <?php foreach ($available_today as $room => $location): ?>
                        <div class="room-card <?= $location === 'Paragon' ? 'paragon' : 'sky' ?>">
                            <?= $room ?> <small>(<?= $location ?>)</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tomorrow's Arrivals -->
    <div class="section">
        <h3>Tomorrow's Arrivals</h3>
        <?php if ($tomorrow_bookings): ?>
            <table>
                <thead><tr>
                    <th>Room</th><th>Guest</th><th>Pax</th><th>Check-in</th><th>Slot</th><th>Type</th><th>Function</th>
                </tr></thead>
                <tbody>
                <?php foreach ($tomorrow_bookings as $b): ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($b['room_number'])?></strong></td>
                        <td><?=htmlspecialchars($b['guest_name'])?></td>
                        <td><?=$b['pax']?></td>
                        <td><?=date('g:i A', strtotime($b['check_in']))?></td>
                        <td><span class="badge <?= $b['time_slot']=='Day'?'badge-day':'badge-night' ?>"><?= $b['time_slot'] ?></span></td>
                        <td><span class="badge <?= $b['booking_type']=='day'?'badge-day':'badge-night' ?>"><?= ucfirst($b['booking_type']) ?></span></td>
                        <td><?=htmlspecialchars($b['function_type'] ?: '—')?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No arrivals tomorrow.</p>
        <?php endif; ?>

        <!-- Available Rooms Tomorrow -->
        <?php if (!empty($available_tomorrow)): ?>
            <div style="margin-top:15px;">
                <strong>Available Rooms Tomorrow:</strong>
                <div class="available-grid">
                    <?php foreach ($available_tomorrow as $room => $location): ?>
                        <div class="room-card <?= $location === 'Paragon' ? 'paragon' : 'sky' ?>">
                            <?= $room ?> <small>(<?= $location ?>)</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upcoming 30 Days -->
    <div class="section">
        <h3>Upcoming Bookings (Next 30 Days)</h3>
        <?php if ($future_bookings): ?>
            <table>
                <thead><tr>
                    <th>Date</th><th>Room</th><th>Guest</th><th>Pax</th><th>Check-in</th><th>Slot</th><th>Type</th>
                </tr></thead>
                <tbody>
                <?php foreach ($future_bookings as $b): ?>
                    <tr>
                        <td><?=date('M j', strtotime($b['check_in']))?></td>
                        <td><strong><?=htmlspecialchars($b['room_number'])?></strong></td>
                        <td><?=htmlspecialchars($b['guest_name'])?></td>
                        <td><?=$b['pax']?></td>
                        <td><?=date('g:i A', strtotime($b['check_in']))?></td>
                        <td><span class="badge <?= $b['time_slot']=='Day'?'badge-day':'badge-night' ?>"><?= $b['time_slot'] ?></span></td>
                        <td><span class="badge <?= $b['booking_type']=='day'?'badge-day':'badge-night' ?>"><?= ucfirst($b['booking_type']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No upcoming bookings in the next 30 days.</p>
        <?php endif; ?>
    </div>

</div>

<script>
// Clock
function updateClock(){
    const n = new Date();
    const o = {timeZone:'Asia/Colombo',hour12:true,hour:'2-digit',minute:'2-digit',second:'2-digit'};
    const [h,m,sP] = n.toLocaleTimeString('en-US',o).split(':');
    const [s,p] = sP.split(' ');
    document.getElementById('clock').innerHTML = `${h}:${m}:<span style="animation:tick 1s infinite;">${s}</span> ${p}`;
}
updateClock(); setInterval(updateClock,1000);
document.getElementById('today').textContent = new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});

// Dark Mode Toggle
document.body.addEventListener('dblclick', () => {
    const isDark = document.body.dataset.theme === 'dark';
    document.body.dataset.theme = isDark ? 'light' : 'dark';
});
</script>

</body>
</html>