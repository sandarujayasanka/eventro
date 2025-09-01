<?php
session_start();

// Restrict access to sellers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit();
}

include '../config/db.php';

$seller_id = $_SESSION['user_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $media_type = $_POST['media_type'] ?? '';
    
    // Validate inputs
    if (empty($title) || empty($media_type) || !in_array($media_type, ['image', 'video'])) {
        $error = "Title and valid media type are required.";
    } else {
        $target_dir = "../uploads/portfolio/";
        $thumb_dir = "../uploads/portfolio/thumbs/";
        
        // Ensure directories exist
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        if (!is_dir($thumb_dir)) mkdir($thumb_dir, 0777, true);
        
        $file = $_FILES['media_file'];
        $file_name = basename($file['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = $media_type === 'image' ? ['jpg', 'jpeg', 'png', 'gif'] : ['mp4', 'webm'];
        
        // Validate file type
        if (!in_array($file_ext, $allowed_types)) {
            $error = "Invalid file type. Allowed: " . implode(', ', $allowed_types);
        } else {
            $new_file_name = uniqid() . '.' . $file_ext;
            $target_file = $target_dir . $new_file_name;
            
            // Handle thumbnail for videos
            $thumbnail = '';
            if ($media_type === 'video' && isset($_FILES['thumbnail']) && !empty($_FILES['thumbnail']['name'])) {
                $thumb_file = $_FILES['thumbnail'];
                $thumb_ext = strtolower(pathinfo($thumb_file['name'], PATHINFO_EXTENSION));
                if (in_array($thumb_ext, ['jpg', 'jpeg', 'png'])) {
                    $thumb_name = uniqid() . '.' . $thumb_ext;
                    $thumb_target = $thumb_dir . $thumb_name;
                    if (move_uploaded_file($thumb_file['tmp_name'], $thumb_target)) {
                        $thumbnail = $thumb_name;
                    } else {
                        $error = "Failed to upload thumbnail.";
                    }
                } else {
                    $error = "Invalid thumbnail type. Allowed: jpg, jpeg, png.";
                }
            }
            
            // Move uploaded file
            if (!isset($error) && move_uploaded_file($file['tmp_name'], $target_file)) {
                // Insert into database
                $insert_query = "INSERT INTO portfolio_gallery (seller_id, type, file_path, thumbnail, title, description, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("isssss", $seller_id, $media_type, $new_file_name, $thumbnail, $title, $description);
                if ($insert_stmt->execute()) {
                    $success = "Media uploaded successfully!";
                    // Redirect to portfolio page to view the new item
                    header("Location: portfolio.php");
                    exit();
                } else {
                    $error = "Failed to save media to database.";
                }
            } else {
                $error = "Failed to upload file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Portfolio Item</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <style>
        .nav-sticky {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .upload-form {
            background: #f8fafc;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navigation -->
    <nav class="nav-sticky shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="portfolio.php" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Portfolio
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard_seller.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="upload-form" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Add New Portfolio Item</h2>
            <?php if(isset($error)): ?>
                <p class="text-red-500 mb-4 text-center"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if(isset($success)): ?>
                <p class="text-green-500 mb-4 text-center"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label for="media_type" class="block text-sm font-medium text-gray-700">Media Type</label>
                    <select id="media_type" name="media_type" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="toggleThumbnailField()">
                        <option value="">Select Type</option>
                        <option value="image">Image</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <div>
                    <label for="media_file" class="block text-sm font-medium text-gray-700">Upload File</label>
                    <input type="file" id="media_file" name="media_file" required
                           accept="image/*,video/mp4,video/webm"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                <div id="thumbnail_field" class="hidden">
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700">Video Thumbnail (Optional)</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                <div class="text-center">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all duration-300">
                        Upload Media
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-gray-400">© <?= date('Y') ?> Portfolio Management. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Toggle Thumbnail Field
        function toggleThumbnailField() {
            const mediaType = document.getElementById('media_type').value;
            const thumbnailField = document.getElementById('thumbnail_field');
            thumbnailField.classList.toggle('hidden', mediaType !== 'video');
        }
    </script>
</body>
</html>