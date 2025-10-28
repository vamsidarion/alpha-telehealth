<?php
include '../config.php';

// Dummy admin id
$admin_id = 1;

// Handle status update
if (isset($_GET['update_status']) && isset($_GET['appointment_id'])) {
    $appointment_id = (int)$_GET['appointment_id'];
    $new_status = $conn->real_escape_string($_GET['update_status']);
    $conn->query("UPDATE appointments SET status='$new_status' WHERE appointment_id=$appointment_id");
}

// Filters
$patient_filter = $_GET['patient'] ?? '';
$doctor_filter = $_GET['doctor'] ?? '';
$status_filter  = $_GET['status'] ?? '';

// Fetch patients and doctors for filters
$patients = $conn->query("SELECT user_id, name FROM users WHERE role='patient'")->fetch_all(MYSQLI_ASSOC);
$doctors  = $conn->query("SELECT user_id, name FROM users WHERE role='doctor'")->fetch_all(MYSQLI_ASSOC);

// Build SQL query
$sql = "SELECT a.*, 
               p.name AS patient_name, 
               d.name AS doctor_name
        FROM appointments a
        JOIN users p ON a.patient_id=p.user_id
        JOIN users d ON a.doctor_id=d.user_id
        WHERE 1=1";

if ($patient_filter !== '') {
    $sql .= " AND a.patient_id=$patient_filter";
}
if ($doctor_filter !== '') {
    $sql .= " AND a.doctor_id=$doctor_filter";
}
if ($status_filter !== '') {
    $sql .= " AND a.status='$status_filter'";
}

$sql .= " ORDER BY a.date_time DESC";
$appointments = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; }
        .container { max-width: 1000px; margin: 30px auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c6e49; color: #fff; }
        tr:hover { background-color: #f1f1f1; }
        form { background: #fff; padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        select, button { padding: 10px; margin-right: 10px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #2c6e49; color: #fff; border: none; cursor: pointer; }
        button:hover { background-color: #1f5037; }
        .status-badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; color: #fff; }
        .status-pending { background-color: orange; }
        .status-completed { background-color: green; }
        .status-cancelled { background-color: red; }
        a.update-link { margin-right:5px; text-decoration:none; color:#fff; padding:5px 8px; border-radius:5px; }
        a.pending { background-color: orange; }
        a.completed { background-color: green; }
        a.cancelled { background-color: red; }
        a.confirmed { background-color: blue; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1 style="text-align:center;">Manage Appointments</h1>

    <!-- Filters -->
    <form method="GET">
        <label>Patient:</label>
        <select name="patient">
            <option value="">All</option>
            <?php foreach($patients as $p) { ?>
                <option value="<?= $p['user_id']; ?>" <?= ($patient_filter==$p['user_id'])?'selected':''; ?>><?= htmlspecialchars($p['name']); ?></option>
            <?php } ?>
        </select>

        <label>Doctor:</label>
        <select name="doctor">
            <option value="">All</option>
            <?php foreach($doctors as $d) { ?>
                <option value="<?= $d['user_id']; ?>" <?= ($doctor_filter==$d['user_id'])?'selected':''; ?>><?= htmlspecialchars($d['name']); ?></option>
            <?php } ?>
        </select>

        <label>Status:</label>
        <select name="status">
            <option value="">All</option>
            <option value="pending" <?= ($status_filter=='pending')?'selected':''; ?>>Pending</option>
            <option value="completed" <?= ($status_filter=='completed')?'selected':''; ?>>Completed</option>
            <option value="cancelled" <?= ($status_filter=='cancelled')?'selected':''; ?>>Cancelled</option>
            <option value="confirmed" <?= ($status_filter=='confirmed')?'selected':''; ?>>Confirmed</option>
        </select>

        <button type="submit">Filter</button>
    </form>

    <!-- Appointments Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Description</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($appointments) > 0): ?>
                <?php foreach($appointments as $a): ?>
                    <tr>
                        <td><?= $a['appointment_id']; ?></td>
                        <td><?= htmlspecialchars($a['patient_name']); ?></td>
                        <td><?= htmlspecialchars($a['doctor_name']); ?></td>
                        <td><?= $a['date_time']; ?></td>
                        <td><span class="status-badge status-<?= $a['status']; ?>"><?= ucfirst($a['status']); ?></span></td>
                        <td><?= htmlspecialchars($a['description']); ?></td>
                        <td>
                            <a href="?appointment_id=<?= $a['appointment_id']; ?>&update_status=pending" class="update-link pending">Pending</a>
                            <a href="?appointment_id=<?= $a['appointment_id']; ?>&update_status=completed" class="update-link completed">Completed</a>
                            <a href="?appointment_id=<?= $a['appointment_id']; ?>&update_status=cancelled" class="update-link cancelled">Cancelled</a>
                            <a href="?appointment_id=<?= $a['appointment_id']; ?>&update_status=confirmed" class="update-link confirmed">Confirmed</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No appointments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
