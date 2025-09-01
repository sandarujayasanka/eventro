<?php
session_start();

// Database connection parameters - CHANGE these to your own DB credentials
$host = 'localhost';
$dbname = 'freelance_system';
$user = 'root';
$pass = '';

// Connect to DB with PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize variables
$name = $email = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    } elseif (strlen($name) > 100) {
        $errors[] = "Name must be less than 100 characters.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    // If no errors, check if email already exists, then insert
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email is already registered.";
        } else {
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $passwordHash])) {
                $success = "Registration successful! You can now <a href='pages/login.php' class='underline'>login</a>.";
                // Clear form values after success
                $name = $email = '';
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register | Eventro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f97316' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>

</head>
<body class="min-h-screen bg-gradient-to-br from-orange-400 via-red-400 to-pink-400 bg-pattern">
    <div class="glass-effect border-b border-white/20">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <img src="../assets/bg logo.png" alt="Eventro Logo" class="h-10">
                </div>
                <div class="hidden md:flex items-center space-x-6 text-sm">
                    <a href="#" class="text-gray-700 hover:text-orange-600 transition-colors">Help</a>
                    <a href="#" class="text-gray-700 hover:text-orange-600 transition-colors">Customer Care</a>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-globe text-gray-600"></i>
                        <select class="bg-transparent border-none text-gray-700 text-sm focus:outline-none">
                            <option>English</option>
                            <option>සිංහල</option>
                            <option>தமிழ்</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="w-full max-w-md">
            <div class="glass-effect rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-orange-600 to-red-500 px-8 py-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Welcome to Eventro!</h1>
                    <p class="text-orange-100 mt-2">Sign up to your account</p>
                </div>

                <div class="px-8 py-8">
                    <?php if ($errors): ?>
                        <div class="mb-6 bg-red-500/20 border border-red-500/30 text-red-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                                <div>
                                    <?php foreach ($errors as $error): ?>
                                        <p class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></p>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="mb-6 bg-green-500/20 border border-green-500/30 text-green-200 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-check-circle text-red-500 mr-3"></i>
                                <div>
                                    <p class="text-red-700 text-sm"><?= $success ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6" novalidate>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" id="name" name="name"required value="<?= htmlspecialchars($name) ?>"
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all bg-white/80"/>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email) ?>"
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all bg-white/80"/>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all bg-white/80"/>
                        </div>

                        <div>
                            <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-500 text-white py-3 px-4 rounded-lg font-semibold hover:from-orange-700 hover:to-red-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                                Register
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-gray-50 px-8 py-4 text-center border-t">
                    <p class="text-sm text-gray-600">
                        Already have an account? <a href="login.php" class="text-orange-600 hover:text-orange-700 font-medium">Sign In</a>
                    </p>
                </div>
            </div>

            <div class="mt-6 text-center">
                <div class="glass-effect rounded-xl p-4">
                    <p class="text-sm text-gray-700 mb-2">Need help?</p>
                    <div class="flex justify-center space-x-4 text-sm">
                        <a href="#" class="text-orange-600 hover:text-orange-700">
                            <i class="fas fa-phone mr-1"></i> Contact Support
                        </a>
                        <a href="#" class="text-orange-600 hover:text-orange-700">
                            <i class="fas fa-question-circle mr-1"></i> FAQ
                        </a>
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
</body>
</html>
