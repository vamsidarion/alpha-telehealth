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
$confirmed = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='confirmed'")->fetch_assoc()['total'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Project ALPHA</title>
    
    <!-- This path is correct (goes UP one folder) -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<section class="hero-main">
    
    <nav>
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="patients.php">Patients</a>
            <a href="doctors.php">Doctors</a>
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </nav>
    
    <div class="hero-content">
        <h2>Admin Dashboard</h2>
        <p>Welcome, Admin. Here is your system overview.</p>
    </div>

</section>

<main class="container">
    
    <h2 class="section-title">System Overview</h2>

    <div class="cards">
        
        <!-- Patients -->
        <div class="card text-only">
            <div class="card-content">
                <p>Total Patients</p>
                <h2><?= $total_patients ?></h2>
                <a href="patients.php">Manage Patients</a>
            </div>
        </div>

        <!-- Doctors -->
        <div class="card text-only">
            <div class="card-content">
                <p>Total Doctors</p>
                <h2><?= $total_doctors ?></h2>
                <a href="doctors.php">Manage Doctors</a>
            </div>
        </div>

        <!-- Employees -->
        <div class="card text-only">
            <div class="card-content">
                <p>Total Employees</p>
                <h2><?= $total_employees ?></h2>
                <a href="employees.php">Manage Employees</a>
            </div>
        </div>

        <!-- Appointments -->
        <div class="card text-only">
            <div class="card-content">
                <p>Total Appointments</p>
                <h2><?= $total_appointments ?></h2>
                <a href="appointments.php">Manage Appointments</a>
            </div>
        </div>

        <!-- Appointment Status -->
        <div class="card text-only">
            <div class="card-content">
                <p>Appointments Status</p>
                <h2 class="small-text">Pending: <?= $pending ?> | Comp: <?= $completed ?> | Cnx: <?= $cancelled ?> | Cmf: <?= $confirmed ?></h2>
                <a href="appointments.php">View Details</a>
            </div>
        </div>

        <!-- Documents -->
        <div class="card text-only">
            <div class="card-content">
                <p>Total Documents</p>
                <h2><?= $total_documents ?></h2>
                <a href="documents.php">Manage Documents</a>
            </div>
        </div>

        <!-- Reports -->
        <div class="card text-only">
            <div class="card-content">
                <p>End-of-Day Reports</p>
                <h2>Summary</h2>
                <a href="reports.php">View Reports</a>
            </div>
        </div>

        <!-- Observations & Maintenance -->
        <div class="card text-only">
            <div class="card-content">
                <p>Logs & Maintenance</p>
                <h2 class="small-text">Logs: <?= $total_logs ?> | Maint: <?= $total_maintenance ?></h2>
                <a href="observations.php">View Dashboard</a>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= date("Y") ?> Project ALPHA | Admin Dashboard</p>
</footer>

</body>
</html>

