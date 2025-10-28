<?php
include '../config.php';

// Fetch counts
$total_patients = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='patient'")->fetch_assoc()['total'];
$total_doctors  = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='doctor'")->fetch_assoc()['total'];
$total_employees = $conn->query("SELECT COUNT(*) as total FROM users WHERE role IN ('doctor','manager','support')")->fetch_assoc()['total'];
$total_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments")->fetch_assoc()['total'];

// Appointment statuses
$pending = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='pending'")->fetch_assoc()['total'];
$completed = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='completed'")->fetch_assoc()['total'];
$cancelled = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='cancelled'")->fetch_assoc()['total'];

// Total documents
$total_documents = $conn->query("SELECT COUNT(*) as total FROM documents")->fetch_assoc()['total'];

// Observations & Maintenance counts
$total_logs = $conn->query("SELECT COUNT(*) AS total FROM activity_logs")->fetch_assoc()['total'] ?? 0;
$total_maintenance = $conn->query("SELECT COUNT(*) AS total FROM maintenance_records")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Project ALPHA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin:0; }
        .container { max-width: 1200px; margin: 30px auto; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); text-align:center; transition: all 0.3s ease; }
        .card:hover { transform: translateY(-3px); box-shadow:0 4px 14px rgba(0,0,0,0.15); }
        .card h2 { font-size:2em; color:#2c6e49; margin:10px 0; }
        .card p { font-size:1em; color:#555; margin-bottom:10px; }
        .card a { display:inline-block; margin-top:10px; text-decoration:none; background:#2c6e49; color:#fff; padding:8px 15px; border-radius:5px; font-weight:bold; }
        .card a:hover { background:#1f5037; }
        h1 { text-align:center; margin-bottom:30px; color:#2c6e49; }
        footer { text-align:center; margin-top:40px; color:#777; font-size:14px; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1>Admin Dashboard</h1>
    <div class="cards">
        <!-- Patients -->
        <div class="card">
            <p>Total Patients</p>
            <h2><?= $total_patients ?></h2>
            <a href="patients.php">Manage Patients</a>
        </div>

        <!-- Doctors -->
        <div class="card">
            <p>Total Doctors</p>
            <h2><?= $total_doctors ?></h2>
            <a href="doctors.php">Manage Doctors</a>
        </div>

        <!-- Employees -->
        <div class="card">
            <p>Total Employees</p>
            <h2><?= $total_employees ?></h2>
            <a href="employees.php">Manage Employees</a>
        </div>

        <!-- Appointments -->
        <div class="card">
            <p>Total Appointments</p>
            <h2><?= $total_appointments ?></h2>
            <a href="appointments.php">Manage Appointments</a>
        </div>

        <!-- Appointment Status -->
        <div class="card">
            <p>Appointments Status</p>
            <h2>P:<?= $pending ?> | C:<?= $completed ?> | X:<?= $cancelled ?></h2>
            <a href="appointments.php">View Details</a>
        </div>

        <!-- Documents -->
        <div class="card">
            <p>Total Documents</p>
            <h2><?= $total_documents ?></h2>
            <a href="documents.php">Manage Documents</a>
        </div>

        <!-- Reports -->
        <div class="card">
            <p>End-of-Day Reports</p>
            <h2>Summary</h2>
            <a href="reports.php">View Reports</a>
        </div>

        <!-- Observations & Maintenance -->
        <div class="card">
            <p>Observations & Maintenance</p>
            <h2>L:<?= $total_logs ?> | M:<?= $total_maintenance ?></h2>
            <a href="observations.php">View Dashboard</a>
        </div>
    </div>
</main>

<footer>
    <p>© <?= date("Y") ?> Project ALPHA | Admin Dashboard</p>
</footer>

</body>
</html>
