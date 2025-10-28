<?php
include '../config.php';
$id = $_GET['id'] ?? null;
if($id){
    $conn->query("DELETE FROM activity_logs WHERE id=$id");
}
header('Location: observations.php'); exit;
