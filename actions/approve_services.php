<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Approve or Reject action handling
if (isset($_GET['action']) && isset($_GET['id'])) {
    $service_id = intval($_GET['id']);
    $action = $_GET['action'] === 'approve' ? 1 : 0;

    $update = "UPDATE services SET approved = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "ii", $action, $service_id);
    mysqli_stmt_execute($stmt);

    // Set success message
    $success_message = $action ? 'Service approved successfully!' : 'Service rejected successfully!';
    
    header("Location: approve_services.php?message=" . urlencode($success_message));
    exit;
}

// Get all services that are not approved yet
$query = "SELECT services.*, users.name AS seller_name FROM services 
          JOIN users ON services.seller_id = users.id 
          WHERE services.approved = 0 
          ORDER BY services.created_at DESC";
$result = mysqli_query($conn, $query);

// Get total counts for statistics
$total_pending = mysqli_num_rows($result);
$total_services_query = "SELECT COUNT(*) as total FROM services";
$total_services = mysqli_fetch_assoc(mysqli_query($conn, $total_services_query))['total'];
$approved_services_query = "SELECT COUNT(*) as total FROM services WHERE approved = 1";
$approved_services = mysqli_fetch_assoc(mysqli_query($conn, $approved_services_query))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Services - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#8b5cf6'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Admin Panel</h1>
                <p class="text-sm text-gray-600 mt-1">Service Management</p>
            </div>
            
            <nav class="mt-6">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="admin_dashboard.php" 
                           class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-primary hover:text-white transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="admin_dashboard.php?page=sellers" 
                           class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-primary hover:text-white transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            Sellers
                        </a>
                    </li>
                    <li>
                        <a href="approve_services.php" 
                           class="flex items-center px-4 py-3 text-white bg-primary rounded-lg">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approve Services
                            <?php if ($total_pending > 0): ?>
                                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= $total_pending ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
                
                <div class="border-t border-gray-200 mt-8 pt-6 px-4">
                    <a href="../actions/logout.php" 
                       class="flex items-center px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800">Service Approval</h2>
                        <p class="text-gray-600 mt-1">Review and approve pending service requests</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                            <span class="text-sm text-gray-600">Total Services:</span>
                            <span class="font-semibold text-gray-800 ml-1"><?= $total_services ?></span>
                        </div>
                        <div class="bg-white px-4 py-2 rounded-lg shadow-sm border">
                            <span class="text-sm text-gray-600">Approved:</span>
                            <span class="font-semibold text-green-600 ml-1"><?= $approved_services ?></span>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <?php if (isset($_GET['message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= htmlspecialchars($_GET['message']) ?>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-700">Pending</h3>
                                <p class="text-3xl font-bold text-yellow-600"><?= $total_pending ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-700">Approved</h3>
                                <p class="text-3xl font-bold text-green-600"><?= $approved_services ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-700">Total</h3>
                                <p class="text-3xl font-bold text-blue-600"><?= $total_services ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Services Section -->
            <?php if ($total_pending > 0): ?>
                <div class="space-y-6">
                    <?php mysqli_data_seek($result, 0); // Reset result pointer ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="bg-white rounded-lg shadow-md border-l-4 border-yellow-400 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                                <div class="flex-1 lg:mr-6">
                                    <div class="flex items-center mb-3">
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            Pending Approval
                                        </span>
                                        <span class="text-sm text-gray-500 ml-3">
                                            <?= date('M d, Y - H:i', strtotime($row['created_at'])) ?>
                                        </span>
                                    </div>
                                    
                                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?= htmlspecialchars($row['title']) ?></h3>
                                    <p class="text-gray-700 mb-4 leading-relaxed"><?= htmlspecialchars($row['description']) ?></p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <strong class="mr-1">Seller:</strong> <?= htmlspecialchars($row['seller_name']) ?>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <strong class="mr-1">District:</strong> <?= htmlspecialchars($row['district']) ?>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <strong class="mr-1">Category:</strong> <?= htmlspecialchars($row['category']) ?>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                            </svg>
                                            <strong class="mr-1">Price:</strong> 
                                            <span class="text-green-600 font-semibold">Rs. <?= number_format($row['price'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Service Image -->
                                <?php if (!empty($row['image'])): ?>
                                <div class="mt-4 lg:mt-0 lg:ml-6">
                                    <div class="w-full lg:w-64 h-48 bg-gray-200 rounded-lg overflow-hidden">
                                        <img src="../assets/uploads/<?= htmlspecialchars($row['image']) ?>" 
                                             alt="Service Image" 
                                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-200">
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200 mt-6">
                                <a href="?action=approve&id=<?= $row['id'] ?>" 
                                   class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors duration-200"
                                   onclick="return confirm('Are you sure you want to approve this service?')">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Approve Service
                                </a>
                                
                                <a href="?action=reject&id=<?= $row['id'] ?>" 
                                   class="inline-flex items-center justify-center px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors duration-200"
                                   onclick="return confirm('Are you sure you want to reject this service? This action cannot be undone.')">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Reject Service
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <!-- No Pending Services Message -->
                <div class="bg-white p-12 rounded-lg shadow-md text-center">
                    <div class="mx-auto w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">All Caught Up!</h3>
                    <p class="text-gray-600 mb-6">No services are currently awaiting approval. Great job keeping up with the reviews!</p>
                    <a href="admin_dashboard.php" 
                       class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript for enhanced user experience -->
    <script>
        // Auto-hide success message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.bg-green-100');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.transition = 'opacity 0.5s ease-out';
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>