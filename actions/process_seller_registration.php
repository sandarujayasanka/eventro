<?php
// DB connection (adjust credentials)
$host = 'localhost';
$db = 'freelance_system';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize & get POST data
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone_country_code = $_POST['phone_country_code'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$nic = $_POST['nic'] ?? '';
$dob = $_POST['dob'] ?? '';
$business_type = $_POST['business_type'] ?? '';
$business_name = $_POST['business_name'] ?? '';
$business_reg_no = $_POST['business_reg_no'] ?? '';
$business_category = $_POST['business_category'] ?? '';
$street_address = $_POST['street_address'] ?? '';
$city = $_POST['city'] ?? '';
$province = $_POST['province'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$country = $_POST['country'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Simple validation
if (!$first_name || !$last_name || !$email || !$password || !$confirm_password) {
    die("Please fill required fields.");
}
if ($password !== $confirm_password) {
    die("Passwords do not match.");
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO sellers (first_name, last_name, email, phone_country_code, phone_number, nic, dob, business_type, business_name, business_reg_no, business_category, street_address, city, province, postal_code, country, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssssssssssss",
    $first_name, $last_name, $email, $phone_country_code, $phone_number, $nic, $dob, $business_type, $business_name, $business_reg_no, $business_category, $street_address, $city, $province, $postal_code, $country, $hashed_password);

// Execute
if ($stmt->execute()) {
    echo "Seller registration successful! Await admin approval.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>