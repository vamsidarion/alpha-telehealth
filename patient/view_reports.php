<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config.php');

// === Patient ID (temporary hardcoded) ===
$patient_id = 3; // replace with session-based login later

// Fetch all reports for this patient
$sql = "
    SELECT r.report_id, r.file_name, r.created_at, u.name AS doctor_name, a.date_time AS appointment_date
    FROM reports r
    JOIN appointments a ON r.appointment_id = a.appointment_id
    JOIN users u ON r.doctor_id = u.user_id
    WHERE r.patient_id = $patient_id
    ORDER BY r.created_at DESC
";
$reports = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reports</title>
    <style>
        body { font-family: Arial; background:#eef6f7; padding:30px; }
        .container { background:#fff; padding:20px; border-radius:10px; max-width:800px; margin:auto; box-shadow:0 0 12px rgba(0,0,0,0.1);}
        table { width:100%; border-collapse: collapse; margin-top:20px;}
        table, th, td { border: 1px solid #ccc; }
        th, td { padding:10px; text-align:left; }
        th { background:#2c6e49; color:#fff; }
        tr:nth-child(even) { background:#f2f2f2; }
        a.button { background:#2c6e49; color:#fff; padding:5px 10px; border-radius:5px; text-decoration:none; }
        a.button:hover { background:#1f5037; }
        h2 { text-align:center; color:#2c6e49; }
    </style>
</head>
<body>
<div class="container">
    <h2>My Reports</h2>

    <?php if($reports && $reports->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Appointment Date</th>
                    <th>Doctor</th>
                    <th>File Name</th>
                    <th>Uploaded On</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $i = 1;
            while($row = $reports->fetch_assoc()):
            ?>
                <tr>
                    <td><?= $i++; ?></td>
                    <td><?= htmlspecialchars($row['appointment_date']); ?></td>
                    <td><?= htmlspecialchars($row['doctor_name']); ?></td>
                    <td><?= htmlspecialchars($row['file_name']); ?></td>
                    <td><?= htmlspecialchars($row['created_at']); ?></td>
                    <td><a class="button" href="../uploads/<?= urlencode($row['file_name']); ?>" target="_blank">Download</a></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No reports found.</p>
    <?php endif; ?>
</div>
</body>
</html>
