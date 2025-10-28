<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../config.php');

// === Doctor ID (temporary hardcoded) ===
$doctor_id = 2; // replace with session-based login later

// Handle form submission
if (isset($_POST['submit'])) {
    $appointment_id = $_POST['appointment_id'] ?? '';
    $patient_id = $_POST['patient_id'] ?? '';

    if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] == 0) {
        $filename = time() . '_' . basename($_FILES['report_file']['name']);
        $target_dir = '../uploads/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['report_file']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO reports (appointment_id, patient_id, doctor_id, file_name, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiis", $appointment_id, $patient_id, $doctor_id, $filename);
            $stmt->execute();
            $stmt->close();
            $success = "Report uploaded successfully!";
        } else {
            $error = "Failed to move uploaded file.";
        }
    } else {
        $error = "Please select a file to upload.";
    }
}

// Fetch completed appointments (case-insensitive)
$appointments_sql = "
    SELECT a.appointment_id, a.patient_id, a.status, u.name AS patient_name
    FROM appointments a
    JOIN users u ON a.patient_id = u.user_id
    WHERE a.doctor_id = $doctor_id AND LOWER(a.status) = 'completed'
    ORDER BY a.date_time DESC
";
$appointments = $conn->query($appointments_sql);

// Debug: uncomment to check rows
// if($appointments) echo "Rows found: ".$appointments->num_rows;
// else echo "SQL Error: ".$conn->error;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Report</title>
    <style>
        body { font-family: Arial; background:#eef6f7; padding:30px; }
        .container { background:#fff; padding:20px; border-radius:10px; max-width:600px; margin:auto; box-shadow:0 0 12px rgba(0,0,0,0.1);}
        input, select { width:100%; padding:10px; margin:8px 0; }
        button { padding:10px 20px; background:#00695c; color:#fff; border:none; border-radius:5px; cursor:pointer; }
        button:hover { background:#004d40; }
        .message { padding:10px; margin-bottom:10px; border-radius:5px; }
        .success { background:#4caf50; color:#fff; }
        .error { background:#f44336; color:#fff; }
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Report</h2>

    <?php if(isset($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Choose Appointment (Completed Only):</label>
        <select name="appointment_id" id="appointment_id" required>
            <option value="">-- Select --</option>
            <?php 
            if($appointments && $appointments->num_rows > 0){
                while($row = $appointments->fetch_assoc()){
                    $selected = ($row['appointment_id'] == ($_GET['appointment_id'] ?? '')) ? 'selected' : '';
                    echo "<option value='{$row['appointment_id']}' data-patient='{$row['patient_id']}' $selected>"
                        .htmlspecialchars($row['patient_name'])." (Appointment ID: {$row['appointment_id']})</option>";
                }
            } else {
                echo "<option value=''>No completed appointments found</option>";
            }
            ?>
        </select>

        <input type="hidden" name="patient_id" id="patient_id" value="<?= $_GET['patient_id'] ?? '' ?>">

        <label>Select Report File:</label>
        <input type="file" name="report_file" required>

        <button type="submit" name="submit">Upload Report</button>
    </form>
</div>

<script>
    const appointmentSelect = document.getElementById('appointment_id');
    const patientInput = document.getElementById('patient_id');

    appointmentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        patientInput.value = selectedOption.dataset.patient;
    });
</script>
</body>
</html>
