<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$seller_id = ($user_role === 'seller') ? $user_id : (isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0);

// If client, ensure seller_id is provided
if ($user_role === 'client' && $seller_id === 0) {
    header("Location: index.php");
    exit();
}

// Fetch seller profile info
$profile_query = "SELECT * FROM sellers WHERE id = ?";
$profile_stmt = $conn->prepare($profile_query);
$profile_stmt->bind_param("i", $seller_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$seller_profile = $profile_result->fetch_assoc();

// Fetch portfolio gallery (previous work images/videos)
$gallery_query = "SELECT * FROM portfolio_gallery WHERE seller_id = ? ORDER BY created_at DESC";
$gallery_stmt = $conn->prepare($gallery_query);
$gallery_stmt->bind_param("i", $seller_id);
$gallery_stmt->execute();
$gallery_result = $gallery_stmt->get_result();
$portfolio_gallery = $gallery_result->fetch_all(MYSQLI_ASSOC);

// Fetch approved services only for portfolio
$services_query = "SELECT * FROM services WHERE seller_id = ? AND approved = 1 ORDER BY created_at DESC";
$services_stmt = $conn->prepare($services_query);
$services_stmt->bind_param("i", $seller_id);
$services_stmt->execute();
$services_result = $services_stmt->get_result();
$approved_services = $services_result->fetch_all(MYSQLI_ASSOC);

// Calculate portfolio stats
$total_services = count($approved_services);
$categories = array_unique(array_column($approved_services, 'category'));
$avg_price = $total_services > 0 ? array_sum(array_column($approved_services, 'price')) / $total_services : 0;
$districts_served = array_unique(array_column($approved_services, 'district'));

// Get recent activity (last 30 days)
$recent_date = date('Y-m-d', strtotime('-30 days'));
$recent_services = array_filter($approved_services, function($service) use ($recent_date) {
    return $service['created_at'] >= $recent_date;
});

// Separate videos and images for showcase
$videos = array_filter($portfolio_gallery, function($item) { return $item['type'] === 'video'; });
$images = array_filter($portfolio_gallery, function($item) { return $item['type'] === 'image'; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seller_profile['name'] ?? 'Seller') ?> - Professional Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <style>
        .hero-gradient {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.6)), 
                        url('../assets/hero.jpg') center/cover no-repeat;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .video-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1rem;
        }
        .social-btn {
            transition: all 0.3s ease;
        }
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .achievement-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        .achievement-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .featured-video {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            border-radius: 12px;
        }
        .featured-video iframe,
        .featured-video video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .nav-sticky {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .subscribe-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subscribe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(238, 90, 36, 0.4);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .tab-btn {
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        .tab-btn.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
        }
        .modal-content {
            position: relative;
            margin: auto;
            padding: 20px;
            width: 90%;
            max-width: 800px;
            top: 50%;
            transform: translateY(-50%);
        }
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #bbb;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Enhanced Navigation -->
    <nav class="nav-sticky shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class=" flessssex items-center">
                    <!-- <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 transition-colors"> -->
                        <!-- <i class="fas fa-arrow-left mr-2"></i> -->
                        <!-- Dashboard -->
                    <!-- </a> -->
                </div>
                <div class="flex items-center space-x-4">
                    
                    <button onclick="shareProfile()" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-share-alt mr-1"></i> Share Portfolio
                    </button>
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($seller_profile['first_name'] ?? $_SESSION['email'], 0, 1)) ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Enhanced Hero Section -->
    <div class="hero-gradient py-20 relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center" data-aos="fade-up">
                <!-- Profile Avatar -->
                <div class="relative inline-block mb-6">
                    <div class="w-40 h-40 bg-white rounded-full flex items-center justify-center text-blue-600 text-6xl font-bold shadow-2xl">
                        <?= strtoupper(substr($seller_profile['first_name'] ?? $_SESSION['email'], 0, 1)) ?>
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center border-4 border-white">
                        <i class="fas fa-check text-white text-lg"></i>
                    </div>
                </div>

                <h1 class="text-5xl font-bold text-white mb-4">
                    <?= htmlspecialchars($seller_profile['first_name'] ?? 'Professional Creator') ?>
                </h1>
                
                <p class="text-xl text-white opacity-90 mb-6 max-w-3xl mx-auto leading-relaxed">
                    <?= htmlspecialchars($seller_profile['business_category'] ?? 'Professional service provider creating amazing experiences and delivering quality work') ?>
                </p>

                <!-- Enhanced Stats Bar -->
                <div class="glass-effect rounded-2xl p-6 mb-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                    <div class="grid grid-cols-3 gap-6 text-center text-white">
                        <div>
                            <div class="text-3xl font-bold"><?= $total_services ?></div>
                            <div class="text-sm opacity-80">Services</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold"><?= count($portfolio_gallery) ?></div>
                            <div class="text-sm opacity-80">Portfolio Items</div>
                        </div>
                        <div>
                            <div class="text-3xl font-bold"><?= count($districts_served) ?></div>
                            <div class="text-sm opacity-80">Locations</div>
                        </div>
                    </div>
                </div>

                <!-- Subscribe/Follow Button -->
                <button class="subscribe-btn mb-8" onclick="followSeller()">
                    <i class="fas fa-bell mr-2"></i>
                    Follow for Updates
                </button>

                <!-- Enhanced Social Media Section -->
                <div class="flex justify-center space-x-4 mb-6" data-aos="fade-up" data-aos-delay="400">
                    <?php if(!empty($seller_profile['phone_number'])): ?>
                        <a href="<?= htmlspecialchars($seller_profile['phone_number']) ?>" target="_blank" 
                           class="social-btn w-14 h-14 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition-all duration-300">
                            <i class="fab fa-facebook-f text-white text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <?php if(!empty($seller_profile['email'])): ?>
                        <a href="<?= htmlspecialchars($seller_profile['email']) ?>" target="_blank" 
                           class="social-btn w-14 h-14 bg-gray-800 hover:bg-gray-900 rounded-full flex items-center justify-center transition-all duration-300">
                            <i class="fas fa-globe text-white text-xl"></i>
                        </a>
                    <?php endif; ?>
                    <?php if(!empty($seller_profile['phone_number'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $seller_profile['phone_number']) ?>" target="_blank" 
                           class="social-btn w-14 h-14 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center transition-all duration-300">
                            <i class="fab fa-whatsapp text-white text-xl"></i>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Contact Info -->
                <div class="flex justify-center items-center space-x-8 text-white text-sm" data-aos="fade-up" data-aos-delay="600">
                    <?php if(!empty($seller_profile['phone_number'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <span><?= htmlspecialchars($seller_profile['phone_number']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="flex items-center">
                        <i class="fas fa-envelope mr-2"></i>
                        <span><?= htmlspecialchars($seller_profile['email'] ?? $_SESSION['email']) ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar mr-2"></i>
                        <span>Since <?= date('M Y', strtotime($seller_profile['created_at'] ?? date('Y-m-d'))) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Tabs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Tab Navigation -->
            <div class="flex justify-center space-x-4 mb-8" data-aos="fade-up">
                <button onclick="switchTab('videos')" class="tab-btn active" id="videos-tab">
                    <i class="fas fa-video mr-2"></i>Videos
                </button>
                <button onclick="switchTab('gallery')" class="tab-btn" id="gallery-tab">
                    <i class="fas fa-images mr-2"></i>Photo Gallery
                </button>
                <?php if($user_role === 'seller'): ?>
                    <button onclick="switchTab('services')" class="tab-btn" id="services-tab">
                        <i class="fas fa-briefcase mr-2"></i>Services
                    </button>
                    <button onclick="switchTab('about')" class="tab-btn" id="about-tab">
                        <i class="fas fa-user mr-2"></i>About
                    </button>
                <?php endif; ?>
            </div>

            <!-- Videos Tab -->
            <div id="videos" class="tab-content active">
                <?php if(count($videos) > 0): ?>
                    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Video Portfolio</h2>
                    
                    <!-- Featured Video -->
                    <?php if(count($videos) > 0): ?>
                        <div class="mb-12" data-aos="fade-up">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Featured Work</h3>
                            <div class="featured-video bg-black rounded-xl overflow-hidden">
                                <video controls poster="../uploads/portfolio/thumbs/<?= htmlspecialchars($videos[0]['thumbnail'] ?? 'default-video.jpg') ?>" class="w-full">
                                    <source src="../uploads/portfolio/<?= htmlspecialchars($videos[0]['file_path']) ?>" type="video/mp4">
                                </video>
                            </div>
                            <div class="mt-4">
                                <h4 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($videos[0]['title']) ?></h4>
                                <p class="text-gray-600"><?= htmlspecialchars($videos[0]['description']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Video Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach(array_slice($videos, 1) as $video): ?>
                            <div class="video-card bg-white shadow-lg" data-aos="zoom-in">
                                <div class="relative">
                                    <video class="w-full h-48 object-cover cursor-pointer" 
                                           poster="../uploads/portfolio/thumbs/<?= htmlspecialchars($video['thumbnail'] ?? 'default-video.jpg') ?>"
                                           onclick="playVideo('<?= htmlspecialchars($video['file_path']) ?>', '<?= htmlspecialchars($video['title']) ?>')">
                                        <source src="../uploads/portfolio/<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                                    </video>
                                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                                        <i class="fas fa-play text-white text-4xl opacity-80 hover:opacity-100 transition-opacity"></i>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 mb-2"><?= htmlspecialchars($video['title']) ?></h4>
                                    <p class="text-gray-600 text-sm"><?= htmlspecialchars($video['description']) ?></p>
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="text-xs text-gray-500"><?= date('M d, Y', strtotime($video['created_at'])) ?></span>
                                        <button onclick="likeVideo(<?= $video['id'] ?>)" class="text-gray-400 hover:text-red-500 transition-colors">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16" data-aos="fade-up">
                        <i class="fas fa-video text-gray-300 text-6xl mb-6"></i>
                        <h3 class="text-2xl font-semibold text-gray-600 mb-4">No Videos Yet</h3>
                        <p class="text-gray-500">Video content will appear here when uploaded.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Photo Gallery Tab -->
            <div id="gallery" class="tab-content">
                <?php if(count($images) > 0): ?>
                    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Photo Gallery</h2>
                    <div class="image-gallery">
                        <?php foreach($images as $image): ?>
                            <div class="relative group cursor-pointer" data-aos="zoom-in" 
                                 onclick="openImageModal('<?= htmlspecialchars($image['file_path']) ?>', '<?= htmlspecialchars($image['title']) ?>')">
                                <img src="../uploads/portfolio/<?= htmlspecialchars($image['file_path']) ?>" 
                                     alt="<?= htmlspecialchars($image['title']) ?>"
                                     class="w-full h-64 object-cover rounded-lg shadow-lg group-hover:shadow-xl transition-all duration-300">
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg">
                                    <div class="absolute bottom-4 left-4 right-4 text-white">
                                        <h4 class="font-semibold"><?= htmlspecialchars($image['title']) ?></h4>
                                        <p class="text-sm opacity-90"><?= htmlspecialchars($image['description']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16" data-aos="fade-up">
                        <i class="fas fa-images text-gray-300 text-6xl mb-6"></i>
                        <h3 class="text-2xl font-semibold text-gray-600 mb-4">No Photos Yet</h3>
                        <p class="text-gray-500">Photo gallery will appear here when uploaded.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Services Tab (Visible to Sellers Only) -->
            <?php if($user_role === 'seller'): ?>
                <div id="services" class="tab-content">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Professional Services</h2>
                    <?php if(count($approved_services) > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <?php foreach($approved_services as $service): ?>
                                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300" data-aos="fade-up">
                                    <div class="h-48 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                        <?php if(!empty($service['images'])): ?>
                                            <img src="../uploads/services/<?= htmlspecialchars($service['images']) ?>" 
                                                 alt="<?= htmlspecialchars($service['title']) ?>"
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-briefcase text-white text-4xl opacity-50"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                                <?= htmlspecialchars($service['category']) ?>
                                            </span>
                                            <span class="text-lg font-bold text-green-600">
                                                LKR <?= number_format($service['price'], 2) ?>
                                            </span>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-3">
                                            <?= htmlspecialchars($service['title']) ?>
                                        </h3>
                                        <p class="text-gray-600 mb-4">
                                            <?= htmlspecialchars(substr($service['description'], 0, 120)) ?>...
                                        </p>
                                        <div class="flex items-center justify-between text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                <?= htmlspecialchars($service['district']) ?>
                                            </div>
                                            <div><?= date('M d, Y', strtotime($service['created_at'])) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-16" data-aos="fade-up">
                            <i class="fas fa-briefcase text-gray-300 text-6xl mb-6"></i>
                            <h3 class="text-2xl font-semibold text-gray-600 mb-4">No Services Available</h3>
                            <p class="text-gray-500">Professional services will appear here when added.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- About Tab (Visible to Sellers Only) -->
            <?php if($user_role === 'seller'): ?>
                <div id="about" class="tab-content">
                    <div class="max-w-4xl mx-auto">
                        <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">About Me</h2>
                        
                        <!-- Achievement Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                            <div class="achievement-card" data-aos="fade-up" data-aos-delay="100">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-briefcase text-blue-600 text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900"><?= $total_services ?></h3>
                                <p class="text-gray-600">Active Services</p>
                            </div>
                            
                            <div class="achievement-card" data-aos="fade-up" data-aos-delay="200">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-tags text-green-600 text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900"><?= count($categories) ?></h3>
                                <p class="text-gray-600">Categories</p>
                            </div>
                            
                            <div class="achievement-card" data-aos="fade-up" data-aos-delay="300">
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-map-marker-alt text-purple-600 text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900"><?= count($districts_served) ?></h3>
                                <p class="text-gray-600">Locations Served</p>
                            </div>
                            
                            <div class="achievement-card" data-aos="fade-up" data-aos-delay="400">
                                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-star text-yellow-600 text-2xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold text-gray-900">LKR <?= number_format($avg_price, 0) ?></h3>
                                <p class="text-gray-600">Average Price</p>
                            </div>
                        </div>

                        <!-- Bio Section -->
                        <div class="bg-gray-50 rounded-2xl p-8 mb-8" data-aos="fade-up">
                            <h3 class="text-2xl font-semibold text-gray-900 mb-4">Professional Bio</h3>
                            <p class="text-gray-700 leading-relaxed text-lg">
                                <?= htmlspecialchars($seller_profile['bio'] ?? 'Passionate professional dedicated to delivering exceptional services and creating memorable experiences. With expertise across multiple domains, I strive to exceed client expectations and bring creative visions to life.') ?>
                            </p>
                        </div>

                        <!-- Contact Section -->
                        <div class="bg-white border border-gray-200 rounded-2xl p-8" data-aos="fade-up">
                            <h3 class="text-2xl font-semibold text-gray-900 mb-6">Get In Touch</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php if(!empty($seller_profile['phone'])): ?>
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-phone mr-3 text-blue-600"></i>
                                        <span><?= htmlspecialchars($seller_profile['phone']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($seller_profile['email'])): ?>
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-envelope mr-3 text-blue-600"></i>
                                        <span><?= htmlspecialchars($seller_profile['email'] ?? $_SESSION['email']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($seller_profile['address'])): ?>
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-map-marker-alt mr-3 text-blue-600"></i>
                                        <span><?= htmlspecialchars($seller_profile['address']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if(!empty($seller_profile['website'])): ?>
                                    <div class="flex items-center text-gray-700">
                                        <i class="fas fa-globe mr-3 text-blue-600"></i>
                                        <a href="<?= htmlspecialchars($seller_profile['website']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                            <?= htmlspecialchars($seller_profile['website']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-6">
                                <a href="mailto:<?= htmlspecialchars($seller_profile['email'] ?? $_SESSION['email']) ?>" 
                                   class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all duration-300">
                                    Send a Message
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Video Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h3 id="videoModalTitle" class="text-white text-xl font-semibold mb-4"></h3>
            <div class="featured-video">
                <video id="modalVideo" controls class="w-full">
                    <source id="modalVideoSource" src="" type="video/mp4">
                </video>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <h3 id="imageModalTitle" class="text-white text-xl font-semibold mb-4"></h3>
            <img id="modalImage" src="" alt="" class="w-full rounded-lg">
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-900 to-blue-900 text-white py-16 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Brand Section -->
                <div class="col-span-1">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-blue-600 text-2xl font-bold mr-3">
                            <?= strtoupper(substr($seller_profile['first_name'] ?? $_SESSION['email'], 0, 1)) ?>
                        </div>
                        <h3 class="text-2xl font-bold">
                            <?= htmlspecialchars($seller_profile['name'] ?? 'Professional Creator') ?>
                        </h3>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">
                        Delivering exceptional creative services with passion and expertise since 
                        <?= date('Y', strtotime($seller_profile['created_at'] ?? date('Y-m-d'))) ?>.
                    </p>
                    <!-- Social Links -->
                    <div class="flex space-x-4 mt-6">
                        <?php if(!empty($seller_profile['phone_number'])): ?>
                            <a href="<?= htmlspecialchars($seller_profile['phone_number']) ?>" target="_blank" 
                                class="social-btn w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center transition-all duration-300">
                                <i class="fab fa-facebook-f text-white"></i>
                            </a>
                        <?php endif; ?>
                        <?php if(!empty($seller_profile['email'])): ?>
                            <a href="<?= htmlspecialchars($seller_profile['email']) ?>" target="_blank" 
                                class="social-btn w-10 h-10 bg-gray-800 hover:bg-gray-900 rounded-full flex items-center justify-center transition-all duration-300">
                                <i class="fas fa-globe text-white"></i>
                            </a>
                        <?php endif; ?>
                        <?php if(!empty($seller_profile['phone_number'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $seller_profile['phone_number']) ?>" target="_blank" 
                                class="social-btn w-10 h-10 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center transition-all duration-300">
                                <i class="fab fa-whatsapp text-white"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Portfolio Links -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold mb-6">Portfolio</h3>
                    <ul class="space-y-3 text-gray-300">
                        <li><a href="#videos" class="hover:text-white transition-colors flex items-center">
                            <i class="fas fa-video mr-2 text-sm"></i> Video Showcase
                        </a></li>
                        <li><a href="#gallery" class="hover:text-white transition-colors flex items-center">
                            <i class="fas fa-images mr-2 text-sm"></i> Photo Gallery
                        </a></li>
                        <?php if($user_role === 'seller'): ?>
                            <li><a href="#services" class="hover:text-white transition-colors flex items-center">
                                <i class="fas fa-briefcase mr-2 text-sm"></i> Services
                            </a></li>
                            <li><a href="#about" class="hover:text-white transition-colors flex items-center">
                                <i class="fas fa-user mr-2 text-sm"></i> About Me
                            </a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold mb-6">Get in Touch</h3>
                    <ul class="space-y-3 text-gray-300">
                        <?php if(!empty($seller_profile['phone_number'])): ?>
                            <li class="flex items-center">
                                <i class="fas fa-phone mr-2 text-sm"></i>
                                <?= htmlspecialchars($seller_profile['phone_number']) ?>
                            </li>
                        <?php endif; ?>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2 text-sm"></i>
                            <?= htmlspecialchars($seller_profile['email'] ?? $_SESSION['email']) ?>
                        </li>
                        <?php if(!empty($seller_profile['address'])): ?>
                            <li class="flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-sm"></i>
                                <?= htmlspecialchars($seller_profile['address']) ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="mt-12 pt-8 border-t border-gray-800 text-center">
                <p>© 2025 Eventro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Tab Switching
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        }

        // Video Modal
        function playVideo(filePath, title) {
            const modal = document.getElementById('videoModal');
            const video = document.getElementById('modalVideo');
            const videoSource = document.getElementById('modalVideoSource');
            const videoTitle = document.getElementById('videoModalTitle');
            
            videoSource.src = '../uploads/portfolio/' + filePath;
            videoTitle.textContent = title;
            video.load();
            modal.style.display = 'block';
        }

        // Image Modal
        function openImageModal(filePath, title) {
            const modal = document.getElementById('imageModal');
            const image = document.getElementById('modalImage');
            const imageTitle = document.getElementById('imageModalTitle');
            
            image.src = '../Uploads/portfolio/' + filePath;
            imageTitle.textContent = title;
            modal.style.display = 'block';
        }

        // Close Modal
        function closeModal() {
            document.getElementById('videoModal').style.display = 'none';
            document.getElementById('imageModal').style.display = 'none';
            const video = document.getElementById('modalVideo');
            video.pause();
        }

        // Share Profile
        function shareProfile() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                alert('Profile URL copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy URL: ', err);
            });
        }

        // Follow Seller (Placeholder)
        function followSeller() {
            alert('Follow feature coming soon!');
            // Implement follow logic here (e.g., AJAX call to server)
        }

        // Like Video (Placeholder)
        function likeVideo(videoId) {
            alert('Like feature coming soon for video ID: ' + videoId);
            // Implement like logic here (e.g., AJAX call to server)
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const videoModal = document.getElementById('videoModal');
            const imageModal = document.getElementById('imageModal');
            if (event.target == videoModal || event.target == imageModal) {
                closeModal();
            }
        }
    </script>
</body>
</html>