<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default for XAMPP
$dbname = 'freelance_system';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>