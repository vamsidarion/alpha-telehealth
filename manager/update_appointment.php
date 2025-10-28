<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config.php';

// Check if form data is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize and validate input
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if ($appointment_id <= 0 || empty($status)) {
        die("Invalid data provided.");
    }

    // Normalize status value (capitalize first letter)
    $status = ucfirst(strtolower($status));

    // Allowed status values (as per ENUM in DB)
    $allowed_status = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

    // Validate status before updating
    if (!in_array($status, $allowed_status)) {
        die("Invalid status value: " . htmlspecialchars($status));
    }

    // Prepare and execute update query
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("si", $status, $appointment_id);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment status updated successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error updating appointment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

} else {
    echo "Access denied.";
}
?>
