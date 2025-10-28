<?php
// view_patient.php
include('../db.php');

// Get patient_id from URL
if (!isset($_GET['patient_id'])) {
    die("Error: Patient ID not provided.");
}
$patient_id = intval($_GET['patient_id']);

// Fetch patient details
$patient_sql = "SELECT * FROM users WHERE id = $patient_id AND role = 'patient'";
$patient_result = $conn->query($patient_sql);

if ($patient_result->num_rows == 0) {
    die("Error: Patient not found.");
}
$patient = $patient_result->fetch_assoc();

// Fetch reports related to this patient
$report_sql = "
    SELECT 
        r.report_id,
        r.appointment_id,
        r.patient_id,
        r.doctor_id,
        r.file_name,
        r.created_at,
        d.name AS doctor_name
    FROM reports r
    LEFT JOIN users d ON r.doctor_id = d.id
    WHERE r.patient_id = $patient_id
    ORDER BY r.created_at DESC
";
$report_result = $conn->query($report_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patient Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f8f8;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #00796b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background: #009688;
            color: white;
            text-align: left;
            padding: 10px;
        }
        td {
            padding: 8px;
        }
        a.btn {
            text-decoration: none;
            color: #fff;
            background: #009688;
            padding: 6px 12px;
            border-radius: 5px;
        }
        a.btn:hover {
            background: #00796b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Patient Details</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars($patient['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone'] ?? 'N/A') ?></p>

    <h2>Reports</h2>
    <?php if ($report_result->num_rows > 0) { ?>
        <table>
            <tr>
                <th>Report ID</th>
                <th>Appointment ID</th>
                <th>Doctor Name</th>
                <th>File Name</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            <?php while ($report = $report_result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $report['report_id'] ?></td>
                    <td><?= $report['appointment_id'] ?></td>
                    <td><?= htmlspecialchars($report['doctor_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($report['file_name']) ?></td>
                    <td><?= htmlspecialchars($report['created_at']) ?></td>
                    <td>
                        <a href="../uploads/reports/<?= urlencode($report['file_name']) ?>" class="btn" target="_blank">View</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No reports available for this patient.</p>
    <?php } ?>
</div>
</body>
</html>
