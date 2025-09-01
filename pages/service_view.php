<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$service_id = $_GET['id'] ?? null;

// Validate service ID
if (!$service_id || !is_numeric($service_id)) {
    echo "Invalid Service ID";
    exit;
}

// Get service details
$query = "SELECT s.*, s1.first_name AS seller_name, s1.phone_number AS seller_phone, s2.last_name AS client_name, s1.id AS seller_id
          FROM services s
          JOIN sellers s1 ON s.seller_id = s1.id
          LEFT JOIN sellers s2 ON s.client_id = s2.id
          WHERE s.id = ? AND s.approved = 1";
$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $service_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    echo "<div class='min-h-screen bg-gray-50 flex items-center justify-center'>";
    echo "<div class='text-center'>";
    echo "<h1 class='text-2xl font-bold text-gray-900 mb-2'>Service not found</h1>";
    echo "<p class='text-gray-600 mb-4'>The service you're looking for doesn't exist or hasn't been approved.</p>";
    echo "<a href='../index.php' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>Back to Home</a>";
    echo "</div>";
    echo "</div>";
    exit;
}

$service = mysqli_fetch_assoc($result);

// Function to safely get image path
function getImagePath($imagePath) {
    if (empty($imagePath)) return null;
    
    // Clean the path
    $cleanPath = str_replace('../', '', $imagePath);
    $cleanPath = trim($cleanPath, '/');
    
    // Possible locations to check
    $possiblePaths = [
        "../assets/uploads/" . basename($cleanPath),
        "../assets/uploads/" . $cleanPath,
        "assets/uploads/" . basename($cleanPath),
        "assets/uploads/" . $cleanPath,
        $cleanPath
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    return null;
}

// Get service images from service_images table
$service_images = [];
$images_query = "SELECT image_path, image_name, is_primary, display_order 
                FROM service_images 
                WHERE service_id = ? 
                ORDER BY is_primary DESC, display_order ASC, id ASC";
$images_stmt = mysqli_prepare($conn, $images_query);

if ($images_stmt) {
    mysqli_stmt_bind_param($images_stmt, "i", $service_id);
    mysqli_stmt_execute($images_stmt);
    $images_result = mysqli_stmt_get_result($images_stmt);
    
    while ($img = mysqli_fetch_assoc($images_result)) {
        $validPath = getImagePath($img['image_path']);
        if ($validPath) {
            $service_images[] = [
                'path' => $validPath,
                'name' => $img['image_name'] ?? 'Service Image',
                'is_primary' => $img['is_primary'] ?? 0
            ];
        }
    }
}

// If no images found in service_images table, check main service image
if (empty($service_images) && !empty($service['image'])) {
    $validPath = getImagePath($service['image']);
    if ($validPath) {
        $service_images[] = [
            'path' => $validPath,
            'name' => 'Service Image',
            'is_primary' => 1
        ];
    }
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $sender_id = $user['id'];
        
        // Determine receiver
        if ($user['id'] == $service['seller_id']) {
            $receiver_id = $service['client_id'] ?? null;
        } else {
            $receiver_id = $service['seller_id'];
        }
        
        if ($receiver_id) {
            $insert = "INSERT INTO messages (service_id, sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $insert);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iiis", $service_id, $sender_id, $receiver_id, $message);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Redirect to prevent form resubmission
                    header("Location: service_view.php?id=" . $service_id . "#messages");
                    exit;
                } else {
                    $error_message = "Failed to send message. Please try again.";
                }
            }
        } else {
            $error_message = "Unable to determine message recipient.";
        }
    }
}

// Fetch messages
$messages = [];
$msg_query = "SELECT m.*, u.name AS sender_name 
              FROM messages m
              JOIN users u ON m.sender_id = u.id
              WHERE m.service_id = ?
              ORDER BY m.sent_at ASC";
$msg_stmt = mysqli_prepare($conn, $msg_query);

