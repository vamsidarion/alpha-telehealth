<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Get all the data from the form
    $doctor_id = $_POST['doctor_id'];
    $patient_id = $_POST['patient_id'];
    $log_date = $_POST['log_date'];
    $problem_area = $_POST['problem_area'];
    $main_diagnosis = $_POST['main_diagnosis'];
    $description = $_POST['description'];
    $medication = $_POST['medication']; // <-- This is the new variable

    // 2. Prepare the SQL statement
    // Notice the new 'medication' column and the extra '?'
    $sql = "INSERT INTO patient_logs (
                doctor_id, 
                patient_id, 
                log_date, 
                problem_area, 
                main_diagnosis, 
                description, 
                medication
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        // Log the error for debugging
        error_log("Error preparing statement: " . $conn->error); 
        // Redirect with a generic error
        header("Location: dashboard.php?error=database_error");
        exit();
    }

    // 3. Bind the parameters
    // Notice the new 's' for the medication string
    $stmt->bind_param("iisssss", 
        $doctor_id, 
        $patient_id, 
        $log_date, 
        $problem_area, 
        $main_diagnosis, 
        $description, 
        $medication
    );

    // 4. Execute and redirect
    if ($stmt->execute()) {
        // Success
        header("Location: dashboard.php?success=log_saved");
        exit();
    } else {
        // Log the error for debugging
        error_log("Error executing statement: " . $stmt->error); 
        // Redirect with a specific error if possible, otherwise generic
        header("Location: dashboard.php?error=" . urlencode($stmt->error));
        exit();
    }

    $stmt->close();
    $conn->close();

} else {
    // Not a POST request
    header("Location: dashboard.php");
    exit();
}
?>

