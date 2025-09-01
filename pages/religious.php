<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

$category = 'religious';
$selected_district = $_GET['district'] ?? '';

// Get all unique districts from services for this category
$district_query = "SELECT DISTINCT district FROM services WHERE approved = 1 AND category = ?";
$stmt = mysqli_prepare($conn, $district_query);
mysqli_stmt_bind_param($stmt, "s", $category);
mysqli_stmt_execute($stmt);
$district_result = mysqli_stmt_get_result($stmt);

// Build the WHERE clause based on filters
$where_conditions = ["services.approved = 1", "services.category = ?"];
$params = [$category];
$param_types = "s";

if (!empty($selected_district)) {
    $where_conditions[] = "services.district = ?";
    $params[] = $selected_district;
    $param_types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get approved photography services
$query_services = "SELECT services.*, sellers.first_name AS seller_name
                   FROM services
                   JOIN sellers ON services.seller_id = sellers.id
                   WHERE $where_clause
                   ORDER BY services.created_at DESC";

$stmt = mysqli_prepare($conn, $query_services);
mysqli_stmt_bind_param($stmt, $param_types, ...$params);
mysqli_stmt_execute($stmt);
$result_services = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Religious Services | Eventro </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .service-card {
            transition: all 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .navbar-shadow {
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06), 0 2px 2px -1px rgba(0, 0, 0, 0.06);
        }
        .hero-bg {
            background: url('../assets/reli.webp') center/cover no-repeat;
            position: relative;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navigation Bar -->
<nav class="bg-white navbar-shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-4">
                <img src="../assets/eventro (1).png" 
                    class="h-8 w-auto sm:h-10 md:h-12 lg:h-13 xl:h-15 object-contain" 
                    alt="Eventro Logo">
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-4">
                    <a href="#" class="menu-item">Help & Support</a>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button class="flex items-center space-x-2 text-gray-700 hover:text-green-600 transition-colors" onclick="toggleDropdown()">
                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-50">
                        <div class="py-2">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user']['email']) ?></p>
                            </div>
                            <div class="border-t border-gray-100 mt-2">
                                <a href="../actions/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<div class="hero-bg text-white py-20">
    <div class="hero-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="mb-6">
            <i class="fas fa-birthday-cake text-6xl mb-4 opacity-90"></i>
        </div>
        <h1 class="text-5xl md:text-6xl font-bold mb-6">Religious Services</h1>
        <p class="text-xl mb-8 opacity-90 max-w-3xl mx-auto">Celebrate another year with unforgettable memories and flawless birthday planning.</p>
        
        <!-- Quick Stats -->
        <div class="flex justify-center items-center space-x-8 text-lg">
            <div class="flex items-center">
                <i class="fas fa-users mr-2"></i>
                <span><?= mysqli_num_rows($result_services) ?>+ Religious Services</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-star mr-2"></i>
                <span>Top Rated</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-shield-alt mr-2"></i>
                <span>Verified Pros</span>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center">
            <div class="flex items-center space-x-2">
                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-white"></i>
                </div>
                <div>
                    <span class="font-semibold text-gray-900 block">Find birthday services in your area</span>
                </div>
            </div>
            
            <div class="flex-1 max-w-md">
                <form method="GET" class="w-full">
                    <select name="district" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" 
                            onchange="this.form.submit()">
                        <option value="">All Districts</option>
                        <?php while ($row = mysqli_fetch_assoc($district_result)) : ?>
                            <option value="<?= htmlspecialchars($row['district']) ?>" <?= $row['district'] === $selected_district ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['district']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
            
            <?php if (!empty($selected_district)): ?>
                <a href="photography.php" class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Clear location
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Religious Events Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Religious Events </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            <?php 
            $photo_types = [
            ['name' => 'Religious Festivals', 'icon' => 'fas fa-church', 'color' => 'blue'], 
            ['name' => 'Charity Events', 'icon' => 'fas fa-hands-helping', 'color' => 'purple'], 
            ['name' => 'Pilgrimages', 'icon' => 'fas fa-route', 'color' => 'yellow'], 
            ['name' => 'Rituals & Ceremonies', 'icon' => 'fas fa-praying-hands', 'color' => 'green'], 
            ['name' => 'Youth Religious Programs', 'icon' => 'fas fa-users', 'color' => 'red'] 
        ];

            
            foreach ($photo_types as $type): ?>
                <div class="text-center p-4 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="w-12 h-12 bg-<?= $type['color'] ?>-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="<?= $type['icon'] ?> text-<?= $type['color'] ?>-600"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700"><?= $type['name'] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Services Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    <?php if (mysqli_num_rows($result_services) > 0): ?>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                Religious Services <?= !empty($selected_district) ? 'in ' . htmlspecialchars($selected_district) : '' ?>
            </h2>
            <p class="text-gray-600">Found <?= mysqli_num_rows($result_services) ?> religious<?= mysqli_num_rows($result_services) !== 1 ? 's' : '' ?></p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php while ($service = mysqli_fetch_assoc($result_services)): ?>
                <div class="service-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden cursor-pointer" onclick="window.location.href='service_view.php?id=<?= $service['id'] ?>'">
                    <!-- Service Image -->
                    <div class="relative h-48 overflow-hidden">
                        <?php if (!empty($service['image'])): ?>
                            <img src="../assets/uploads/<?= htmlspecialchars($service['image']) ?>" 
                                 alt="Photography Service" 
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center">
                                <i class="fas fa-birthday-cake text-white text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        
                    </div>

                    <!-- Service Details -->
                    <div class="p-4">

                        <!-- Service Title -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                            <?= htmlspecialchars($service['title']) ?>
                        </h3>
                        <!-- Service Description -->
                        <div class="flex items-center mb-2">
                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($service['description']); ?></span>
                        </div>

                        <!-- Company name -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Company Name</span>
                            <span class="text-sm text-gray-600"> <?php echo htmlspecialchars($service['seller_name']); ?></span>
                        </div>

                        <!-- Location -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Location</span>
                            <span class="text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-1"></i>
                            <?php echo htmlspecialchars($service['district']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <i class="fas fa-church text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Religious Events found</h3>
            <p class="text-gray-500">
                <?php if ($selected_district): ?>
                    No Religious Events found in <?= htmlspecialchars($selected_district) ?>. Try selecting a different location.
                <?php else: ?>
                    No Religious Events are currently available. Check back later!
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <img src="../assets/bg logo.png" class="h-10 mb-4" alt="Eventro Logo">
                <p class="text-gray-300">Eventro is Sri Lanka’s dedicated event service marketplace.</p>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Contact Info</h4>
                <ul class="space-y-2 text-gray-300">
                    <li> info@eventro.com</li>
                    <li> 011 2 333 444</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">About eventro</h4>
                <ul class="space-y-2 text-gray-300">
                    <li><a href="../pages/about.php" class="hover:text-white">About Us</a></li>
                    <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Help & Support</h4>
                <ul class="space-y-2 text-gray-300">
                    <li><a href="../pages/faq.php" class="hover:text-white">FAQ</a></li>
                    <li><a href="#" class="hover:text-white">Trust & Safety</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
            <p>© 2025 Eventro. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const profileDropdown = document.getElementById('profileDropdown');
        if (!event.target.closest('#profileDropdown')) {
            profileDropdown.querySelector('.absolute').classList.add('hidden');
        }
    });
</script>

</body>
</html>