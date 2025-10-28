<?php
include '../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];

    if(!empty($title) && !empty($description) && !empty($status)) {
        $stmt = $conn->prepare("INSERT INTO maintenance_records (title, description, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $status);

        if($stmt->execute()) {
            $message = "Maintenance record added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Maintenance Record</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; }
        .container { max-width: 600px; margin:50px auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1);}
        h1 { text-align:center; color:#2c6e49; margin-bottom:20px; }
        label { display:block; margin-top:10px; font-weight:bold; }
        input[type="text"], textarea, select { width:100%; padding:8px; margin-top:5px; border-radius:5px; border:1px solid #ccc; }
        button { margin-top:20px; background:#2c6e49; color:#fff; padding:10px 20px; border:none; border-radius:5px; font-weight:bold; cursor:pointer; }
        button:hover { background:#1f5037; }
        .message { margin-top:15px; text-align:center; color:green; font-weight:bold; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h1>Add Maintenance Record</h1>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>
    <form method="POST">
        <label for="title">Title</label>
        <input type="text" name="title" placeholder="Maintenance title" required>

        <label for="description">Description</label>
        <textarea name="description" rows="5" placeholder="Describe the maintenance task..." required></textarea>

        <label for="status">Status</label>
        <select name="status" required>
            <option value="Pending">Pending</option>
            <option value="In Progress">In Progress</option>
            <option value="Completed">Completed</option>
        </select>

        <button type="submit">Add Maintenance Record</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
