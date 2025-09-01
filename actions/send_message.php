<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user']['id'];
    $receiver_id = $_POST['receiver_id'];
    $service_id = $_POST['service_id'];
    $message = trim($_POST['message']);

    if (!empty($message)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO messages (service_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiis", $service_id, $sender_id, $receiver_id, $message);
        mysqli_stmt_execute($stmt);
    }

    header("Location: ../pages/service_view.php?id=" . $service_id);
    exit;
}
?>