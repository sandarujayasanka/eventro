<?php
session_start();
require_once '../config/db.php';

// Redirect if not a client (optional, based on your logic)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    // header("Location: ../pages/login.php");
    // exit;
}

$selected_district = $_GET['district'] ?? '';
$selected_category = $_GET['category'] ?? '';

// Get all unique districts from services
$district_query = "SELECT DISTINCT district FROM services WHERE approved = 1";
$district_result = mysqli_query($conn, $district_query);

// Get all unique categories from services
$category_query = "SELECT DISTINCT category FROM services WHERE approved = 1 AND category IS NOT NULL AND category != ''";
$category_result = mysqli_query($conn, $category_query);

// Build the WHERE clause based on filters
$where_conditions = ["services.approved = 1"];
$params = [];
$param_types = "";

if (!empty($selected_district)) {
    $where_conditions[] = "services.district = ?";
    $params[] = $selected_district;
    $param_types .= "s";
}

if (!empty($selected_category)) {
    $where_conditions[] = "services.category = ?";
    $params[] = $selected_category;
    $param_types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get approved services based on selected filters
$query_services = "SELECT services.*, sellers.first_name AS seller_name
                   FROM services
                   JOIN sellers ON services.seller_id = sellers.id
                   WHERE $where_clause";

if (!empty($params)) {
    $stmt = mysqli_prepare($conn, $query_services);
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    mysqli_stmt_execute($stmt);
    $result_services = mysqli_stmt_get_result($stmt);
} else {
    $result_services = mysqli_query($conn, $query_services);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .btn-eventro {
            background: rgb(6, 6, 6);
            transition: all 0.3s ease;
        }
        .btn-eventro:hover {
            background: rgb(95, 69, 56);
            transform: translateY(-2px);
        }
        .service-card {
            transition: all 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        .search-focus:focus {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.2);
            border-color:rgb(0, 0, 0);
        }
        .category-btn {
            transition: all 0.3s ease;
        }
        .category-btn:hover {
            transform: translateY(-2px);
        }
        .category-btn.active {
            background:rgb(9, 9, 9);
            color: white;
            box-shadow: 0 4px 10px rgba(245, 114, 36, 0.3);
        }
        .logo-img {
            max-height: 40px;
            width: auto;
        }
        .eventro-nav {
            background-color:rgb(255, 255, 255);
            height: 60px;
        }
        .menu-item {
            color: black;
            padding: 0 10px;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .menu-item:hover {
            color:rgb(212, 40, 40);
        }
        
        /* Image Slider Styles */
        .slider-container {
            position: relative;
            height: 500px;
            overflow: hidden;
        }
        
        .slider-wrapper {
            display: flex;
            transition: transform 0.5s ease-in-out;
            height: 100%;
        }
        
        .slide {
            min-width: 100%;
            height: 100%;
            position: relative;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .slide-content {
            position: relative;
            z-index: 2;
            text-center;
            color: white;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 15px 20px;
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border-radius: 50%;
            z-index: 3;
        }
        
        .slider-nav:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }
        
        .slider-nav.prev {
            left: 20px;
        }
        
        .slider-nav.next {
            right: 20px;
        }
        
        .slider-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3;
        }
        
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .dot.active {
            background: white;
            transform: scale(1.2);
        }
        
        .slide-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .slide-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }
        
        /* Category Cards  */
        .category-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            min-height: 90px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .category-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border-color: #1e293b;
        }

        .category-card.active {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            border-color: #1e293b;
            box-shadow: 0 8px 25px rgba(30, 41, 59, 0.3);
        }

        .category-card.active:hover {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }

        .category-icon {
            font-size: 1.5rem;
            margin-bottom: 6px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-card:hover .category-icon {
            transform: scale(1.1);
        }

        .category-card.active .category-icon {
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            transition: color 0.3s ease;
            line-height: 1.2;
        }

        .category-card.active .category-title {
            color: white;
        }

        .category-card.active .category-subtitle {
            color: #cbd5e1;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .category-card:hover::before {
            left: 100%;
        }
        .categories-container {
            position: relative;
            padding: 24px 0;
        }
        @media (max-width: 768px) {
            .category-card {
                min-height: 80px;
                padding: 10px;
            }            
            .category-icon {
                font-size: 1.25rem;
                margin-bottom: 4px;
            }            
            .category-title {
                font-size: 0.8rem;
            }
            .categories-container {
                padding: 20px 0;
            }
        }

        @media (max-width: 768px) {
            .slider-container {
                height: 400px;
            }
            
            .slide-title {
                font-size: 2rem;
            }
            
            .slide-subtitle {
                font-size: 1rem;
            }
            
            .slider-nav {
                padding: 10px 15px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="eventro-nav sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center h-full justify-between">
            <div class="flex items-center space-x-4">
                <img src="../assets/eventro (1).png" 
                    class="h-8 w-auto sm:h-10 md:h-12 lg:h-13 xl:h-15 object-contain" 
                    alt="Eventro Logo">
            </div>
            <!-- Search Bar -->
            <div class="flex-1 hidden sm:flex mx-4 max-w-3xl">
                <div class="relative w-full">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                    <input type="text" id="searchInput" class="w-full pl-10 pr-3 py-2 border border-gray-300 bg-white placeholder-gray-500 search-focus focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm" placeholder="Search in Eventro...">
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="../pages/faq.php" class="menu-item">Help & Support</a>
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
    </nav>

    <!-- Image Slider Hero Section -->
    <div class="slider-container">
        <div class="slider-wrapper" id="sliderWrapper">
            <!-- Slide 1 - Wedding -->
            <div class="slide" style="background-image: url('../assets/wedding.avif');">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <h1 class="slide-title">Perfect Weddings</h1>
                    <p class="slide-subtitle">Create magical moments with our professional wedding services</p>
                    <a href="#services" class="btn-eventro text-white px-8 py-3 rounded-full text-lg font-medium shadow-lg">Explore Wedding Services</a>
                </div>
            </div>
            
            <!-- Slide 2 - Photography -->
            <div class="slide" style="background-image: url('../assets/photography.avif');">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <h1 class="slide-title">Professional Photography</h1>
                    <p class="slide-subtitle">Capture your precious moments with expert photographers</p>
                    <a href="#services" class="btn-eventro text-white px-8 py-3 rounded-full text-lg font-medium shadow-lg">Find Photographers</a>
                </div>
            </div>
            
            <!-- Slide 3 - Corporate Events -->
            <div class="slide" style="background-image: url('../assets/corporate.avif');">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <h1 class="slide-title">Corporate Events</h1>
                    <p class="slide-subtitle">Professional services for your business events and conferences</p>
                    <a href="#services" class="btn-eventro text-white px-8 py-3 rounded-full text-lg font-medium shadow-lg">Corporate Services</a>
                </div>
            </div>
            
            <!-- Slide 4 - Birthday Parties -->
            <div class="slide" style="background-image: url('../assets/birthday.avif');">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <h1 class="slide-title">Birthday Celebrations</h1>
                    <p class="slide-subtitle">Make birthdays unforgettable with our party services</p>
                    <a href="#services" class="btn-eventro text-white px-8 py-3 rounded-full text-lg font-medium shadow-lg">Party Services</a>
                </div>
            </div>
        </div>
        
        <!-- Navigation Arrows -->
        <button class="slider-nav prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-nav next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <!-- Dots Indicator -->
        <div class="slider-dots">
            <span class="dot active" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
            <span class="dot" onclick="currentSlide(3)"></span>
            <span class="dot" onclick="currentSlide(4)"></span>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <div class="relative" id="locationDropdown">
                        <button type="button" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-4 py-2.5 text-left focus:ring-2 focus:ring-orange-500" onclick="toggleLocationDropdown()">
                            <div class="flex items-center justify-between">
                                <span class="<?php echo empty($selected_district) ? 'text-gray-500' : 'text-gray-900'; ?>">
                                    <?php echo empty($selected_district) ? 'Select District' : htmlspecialchars($selected_district); ?>
                                </span>
                                <i class="fas fa-chevron-down text-gray-400" id="locationChevron"></i>
                            </div>
                        </button>
                        <div id="locationMenu" class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden">
                            <div class="p-3 border-b border-gray-100">
                                <input type="text" id="districtSearch" placeholder="Search districts..." class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <button type="button" class="w-full px-4 py-2 text-left hover:bg-gray-50 <?php echo empty($selected_district) ? 'bg-orange-50' : ''; ?>" data-value="" onclick="selectLocation('')">All Districts</button>
                                <?php while ($row = mysqli_fetch_assoc($district_result)): ?>
                                    <button type="button" class="w-full px-4 py-2 text-left hover:bg-gray-50 <?php echo $row['district'] === $selected_district ? 'bg-orange-50' : ''; ?>" data-value="<?php echo htmlspecialchars($row['district']); ?>" data-search="<?php echo strtolower($row['district']); ?>" onclick="selectLocation('<?php echo htmlspecialchars($row['district']); ?>')">
                                        <?php echo htmlspecialchars($row['district']); ?>
                                    </button>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    <form method="GET" id="locationForm" class="hidden">
                        <?php if (!empty($selected_category)): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
                        <?php endif; ?>
                        <input type="hidden" name="district" id="selectedDistrict" value="<?php echo htmlspecialchars($selected_district); ?>">
                    </form>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-orange-500" onchange="this.form.submit()" form="locationForm">
                        <option value="">All Categories</option>
                        <?php while ($row = mysqli_fetch_assoc($category_result)): ?>
                            <option value="<?php echo $row['category']; ?>" <?php echo $row['category'] === $selected_category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['category']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if (!empty($selected_district) || !empty($selected_category)): ?>
                    <a href="?" class="btn-eventro text-white px-4 py-2.5 rounded-lg text-sm font-medium mt-8">Clear Filters</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Category Cards -->
        <div class="categories-container">
            <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <?php
                $predefined_categories = [
                ['name' => 'Photography', 'icon' => 'fas fa-camera', 'url' => 'photography.php'],
                ['name' => 'Weddings', 'icon' => 'fas fa-heart', 'url' => 'weddings.php'],
                ['name' => 'Birthdays', 'icon' => 'fas fa-birthday-cake', 'url' => 'birthdays.php'],
                ['name' => 'Corporate', 'icon' => 'fas fa-briefcase', 'url' => 'corporate.php'],
                ['name' => 'Entertainment', 'icon' => 'fas fa-music', 'url' => 'entertainment.php'],
                ['name' => 'Cultural', 'icon' => 'fas fa-theater-masks', 'url' => 'cultural.php'],
                ['name' => 'Religious', 'icon' => 'fas fa-praying-hands', 'url' => 'religious.php'],
                ['name' => 'Exhibition', 'icon' => 'fas fa-image', 'url' => 'exhibition.php'],
                ['name' => 'Educational', 'icon' => 'fas fa-graduation-cap', 'url' => 'educational.php'],
            ];
                foreach ($predefined_categories as $cat):
                    $url = $cat['url'] . (!empty($selected_district) ? '?district=' . urlencode($selected_district) : '');
                    $isActive = $selected_category === $cat['name'];
                ?>
                    <div class="category-card <?php echo $isActive ? 'active' : ''; ?>" onclick="window.location.href='<?php echo htmlspecialchars($url); ?>'">
                        <div class="category-icon">
                            <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                        </div>
                        <div class="category-title"><?php echo htmlspecialchars($cat['name']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    <div id="services" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <?php if (mysqli_num_rows($result_services) > 0): ?>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                <?php
                if (!empty($selected_category) && !empty($selected_district)) {
                    echo htmlspecialchars($selected_category) . " services in " . htmlspecialchars($selected_district);
                } elseif (!empty($selected_category)) {
                    echo htmlspecialchars($selected_category) . " services";
                } elseif (!empty($selected_district)) {
                    echo "Services in " . htmlspecialchars($selected_district);
                } else {
                    echo "All services";
                }
                ?>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php while ($service = mysqli_fetch_assoc($result_services)): ?>
                    <div class="service-card bg-white rounded-lg shadow-md overflow-hidden cursor-pointer" onclick="window.location.href='service_view.php?id=<?php echo $service['id']; ?>'">
                        <div class="h-48">
                            <?php if (!empty($service['image'])): ?>
                                <img src="../assets/uploads/<?php echo htmlspecialchars($service['image']); ?>" alt="Service Image" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-3xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2"><?php echo htmlspecialchars($service['title']); ?></h3>
                            <div class="flex items-center mb-2">
                                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($service['description']); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Company Name</span>
                                <span class="text-sm text-gray-600"> <?php echo htmlspecialchars($service['seller_name']); ?></span>
                            </div>
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
                <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No services found</h3>
                <p class="text-gray-500">Try adjusting your filters or check back later!</p>
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

        // Image Slider JavaScript
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            const sliderWrapper = document.getElementById('sliderWrapper');
            sliderWrapper.style.transform = `translateX(-${index * 100}%)`;
            
            // Update dots
            dots.forEach(dot => dot.classList.remove('active'));
            dots[index].classList.add('active');
            
            currentSlideIndex = index;
        }

        function changeSlide(direction) {
            currentSlideIndex += direction;
            
            if (currentSlideIndex >= totalSlides) {
                currentSlideIndex = 0;
            } else if (currentSlideIndex < 0) {
                currentSlideIndex = totalSlides - 1;
            }
            
            showSlide(currentSlideIndex);
        }

        function currentSlide(index) {
            showSlide(index - 1);
        }

        // Auto-slide functionality
        setInterval(() => {
            changeSlide(1);
        }, 5000);

        // Existing JavaScript functions
        function toggleLocationDropdown() {
            const menu = document.getElementById('locationMenu');
            const chevron = document.getElementById('locationChevron');
            menu.classList.toggle('hidden');
            chevron.style.transform = menu.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function selectLocation(district) {
            document.getElementById('selectedDistrict').value = district;
            document.getElementById('locationForm').submit();
        }

        document.addEventListener('click', function(event) {
            if (!event.target.closest('#locationDropdown')) {
                document.getElementById('locationMenu').classList.add('hidden');
                document.getElementById('locationChevron').style.transform = 'rotate(0deg)';
            }
            if (!event.target.closest('#menuToggle') && !event.target.closest('#menuDropdown')) {
                const menuDropdown = document.getElementById('menuDropdown');
                if (menuDropdown) {
                    menuDropdown.classList.add('hidden');
                }
            }
        });

        document.getElementById('districtSearch')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.location-option').forEach(option => {
                const districtName = option.getAttribute('data-search') || option.textContent.toLowerCase();
                option.style.display = districtName.includes(searchTerm) ? 'block' : 'none';
            });
        });

        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.service-card').forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const seller = card.querySelector('.text-gray-600').textContent.toLowerCase();
                card.style.display = title.includes(searchTerm) || seller.includes(searchTerm) ? 'block' : 'none';
            });
        });

        // Menu Toggle (if exists)
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                const menuDropdown = document.getElementById('menuDropdown');
                menuDropdown.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>