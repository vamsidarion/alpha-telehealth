<?php
include '../config.php';

// ===== Skip login: Hardcoded demo patient ID =====
$patient_id = 1; // Replace with an existing patient ID in your users table

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
    SELECT * 
    FROM documents
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Dashboard</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body { font-family: Arial, sans-serif; background:#f4f6f8; margin:0; }
.container { max-width:1200px; margin:30px auto; padding:20px; }
h1 { text-align:center; color:#2c6e49; margin-bottom:30px; }
section { margin-bottom:50px; }
table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
th, td { padding:12px 15px; border-bottom:1px solid #ddd; text-align:left; }
th { background:#2c6e49; color:#fff; }
tr:hover { background:#f9f9f9; }
.btn { display:inline-block; background:#2c6e49; color:#fff; padding:5px 10px; border-radius:5px; text-decoration:none; }
.btn:hover { background:#1f5037; }
</style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
<h1>Welcome, <?= htmlspecialchars($patient['name']) ?></h1>

<!-- Profile -->
<section>
<h2>Profile</h2>
<p><strong>Name:</strong> <?= $patient['name'] ?></p>
<p><strong>Email:</strong> <?= $patient['email'] ?></p>
<p><strong>Phone:</strong> <?= $patient['phone'] ?></p>
</section>


<!-- Book New Appointment -->
<section>
<h2>Book New Appointment</h2>
<form action="book_appointment.php" method="POST">
    <label for="doctor_id">Select Doctor:</label>
    <select name="doctor_id" id="doctor_id" required>
        <option value="">--Choose Doctor--</option>
        <?php
        $doctors = $conn->query("SELECT user_id, name FROM users WHERE role='doctor'");
        while($doc = $doctors->fetch_assoc()){
            echo "<option value='{$doc['user_id']}'>{$doc['name']}</option>";
        }
        ?>
    </select>

    <label for="date_time">Select Date & Time:</label>
    <input type="datetime-local" name="date_time" id="date_time" required>

    <button type="submit" class="btn">Book Appointment</button>
</form>
</section>


<!-- Appointments -->
<section>
<h2>Your Appointments</h2>
<table>
<tr>
<th>ID</th>
<th>Doctor</th>
<th>Date / Time</th>
<th>Status</th>
</tr>
<?php if($appointments && $appointments->num_rows>0): ?>
    <?php while($a = $appointments->fetch_assoc()): ?>
    <tr>
        <td><?= $a['appointment_id'] ?></td>
        <td><?= $a['doctor_name'] ?? 'Unassigned' ?></td>
        <td><?= $a['date_time'] ?></td>
        <td><?= ucfirst($a['status']) ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No appointments found.</td></tr>
<?php endif; ?>
</table>
</section>

<!-- Documents -->
<section>
<h2>Your Documents</h2>
<table>
<tr>
<th>ID</th>
<th>File Name</th>
<th>Type</th>
<th>Uploaded At</th>
<th>Uploaded On</th>
<th>Action</th>
</tr>
<?php if($documents && $documents->num_rows>0): ?>
    <?php while($doc = $documents->fetch_assoc()): ?>
    <tr>
        <td><?= $doc['document_id'] ?></td>
        <td><?= $doc['file_name'] ?></td>
        <td><?= $doc['type'] ?></td>
        <td><?= $doc['uploaded_at'] ?></td>
        <td><?= $doc['uploaded_on'] ?></td>
        <td><a href="../uploads/<?= $doc['file_name'] ?>" target="_blank" class="btn">View / Download</a></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No documents found.</td></tr>
<?php endif; ?>
</table>
</section>

<!-- Consulted Reports -->
<section>
<h2>Consulted Reports</h2>
<table>
<tr>
<th>ID</th>
<th>Doctor</th>
<th>Appointment Date</th>
<th>Report File</th>
<th>Uploaded On</th>
<th>Action</th>
</tr>
<?php if($reports && $reports->num_rows>0): ?>
    <?php while($rep = $reports->fetch_assoc()): ?>
    <tr>
        <td><?= $rep['report_id'] ?></td>
        <td><?= $rep['doctor_name'] ?></td>
        <td><?= $rep['appointment_date'] ?></td>
        <td><?= $rep['file_name'] ?></td>
        <td><?= $rep['created_at'] ?></td>
        <td><a href="../reports/<?= $rep['file_name'] ?>" target="_blank" class="btn">View / Download</a></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="6">No reports found.</td></tr>
<?php endif; ?>
</table>
</section>

<!-- Call Integration -->
<section>
<h2>Consultation Calls</h2>
<table>
<tr>
<th>Appointment ID</th>
<th>Doctor</th>
<th>Date / Time</th>
<th>Call</th>
</tr>
<?php
$pending_calls = $conn->query("
    SELECT a.appointment_id, d.name AS doctor_name, a.date_time
    FROM appointments a
    LEFT JOIN users d ON a.doctor_id = d.user_id
    WHERE a.patient_id = $patient_id AND a.status='pending'
    ORDER BY a.date_time ASC
");
?>
<?php if($pending_calls && $pending_calls->num_rows>0): ?>
    <?php while($c = $pending_calls->fetch_assoc()): ?>
    <tr>
        <td><?= $c['appointment_id'] ?></td>
        <td><?= $c['doctor_name'] ?></td>
        <td><?= $c['date_time'] ?></td>
        <td><a href="call.php?appointment_id=<?= $c['appointment_id'] ?>" class="btn">Join Call</a></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4">No upcoming calls.</td></tr>
<?php endif; ?>
</table>
</section>

</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
