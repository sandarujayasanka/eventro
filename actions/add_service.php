<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seller_id = $_SESSION['user']['id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $price = $_POST['price'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    // Try to find a client in same district
    $clientQuery = "SELECT id FROM users WHERE role = 'client' AND district = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $clientQuery);
    mysqli_stmt_bind_param($stmt, "s", $district);
    mysqli_stmt_execute($stmt);
    $clientResult = mysqli_stmt_get_result($stmt);
    $clientData = mysqli_fetch_assoc($clientResult);

    $client_id = $clientData ? $clientData['id'] : null;

    // First, save the service (without image in services table for now)
    if ($client_id !== null) {
        $query = "INSERT INTO services (seller_id, client_id, title, description, price, district, category) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iissdss", $seller_id, $client_id, $title, $description, $price, $district, $category);
    } else {
        $query = "INSERT INTO services (seller_id, title, description, price, district, category) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "issdss", $seller_id, $title, $description, $price, $district, $category);
    }

    if (mysqli_stmt_execute($stmt)) {
        $service_id = mysqli_insert_id($conn);
        
        // Handle multiple image uploads
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $upload_dir = '../assets/uploads/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $uploaded_count = 0;
            
            // Loop through each uploaded file
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_extension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                    
                    if (in_array($file_extension, $allowed_types)) {
                        $new_filename = 'service_' . $service_id . '_' . time() . '_' . $i . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                            // Check if service_images table exists, if not, update services table with first image
                            if ($uploaded_count === 0) {
                                // Update services table with first image as main image
                                $update_query = "UPDATE services SET image = ? WHERE id = ?";
                                $update_stmt = mysqli_prepare($conn, $update_query);
                                mysqli_stmt_bind_param($update_stmt, "si", $new_filename, $service_id);
                                mysqli_stmt_execute($update_stmt);
                            }
                            
                            // Try to save to service_images table if it exists
                            $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'service_images'");
                            if (mysqli_num_rows($check_table) > 0) {
                                $image_query = "INSERT INTO service_images (service_id, image_path, image_name, is_primary) 
                                               VALUES (?, ?, ?, ?)";
                                $img_stmt = mysqli_prepare($conn, $image_query);
                                $is_primary = ($uploaded_count === 0) ? 1 : 0;
                                mysqli_stmt_bind_param($img_stmt, "issi", $service_id, $upload_path, $new_filename, $is_primary);
                                mysqli_stmt_execute($img_stmt);
                            }
                            
                            $uploaded_count++;
                        }
                    }
                }
            }
        }
        
        header("Location: ../pages/dashboard_seller.php?success=1&images=" . $uploaded_count);
        exit;
    } else {
        header("Location: ../pages/dashboard_seller.php?error=1");
        exit;
    }
}
?>