<?php
include '../config.php';

// ===== Skip login: Hardcoded demo patient ID =====
$patient_id = 3; // Replace with an existing patient ID in your users table

// ===== Fetch patient profile =====
$patient = $conn->query("SELECT * FROM users WHERE user_id = $patient_id")->fetch_assoc();

// ===== Fetch appointments =====
$appointments = $conn->query("
    SELECT a.appointment_id, d.name AS doctor_name, a.date_time, a.status
    FROM appointments a
    LEFT JOIN users d ON a.doctor_id = d.user_id
    WHERE a.patient_id = $patient_id
    ORDER BY a.date_time DESC
");

// ===== Fetch documents =====
$documents = $conn->query("
    SELECT * FROM documents
    WHERE patient_id = $patient_id
    ORDER BY uploaded_on DESC
");

// ===== Fetch consulted reports =====
$reports = $conn->query("
    SELECT r.report_id, r.file_name, r.created_at, d.name AS doctor_name, a.date_time AS appointment_date
    FROM reports r
    LEFT JOIN users d ON r.doctor_id = d.user_id
    LEFT JOIN appointments a ON r.appointment_id = a.appointment_id
    WHERE r.patient_id = $patient_id
    ORDER BY r.created_at DESC
");

// ===== Fetch doctors for booking dropdown =====
$doctors = $conn->query("SELECT user_id, name FROM users WHERE role='doctor'");

// ===== Fetch pending calls =====
$pending_calls = $conn->query("
    SELECT a.appointment_id, d.name AS doctor_name, a.date_time
    FROM appointments a
    LEFT JOIN users d ON a.doctor_id = d.user_id
    WHERE a.patient_id = $patient_id AND a.status='confirmed'
    ORDER BY a.date_time ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Dashboard</title>

<!-- 
    Internal CSS for the Patient Dashboard
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
      === THIS IS THE LINE THAT WAS CHANGED (New Image URL Below) ===
      ===============================================================
    */
    background: url('https://i.pinimg.com/1200x/f1/f8/d6/f1f8d68df2f29bb2dcc3d6f73b637b47.jpg') no-repeat center center;
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

/* Container & Card Styles */
.container { 
    max-width:1200px; 
    margin: 60px auto; 
    padding:0 20px; 
}

.section-title { 
    text-align:center; 
    margin-bottom:40px; 
    color: var(--primary-color); 
    font-size:2.2em; 
    font-family: var(--font-heading);
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


/* === INTERNAL CONTENT STYLES === */
/* Styles for tables, forms, etc. on internal pages */

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
    border-collapse: separate; /* Use separate for clean rounded corners */
    border-spacing: 0;
    margin-top: 15px;
    font-size: 0.95em;
    border: 1px solid #e0e0e0;
    border-radius: 8px; /* Rounded corners for table */
    overflow: hidden; /* Clips content to rounded corners */
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
    border-bottom: none; /* No bottom border on last row */
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
    margin-bottom: -5px; /* Pulls label closer to input */
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
    box-sizing: border-box; /* Important! */
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
    width: auto; /* Don't force full width */
    justify-self: start; /* Align button to the left */
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

/* Box for saved descriptions */
.box {
    background: var(--bg-light);
    padding: 15px;
    border-radius: 5px;
    margin-top: 10px;
    border-left: 4px solid var(--accent-color);
    font-size: 0.95em;
}
.box p {
    margin: 8px 0;
}
.box small {
    color: #777;
    display: block;
    margin-top: 5px;
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
    background-color: #fff8e1; /* Light yellow */
    color: #f57f17; /* Dark yellow/orange */
}
.status-badge.status-confirmed {
    background-color: #e3f2fd; /* Light blue */
    color: #0d47a1; /* Dark blue */
}
.status-badge.status-completed {
    background-color: #e8f5e9; /* Light green */
    color: #1b5e20; /* Dark green */
}
.status-badge.status-cancelled {
    background-color: #ffebee; /* Light red */
    color: #b71c1c; /* Dark red */
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
        margin-left: 10px; /* Add some space back */
    }
    .hero-main {
        padding-top: 200px; /* More space for stacked nav */
    }
    .hero-content h2 {
        font-size: 2.2em; /* Smaller hero title on mobile */
    }
    .section-title {
        font-size: 1.8em; /* Smaller section titles */
    }
    input[type="submit"] {
        width: 100%; /* Full width button on mobile */
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
            <a href="profile.php">Profile</a>
            <a href="documents.php">Documents</a>
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </nav>
    
    <div class="hero-content">
        <h2>Welcome, <?= htmlspecialchars($patient['name']) ?></h2>
        <p>Manage your appointments, documents, and health profile.</p>
    </div>
</section>

<!-- Main Page Content -->
<div class="container">

    <!-- Profile Section -->
    <section class="section">
        <h2>Your Profile</h2>
        <div class="box">
            <p><strong>Name:</strong> <?= htmlspecialchars($patient['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
        </div>
        <a href="profile.php?id=<?= $patient_id ?>" target="_blank" class="btn" style="margin-top: 15px;">Edit Profile</a>
    </section>

    <!-- Book an Appointment Section -->
    <section class="section">
        <h2>Book an Appointment</h2>
        <form method="POST" action="book_appointment.php">
            <label for="doctor_id"><strong>Select Doctor:</strong></label>
            <select name="doctor_id" id="doctor_id" required>
                <option value="">-- Choose Doctor --</option>
                <?php
                // Reset pointer and loop
                if ($doctors->num_rows > 0) {
                    $doctors->data_seek(0);
                    while ($row = $doctors->fetch_assoc()) {
                        echo "<option value='{$row['user_id']}'>" . htmlspecialchars($row['name']) . "</option>";
                    }
                }
                ?>
            </select>

            <label for="date_time"><strong>Preferred Date & Time:</strong></label>
            <input type="datetime-local" id="date_time" name="date_time" required>

            <label for="description"><strong>Describe Your Health Issue / Reason for Consultation:</strong></label>
            <textarea name="description" id="description" rows="4" placeholder="Explain your symptoms, health concerns, or reason for consultation..." required></textarea>

            <input type="hidden" name="patient_id" value="<?= $patient_id; ?>">

            <input type="submit" value="Book Appointment">
        </form>
    </section>

    <!-- Consultation Calls Section -->
    <section class="section">
        <h2>Consultation Calls</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Appointment ID</th>
                    <th>Doctor</th>
                    <th>Date / Time</th>
                    <th>Call</th>
                </tr>
                <?php if($pending_calls && $pending_calls->num_rows > 0): ?>
                    <?php while($c = $pending_calls->fetch_assoc()): ?>
                    <tr>
                        <td><?= $c['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($c['doctor_name']) ?></td>
                        <td><?= $c['date_time'] ?></td>
                        <td><a href="../call/patient_call.php?appointment_id=<?= $c['appointment_id'] ?>" class="btn">Join Call</a></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="4">No upcoming calls.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </section>

<!-- This line had a PHP error, I removed it. -->
<!-- WELCOME, <?php $a['patient_name']; ?> -->

    <!-- Appointments Section -->
    <section class="section">
        <h2>Your Appointments</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Doctor</th>
                    <th>Date / Time</th>
                    <th>Status</th>
                </tr>
                <?php if($appointments && $appointments->num_rows > 0): ?>
                    <?php while($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?= $a['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($a['doctor_name'] ?? 'Unassigned') ?></td>
                        <td><?= $a['date_time'] ?></td>
                        <td><span class="status-badge status-<?= $a['status']; ?>"><?= ucfirst($a['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="4">No appointments found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </section>

    <!-- Documents Section -->
    <section class="section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2>Your Documents</h2>
            <a href="documents.php" target="_blank" class="btn">Upload New Document</a>
        </div>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>File Name</th>
                    <th>Type</th>
                    <th>Uploaded At</th>
                    <th>Uploaded On</th>
                    <th>Action</th>
                </tr>
                <?php if($documents && $documents->num_rows > 0): ?>
                    <?php while($doc = $documents->fetch_assoc()): 
                        $filePath = "../uploads/" . $doc['file_name'];
                        $fileExists = file_exists($filePath);
                    ?>
                    <tr>
                        <td><?= $doc['document_id'] ?></td>
                        <td><?= htmlspecialchars($doc['file_name']) ?></td>
                        <td><?= htmlspecialchars($doc['type']) ?></td>
                        <td><?= htmlspecialchars($doc['uploaded_at']) ?></td>
                        <td><?= htmlspecialchars($doc['uploaded_on']) ?></td>
                        <td>
                            <?php if($fileExists): ?>
                                <a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="btn">View / Download</a>
                            <?php else: ?>
                                <span style="color:red;">File not found</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="6">No documents found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </section>

    <!-- Consulted Reports Section -->
    <section class="section">
        <h2>Consulted Reports</h2>
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Doctor</th>
                    <th>Appointment Date</th>
                    <th>Report File</th>
                    <th>Uploaded On</th>
                    <th>Action</th>
                </tr>
                <?php if($reports && $reports->num_rows > 0): ?>
                    <?php while($rep = $reports->fetch_assoc()): 
                        // Assuming reports are in a different folder, adjust as needed
                        $reportFilePath = "../reports/" . $rep['file_name'];
                        $reportFileExists = file_exists($reportFilePath);
                    ?>
                    <tr>
                        <td><?= $rep['report_id'] ?></td>
                        <td><?= htmlspecialchars($rep['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($rep['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($rep['file_name']) ?></td>
                        <td><?= $rep['created_at'] ?></td>
                        <td>
                            <?php if($reportFileExists): ?>
                                <a href="<?= htmlspecialchars($reportFilePath) ?>" target="_blank" class="btn">View / Download</a>
                            <?php else: ?>
                                <span style="color:red;">File not found</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="6">No reports found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </section>

</div> <!-- .container -->

<!-- Standard Footer -->
<footer>
    <p>&copy; 2025 Project ALPHA | <a href="#">Contact</a> | <a href="#">Privacy Policy</a></p>
</footer>

</body>
</html>

