<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: ../pages/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Eventro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
    <!-- Header -->
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
            <!-- Login Card -->
            <div class="glass-effect rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-orange-600 to-red-500 px-8 py-6 text-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Welcome Back!</h1>
                    <p class="text-orange-100 mt-2">Sign in to your account</p>
                </div>

                <!-- Login Form -->
                <div class="px-8 py-8">
                    <!-- Error Message -->
                    <?php if (isset($_GET['error'])): ?>
                        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                                <p class="text-red-700 text-sm"><?= htmlspecialchars($_GET['error']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="../actions/login.php" id="loginForm" class="space-y-6">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <input type="email" name="email" id="emailInput" required
                                    class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all bg-white/80" placeholder="Enter your email address">
                                <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="passwordInput" required
                                    class="w-full px-4 py-3 pl-12 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all bg-white/80" placeholder="Enter your password">
                                <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <button type="button" id="togglePassword" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit id="submitBtn class="w-full bg-gradient-to-r from-orange-600 to-red-500 text-white py-3 px-4 rounded-lg font-semibold hover:from-orange-700 hover:to-red-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                        </button>                        
                    </form>
                </div>

                <div class="bg-gray-50 px-8 py-4 text-center border-t">
                    <p class="text-sm text-gray-600">
                        Don't have an account? 
                        <a href="register.php" class="text-orange-600 hover:text-orange-700 font-medium">
                            Register here
                        </a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('passwordInput');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Form submission with loading state
            const loginForm = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (loginForm && submitBtn) {
                loginForm.addEventListener('submit', function() {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...';
                    submitBtn.disabled = true;
                    
                    // Reset button if form submission fails (for better UX)
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }
                    }, 5000);
                });
            }

            // Input focus animations
            const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('transform', 'scale-105');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('transform', 'scale-105');
                });
            });

            // Social login placeholder functionality
            document.querySelectorAll('button[type="button"]').forEach(button => {
                if (button.id !== 'togglePassword') {
                    button.addEventListener('click', function() {
                        const platform = this.textContent.trim();
                        alert(`${platform} login integration coming soon!`);
                    });
                }
            });
        });
    </script>
</body>
</html>