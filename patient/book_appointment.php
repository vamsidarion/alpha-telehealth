<?php
include '../config.php';
session_start();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $date_time = $_POST['date_time'];
    $description = trim($_POST['description']);

    if (!empty($patient_id) && !empty($doctor_id) && !empty($date_time) && !empty($description)) {

        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, date_time, description, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiss", $patient_id, $doctor_id, $date_time, $description);

        if ($stmt->execute()) {
            echo "<script>alert('Appointment booked successfully!'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Error while booking appointment: " . addslashes($conn->error) . "'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
    }
}
?>
