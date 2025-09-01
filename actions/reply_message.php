<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header("Location: ../pages/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user']['id']; // Seller ID
    $receiver_id = (int)$_POST['receiver_id']; // Client ID
    $message = trim($_POST['message']);

    if (!empty($message) && $receiver_id > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "iis", $sender_id, $receiver_id, $message);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../pages/dashboard_seller.php?reply_sent=1");
            exit;
        } else {
            echo "Failed to send reply.";
        }
    } else {
        echo "Invalid reply.";
    }
} else {
    echo "Invalid request.";
}