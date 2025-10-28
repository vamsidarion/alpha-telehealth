<?php
include '../config.php';




// Assume patient is logged in
$patient_id = 3; // Replace with session value: $_SESSION['user_id']

$upload_dir = '../uploads/';

// Ensure upload directory exists and is writable
if(!is_dir($upload_dir)){
    if(!mkdir($upload_dir, 0777, true)){
        die("Failed to create upload directory. Check folder permissions.");
    }
}
if(!is_writable($upload_dir)){
    die("Upload directory is not writable. Check folder permissions.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

    $file = $_FILES['document'];
    $type = $_POST['type'] ?? 'general';

    // Sanitize file name and prevent collisions
    $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
    $target_file = $upload_dir . $file_name;

    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        // Insert into documents table
        $sql = "INSERT INTO documents (file_name, type, uploaded_at, uploaded_by, patient_id)
                VALUES ('$file_name', '$type', NOW(), $patient_id, $patient_id)";
        if($conn->query($sql)){
            $success = "Document uploaded successfully!";
        } else {
            $error = "Database error: " . $conn->error;
        }
    } else {
        $error = "Failed to move uploaded file. Check permissions.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Document</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; }
        .container { max-width: 600px; margin:50px auto; }
        form { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        input, select { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
        input[type="submit"] { background:#2c6e49; color:#fff; border:none; cursor:pointer; }
        input[type="submit"]:hover { background:#1f5037; }
        .success { color:green; text-align:center; }
        .error { color:red; text-align:center; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1>Upload Document</h1>

    <?php if(!empty($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Document Type</label>
        <select name="type" required>
            <option value="general">General</option>
            <option value="prescription">Prescription</option>
            <option value="report">Report</option>
        </select>

        <label>Select File</label>
        <input type="file" name="document" required>

        <input type="submit" value="Upload">
    </form>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
