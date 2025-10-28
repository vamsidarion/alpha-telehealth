<?php
include '../config.php';

// Assuming manager is logged in
$manager_id = $_SESSION['manager_id'] ?? 1; // replace with session check

// Summary counts
$total_appointments = $conn->query("SELECT COUNT(*) as total FROM appointments")->fetch_assoc()['total'];
$pending = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='pending'")->fetch_assoc()['total'];
$completed = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='completed'")->fetch_assoc()['total'];
$cancelled = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='cancelled'")->fetch_assoc()['total'];
$confirmed = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='confirmed'")->fetch_assoc()['total'];


// Filters
$filter_status = $_GET['status'] ?? '';
$search_patient = $_GET['patient'] ?? '';
$search_doctor = $_GET['doctor'] ?? '';

// Appointments Query
$sql = "
SELECT a.appointment_id, p.name AS patient, d.name AS doctor, a.date_time, a.status
FROM appointments a
LEFT JOIN users p ON a.patient_id = p.user_id
LEFT JOIN users d ON a.doctor_id = d.user_id
WHERE 1=1
";
if ($filter_status) {
    $sql .= " AND a.status='" . $conn->real_escape_string($filter_status) . "'";
}
if ($search_patient) {
    $sql .= " AND p.name LIKE '%" . $conn->real_escape_string($search_patient) . "%'";
}
if ($search_doctor) {
    $sql .= " AND d.name LIKE '%" . $conn->real_escape_string($search_doctor) . "%'";
}
$sql .= " ORDER BY a.date_time DESC";

$appointments = $conn->query($sql);

// Fetch doctors for dropdown
$doctors = $conn->query("SELECT user_id, name FROM users WHERE role='doctor'");

// Documents Query (with correct columns)
$documents = $conn->query("
    SELECT d.document_id, d.file_name, d.patient_id, d.uploaded_by, d.type, d.uploaded_at, d.uploaded_on, u.name AS patient_name
    FROM documents d
    LEFT JOIN users u ON d.patient_id = u.user_id
    ORDER BY d.uploaded_on DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manager Dashboard</title>
<!-- 
    Link to external stylesheet has been removed.
    All styles are now "internal" in the <style> block below.
-->
<style>
/* Import Google Fonts at the top */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Open+Sans:wght@400;600&display=swap');

:root {
    /* New green color palette */
    --primary-color: #3a5a40; /* Deeper, more natural green */
    --primary-hover: #303d2b; /* Darker shade */
    --secondary-color: #588157;
    --accent-color: #a3b18a; /* Muted green for placeholders */
    --bg-light: #f8f9fa; /* A very light, clean background */
    --card-bg: #ffffff;
    --text-light: #ffffff;
    --text-dark: #333333;
    --font-heading: 'Montserrat', sans-serif;
    --font-body: 'Open Sans', sans-serif;
}

body { 
    font-family: var(--font-body); 
    margin:0; 
    background: var(--bg-light); 
    color: var(--text-dark);
}

/* HERO SECTION */
.hero-main {
    position: relative;
    padding: 80px 20px;
    padding-top: 150px; /* Extra space for the nav bar */
    text-align: center;
    color: var(--text-light);
    
    /* ===============================================================
      === THIS IS THE NEW IMAGE FOR THE MANAGER DASHBOARD ===
      ===============================================================
    */
    background: url('https://images.pexels.com/photos/3184338/pexels-photo-3184338.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center;
    background-size: cover;
    min-height: 50vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.hero-main::before {
    /* This is the dark overlay for text contrast */
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.5); 
    z-index: 1;
}
.hero-main > * { 
    /* Ensures all content is above the overlay */
    position: relative;
    z-index: 2;
}

/* Nav Bar */
.hero-main nav {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    max-width: 1200px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: rgba(255, 255, 255, 0.1); /* Transparent white */
    -webkit-backdrop-filter: blur(5px); /* Safari support */
    backdrop-filter: blur(5px);
    border-radius: 8px;
    box-sizing: border-box;
}
.nav-logo {
    font-size: 1.5em;
    font-weight: 700;
    font-family: var(--font-heading);
    color: var(--text-light);
    text-decoration: none;
}
.nav-links a {
    color: var(--text-light);
    text-decoration: none;
    margin-left: 20px;
    font-weight: 600;
    font-size: 0.9em;
}
.nav-links a.nav-button {
    background: var(--text-light);
    color: var(--primary-color);
    padding: 8px 15px;
    border-radius: 5px;
}
.nav-links a.nav-button:hover {
    background: #eee;
}

/* Hero Content */
.hero-content {
    max-width: 600px;
    margin: 40px auto 0 auto;
}
.hero-content h2 {
    font-family: var(--font-heading);
    font-size: 3em;
    margin: 0 0 15px 0;
}
.hero-content p {
    font-size: 1.2em;
    margin-bottom: 30px;
    opacity: 0.9;
}

/* Container */
.container { 
    max-width:1200px; 
    margin: 60px auto; 
    padding:0 20px; 
}

/* Footer */
footer { 
    background: var(--primary-color); 
    color: rgba(255, 255, 255, 0.8); 
    padding:40px 20px; 
    text-align:center; 
    margin-top: 60px; /* Added margin */
}
footer a { color: var(--text-light); margin:0 10px; text-decoration:none; font-weight: 600; }
footer a:hover { text-decoration:underline; }


/* === ADMIN/MANAGER DASHBOARD CARD STYLES === */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.card-stat {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}
.card-stat p {
    font-size: 1em;
    color: #555;
    margin: 0 0 10px 0;
    font-family: var(--font-heading);
    font-weight: 600;
}
.card-stat h2 {
    font-size: 2.5em;
    color: var(--primary-color);
    margin: 0 0 15px 0;
    font-family: var(--font-heading);
}

/* === INTERNAL CONTENT STYLES === */
.section {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}
.section h2 {
    font-family: var(--font-heading);
    color: var(--primary-color);
    margin-top: 0;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--bg-light);
    padding-bottom: 10px;
}

/* Table Styles */
.table-wrapper {
    overflow-x: auto; /* Makes tables responsive */
}
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
    font-size: 0.95em;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}
