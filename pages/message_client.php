<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user']['id'];

$query = "SELECT messages.*, u1.name AS sender_name, u2.name AS receiver_name
          FROM messages
          JOIN users u1 ON messages.sender_id = u1.id
          JOIN users u2 ON messages.receiver_id = u2.id
          WHERE sender_id = ? OR receiver_id = ?
          ORDER BY sent_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $client_id, $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-10">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Your Messages</h1>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($msg = mysqli_fetch_assoc($result)) : ?>
                <div class="border-b py-3">
                    <p><strong>From:</strong> <?= htmlspecialchars($msg['sender_name']) ?></p>
                    <p><strong>To:</strong> <?= htmlspecialchars($msg['receiver_name']) ?></p>
                    <p><?= htmlspecialchars($msg['message']) ?></p>
                    <p class="text-xs text-gray-500"><?= $msg['sent_at'] ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-600">No messages found.</p>
        <?php endif; ?>
    </div>
</body>
</html>