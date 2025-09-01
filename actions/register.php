<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']); // 'admin', 'seller', or 'client'

    $query = "INSERT INTO users (name, email, password, role) 
              VALUES ('$name', '$email', '$password', '$role')";

    if (mysqli_query($conn, $query)) {
        header("Location: ../pages/login.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>