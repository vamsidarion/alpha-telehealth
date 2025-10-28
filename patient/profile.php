<?php
session_start();
include '../config.php';

// Example: patient ID (replace with session user_id in real login)
$patient_id = 3;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $update = "UPDATE users SET name='$name', email='$email' WHERE user_id=$patient_id";
    if ($conn->query($update)) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Fetch current patient info
$sql = "SELECT * FROM users WHERE user_id=$patient_id";
$result = $conn->query($sql);
$patient = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        form { max-width: 500px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        label { display: block; margin: 15px 0 5px; font-weight: bold; }
        input[type="text"], input[type="email"] { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"] { margin-top: 20px; padding: 10px 20px; background: #2c6e49; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        input[type="submit"]:hover { background: #1f5037; }
        .message { text-align: center; margin: 10px 0; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main>
    <h1 style="text-align:center;">My Profile</h1>

    <?php if (!empty($success)) echo "<p class='message success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

    <form method="POST">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($patient['name']); ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($patient['email']); ?>" required>

        <input type="submit" value="Update Profile">
    </form>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
