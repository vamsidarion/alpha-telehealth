<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php'; // DB connection

$doctor_id = 2; // Hardcoded for now, replace with session later

// ===== Doctor Profile =====
$doctor = $conn->query("SELECT * FROM users WHERE user_id = $doctor_id AND role='doctor'")->fetch_assoc();

// ===== Appointments Assigned =====
$appointments = $conn->query("
    SELECT a.appointment_id, a.patient_id, a.date_time, a.status, u.name AS patient_name
    FROM appointments a
    JOIN users u ON a.patient_id = u.user_id
    WHERE a.doctor_id = $doctor_id
    ORDER BY a.date_time ASC
");

// ===== Patients List (for document access) =====
// Added a query to get ALL patients for the log dropdown
$all_patients_query = $conn->query("SELECT user_id, name FROM users WHERE role='patient'");

// ===== Reports by Doctor =====
$reports = $conn->query("
    SELECT r.report_id, r.file_name, r.patient_id, u.name AS patient_name, r.created_at
    FROM reports r
    JOIN users u ON r.patient_id = u.user_id
    WHERE r.doctor_id = $doctor_id
    ORDER BY r.created_at DESC
");

// ===== Saved Prescriptions (This is now part of the logs) =====
// We can fetch old logs to show here
$old_logs = $conn->query("
    SELECT * FROM patient_logs 
    WHERE doctor_id = $doctor_id 
    ORDER BY log_date DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard | Project ALPHA</title>
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
      === THIS IS THE IMAGE FOR THE DOCTOR DASHBOARD ===
      ===============================================================
    */
    background: url('https://images.pexels.com/photos/3845126/pexels-photo-3845126.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1') no-repeat center center;
    background-size: cover;
    min-height: 40vh; /* Shorter hero */
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
<section class="hero-main">
    <!-- Semi-transparent nav bar -->
    <nav>
        <a href="../index.php" class="nav-logo">Project ALPHA</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="../logout.php" class="nav-button">Logout</a>
        </div>
    </nav>
    
    <div class="hero-content">
        <!-- Title uses the doctor's name -->
        <h2>Dr. <?= htmlspecialchars($doctor['name']); ?>'s Dashboard</h2>
    </div>
</section>

<!-- Main Page Content -->
<div class="container">
    
    <!-- 1. Doctor Profile -->
    <div class="section">
        <h2>Profile</h2>
        <div class="box">
            <p><strong>Name:</strong> <?= htmlspecialchars($doctor['name']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($doctor['email']); ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($doctor['phone']); ?></p>
        </div>
    </div>

    <!-- 2. Appointment Status -->
    <div class="section">
        <h2>Appointments Assigned</h2>
        <?php if($appointments && $appointments->num_rows > 0): ?>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>#</th>
                        <th>Patient Name</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Call</th>
                    </tr>
                    <?php $i=1; while($a = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= htmlspecialchars($a['patient_name']); ?></td>
                        <td><?= $a['date_time']; ?></td>
                        <td><span class="status-badge status-<?= $a['status']; ?>"><?= ucfirst($a['status']); ?></span></td>
                        <td>
                            <?php if($a['status']=='confirmed'): ?>
                                <a class="btn" href="../call/doctor_call.php?appointment_id=<?= $a['appointment_id']; ?>" target="_blank">Start Call</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php else: ?>
            <p>No appointments assigned.</p>
        <?php endif; ?>
    </div>

    <!-- 3. Patients Documents Access -->
    <div class="section">
        <h2>Patients Documents & Logs</h2>
        <?php 
        // Reset pointer
        if ($all_patients_query) $all_patients_query->data_seek(0); 
        ?>
        <?php if($all_patients_query && $all_patients_query->num_rows > 0): ?>
            <?php while($p = $all_patients_query->fetch_assoc()): ?>
                <p style="padding: 8px 0; border-bottom: 1px solid var(--bg-light);">
                    <strong><?= htmlspecialchars($p['name']); ?></strong> - 
                    <a class="btn" href="../admin/documents.php?id=<?= $p['user_id']; ?>" target="_blank">View Documents</a>
                    <a class="btn" href="../patient/patient_visual_log.php?id=<?= $p['user_id']; ?>" target="_blank" style="background-color: var(--accent-color);">View Visual Log</a>
                </p>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No patients found.</p>
        <?php endif; ?>
    </div>

    <!-- 4. THIS IS THE NEW, UPDATED FORM -->
    <div class="section">
        <h2>Create Patient Log</h2>
        <form method="post" action="save_patient_log.php">
            
            <input type="hidden" name="doctor_id" value="<?= $doctor_id; ?>">
            <input type="hidden" name="log_date" value="<?= date('Y-m-d H:i:s'); ?>">

            <!-- Select Patient -->
            <label for="log_patient">Select Patient:</label>
            <select name="patient_id" id="log_patient" required>
                <option value="">-- Choose a patient --</option>
                <?php
                // Reset pointer and loop
                if ($all_patients_query) $all_patients_query->data_seek(0);
                while($p = $all_patients_query->fetch_assoc()):
                ?>
                    <option value="<?= $p['user_id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <!-- Select Problem Area -->
            <label for="log_problem">Problem Area:</label>
            <select name="problem_area" id="log_problem" required>
                <option value="">-- Choose area --</option>
                <option value="Head">Head</option>
                <option value="Chest">Chest</option>
                <option value="Abdomen">Abdomen</option>
                <option value="Left Shoulder">Left Shoulder</option>
                <option value="Right Shoulder">Right Shoulder</option>
                <option value="Left Knee">Left Knee</option>
                <option value="Right Knee">Right Knee</option>
                <!-- Add more as needed -->
            </select>
            
            <!-- Main Diagnosis -->
            <label for="log_diag">Main Diagnosis:</label>
            <input type="text" name="main_diagnosis" id="log_diag" placeholder="e.g., [S43.4] Sprain of shoulder joint" required>

            <!-- Doctor's Notes -->
            <label for="log_desc">Doctor's Notes / Description:</label>
            <textarea name="description" id="log_desc" rows="4"></textarea>

            <!-- MEDICATION - THIS IS THE NEW FIELD -->
            <label for="log_med">Medication Prescribed:</label>
            <textarea name="medication" id="log_med" rows="3" placeholder="e.g., Acelofenac 100mg (1 daily)
Diclofenac Topical Gel 2% (as needed)"></textarea>
            
            <input type="submit" value="Save Log">
        </form>
    </div>

    <!-- 5. Section for Recent Logs -->
    <div class="section">
        <h2>Recent Logs</h2>
        <?php if($old_logs && $old_logs->num_rows > 0): ?>
            <?php while($log = $old_logs->fetch_assoc()): ?>
                <div class="box">
                    <strong><?= htmlspecialchars($log['problem_area']) ?>:</strong> <?= htmlspecialchars($log['main_diagnosis']) ?><br>
                    <small>Saved at: <?= $log['log_date']; ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
             <p>No recent logs found.</p>
        <?php endif; ?>
    </div>


</div> <!-- .container -->

<!-- Standard Footer -->
<footer>
    <p>&copy; <?= date("Y") ?> Project ALPHA | <a href="#">Contact</a> | <a href="#">Privacy Policy</a></p>
</footer>

</body>
</html>

