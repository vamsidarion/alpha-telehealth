<?php
include '../config.php';

// Fetch all documents with uploader info
$docs = $conn->query("
    SELECT d.document_id, d.file_name, d.type, d.uploaded_at, d.patient_id, d.uploaded_by,
           u.name AS uploader_name, u.role AS uploader_role
    FROM documents d
    LEFT JOIN users u ON d.uploaded_by = u.user_id
    ORDER BY d.uploaded_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Documents</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f8; }
        .container { max-width: 1000px; margin:30px auto; }
        h1 { text-align:center; color:#2c6e49; margin-bottom:30px; }
        table { width: 100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding:12px 15px; border-bottom:1px solid #ddd; text-align:left; }
        th { background-color:#2c6e49; color:#fff; }
        tr:hover { background-color:#f1f1f1; }
        a.download { text-decoration:none; color:#fff; background:#2c6e49; padding:5px 10px; border-radius:5px; }
        a.download:hover { background:#1f5037; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1>All Uploaded Documents</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Uploader Name</th>
                <th>Role</th>
                <th>Document Type</th>
                <th>File Name</th>
                <th>Uploaded At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($docs) > 0): ?>
                <?php foreach($docs as $doc): ?>
                    <tr>
                        <td><?= $doc['document_id']; ?></td>
                        <td><?= htmlspecialchars($doc['uploader_name'] ?? 'Unknown'); ?></td>
                        <td><?= ucfirst($doc['uploader_role'] ?? 'N/A'); ?></td>
                        <td><?= htmlspecialchars($doc['type']); ?></td>
                        <td><?= htmlspecialchars($doc['file_name']); ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($doc['uploaded_at'])); ?></td>
                        <td>
                            <a class="download" href="../uploads/<?= $doc['file_name']; ?>" download>Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No documents uploaded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
