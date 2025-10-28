<?php
include '../config.php';

// Dummy admin id
$admin_id = 1;

// Today's date
$today = date('Y-m-d');

// Total patients consulted today
$total_patients_today = $conn->query("
    SELECT COUNT(DISTINCT patient_id) as total 
    FROM appointments 
    WHERE DATE(date_time)='$today' AND status='completed'
")->fetch_assoc()['total'];

// Total appointments today
$total_appointments_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM appointments 
    WHERE DATE(date_time)='$today'
")->fetch_assoc()['total'];

// Appointments by status today
$pending_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM appointments 
    WHERE DATE(date_time)='$today' AND status='pending'
")->fetch_assoc()['total'];

$completed_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM appointments 
    WHERE DATE(date_time)='$today' AND status='completed'
")->fetch_assoc()['total'];

$cancelled_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM appointments 
    WHERE DATE(date_time)='$today' AND status='cancelled'
")->fetch_assoc()['total'];

// Total reports uploaded today
$total_reports_today = $conn->query("
    SELECT COUNT(*) as total 
    FROM documents 
    WHERE DATE(uploaded_at)='$today'
")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>End-of-Day Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; }
        .container { max-width: 1000px; margin:30px auto; }
        h1 { text-align:center; color:#2c6e49; margin-bottom:30px; }
        .report-card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-bottom:20px; text-align:center; }
        .report-card h2 { font-size:2em; color:#2c6e49; margin:10px 0; }
        .report-card p { font-size:1.1em; color:#555; margin:5px 0; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1>End-of-Day Reports (<?= date('d M Y'); ?>)</h1>

    <div class="report-card">
        <p>Total Patients Consulted</p>
        <h2><?= $total_patients_today ?></h2>
    </div>

    <div class="report-card">
        <p>Total Appointments Today</p>
        <h2><?= $total_appointments_today ?></h2>
        <p>Pending: <?= $pending_today ?> | Completed: <?= $completed_today ?> | Cancelled: <?= $cancelled_today ?></p>
    </div>

    <div class="report-card">
        <p>Total Reports Uploaded Today</p>
        <h2><?= $total_reports_today ?></h2>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
