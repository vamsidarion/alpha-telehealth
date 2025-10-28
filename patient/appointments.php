<?php
include '../config.php';

// Temporary dummy patient ID
$patient_id = 3;

// Handle new appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $doctor_id = (int)$_POST['doctor_id'];
    $date_time = $conn->real_escape_string($_POST['date_time']);
    $description = $conn->real_escape_string($_POST['description']);

    $sql = "INSERT INTO appointments (patient_id, doctor_id, date_time, description) VALUES ($patient_id, $doctor_id, '$date_time', '$description')";
    if ($conn->query($sql)) {
        $success = "Appointment booked successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Handle cancellation
if (isset($_GET['cancel'])) {
    $appointment_id = (int)$_GET['cancel'];
    $conn->query("UPDATE appointments SET status='cancelled' WHERE appointment_id=$appointment_id AND patient_id=$patient_id");
}

// Filter by status
$status_filter = $_GET['status'] ?? 'all';
$sql = "SELECT a.*, u.name AS doctor_name 
        FROM appointments a 
        JOIN users u ON a.doctor_id=u.user_id 
        WHERE a.patient_id=$patient_id";

if ($status_filter !== 'all') {
    $sql .= " AND a.status='$status_filter'";
}

$sql .= " ORDER BY a.date_time DESC";
$result = $conn->query($sql);
$appointments = $result->fetch_all(MYSQLI_ASSOC);

// Fetch doctors for booking
$doctor_result = $conn->query("SELECT user_id, name FROM users WHERE role='doctor'");
$doctors = $doctor_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .appointments { max-width: 800px; margin: 30px auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c6e49; color: #fff; }
        tr:hover { background-color: #f1f1f1; }
        form { background: #fff; padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input, select, textarea { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"], button { background-color: #2c6e49; color: #fff; border: none; cursor: pointer; padding: 10px 20px; border-radius:5px; margin:5px; }
        input[type="submit"]:hover, button:hover { background-color: #1f5037; }
        .message { text-align:center; margin: 10px 0; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
        a.cancel { color: red; text-decoration: none; }
        a.cancel:hover { text-decoration: underline; }

        /* Status badges */
        .status-badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; color: #fff; }
        .status-pending { background-color: orange; }
        .status-completed { background-color: green; }
        .status-cancelled { background-color: red; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main>
    <h1 style="text-align:center;">My Appointments</h1>

    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

    <!-- Filter Buttons -->
    <form method="GET" style="text-align:center;margin-bottom:20px;">
        <button type="submit" name="status" value="all">All</button>
        <button type="submit" name="status" value="pending">Pending</button>
        <button type="submit" name="status" value="completed">Completed</button>
        <button type="submit" name="status" value="cancelled">Cancelled</button>
        <button type="submit" name="status" value="confirmed">Confirmed</button>
    </form>

    <!-- Booking Form -->
    <form method="POST">
        <h3>Book New Appointment</h3>
        <label for="doctor_id">Select Doctor</label>
        <select name="doctor_id" id="doctor_id" required>
            <option value="">--Select Doctor--</option>
            <?php foreach($doctors as $doc) { ?>
                <option value="<?= $doc['user_id']; ?>"><?= htmlspecialchars($doc['name']); ?></option>
            <?php } ?>
        </select>

        <label for="date_time">Appointment Date & Time</label>
        <input type="datetime-local" name="date_time" id="date_time" required>

        <label for="description">Reason / Description</label>
        <textarea name="description" id="description" rows="3"></textarea>

        <input type="submit" name="book_appointment" value="Book Appointment">
    </form>

    <!-- Appointment List -->
    <div class="appointments">
        <h3>Your Appointments</h3>
        <table>
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($appointments) > 0): ?>
                    <?php foreach($appointments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['doctor_name']); ?></td>
                            <td><?= $a['date_time']; ?></td>
                            <td>
                                <span class="status-badge status-<?= $a['status']; ?>">
                                    <?= ucfirst($a['status']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($a['description']); ?></td>
                            <td>
                                <?php if($a['status']=='pending'): ?>
                                    <a href="?cancel=<?= $a['appointment_id']; ?>" class="cancel">Cancel</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">No appointments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
