<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = intval($_POST['doctor_id']);
    $description = $conn->real_escape_string($_POST['description']);

    if (!empty($description)) {
        $sql = "INSERT INTO descriptions (doctor_id, description, created_at) 
                VALUES ($doctor_id, '$description', NOW())";
        if ($conn->query($sql)) {
            echo "Description saved successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Description cannot be empty.";
    }
} else {
    echo "Invalid request.";
}
?>
