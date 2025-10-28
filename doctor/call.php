<?php
// call.php — handles doctor-patient video call
session_start();

// For now, get appointment_id from URL
if (!isset($_GET['appointment_id'])) {
    die("Appointment ID missing.");
}

$appointment_id = intval($_GET['appointment_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Video Call</title>
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
  <script>
    const appointmentId = <?= $appointment_id ?>;
    const socket = io("http://localhost:3000"); // Your Node.js socket server

    socket.on("connect", () => {
      console.log("Connected to socket server");
      socket.emit("joinRoom", appointmentId);
    });

    socket.on("callUser", (data) => {
      console.log("Incoming call from:", data);
      // You can integrate WebRTC here later
      alert("Call connected for Appointment ID: " + appointmentId);
    });
  </script>
</head>
<body style="font-family: Arial; text-align: center; margin-top: 50px;">
  <h2>Video Call — Appointment #<?= $appointment_id ?></h2>
  <p>Connecting to patient...</p>
</body>
</html>
