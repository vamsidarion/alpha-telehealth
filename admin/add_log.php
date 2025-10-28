<?php
include '../config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if(!empty($user_id) && !empty($action)) {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $action);
        if($stmt->execute()) {
            $message = "Activity log added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill all fields.";
    }
}

// Fetch all users for dropdown
$users = $conn->query("SELECT user_id, name FROM users ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Activity Log</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; }
        .container { max-width: 500px; margin:50px auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1);}
        h1 { text-align:center; color:#2c6e49; margin-bottom:20px; }
        label { display:block; margin-top:10px; font-weight:bold; }
        select, input[type="text"], textarea { width:100%; padding:8px; margin-top:5px; border-radius:5px; border:1px solid #ccc; }
        button { margin-top:20px; background:#2c6e49; color:#fff; padding:10px 20px; border:none; border-radius:5px; font-weight:bold; cursor:pointer; }
        button:hover { background:#1f5037; }
        .message { margin-top:15px; text-align:center; color:green; font-weight:bold; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container">
    <h1>Add Activity Log</h1>
    <?php if($message) echo "<div class='message'>$message</div>"; ?>
    <form method="POST">
        <label for="user_id">User</label>
        <select name="user_id" required>
            <option value="">Select User</option>
            <?php while($user = $users->fetch_assoc()): ?>
                <option value="<?= $user['user_id'] ?>"><?= $user['name'] ?></option>
            <?php endwhile; ?>
        </select>

        <label for="action">Action</label>
        <input type="text" name="action" placeholder="Describe action..." required>

        <button type="submit">Add Log</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
