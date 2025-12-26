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

    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $today    = date('Y-m-d');

    // === 1. Tomorrow's Bookings ===
    $tomorrow_query = "
        SELECT wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax,
               ft.name AS function_name, v.name AS venue_name, wb.day_or_night,
               COALESCE(SUM(p.total_amount),0) AS total_amount
        FROM wedding_bookings wb
        LEFT JOIN function_types ft ON wb.function_type_id = ft.id
        LEFT JOIN venues v ON wb.venue_id = v.id
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date = :tomorrow
        GROUP BY wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax, ft.name, v.name, wb.day_or_night
        UNION
        SELECT wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax,
               ft.name AS function_name, v.name AS venue_name, wbh.day_or_night,
               COALESCE(SUM(p.total_amount),0) AS total_amount
        FROM wedding_bookings_history wbh
        LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
        LEFT JOIN venues v ON wbh.venue_id = v.id
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date = :tomorrow
        GROUP BY wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax, ft.name, v.name, wbh.day_or_night
    ";
    $stmt = $conn->prepare($tomorrow_query);
    $stmt->execute(['tomorrow' => $tomorrow]);
    $tomorrow_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 2. Today's Bookings ===
    $today_query = "
        SELECT wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax,
               ft.name AS function_name, v.name AS venue_name, wb.day_or_night,
               wb.time_from, wb.time_from_am_pm, wb.time_to, wb.time_to_am_pm,
               COALESCE(SUM(p.total_amount),0) AS total_amount
        FROM wedding_bookings wb
        LEFT JOIN function_types ft ON wb.function_type_id = ft.id
        LEFT JOIN venues v ON wb.venue_id = v.id
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date = :today
        GROUP BY wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax,
                 ft.name, v.name, wb.day_or_night, wb.time_from, wb.time_from_am_pm, wb.time_to, wb.time_to_am_pm
        UNION
        SELECT wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax,
               ft.name AS function_name, v.name AS venue_name, wbh.day_or_night,
               wbh.time_from, wbh.time_from_am_pm, wbh.time_to, wbh.time_to_am_pm,
               COALESCE(SUM(p.total_amount),0) AS total_amount
        FROM wedding_bookings_history wbh
        LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
        LEFT JOIN venues v ON wbh.venue_id = v.id
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date = :today
        GROUP BY wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax,
                 ft.name, v.name, wbh.day_or_night, wbh.time_from, wbh.time_from_am_pm, wbh.time_to, wbh.time_to_am_pm
    ";
    $stmt = $conn->prepare($today_query);
    $stmt->execute(['today' => $today]);
    $today_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 3. Future Bookings (Next 60 Days) ===
    $future_query = "
        SELECT wb.booking_date, wb.booking_reference, wb.full_name, wb.couple_name, wb.no_of_pax,
               ft.name AS function_name, v.name AS venue_name, wb.day_or_night,
               COALESCE(SUM(p.total_amount),0) AS total_amount
        FROM wedding_bookings wb
        LEFT JOIN function_types ft ON wb.function_type_id = ft.id
        LEFT JOIN venues v ON wb.venue_id = v.id
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date > CURDATE() AND wb.booking_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
        GROUP BY wb.booking_reference
        UNION
        SELECT wbh.booking_date, wbh.booking_reference, wbh.full_name, wbh.couple_name, wbh.no_of_pax,
               ft.name AS function_name, v.name AS venue_name, wbh.day_or_night,
               COALESCE(SUM(p.total_amount),0) AS total_amount
        FROM wedding_bookings_history wbh
        LEFT JOIN function_types ft ON wbh.function_type_id = ft.id
        LEFT JOIN venues v ON wbh.venue_id = v.id
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date > CURDATE() AND wbh.booking_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
        GROUP BY wbh.booking_reference
        ORDER BY booking_date ASC
    ";
    $stmt = $conn->prepare($future_query);
    $stmt->execute();
    $future_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 4. Daily Watch (Next 7 Days - Projected Revenue) ===
    $daily_watch_query = "
        SELECT DATE(wb.booking_date) AS booking_date,
               COALESCE(SUM(p.total_amount), 0) AS total_paid,
               COUNT(*) AS bookings_count
        FROM wedding_bookings wb
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(wb.booking_date)
        UNION
        SELECT DATE(wbh.booking_date), COALESCE(SUM(p.total_amount), 0), COUNT(*)
        FROM wedding_bookings_history wbh
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(wbh.booking_date)
        ORDER BY booking_date
    ";
    $stmt = $conn->prepare($daily_watch_query);
    $stmt->execute();
    $daily_watch = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 5. Monthly Watch (Next 12 Months) ===
    $monthly_watch_query = "
        SELECT DATE_FORMAT(wb.booking_date, '%Y-%m') AS month,
               COALESCE(SUM(p.total_amount), 0) AS total_paid
        FROM wedding_bookings wb
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date >= CURDATE() AND wb.booking_date < DATE_ADD(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(wb.booking_date, '%Y-%m')
        UNION
        SELECT DATE_FORMAT(wbh.booking_date, '%Y-%m'), COALESCE(SUM(p.total_amount), 0)
        FROM wedding_bookings_history wbh
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date >= CURDATE() AND wbh.booking_date < DATE_ADD(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(wbh.booking_date, '%Y-%m')
        ORDER BY month
    ";
    $stmt = $conn->prepare($monthly_watch_query);
    $stmt->execute();
    $monthly_watch = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 6. Yearly Watch (Next 5 Years) ===
    $yearly_watch_query = "
        SELECT YEAR(wb.booking_date) AS year,
               COALESCE(SUM(p.total_amount), 0) AS total_paid
        FROM wedding_bookings wb
        LEFT JOIN payments p ON wb.booking_reference = p.booking_reference
        WHERE wb.booking_date >= CURDATE() AND wb.booking_date < DATE_ADD(CURDATE(), INTERVAL 5 YEAR)
        GROUP BY YEAR(wb.booking_date)
        UNION
        SELECT YEAR(wbh.booking_date), COALESCE(SUM(p.total_amount), 0)
        FROM wedding_bookings_history wbh
        LEFT JOIN payments p ON wbh.booking_reference = p.booking_reference
        WHERE wbh.booking_date >= CURDATE() AND wbh.booking_date < DATE_ADD(CURDATE(), INTERVAL 5 YEAR)
        GROUP BY YEAR(wbh.booking_date)
        ORDER BY year
    ";
    $stmt = $conn->prepare($yearly_watch_query);
    $stmt->execute();
    $yearly_watch = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // === 7. Past Payments (for charts) ===
    $daily_query = "SELECT DATE(payment_date) AS d, COALESCE(SUM(total_amount),0) AS a
        FROM (SELECT payment_date, total_amount FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              UNION ALL
              SELECT payment_date, total_amount FROM room_payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             ) t GROUP BY d ORDER BY d";
    $daily_payments = $conn->query($daily_query)->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $monthly_query = "SELECT DATE_FORMAT(payment_date,'%Y-%m') AS m, COALESCE(SUM(total_amount),0) AS a
        FROM (SELECT payment_date, total_amount FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
              UNION ALL
              SELECT payment_date, total_amount FROM room_payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 24 MONTH)
             ) t GROUP BY m ORDER BY m";
    $monthly_payments = $conn->query($monthly_query)->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $yearly_query = "SELECT YEAR(payment_date) AS y, COALESCE(SUM(total_amount),0) AS a
        FROM (SELECT payment_date, total_amount FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 10 YEAR)
              UNION ALL
              SELECT payment_date, total_amount FROM room_payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 10 YEAR)
             ) t GROUP BY y ORDER BY y";
    $yearly_payments = $conn->query($yearly_query)->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch (PDOException $e) {
    $error_message = "DB Error: " . $e->getMessage();
    $tomorrow_bookings = $today_bookings = $future_bookings = $daily_watch = $monthly_watch = $yearly_watch = $daily_payments = $monthly_payments = $yearly_payments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>iPad Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root{--p:#6c5ce7;--s:#e84393;--d:#2d3436;--l:#f5f6fa;--bg:#f9f9f9;--pan:#fff;--txt:#2d3436;--warn:#f39c12;}
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
        table{width:100%;border-collapse:collapse;font-size:.85rem;}
        th{background:var(--p);color:#fff;padding:8px 6px;text-align:left;}
        td{padding:8px 6px;border-bottom:1px solid rgba(0,0,0,.1);}
        tr:hover{background:rgba(0,0,0,.05);}
        .no-data{text-align:center;font-style:italic;color:#777;padding:15px;}
        .charts{display:grid;grid-template-columns:1fr;gap:20px;}
        .chart-box{background:var(--pan);border-radius:10px;padding:15px;box-shadow:0 4px 12px rgba(0,0,0,.08);}
        .chart-box canvas{height:220px !important;}
        .watch-grid{display:grid;grid-template-columns:1fr;gap:15px;margin-top:15px;}
        .watch-card{background:var(--warn);color:#fff;padding:12px;border-radius:8px;text-align:center;font-weight:600;}
        @media (orientation:landscape){.charts,.watch-grid{grid-template-columns:repeat(2,1fr);}}
        @media (min-width:900px){.watch-grid{grid-template-columns:repeat(3,1fr);}}
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

    <!-- Tomorrow -->
    <div class="section">
        <h3>Tomorrow's Bookings</h3>
        <?php if ($tomorrow_bookings): ?>
            <table><thead><tr><th>Ref</th><th>Name</th><th>Couple</th><th>Pax</th><th>Type</th><th>Hall</th><th>Day/Night</th><th>Paid</th></tr></thead><tbody>
            <?php foreach ($tomorrow_bookings as $b): ?>
                <tr>
                    <td><?=htmlspecialchars($b['booking_reference']??'—')?></td>
                    <td><?=htmlspecialchars($b['full_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['couple_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['no_of_pax']??'—')?></td>
                    <td><?=htmlspecialchars($b['function_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['venue_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['day_or_night']??'—')?></td>
                    <td><?=$b['total_amount']>0?'LKR '.number_format($b['total_amount'],2):'—'?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php else: ?><p class="no-data">No bookings tomorrow.</p><?php endif; ?>
    </div>

    <!-- Today -->
    <div class="section">
        <h3>Today's Bookings</h3>
        <?php if ($today_bookings): ?>
            <table><thead><tr><th>Ref</th><th>Name</th><th>Couple</th><th>Pax</th><th>Type</th><th>Hall</th><th>Day/Night</th><th>Time</th><th>Paid</th></tr></thead><tbody>
            <?php foreach ($today_bookings as $b): ?>
                <tr>
                    <td><?=htmlspecialchars($b['booking_reference']??'—')?></td>
                    <td><?=htmlspecialchars($b['full_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['couple_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['no_of_pax']??'—')?></td>
                    <td><?=htmlspecialchars($b['function_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['venue_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['day_or_night']??'—')?></td>
                    <td><?=htmlspecialchars(($b['time_from']??'').' '.($b['time_from_am_pm']??'').' - '.($b['time_to']??'').' '.($b['time_to_am_pm']??''))?></td>
                    <td><?=$b['total_amount']>0?'LKR '.number_format($b['total_amount'],2):'—'?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php else: ?><p class="no-data">No bookings today.</p><?php endif; ?>
    </div>

    <!-- Future Bookings (Next 60 Days) -->
    <div class="section">
        <h3>Upcoming Bookings (Next 60 Days)</h3>
        <?php if ($future_bookings): ?>
            <table><thead><tr><th>Date</th><th>Ref</th><th>Name</th><th>Pax</th><th>Type</th><th>Hall</th><th>Day/Night</th><th>Paid</th></tr></thead><tbody>
            <?php foreach ($future_bookings as $b): ?>
                <tr>
                    <td><?=date('M j', strtotime($b['booking_date']))?></td>
                    <td><?=htmlspecialchars($b['booking_reference']??'—')?></td>
                    <td><?=htmlspecialchars($b['full_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['no_of_pax']??'—')?></td>
                    <td><?=htmlspecialchars($b['function_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['venue_name']??'—')?></td>
                    <td><?=htmlspecialchars($b['day_or_night']??'—')?></td>
                    <td><?=$b['total_amount']>0?'LKR '.number_format($b['total_amount'],2):'—'?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
        <?php else: ?><p class="no-data">No upcoming bookings.</p><?php endif; ?>
    </div>

    <!-- Watch: Future Revenue -->
    <div class="section">
        <h3>Watch: Projected Revenue</h3>
        <div class="watch-grid">
            <div class="watch-card">
                <div style="font-size:1.1rem;">Next 7 Days</div>
                <div style="font-size:1.5rem;margin-top:4px;">
                    LKR <?=number_format(array_sum(array_column($daily_watch, 'total_paid')), 2)?>
                </div>
            </div>
            <div class="watch-card">
                <div style="font-size:1.1rem;">Next 12 Months</div>
                <div style="font-size:1.5rem;margin-top:4px;">
                    LKR <?=number_format(array_sum(array_column($monthly_watch, 'total_paid')), 2)?>
                </div>
            </div>
            <div class="watch-card">
                <div style="font-size:1.1rem;">Next 5 Years</div>
                <div style="font-size:1.5rem;margin-top:4px;">
                    LKR <?=number_format(array_sum(array_column($yearly_watch, 'total_paid')), 2)?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts">
        <div class="chart-box">
            <h3 style="margin-bottom:8px;">Daily Payments (30d)</h3>
            <canvas id="dailyChart"></canvas>
        </div>
        <div class="chart-box">
            <h3 style="margin-bottom:8px;">Monthly Payments (24m)</h3>
            <canvas id="monthlyChart"></canvas>
        </div>
        <div class="chart-box">
            <h3 style="margin-bottom:8px;">Yearly Payments (10y)</h3>
            <canvas id="yearlyChart"></canvas>
        </div>
    </div>

</div>

<script>
/* Clock */
function updateClock(){
    const n = new Date();
    const o = {timeZone:'Asia/Colombo',hour12:true,hour:'2-digit',minute:'2-digit',second:'2-digit'};
    const [h,m,sP] = n.toLocaleTimeString('en-US',o).split(':');
    const [s,p] = sP.split(' ');
    document.getElementById('clock').innerHTML = `${h}:${m}:<span style="animation:tick 1s infinite;">${s}</span> ${p}`;
}
updateClock(); setInterval(updateClock,1000);
document.getElementById('today').textContent = new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});

/* Chart Data */
const dailyData = {labels:[<?php $d=new DateTime();$d->modify('-29 days');for($i=0;$i<30;$i++){echo"'".$d->format('Y-m-d')."',";$d->modify('+1 day');}?>],
    datasets:[{label:'Daily (LKR)',data:[<?php $a=array_fill(0,30,0);foreach($daily_payments as $p){$i=array_search($p['d'],array_map(fn($x)=>(new DateTime())->modify('-29 days')->modify("+$x days")->format('Y-m-d'),range(0,29)));if($i!==false)$a[$i]=$p['a'];}echo implode(',',$a);?>],
        borderColor:'rgba(108,92,231,1)',backgroundColor:c=> {const g=c.chart.ctx.createLinearGradient(0,0,0,220);g.addColorStop(0,'rgba(108,92,231,0.5)');g.addColorStop(1,'rgba(108,92,231,0)');return g;},fill:true,tension:.4}]};

const monthlyData = {labels:[<?php $m=new DateTime();$m->modify('-23 months');for($i=0;$i<24;$i++){echo"'".$m->format('Y-m')."',";$m->modify('+1 month');}?>],
    datasets:[{label:'Monthly (LKR)',data:[<?php $a=array_fill(0,24,0);foreach($monthly_payments as $p){$i=array_search($p['m'],array_map(fn($x)=>(new DateTime())->modify('-23 months')->modify("+$x months")->format('Y-m'),range(0,23)));if($i!==false)$a[$i]=$p['a'];}echo implode(',',$a);?>],
        borderColor:'rgba(232,67,147,1)',backgroundColor:c=>{const g=c.chart.ctx.createLinearGradient(0,0,0,220);g.addColorStop(0,'rgba(232,67,147,0.5)');g.addColorStop(1,'rgba(232,67,147,0)');return g;},fill:true,tension:.4}]};

const yearlyData = {labels:[<?php $y=(int)date('Y')-9;for($i=0;$i<10;$i++)echo"'".($y+$i)."',";?>],
    datasets:[{label:'Yearly (LKR)',data:[<?php $a=array_fill(0,10,0);foreach($yearly_payments as $p){$i=array_search($p['y'],array_map(fn($x)=>($y+$x),range(0,9)));if($i!==false)$a[$i]=$p['a'];}echo implode(',',$a);?>],
        borderColor:'rgba(0,184,148,1)',backgroundColor:c=>{const g=c.chart.ctx.createLinearGradient(0,0,0,220);g.addColorStop(0,'rgba(0,184,148,0.5)');g.addColorStop(1,'rgba(0,184,148,0)');return g;},fill:true,tension:.4}]};

const cfg = {type:'line',options:{responsive:true,maintainAspectRatio:false,
    plugins:{legend:{labels:{color:()=>document.body.dataset.theme==='dark'?'#fff':'#2d3436'}}},
    scales:{y:{beginAtZero:true,ticks:{color:()=>document.body.dataset.theme==='dark'?'#fff':'#2d3436'},
               grid:{color:()=>document.body.dataset.theme==='dark'?'rgba(255,255,255,.1)':'rgba(0,0,0,.1)'}},
            x:{ticks:{color:()=>document.body.dataset.theme==='dark'?'#fff':'#2d3436'}}}}};

new Chart(document.getElementById('dailyChart'), {...cfg, data:dailyData});
new Chart(document.getElementById('monthlyChart'), {...cfg, data:monthlyData});
new Chart(document.getElementById('yearlyChart'), {...cfg, data:yearlyData});

/* Dark Mode Toggle */
document.body.addEventListener('dblclick', () => {
    const isDark = document.body.dataset.theme === 'dark';
    document.body.dataset.theme = isDark ? 'light' : 'dark';
});
</script>
</body>
</html>