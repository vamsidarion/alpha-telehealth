<?php
include '../config.php';

// Ensure the form was submitted properly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    // Collect form values safely
    $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $uploaded_on = date('Y-m-d H:i:s');

    // Validate required fields
    if ($patient_id > 0 && $type !== '' && $_FILES['file']['error'] === 0) {

        // Prepare filename and upload path
        $original_name = basename($_FILES['file']['name']);
        $file_name = "doc_" . uniqid() . "_" . $original_name;
        $target_dir = "../uploads/";
        $target_path = $target_dir . $file_name;

        // Create uploads folder if not exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO documents (patient_id, type, file_name, uploaded_on) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $patient_id, $type, $file_name, $uploaded_on);
            $stmt->execute();
            header("Location: documents.php?upload=success");

            exit;
        } else {
            echo "<p style='color:red;'>File upload failed. Please check folder permissions.</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid form data. Please select a file and document type.</p>";
    }
} else {
    echo "<p style='color:red;'>Invalid access. Please upload through the dashboard form.</p>";
}
?>
