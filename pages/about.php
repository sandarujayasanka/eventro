<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About | Eventro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <style>
    .daraz-nav {
      background-color: white;
      height: 60px;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Header -->
  <header class="daraz-nav sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center h-full justify-between">
      <div class="flex items-center space-x-4">
        <img src="../assets/bg logo.png" 
             class="h-8 w-auto sm:h-10 md:h-12 lg:h-13 xl:h-15 object-contain" 
             alt="Eventro Logo">
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-12">
    <!-- About Section -->
    <div class="max-w-4xl mx-auto">
      <!-- Hero Section -->
      <div class="text-center mb-12">
        <div class="mb-8">
          <div class="inline-block relative">
            <div class="w-32 h-32 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="relative">
                <!-- Person illustration -->
                <div class="w-20 h-20 bg-emerald-600 rounded-full relative">
                  <div class="w-8 h-8 bg-amber-200 rounded-full absolute top-2 left-6"></div>
                  <div class="w-10 h-6 bg-gray-800 rounded-t-full absolute top-1 left-5"></div>
                  <div class="w-12 h-16 bg-emerald-600 rounded-b-full absolute top-8 left-4"></div>
                  <div class="w-3 h-8 bg-amber-200 rounded-full absolute top-10 left-2 transform rotate-12"></div>
                  <div class="w-3 h-8 bg-amber-200 rounded-full absolute top-10 right-2 transform -rotate-12"></div>
                  <div class="w-2 h-3 bg-gray-800 rounded absolute top-12 right-1"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-4">About Eventro</h1>
        <p class="text-gray-600 max-w-2xl mx-auto leading-relaxed">
          Eventro is Sri Lanka’s all-in-one platform for event services. From weddings and birthdays to corporate events and private parties, we help clients find verified service providers such as photographers, caterers, decorators, entertainers, and more — with ease and trust.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid md:grid-cols-2 gap-8 mb-12">
        <!-- Selling -->
        <div class="bg-white rounded-lg p-8 shadow-sm">
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fas fa-tag text-blue-600 text-xl"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800 mb-3">Have event services to offer?</h3>
              <p class="text-gray-600 text-sm leading-relaxed mb-4">
                Create a free Eventro seller account and start showcasing your event-related services today! Whether you’re a photographer, DJ, decorator, or event planner, our platform helps you connect with clients across Sri Lanka. Use promotions to boost your visibility and stand out from the crowd.
              </p>
            </div>
          </div>
        </div>

        <!-- Buying -->
        <div class="bg-white rounded-lg p-8 shadow-sm">
          <div class="flex items-start space-x-4">
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fas fa-search text-orange-600 text-xl"></i>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-800 mb-3">Looking to plan an event?</h3>
              <p class="text-gray-600 text-sm leading-relaxed">
                Eventro makes it easy to find the perfect professionals for any occasion. Explore a wide range of verified service providers by category, district, or budget. Whether it’s a wedding, birthday, or corporate function — we’ve got you covered.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact -->
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
          <p class="text-gray-300">
            Eventro is Sri Lanka’s dedicated event service marketplace.
          </p>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Contact Info</h4>
          <ul class="space-y-2 text-gray-300">
            <li> info@eventro.com</li>
            <li> 011 2 333 444</li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-4">About Eventro</h4>
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