table, th, td {
    border-bottom: 1px solid #e0e0e0;
}
th, td {
    padding: 12px 15px;
    text-align: left;
    line-height: 1.6;
}
th {
    background: var(--primary-color);
    color: var(--text-light);
    font-family: var(--font-heading);
    font-weight: 600;
    border-bottom: none;
}
tr:nth-child(even) {
    background: var(--bg-light);
}
tr:hover {
    background: #f0f0f0;
}
tr:last-child td {
    border-bottom: none;
}

/* Form Styles */
form {
    display: grid;
    gap: 15px;
}
label {
    font-weight: 600;
    font-family: var(--font-heading);
    color: var(--primary-color);
    font-size: 0.9em;
    margin-bottom: -5px;
}
textarea,
input[type="text"],
input[type="email"],
input[type="password"],
input[type="datetime-local"],
select {
    width: 100%;
    padding: 12px;
    font-family: var(--font-body);
    font-size: 1em;
    color: var(--text-dark);
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
    background: #fff;
}
select {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%233a5a40' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
}
input[type="submit"] {
    background: var(--secondary-color);
    color: var(--text-light);
    padding: 12px 22px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1em;
    font-family: var(--font-heading);
    transition: background-color 0.3s ease;
    width: auto;
    justify-self: start;
}
input[type="submit"]:hover {
    background: var(--primary-color);
}

/* Button Styles */
.btn {
    text-decoration: none;
    color: var(--text-light);
    background: var(--secondary-color);
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: 600;
    font-size: 0.9em;
    display: inline-block;
    transition: background-color 0.3s ease;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
}
.btn:hover {
    background: var(--primary-color);
    color: var(--text-light);
}

/* === FILTER FORM STYLE === */
.filter-form {
    display: flex;
    flex-wrap: wrap; /* Allows items to wrap on small screens */
    gap: 15px;
    padding: 20px;
    background: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    align-items: center;
}
.filter-form input[type="text"],
.filter-form select {
    width: auto; /* Allow inputs to size based on content */
    flex-grow: 1; /* Allow inputs to grow and fill space */
    min-width: 150px; /* Minimum width for inputs */
    margin: 0; /* Remove default margins */
}
.filter-form .btn {
    flex-shrink: 0; /* Prevent button from shrinking */
    padding: 12px 22px; /* Match input height */
    font-size: 1em;
}

/* === STATUS BADGES === */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.85em;
    text-transform: capitalize;
}
.status-badge.status-pending {
    background-color: #fff8e1;
    color: #f57f17;
}
.status-badge.status-confirmed {
    background-color: #e3f2fd;
    color: #0d47a1;
}
.status-badge.status-completed {
    background-color: #e8f5e9;
    color: #1b5e20;
}
.status-badge.status-cancelled {
    background-color: #ffebee;
    color: #b71c1c;
}

