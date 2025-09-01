<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $check_query = "SELECT * FROM services WHERE id = $id";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $update_query = "UPDATE services SET approved = 1, status = 'approved' WHERE id = $id";
        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Service approved successfully'); window.location.href='admin.php';</script>";
            exit;
        } else {
            echo "<script>alert('Failed to approve the service');</script>";
        }
    } else {
        echo "<script>alert('Service not found');</script>";
    }
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$pending_query = "SELECT services.*, s1.first_name AS seller_name, s2.last_name AS client_name
          FROM services
          JOIN sellers s1 ON services.seller_id = s1.id
          LEFT JOIN sellers s2 ON services.client_id = s2.id
          WHERE approved = 0";
$pending_result = mysqli_query($conn, $pending_query);

$sellers_query = "SELECT id, first_name, email, created_at, 
                  (SELECT COUNT(*) FROM services WHERE seller_id = sellers.id) as total_services,
                  (SELECT COUNT(*) FROM services WHERE seller_id = sellers.id AND approved = 1) as approved_services
                  FROM sellers";
$sellers_result = mysqli_query($conn, $sellers_query);

$total_services_query = "SELECT COUNT(*) as total FROM services";
$total_services = mysqli_fetch_assoc(mysqli_query($conn, $total_services_query))['total'];

$pending_services_query = "SELECT COUNT(*) as total FROM services WHERE approved = 0";
$pending_services = mysqli_fetch_assoc(mysqli_query($conn, $pending_services_query))['total'];

$total_sellers_query = "SELECT COUNT(*) as total FROM sellers";
$total_sellers = mysqli_fetch_assoc(mysqli_query($conn, $total_sellers_query))['total'];

$total_clients_query = "SELECT COUNT(*) as total FROM users WHERE role = 'client'";
$total_clients = mysqli_fetch_assoc(mysqli_query($conn, $total_clients_query))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    <style>
        #sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 767px) {
            #sidebar {
                transform: translateX(-100%);
            }
            #sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex flex-col md:flex-row">
        <!-- Hamburger Menu Button -->
        <button id="menu-toggle" class="md:hidden p-4 text-gray-600 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Sidebar -->
        <div id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 bg-white shadow-lg min-h-screen md:transform-none z-50">
            <div class="border-b flex items-center justify-center h-16 bg-white text-white">
                <img src="../assets/bg logo.png" class="h-8 w-auto sm:h-10 object-contain" alt="Eventro Logo">
            </div>
            <div class="p-4 border-b border-gray-200">
                <p class="text-sm text-gray-900">Welcome, Administrator</p>
            </div>
            
            <nav class="mt-6">
                <ul class="space-y-2 px-4">
                    <li>
                        <a href="?page=dashboard" 
                           class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-primary hover:text-white transition-colors duration-200 <?= $current_page == 'dashboard' ? 'bg-primary text-white' : '' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="?page=sellers" 
                           class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-primary hover:text-white transition-colors duration-200 <?= $current_page == 'sellers' ? 'bg-primary text-white' : '' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            Sellers
                        </a>
                    </li>
                    <li>
                        <a href="?page=pending" 
                           class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-primary hover:text-white transition-colors duration-200 <?= $current_page == 'pending' ? 'bg-primary text-white' : '' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pending Services
                            <?php if ($pending_services > 0): ?>
                                <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= $pending_services ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
                
                <div class="absolute bottom-4 border-gray-200 mt-8 pt-6 px-4">
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
        <div class="flex-1 p-4 md:p-8">
            <?php if ($current_page == 'dashboard'): ?>
                <!-- Dashboard Overview -->
                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Dashboard Overview</h2>
                    
                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
                        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-700">Total Services</h3>
                                    <p class="text-2xl md:text-3xl font-bold text-blue-600"><?= $total_services ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-yellow-100 rounded-full">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-700">Pending Services</h3>
                                    <p class="text-2xl md:text-3xl font-bold text-yellow-600"><?= $pending_services ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-700">Total Sellers</h3>
                                    <p class="text-2xl md:text-3xl font-bold text-green-600"><?= $total_sellers ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                            <div class="flex items-center">
                                <div class="p-3 bg-purple-100 rounded-full">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-700">Total Clients</h3>
                                    <p class="text-2xl md:text-3xl font-bold text-purple-600"><?= $total_clients ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="bg-white p-4 md:p-6 rounded-lg shadow-md">
                        <h3 class="text-lg md:text-xl font-semibold text-gray-800 mb-4">Quick Actions</h3>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="?page=pending" class="bg-primary text-white px-4 py-2 md:px-6 md:py-3 rounded-lg hover:bg-blue-700 transition-colors text-center">
                                Review Pending Services
                            </a>
                            <a href="?page=sellers" class="bg-green-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-lg hover:bg-green-700 transition-colors text-center">
                                View All Sellers
                            </a>
                        </div>
                    </div>
                </div>

            <?php elseif ($current_page == 'sellers'): ?>
                <!-- Sellers List -->
                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">All Sellers</h2>
                    
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Services</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($seller = mysqli_fetch_assoc($sellers_result)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 bg-primary rounded-full flex items-center justify-center">
                                                    <span class="text-white text-xs font-semibold">
                                                        <?= strtoupper(substr($seller['first_name'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($seller['first_name']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500 break-all">
                                            <?= htmlspecialchars($seller['email']) ?>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                                <?= $seller['total_services'] ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                                <?= $seller['approved_services'] ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M d, Y', strtotime($seller['created_at'])) ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($current_page == 'pending'): ?>
                <!-- Pending Services -->
                <div class="mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">Pending Services for Approval</h2>
                    
                    <?php if (mysqli_num_rows($pending_result) == 0): ?>
                        <div class="bg-white p-6 md:p-8 rounded-lg shadow-md text-center">
                            <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <h3 class="text-lg md:text-xl font-semibold text-gray-800 mb-2">All Caught Up!</h3>
                            <p class="text-gray-600">No pending services to review at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php while ($service = mysqli_fetch_assoc($pending_result)): ?>
                            <div class="bg-white p-4 md:p-6 rounded-lg shadow-md border-l-4 border-yellow-400">
                                <div class="flex flex-col md:flex-row justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($service['title']) ?></h3>
                                        <p class="text-gray-700 mb-4 leading-relaxed text-sm md:text-base"><?= htmlspecialchars($service['description']) ?></p>
                                        
                                        <div class="grid grid-cols-1 gap-2 md:gap-4 mb-4">
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <strong>Seller:</strong> <?= htmlspecialchars($service['seller_name']) ?>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <strong>Category:</strong> <?= htmlspecialchars($service['category']) ?>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 mr="2" fill="none" stroke="currentColor" viewBox="0 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08 .402 2.599 1M12 8V7m0 1v-8m0 0v-1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                                </svg>
                                                <strong>Price:</strong> Rs. <?= number_format($service['price'], 2) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                                    <form action="../actions/approve_services.php" method="POST" class="w-full sm:w-auto">
                                        <input type="hidden" name="service_id" value="service_id <?= $service['id'] ?>">
                                        <button type="submit" 
                                                class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-width="2" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Approve
                                        </button>
                                    </form>
                                    
                                    <form action="/actions/reject_services.php" method="POST" class="w-full sm:w-auto">
                                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                                        <button type="submit" 
                                                class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center justify-center"
                                                onclick="return confirm('Are you sure you want to reject this service?')">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0" 24 24">
                                                <path stroke-linecap="round" stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // JavaScript for sidebar toggle
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 768 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html>