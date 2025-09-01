<?php
session_start();
require __DIR__ . '/../config/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: ../pages/login.php?error=" . urlencode("Email and password are required."));
    exit;
}

// Check users table
$user_query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 1) {
    $user = $user_result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user'] = $user;

        if ($user['role'] === 'admin') {
            header("Location: ../pages/dashboard_admin.php");
        } else {
            header("Location: ../pages/dashboard_client.php");
        }
        exit;
    } else {
        header("Location: ../pages/login.php?error=" . urlencode("Invalid password."));
        exit;
    }
}

// Check sellers table
$seller_query = "SELECT * FROM sellers WHERE email = ?";
$stmt = $conn->prepare($seller_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$seller_result = $stmt->get_result();

if ($seller_result->num_rows === 1) {
    $seller = $seller_result->fetch_assoc();
    if (password_verify($password, $seller['password'])) {
        $_SESSION['user_id'] = $seller['id'];
        $_SESSION['email'] = $seller['email'];
        $_SESSION['role'] = 'seller';
        $_SESSION['user'] = $seller;
        header("Location: ../pages/dashboard_seller.php");
        exit;
    } else {
        header("Location: ../pages/login.php?error=" . urlencode("Invalid password."));
        exit;
    }
}

header("Location: ../pages/login.php?error=" . urlencode("No account found with that email."));
exit;
