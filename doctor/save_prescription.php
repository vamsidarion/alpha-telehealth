<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = intval($_POST['doctor_id']);
    $prescription = $conn->real_escape_string($_POST['prescription']);

    if (!empty($prescription)) {
        $sql = "INSERT INTO prescriptions (doctor_id, prescription, created_at) 
                VALUES ($doctor_id, '$prescription', NOW())";
        if ($conn->query($sql)) {
            echo "Prescription saved successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Prescription cannot be empty.";
    }
} else {
    echo "Invalid request.";
}
?>
