<?php
include '../config.php';

// Dummy admin ID for now
$admin_id = 1;

// Handle Add Patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_patient'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']); // For now plain text

    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', 'patient')";
    if ($conn->query($sql)) {
        $success = "Patient added successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

// Handle Delete Patient
if (isset($_GET['delete'])) {
    $patient_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE user_id=$patient_id AND role='patient'");
}

// Fetch all patients
$result = $conn->query("SELECT * FROM users WHERE role='patient' ORDER BY user_id DESC");
$patients = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Patients</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; }
        .container { max-width: 900px; margin: 30px auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c6e49; color: #fff; }
        tr:hover { background-color: #f1f1f1; }
        form { background: #fff; padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 8px 0; border-radius: 5px; border: 1px solid #ccc; }
        input[type="submit"] { background-color: #2c6e49; color: #fff; border: none; cursor: pointer; padding: 10px 20px; border-radius:5px; }
        input[type="submit"]:hover { background-color: #1f5037; }
        .action-links a { margin-right: 10px; color: red; text-decoration: none; }
        .success { color: green; text-align:center; }
        .error { color: red; text-align:center; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1 style="text-align:center;">Manage Patients</h1>

    <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <!-- Add Patient Form -->
    <form method="POST">
        <h3>Add New Patient</h3>
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="password" placeholder="Password" required>
        <input type="submit" name="add_patient" value="Add Patient">
    </form>

    <!-- Patients List -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($patients) > 0): ?>
                <?php foreach($patients as $p): ?>
                    <tr>
                        <td><?= $p['user_id']; ?></td>
                        <td><?= htmlspecialchars($p['name']); ?></td>
                        <td><?= htmlspecialchars($p['email']); ?></td>
                        <td class="action-links">
                            <a href="patients.php?delete=<?= $p['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this patient?');">Delete</a>
                            <!-- Edit functionality can be added later -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No patients found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