if ($msg_stmt) {
    mysqli_stmt_bind_param($msg_stmt, "i", $service_id);
    mysqli_stmt_execute($msg_stmt);
    $messages_result = mysqli_stmt_get_result($msg_stmt);
    
    while ($msg = mysqli_fetch_assoc($messages_result)) {
        $messages[] = $msg;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> | Eventro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .contact-btn {
            background: linear-gradient(45deg, #FF6B35, #F7931E);
        }
        
        .contact-btn:hover {
            background: linear-gradient(45deg, #E55A2B, #D67C1A);
        }
        
        .chat-message {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .service-image { transition: transform 0.3s ease; }
        .service-image:hover { transform: scale(1.02); }
        .thumbnail { transition: all 0.3s ease; }
        .thumbnail:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .thumbnail.active { border: 3px solid #16a34a; box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2); }
        
        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.9);
        }
        .modal-content {
            margin: auto; display: block; width: 80%; max-width: 700px; max-height: 80%;
            object-fit: contain; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        }
        .close {
            position: absolute; top: 15px; right: 35px; color: #f1f1f1;
            font-size: 40px; font-weight: bold; cursor: pointer; z-index: 1001;
        }
        .close:hover { color: #bbb; }
        .modal-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5); color: white; border: none;
            padding: 16px; font-size: 18px; cursor: pointer; border-radius: 4px; z-index: 1001;
        }
        .modal-nav:hover { background: rgba(0, 0, 0, 0.8); }
        .modal-prev { left: 20px; }
        .modal-next { right: 20px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Navigation Bar -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="<?= $user['role'] == 'client' ? 'dashboard_client.php' : 'dashboard_seller.php' ?>" 
                   class="text-gray-600 hover:text-green-700 mr-4">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <div class="flex items-center">
                    <?php if (file_exists("../assets/eventro (1).png")): ?>
                        <img src="../assets/eventro (1).png" class="h-10 w-auto" alt="Eventro Logo">
                    <?php else: ?>
                        <!-- <h1 class="text-xl font-bold text-green-600">Eventro</h1> -->
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($user['name']) ?></span>
                </div>
            </div>
        </div>
    </div>
</nav>

<?php if (isset($error_message)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
            <span class="text-red-700"><?= htmlspecialchars($error_message) ?></span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Image Modal -->
<div id="imageModal" class="modal">
    <span class="close" onclick="closeModal()">×</span>
    <img class="modal-content" id="modalImage" alt="Service Image">
    <?php if (count($service_images) > 1): ?>
        <button class="modal-nav modal-prev" onclick="changeModalImage(-1)">❮</button>
        <button class="modal-nav modal-next" onclick="changeModalImage(1)">❯</button>
    <?php endif; ?>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Service Details Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                
                <!-- Service Images Gallery -->
                <?php if (!empty($service_images)): ?>
                    <!-- Main Image -->
                    <div class="h-64 md:h-80 overflow-hidden relative">
                        <?php $mainImage = $service_images[0]; ?>
                        <img src="<?= htmlspecialchars($mainImage['path']) ?>" 
                             alt="<?= htmlspecialchars($mainImage['name']) ?>" 
                             class="w-full h-full object-cover service-image cursor-pointer"
                             id="mainImage"
                             onclick="openModal(0)"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/><text x=%2250%25%22 y=%2250%25%22 font-family=%22Arial%22 font-size=%2218%22 fill=%22%23666%22 text-anchor=%22middle%22 dy=%220.3em%22>Image not available</text></svg>'">
                        
                        <?php if (count($service_images) > 1): ?>
                            <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
                                <i class="fas fa-images mr-1"></i><?= count($service_images) ?> Photos
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Thumbnail Gallery -->
                    <?php if (count($service_images) > 1): ?>
                        <div class="p-4 bg-gray-50">
                            <div class="flex space-x-3 overflow-x-auto pb-2">
                                <?php foreach ($service_images as $index => $image): ?>
                                    <div class="flex-shrink-0">
                                        <img src="<?= htmlspecialchars($image['path']) ?>" 
                                             alt="<?= htmlspecialchars($image['name']) ?>" 
                                             class="w-16 h-16 object-cover rounded-lg cursor-pointer thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                             onclick="changeMainImage(<?= $index ?>, this)"
                                             onerror="this.style.display='none'">
                                        <?php if ($image['is_primary']): ?>
                                            <div class="text-center mt-1">
                                                <span class="text-xs text-green-600 font-medium">Primary</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="h-64 md:h-80 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-image text-gray-400 text-6xl mb-4"></i>
                            <p class="text-gray-500">No images available</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Service Info -->
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($service['title']) ?></h1>
                    
                    <!-- Seller Info -->
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <a href="../pages/portfolio.php?seller_id=<?= $service['seller_id'] ?>" class="hover:text-green-600 transition-colors duration-200">
                                <h3 class="font-semibold text-gray-900 hover:text-green-600"><?= htmlspecialchars($service['seller_name']) ?></h3>
                            </a>
                            <div class="flex items-center">
                                <div class="flex text-yellow-400 mr-2">
                                    <?php for($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star text-sm"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-sm text-gray-600">(4.9) • Service Provider</span>
                            </div>
                        </div>
                    </div>

                    <!-- Price Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-gray-900">Starting Price:</span>
                            <span class="text-2xl font-bold text-green-600">Rs. <?= number_format($service['price'], 2) ?></span>
                        </div>
                    </div>

                    <!-- Service Description -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">About this service</h2>
                        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                    </div>

                    <!-- Service Features -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span class="text-gray-700">Professional Quality</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-handshake text-green-500 mr-2"></i>
                            <span class="text-gray-700">Reliable Service</span>
                        </div>
                        <?php if (!empty($service['district'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                            <span class="text-gray-700"><?= htmlspecialchars($service['district']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($service['category'])): ?>
                        <div class="flex items-center">
                            <i class="fas fa-tag text-green-500 mr-2"></i>
                            <span class="text-gray-700"><?= htmlspecialchars($service['category']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Contact -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                
                <!-- Contact Card -->
                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Service Provider</h3>
                    
                    <!-- Contact Buttons -->
                    <div class="space-y-3 mb-6">
                        <?php if (!empty($service['seller_phone'])): ?>
                            <a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $service['seller_phone'])) ?>" 
                               class="contact-btn w-full text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center">
                                <i class="fas fa-phone mr-2"></i>
                                Call Now
                            </a>
                        <?php else: ?>
                            <button class="contact-btn w-full text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center" disabled>
                                <i class="fas fa-phone mr-2"></i>
                                Call Now (Number Unavailable)
                            </button>
                        <?php endif; ?>
                        <?php if (!empty($service['seller_phone'])): ?>
                            <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $service['seller_phone'])) ?>" 
                               class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                                <i class="fab fa-whatsapp mr-2"></i>
                                WhatsApp
                            </a>
                        <?php else: ?>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors flex items-center justify-center" disabled>
                                <i class="fab fa-whatsapp mr-2"></i>
                                WhatsApp (Number Unavailable)
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Message -->
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-3">Send a quick message:</p>
                        <form method="POST" class="space-y-3" id="messageForm">
                            <textarea name="message" id="messageInput"
                                    rows="3" 
                                    placeholder="Hi, I'm interested in your service..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none"
                                    required
                                    maxlength="500"></textarea>
                            <button type="submit" 
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                                <i class="fas fa-paper-plane mr-2"></i>Send Message
                            </button>
                        </form>
                    </div>
                </div>
                <!-- Chat History -->
                <?php if (!empty($messages)): ?>
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-4 border-b">
                        <h4 class="font-semibold text-gray-900">Message History</h4>
                    </div>
                    <div class="max-h-64 overflow-y-auto p-4 space-y-3">
                        <?php foreach (array_slice($messages, -5) as $msg): ?>
                            <div class="chat-message flex <?= $msg['sender_id'] == $user['id'] ? 'justify-end' : 'justify-start' ?>">
                                <div class="max-w-xs">
                                    <div class="<?= $msg['sender_id'] == $user['id'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900' ?> px-3 py-2 rounded-lg text-sm">
                                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1 <?= $msg['sender_id'] == $user['id'] ? 'text-right' : 'text-left' ?>">
                                        <?= date('M j, g:i A', strtotime($msg['sent_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>        
    </div>
    <!-- Additional Information Tabs -->
    <div class="mt-12">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Tab Headers -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <button class="py-4 px-1 border-b-2 border-green-600 font-medium text-sm text-green-600" onclick="showTab('details', this)">
                        Service Details
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <div id="details-tab" class="tab-content">
                    <h3 class="text-lg font-semibold mb-4">Service Information</h3>
                    <p class="text-gray-700 mb-4"><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Service Includes:</h4>
                            <ul class="space-y-1 text-gray-700">
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Professional consultation</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Implementation support</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>Quality assurance</li>
                                <li><i class="fas fa-check text-green-500 mr-2"></i>After-service support</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Service Details:</h4>
                            <ul class="space-y-1 text-gray-700">
                                <?php if (!empty($service['district'])): ?>
                                <li><strong>Location:</strong> <?= htmlspecialchars($service['district']) ?></li>
                                <?php endif; ?>
                                <?php if (!empty($service['category'])): ?>
                                <li><strong>Category:</strong> <?= htmlspecialchars($service['category']) ?></li>
                                <?php endif; ?>
                                <li><strong>Service Provider:</strong> <?= htmlspecialchars($service['seller_name']) ?></li>
                                <li><strong>Starting Price:</strong> Rs. <?= number_format($service['price'], 2) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    const serviceImages = <?= json_encode(array_column($service_images, 'path')) ?>;
    let currentModalIndex = 0;

    function changeMainImage(index, thumbnail) {
        const mainImage = document.getElementById('mainImage');
        if (mainImage && serviceImages[index]) {
            mainImage.src = serviceImages[index];
            
            // Update thumbnail active state
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
    }

    function openModal(index) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        
        if (serviceImages[index]) {
            currentModalIndex = index;
            modal.style.display = 'block';
            modalImage.src = serviceImages[index];
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal() {
        const modal = document.getElementById('imageModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function changeModalImage(direction) {
        if (serviceImages.length <= 1) return;
        
        currentModalIndex += direction;
        
        if (currentModalIndex >= serviceImages.length) {
            currentModalIndex = 0;
        }
        if (currentModalIndex < 0) {
            currentModalIndex = serviceImages.length - 1;
        }
        
        const modalImage = document.getElementById('modalImage');
        modalImage.src = serviceImages[currentModalIndex];
    }

    // Event listeners
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        const modal = document.getElementById('imageModal');
        if (modal.style.display === 'block') {
            if (event.key === 'Escape') {
                closeModal();
            } else if (event.key === 'ArrowLeft') {
                changeModalImage(-1);
            } else if (event.key === 'ArrowRight') {
                changeModalImage(1);
            }
        }
    });

    // Auto-scroll chat to bottom
    function scrollChatToBottom() {
        const chatContainer = document.getElementById('chatContainer');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        scrollChatToBottom();
    });

    // Handle form submission
    document.getElementById('messageForm')?.addEventListener('submit', function(e) {
        const messageInput = document.getElementById('messageInput');
        const submitButton = e.target.querySelector('button[type="submit"]');
        
        if (messageInput.value.trim() === '') {
            e.preventDefault();
            return false;
        }
        
        // Disable button to prevent double submission
        submitButton.disabled = true;
        setTimeout(() => {
            submitButton.disabled = false;
        }, 2000);
    });
</script>
</body>
</html>