<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user']['id'];

$query = "SELECT messages.*, users.name AS sender_name 
          FROM messages 
          JOIN users ON messages.sender_id = users.id 
          WHERE receiver_id = ? 
          ORDER BY sent_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages from Clients</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-bold mb-4">Messages from Clients</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($msg = mysqli_fetch_assoc($result)) : ?>
                <div class="border-b py-3">
                    <p><strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong> <?= htmlspecialchars($msg['message']) ?></p>
                    <p class="text-xs text-gray-500"><?= $msg['sent_at'] ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-600">No messages found.</p>
        <?php endif; ?>
    </div>
</body>
</html>