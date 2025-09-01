<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | Eventro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .eventro-nav {
            background-color:rgb(255, 255, 255);
            height: 60px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="eventro-nav sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center h-full justify-between">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-4">
                        <img src="../assets/bg logo.png" 
                            class="h-8 w-auto sm:h-10 md:h-12 lg:h-13 xl:h-15 object-contain" 
                            alt="Eventro Logo">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-question-circle text-3xl text-emerald-600"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Frequently Asked Questions</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Find answers to the most common questions about using eventro
                </p>
            </div>

            <!-- Search Box -->
            <div class="mb-8">
                <div class="relative max-w-md mx-auto">
                    <input type="text" placeholder="Search FAQs..." class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                </div>
            </div>

            <!-- FAQ Categories -->
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <button class="faq-category bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-left border-l-4 border-emerald-500" data-category="general">
                    <i class="fas fa-home text-emerald-600 mb-2"></i>
                    <h3 class="font-semibold text-gray-800">General</h3>
                    <p class="text-sm text-gray-600">Basic questions about eventro</p>
                </button>
                <button class="faq-category bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-left border-l-4 border-blue-500" data-category="selling">
                    <i class="fas fa-tag text-blue-600 mb-2"></i>
                    <h3 class="font-semibold text-gray-800">Selling</h3>
                    <p class="text-sm text-gray-600">How to sell items on eventro</p>
                </button>
                <button class="faq-category bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition-shadow text-left border-l-4 border-orange-500" data-category="buying">
                    <i class="fas fa-shopping-cart text-orange-600 mb-2"></i>
                    <h3 class="font-semibold text-gray-800">Buying</h3>
                    <p class="text-sm text-gray-600">Tips for buying on eventro</p>
                </button>
            </div>

            <!-- FAQ Items -->
            <div class="space-y-4">
                <!-- General Questions -->
                <div class="faq-section" data-category="general">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-home text-emerald-600 mr-3"></i>
                        General Questions
                    </h2>
                    
                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">What is eventro?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">Welcome to Sri Lanka's leading event platform where all types of events come together in one place. 
                                                     From weddings and parties to corporate functions and cultural festivals, we connect organizers and attendees islandwide. 
                                                     Browse or post events across a wide range of categories – all in one convenient location!</p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">Is it free to use eventro?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">Yes! Creating an account and posting your event is absolutely free. Whether it's a wedding, concert, or corporate function, your basic ad will appear across the platform, helping people discover your event.
                                                     Want more visibility? You can also upgrade to premium features to reach a wider audience and get noticed faster.</p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">How do I create an account?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">Click on the "Login" button at the top of the page, then select "Sign Up". You can register using your email address. It takes less than 2 minutes!</p>
                        </div>
                    </div>
                </div>

                <!-- Selling Questions -->
                <div class="faq-section" data-category="selling">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-tag text-blue-600 mr-3"></i>
                        Selling on Eventro
                    </h2>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">How do I become a seller?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">Click on the <strong>"Become a Seller"</strong> button at the top of the site. Fill out the registration form with your service details, and once approved by our team, you can start posting your event-related services.</p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">How do I post an ad?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">Once you're registered as a seller, click "Post Your Ad" from your seller dashboard. Choose the relevant category, fill in your service details, add images, and submit your ad for review.</p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">How long does it take for my ad to be published?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">All ads go through a quick review process to ensure quality. Typically, your ad will be live within 24 hours unless further verification is required.</p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">Will my ad be visible immediately after posting?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">No. After you post your ad, it will first be reviewed by our admin team to ensure it meets our guidelines. Once approved, your ad will be visible to clients on the platform. This helps maintain quality and safety for all users.</p>
                        </div>
                    </div>


                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">What are the promotion options available?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">To boost visibility, you can promote your ad using options like <strong>Top Ads</strong>, <strong>Urgent Tags</strong>, or <strong>Highlighted Listings</strong>. These help your services reach more clients faster.</p>
                        </div>
                    </div>
                </div>


                <!-- Buying Questions -->
                <div class="faq-section" data-category="buying">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-shopping-cart text-green-600 mr-3"></i>
                        Buying on Eventro
                    </h2>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">How do I contact a seller?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">
                                Each ad includes <strong>WhatsApp</strong> and <strong>Call Now</strong> buttons. To use these options, you must first <strong>log in to your Eventro account</strong>. Once logged in, you can directly reach out to sellers.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">Do I need an account to contact sellers?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">
                                Yes, you need to have an Eventro account to contact sellers. This is required to ensure trust, safety, and a better experience for both buyers and sellers.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">Is it safe to contact sellers directly?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">
                                Yes. All seller ads are reviewed and approved by our admin team before being published. This ensures you are contacting verified and trusted sellers.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item bg-white rounded-lg shadow-sm mb-4">
                        <button class="faq-toggle w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                            <span class="font-medium text-gray-800">Can I negotiate or request customized services?</span>
                            <i class="fas fa-chevron-down text-gray-500 transform transition-transform"></i>
                        </button>
                        <div class="faq-content hidden px-6 pb-4">
                            <p class="text-gray-600">
                                Absolutely! You can discuss pricing, packages, or specific needs directly with the seller using the WhatsApp or Call options once you're logged in.
                            </p>
                        </div>
                    </div>
                </div>


            <!-- Contact Section -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-8 mt-12 text-center">
                <h3 class="text-xl font-bold text-gray-800 mb-3">Still have questions?</h3>
                <p class="text-gray-600 mb-6">Our support team is here to help you</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <div class="flex items-center space-x-2 text-emerald-600">
                        <i class="fas fa-phone"></i>
                        <div>
                            <div class="font-semibold">Call us</div>
                            <div class="text-sm">011 2 333 444</div>
                        </div>
                    </div>
                    
                    <div class="hidden sm:block w-px h-12 bg-gray-300"></div>
                    
                    <div class="flex items-center space-x-2 text-emerald-600">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <div class="font-semibold">Email us</div>
                            <div class="text-sm">info@eventro.com</div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">
                    8am - 5pm on weekdays | 8am - 4pm on weekends and mercantile holidays
                </p>
            </div>
        </div>
    </main>

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
        // FAQ Toggle Functionality
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                const icon = button.querySelector('i');
                
                // Toggle content visibility
                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    content.classList.add('hidden');
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });

        // Category Filter Functionality
        document.querySelectorAll('.faq-category').forEach(button => {
            button.addEventListener('click', () => {
                const category = button.dataset.category;
                
                // Remove active class from all category buttons
                document.querySelectorAll('.faq-category').forEach(btn => {
                    btn.classList.remove('bg-emerald-100', 'border-emerald-500');
                    btn.classList.add('bg-white');
                });
                
                // Add active class to clicked button
                button.classList.add('bg-emerald-100', 'border-emerald-500');
                button.classList.remove('bg-white');
                
                // Show/hide FAQ sections
                document.querySelectorAll('.faq-section').forEach(section => {
                    if (section.dataset.category === category) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        const searchInput = document.querySelector('input[placeholder="Search FAQs..."]');
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-toggle span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-content p').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = searchTerm === '' ? 'block' : 'none';
                }
            });
        });

        // Set default active category
        document.querySelector('.faq-category[data-category="general"]').click();
    </script>
</body>
</html>