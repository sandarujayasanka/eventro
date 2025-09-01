<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: index.php");
    exit();
}

include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$seller_email = $_SESSION['email'];

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$services_query = "SELECT * FROM services WHERE seller_id = ? ORDER BY created_at DESC";
$services_stmt = $conn->prepare($services_query);
$services_stmt->bind_param("i", $seller_id);
$services_stmt->execute();
$services_result = $services_stmt->get_result();
$services = $services_result->fetch_all(MYSQLI_ASSOC);

$pending_count = count(array_filter($services, function($s) { 
    return isset($s['status'], $s['approved']) && $s['status'] == 'pending' && $s['approved'] == 0;
}));

$approved_count = count(array_filter($services, function($s) { 
    return isset($s['approved']) && $s['approved'] == 1;
}));

$rejected_count = count(array_filter($services, function($s) { 
    return isset($s['status']) && $s['status'] == 'rejected';
}));

$profile_query = "SELECT * FROM sellers WHERE id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param("i", $seller_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$seller_profile = $profile_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 767px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .content {
                margin-left: 0 !important;
            }
        }
        /* Disable AOS animations on mobile for performance */
        @media (max-width: 767px) {
            [data-aos] {
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Hamburger Menu Button -->
    <button id="menu-toggle" class="md:hidden fixed top-4 left-4 z-50 p-2 text-gray-600 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg sidebar" id="sidebar">
        <div class="flex items-center justify-center h-16 bg-blue-600 text-white">
            <img src="../assets/bg logo.png" class="h-8 w-auto sm:h-10 object-contain" alt="Eventro Logo">
        </div>
        <nav class="mt-6">
            <div class="px-3 space-y-3">
                <a href="?page=dashboard" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors <?= $current_page == 'dashboard' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="?page=services" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors <?= $current_page == 'services' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="fas fa-list w-5 h-5 mr-3"></i>
                    My Services
                    <?php if (count($services) > 0): ?>
                        <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full"><?= count($services) ?></span>
                    <?php endif; ?>
                </a>
                <a href="?page=add-service" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors <?= $current_page == 'add-service' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="fas fa-plus-circle w-5 h-5 mr-3"></i>
                    Add Service
                </a>
                <a href="?page=profile" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors <?= $current_page == 'profile' ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' ?>">
                    <i class="fas fa-user w-5 h-5 mr-3"></i>
                    Profile
                </a>
                <a href="../pages/portfolio.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-briefcase w-5 h-5 mr-3"></i>
                    Portfolio
                </a>
                <a href="../pages/add_portfolio.php" class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-plus-circle w-5 h-5 mr-3"></i>
                    Add Portfolio Item
                </a>
            </div>
            <div class="absolute bottom-4 left-0 right-0 px-3">
                <a href="../actions/logout.php" class="flex items-center px-4 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors w-full">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                    Logout
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="md:ml-64 content min-h-screen">
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-4 md:px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-800">
                        <?php
                        switch($current_page) {
                            case 'services': echo 'My Services'; break;
                            case 'add-service': echo 'Add New Service'; break;
                            case 'profile': echo 'My Profile'; break;
                            default: echo 'Dashboard Overview';
                        }
                        ?>
                    </h2>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-600 hidden sm:inline"><?= htmlspecialchars($seller_profile['name'] ?? $seller_email) ?></span>
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            <?= strtoupper(substr($seller_profile['name'] ?? $seller_email, 0, 1)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 md:p-6">
            <?php if ($current_page == 'dashboard'): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8" data-aos="fade-up">
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i class="fas fa-list text-blue-600 text-lg md:text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs md:text-sm font-medium text-gray-600">Total Services</p>
                                <p class="text-xl md:text-2xl font-semibold text-gray-900"><?= count($services) ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <i class="fas fa-check-circle text-green-600 text-lg md:text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs md:text-sm font-medium text-gray-600">Approved</p>
                                <p class="text-xl md:text-2xl font-semibold text-gray-900"><?= $approved_count ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <i class="fas fa-clock text-yellow-600 text-lg md:text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs md:text-sm font-medium text-gray-600">Pending</p>
                                <p class="text-xl md:text-2xl font-semibold text-gray-900"><?= $pending_count ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 md:p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <i class="fas fa-times-circle text-red-600 text-lg md:text-xl"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs md:text-sm font-medium text-gray-600">Rejected</p>
                                <p class="text-xl md:text-2xl font-semibold text-gray-900"><?= $rejected_count ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow" data-aos="fade-up">
                    <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900">Recent Services</h3>
                    </div>
                    <div class="p-4 md:p-6">
                        <?php if (count($services) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach (array_slice($services, 0, 5) as $service): ?>
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border border-gray-200 rounded-lg">
                                        <div class="mb-3 sm:mb-0">
                                            <h4 class="text-sm md:text-base font-medium text-gray-900"><?= htmlspecialchars($service['title']) ?></h4>
                                            <p class="text-xs md:text-sm text-gray-600"><?= htmlspecialchars($service['category']) ?> • <?= htmlspecialchars($service['district']) ?></p>
                                        </div>
                                        <div class="flex items-center space-x-3 w-full sm:w-auto">
                                            <span class="text-base md:text-lg font-semibold text-gray-900">LKR <?= number_format($service['price'], 2) ?></span>
                                            <?php
                                            $badgeClass = $service['approved'] == 1 ? 'bg-green-100 text-green-600' : 
                                                          ($service['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                            $badgeText = $service['approved'] == 1 ? 'Approved' : 
                                                         ($service['status'] === 'rejected' ? 'Rejected' : 'Pending');
                                            ?>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                                <?= htmlspecialchars($badgeText) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-plus-circle text-3xl md:text-4xl text-gray-300 mb-4"></i>
                                <p class="text-base md:text-lg text-gray-600 mb-4">No services yet. Start by adding your first service!</p>
                                <a href="?page=add-service" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                                    Add Service
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($current_page == 'services'): ?>
                <div class="bg-white rounded-lg shadow" data-aos="fade-up">
                    <div class="px-4 md:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 sm:mb-0">All Services</h3>
                        <a href="?page=add-service" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                            <i class="fas fa-plus mr-2"></i>Add New Service
                        </a>
                    </div>
                    <div class="p-4 md:p-6">
                        <?php if (count($services) > 0): ?>
                            <div class="grid gap-4 md:gap-6">
                                <?php foreach ($services as $service): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 md:p-6">
                                        <div class="flex flex-col md:flex-row justify-between items-start mb-4">
                                            <div class="mb-3 md:mb-0">
                                                <h4 class="text-base md:text-xl font-semibold text-gray-900 mb-2"><?= htmlspecialchars($service['title']) ?></h4>
                                                <p class="text-sm md:text-base text-gray-600 mb-3"><?= htmlspecialchars($service['description']) ?></p>
                                                <div class="flex flex-wrap gap-3 text-xs md:text-sm text-gray-600">
                                                    <span><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($service['category']) ?></span>
                                                    <span><i class="fas fa-map-marker-alt mr-1"></i><?= htmlspecialchars($service['district']) ?></span>
                                                    <span><i class="fas fa-calendar mr-1"></i><?= date('M d, Y', strtotime($service['created_at'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="text-left md:text-right w-full md:w-auto">
                                                <div class="text-lg md:text-2xl font-bold text-gray-900 mb-2">LKR <?= number_format($service['price'], 2) ?></div>
                                                <?php
                                                $badgeClass = $service['approved'] == 1 ? 'bg-green-100 text-green-600' : 
                                                              ($service['status'] == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                                $statusText = $service['approved'] == 1 ? 'Approved' : 
                                                              ($service['status'] == 'rejected' ? 'Rejected' : 'Pending Approval');
                                                ?>
                                                <span class="px-2 py-1 rounded-full text-xs md:text-sm font-medium <?= $badgeClass ?>">
                                                    <?= htmlspecialchars($statusText) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if (isset($service['status']) && $service['status'] === 'rejected'): ?>
                                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                                                <div class="flex">
                                                    <i class="fas fa-exclamation-circle text-red-600 mr-3 mt-1"></i>
                                                    <div>
                                                        <h5 class="text-sm md:text-base font-medium text-red-800">Service Rejected</h5>
                                                        <p class="text-xs md:text-sm text-red-700 mt-1">
                                                            <?= isset($service['rejection_reason']) ? htmlspecialchars($service['rejection_reason']) : 'Please contact admin for more details.' ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 md:py-12">
                                <i class="fas fa-inbox text-4xl md:text-5xl text-gray-300 mb-4"></i>
                                <p class="text-lg md:text-xl text-gray-600 mb-4">No services found</p>
                                <p class="text-sm md:text-base text-gray-500 mb-6">Start building your service portfolio today!</p>
                                <a href="?page=add-service" class="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-lg hover:bg-blue-700 text-sm md:text-base">
                                    <i class="fas fa-plus mr-2"></i>Add Your First Service
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($current_page == 'add-service'): ?>
                <div class="max-w-2xl mx-auto" data-aos="fade-up">
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Create New Service</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">Fill in the details below to submit your service for approval</p>
                        </div>
                        <div class="p-4 md:p-6">
                            <form action="../actions/add_service.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                                <input type="hidden" name="seller_id" value="<?= htmlspecialchars($seller_id) ?>">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Service Title *</label>
                                    <input type="text" id="title" name="title" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base"
                                           placeholder="Enter a clear, descriptive title">
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Service Description *</label>
                                    <textarea id="description" name="description" rows="4" required 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base"
                                              placeholder="Describe your service in detail"></textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="district" class="block text-sm font-medium text-gray-700 mb-2">District *</label>
                                        <select id="district" name="district" required 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base">
                                            <option value="">Select District</option>
                                            <option value="Colombo">Colombo</option>
                                            <option value="Kalutara">Kalutara</option>
                                            <option value="Gampaha">Gampaha</option>
                                            <option value="Galle">Galle</option>
                                            <option value="Kandy">Kandy</option>
                                            <option value="Matara">Matara</option>
                                            <option value="Hambantota">Hambantota</option>
                                            <option value="Ratnapura">Ratnapura</option>
                                            <option value="Kegalle">Kegalle</option>
                                            <option value="Kurunegala">Kurunegala</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                        <select id="category" name="category" required 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base">
                                            <option value="">Select Category</option>
                                            <option value="Photography">Photography</option>
                                            <option value="Weddings">Weddings</option>
                                            <option value="Entertainment">Entertainment</option>
                                            <option value="Corporate">Corporate</option>
                                            <option value="Cultural">Cultural</option>
                                            <option value="Birthday">Birthday</option>
                                            <option value="Exhibition">Exhibition</option>
                                            <option value="Religious">Religious</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (LKR) *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">LKR</span>
                                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div>
                                    <label for="images" class="block text-sm font-medium text-gray-700 mb-2">Service Images *</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400">
                                        <input type="file" id="images" name="images[]" accept="image/*" multiple required 
                                               class="hidden" onchange="handleFileSelect(event)">
                                        <label for="images" class="cursor-pointer">
                                            <i class="fas fa-cloud-upload-alt text-2xl md:text-3xl text-gray-400 mb-3"></i>
                                            <div class="text-gray-600 text-sm">
                                                <span class="font-medium text-blue-600">Click to upload</span> or drag and drop
                                            </div>
                                            <p class="text-xs text-gray-500 mt-2">PNG, JPG, JPEG up to 5MB each</p>
                                        </label>
                                    </div>
                                    <div id="file-list" class="mt-3 space-y-2"></div>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-4 border-t border-gray-200">
                                    <a href="?page=services" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm md:text-base text-center">
                                        Cancel
                                    </a>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm md:text-base flex items-center justify-center">
                                        <i class="fas fa-paper-plane mr-2"></i>Submit for Approval
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php elseif ($current_page == 'profile'): ?>
                <div class="max-w-2xl mx-auto" data-aos="fade-up">
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                            <h3 class="text-base md:text-lg font-semibold text-gray-900">Profile Information</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">Manage your account details</p>
                        </div>
                        <div class="p-4 md:p-6">
                            <form action="../actions/update_profile.php" method="POST" class="space-y-6">
                                <div class="flex justify-center mb-6">
                                    <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-2xl md:text-3xl font-bold">
                                        <?= strtoupper(substr($seller_profile['name'] ?? $seller_email, 0, 1)) ?>
                                    </div>
                                </div>
                                <div>
                                    <label for="profile_name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" id="profile_name" name="name" 
                                           value="<?= htmlspecialchars($seller_profile['name'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base"
                                           placeholder="Enter your full name">
                                </div>
                                <div>
                                    <label for="profile_email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                                    <input type="email" id="profile_email" name="email" 
                                           value="<?= htmlspecialchars($seller_profile['email'] ?? '') ?>" readonly
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 text-sm md:text-base"
                                           placeholder="your.email@example.com">
                                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed</p>
                                </div>
                                <div>
                                    <label for="profile_phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" id="profile_phone" name="phone" 
                                           value="<?= htmlspecialchars($seller_profile['phone'] ?? '') ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base"
                                           placeholder="+94 XX XXX XXXX">
                                </div>
                                <div>
                                    <label for="profile_bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                    <textarea id="profile_bio" name="bio" rows="4"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm md:text-base"
                                              placeholder="Tell us about yourself and your expertise..."><?= htmlspecialchars($seller_profile['bio'] ?? '') ?></textarea>
                                </div>
                                <div class="pt-4 border-t border-gray-200">
                                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm md:text-base flex items-center justify-center">
                                        <i class="fas fa-save mr-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Sidebar toggle
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

        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            disable: window.innerWidth < 768 // Disable on mobile
        });

        // File select handler
        function handleFileSelect(event) {
            const fileList = document.getElementById('file-list');
            fileList.innerHTML = '';
            const files = event.target.files;
            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileItem = document.createElement('div');
                    fileItem.className = 'flex items-center justify-between p-2 bg-gray-50 rounded-lg text-sm';
                    fileItem.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-image text-blue-500 mr-2"></i>
                            <span class="truncate max-w-[150px] md:max-w-[200px]">${file.name}</span>
                            <span class="text-xs text-gray-500 ml-2">(${(file.size / 1024).toFixed(1)} KB)</span>
                        </div>
                        <i class="fas fa-check text-green-500"></i>
                    `;
                    fileList.appendChild(fileItem);
                }
            }
        }

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action="../actions/add_service.php"]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const price = document.getElementById('price').value;
                    if (parseFloat(price) <= 0) {
                        e.preventDefault();
                        alert('Please enter a valid price greater than 0');
                        return false;
                    }
                    const files = document.getElementById('images').files;
                    if (files.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one image for your service');
                        return false;
                    }
                    for (let i = 0; i < files.length; i++) {
                        if (files[i].size > 5 * 1024 * 1024) {
                            e.preventDefault();
                            alert('Each image must be less than 5MB in size');
                            return false;
                        }
                    }
                });
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>