/* === RESPONSIVE STYLES === */
@media (max-width: 768px) {
    .hero-main nav {
        flex-direction: column;
        padding: 10px;
        width: 100%;
        border-radius: 0;
        top: 0;
    }
    .nav-links {
        margin-top: 10px;
        gap: 10px;
    }
    .nav-links a {
        margin-left: 10px;
    }
    .hero-main {
        padding-top: 200px;
    }
    .hero-content h2 {
        font-size: 2.2em;
    }
    .filter-form {
        flex-direction: column;
        align-items: stretch; /* Make inputs full width */
    }
    .filter-form input[type="text"],
    .filter-form select,
    .filter-form .btn {
        width: 100%; /* Full width on mobile */
    }
}
@media (max-width: 480px) {
    .nav-links {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .nav-links a {
        margin-left: 0;
        width: 80%;
        text-align: center;
    }
    .nav-links a.nav-button {
        padding: 10px;
    }
}
</style>
</head>
<body>

<!-- Hero Header Section -->
<section class="hero-main" style="min-height: 40vh;">
    <!-- Semi-transparent nav bar -->
    <nav>
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="reports.php">Reports</a>
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </nav>
    
    <div class="hero-content">
        <h2>Manager Dashboard</h2>
    </div>
</section>

<!-- Main Page Content -->
<div class="container">

<!-- Summary Cards -->
<div class="cards-grid">
    <div class="card-stat"><p>Total Appointments</p><h2><?= $total_appointments ?></h2></div>
    <div class="card-stat"><p>Pending</p><h2><?= $pending ?></h2></div>
    <div class="card-stat"><p>Completed</p><h2><?= $completed ?></h2></div>
    <div class="card-stat"><p>Cancelled</p><h2><?= $cancelled ?></h2></div>
    <div class="card-stat"><p>Confirmed</p><h2><?= $confirmed ?></h2></div>
</div>

<!-- Filter Form -->
<form method="GET" class="filter-form">
    <input type="text" name="patient" placeholder="Search Patient Name" value="<?= htmlspecialchars($search_patient) ?>">
    <input type="text" name="doctor" placeholder="Search Doctor Name" value="<?= htmlspecialchars($search_doctor) ?>">
    <select name="status">
        <option value="">All Status</option>
        <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="confirmed" <?= $filter_status == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
        <option value="completed" <?= $filter_status == 'completed' ? 'selected' : '' ?>>Completed</option>
        <option value="cancelled" <?= $filter_status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
    <button type="submit" class="btn">Filter</button>
</form>

<!-- Appointments Table -->
<div class="section">
    <h2>Manage Appointments</h2>
    <div class="table-wrapper">
        <table>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Date / Time</th>
            <th>Status</th>
            <th>Assign / Update</th>
        </tr>
        <?php if ($appointments && $appointments->num_rows > 0) : ?>
            <?php while ($a = $appointments->fetch_assoc()) : ?>
                <tr>
                    <td><?= $a['appointment_id'] ?></td>
                    <td><?= htmlspecialchars($a['patient']) ?></td>
                    <td><?= htmlspecialchars($a['doctor'] ?? 'Unassigned') ?></td>
                    <td><?= $a['date_time'] ?></td>
                    <td><span class="status-badge status-<?= $a['status']; ?>"><?= ucfirst($a['status']); ?></span></td>
                    <td>
                        <!-- Update form now uses grid layout from form styles -->
                        <form method="POST" action="update_appointment.php" style="display:inline-grid; gap: 5px; grid-template-columns: 1fr 1fr auto; align-items: center;">
                            <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
                            <select name="doctor_id" required>
                                <option value="">Assign Doctor</option>
                                <?php 
                                // Reset and loop doctors
                                if ($doctors->num_rows > 0) {
                                    $doctors->data_seek(0);
                                    while($doc = $doctors->fetch_assoc()): ?>
                                        <option value="<?= $doc['user_id'] ?>" <?= ($doc['name'] == $a['doctor']) ? 'selected' : '' ?>><?= htmlspecialchars($doc['name']) ?></option>
                                <?php endwhile;
                                }
                                ?>
                            </select>
                            <select name="status">
                                <option value="pending" <?= $a['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $a['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $a['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $a['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn" style="padding: 12px 15px; font-size: 0.9em;">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr><td colspan="6">No appointments found.</td></tr>
        <?php endif; ?>
        </table>
    </div>
</div>

<!-- Documents Section -->
<div class="section">
    <h2>Patient Documents</h2>
    <div class="table-wrapper">
        <table>
        <tr>
            <th>ID</th>
            <th>Patient</th>
            <th>File Name</th>
            <th>Type</th>
            <th>Uploaded At</th>
            <th>Uploaded On</th>
            <th>Action</th>
        </tr>
        <?php if ($documents && $documents->num_rows > 0) : ?>
            <?php while ($doc = $documents->fetch_assoc()) :
                $filePath = "../uploads/" . $doc['file_name'];
                $fileExists = file_exists($filePath);
            ?>
                <tr>
                    <td><?= $doc['document_id'] ?></td>
                    <td><?= htmlspecialchars($doc['patient_name']) ?></td>
                    <td><?= htmlspecialchars($doc['file_name']) ?></td>
                    <td><?= htmlspecialchars($doc['type']) ?></td>
                    <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                    <td><?= htmlspecialchars($doc['uploaded_on']) ?></td>
                    <td>
                        <?php if ($fileExists) : ?>
                            <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="btn">View</a>
                        <?php else : ?>
                            <span style="color:red;">File not found</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr><td colspan="7">No documents found.</td></tr>
        <?php endif; ?>
        </table>
    </div>
</div>

</div> <!-- .container -->

<!-- Standard Footer -->
<footer>
    <p>&copy; 2025 Project ALPHA | <a href="#">Contact</a> | <a href="#">Privacy Policy</a></p>
</footer>

</body>
</html>

