<?php
include '../config/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone_code = $_POST['phone_code'];
    $phone_number = $_POST['phone_number'];
    $nic = $_POST['nic'];
    $dob = $_POST['dob'];
    $business_type = $_POST['business_type'];
    $business_name = $_POST['business_name'];
    $business_reg_no = $_POST['business_reg_no'];
    $business_category = $_POST['business_category'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $phone_full = $phone_code . $phone_number;

        $stmt = $conn->prepare("INSERT INTO sellers (first_name, last_name, email, phone_number, nic, dob, business_type, business_name, business_reg_no, business_category, address, city, province, postal_code, country, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssss", $first_name, $last_name, $email, $phone_full, $nic, $dob, $business_type, $business_name, $business_reg_no, $business_category, $address, $city, $province, $postal_code, $country, $hashed_password);

        if ($stmt->execute()) {
            $success = "Seller registered successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Become a Seller | Eventro</title>
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
            <div class="flex items-center">
                <img src="../assets/bg logo.png" alt="Eventro Logo" class="h-10">
        
                <div class="ml-auto text-sm text-gray-400">
                    Already have an account? 
                    <a href="login.php" class="text-gray-700 hover:text-orange-600 transition-colors">Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="glass-effect rounded-2xl shadow-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-orange-600 to-red-500 px-8 py-6 text-center">
                <h1 class="text-2xl font-bold text-white">Become a Seller</h1>
                <p class="text-gray-300 mt-2">Join thousands of sellers and start your e-commerce journey</p>
            </div>

            <form class="p-8 space-y-8" method="POST" action="">
                <?php if ($message): ?>
                <div class="p-4 mb-6 text-center rounded <?php echo $message === "ඔබගේ Seller ගිණුම සාර්ථකව සෑදුවා!" ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <!-- Personal Information -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-600 mb-6 flex items-center">
                        <i class="fas fa-user-circle text-gray-800 mr-3"></i>
                        Personal Information
                    </h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="Enter your first name" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="Enter your last name" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="your.email@example.com" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="phone_number">Phone Number *</label>
                            <div class="flex">
                                <select id="phone_code" name="phone_code" required
                                    class="px-3 py-3 border border-gray-300 rounded-l-lg  text-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all">
                                    <option value="+94" <?php echo (isset($phoneCode) && $phoneCode === '+94') ? 'selected' : ''; ?>>+94</option>
                                </select>
                                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phoneNumber ?? ''); ?>" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                    placeholder="771234567" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="nic">NIC Number *</label>
                            <input type="text" id="nic" name="nic" value="<?php echo htmlspecialchars($nic ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="200012345678" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="dob">Date of Birth *</label>
                            <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all" />
                        </div>
                    </div>
                </div>

                <!-- Business Information -->
                <div class="border-t border-gray-700 pt-8">
                    <h2 class="text-xl font-semibold text-gray-600 mb-6 flex items-center">
                        <i class="fas fa-store text-gray-800 mr-3"></i>
                        Business Information
                    </h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="business_type">Business Type *</label>
                            <select id="business_type" name="business_type" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all">
                                <option value="">Select Business Type</option>
                                <option value="Individual Seller" <?php echo (isset($businessType) && $businessType === 'Individual Seller') ? 'selected' : ''; ?>>Individual Seller</option>
                                <option value="Private Limited Company" <?php echo (isset($businessType) && $businessType === 'Private Limited Company') ? 'selected' : ''; ?>>Private Limited Company</option>
                                <option value="Partnership" <?php echo (isset($businessType) && $businessType === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                <option value="Sole Proprietorship" <?php echo (isset($businessType) && $businessType === 'Sole Proprietorship') ? 'selected' : ''; ?>>Sole Proprietorship</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="business_name">Business Name *</label>
                            <input type="text" id="business_name" name="business_name" value="<?php echo htmlspecialchars($businessName ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="Your Business Name" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="business_reg_no">Business Registration Number</label>
                            <input type="text" id="business_reg_no" name="business_reg_no" value="<?php echo htmlspecialchars($businessRegNo ?? ''); ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="BR/12345/2023" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="business_category">Business Category *</label>
                            <select id="business_category" name="business_category" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all">
                                <option value="">Select Category</option>
                                <option value="Photography" <?php echo (isset($businessCategory) && $businessCategory === 'Photography') ? 'selected' : ''; ?>>Photography</option>
                                <option value="Weddings" <?php echo (isset($businessCategory) && $businessCategory === 'Weddings') ? 'selected' : ''; ?>>Weddings</option>
                                <option value="Birthdays" <?php echo (isset($businessCategory) && $businessCategory === 'Birthdays') ? 'selected' : ''; ?>>Birthdays</option>
                                <option value="Entertainment" <?php echo (isset($businessCategory) && $businessCategory === 'Entertainment') ? 'selected' : ''; ?>>Entertainment</option>
                                <option value="Cultural" <?php echo (isset($businessCategory) && $businessCategory === 'Cultural') ? 'selected' : ''; ?>>Cultural</option>
                                <option value="Religious" <?php echo (isset($businessCategory) && $businessCategory === 'Religious') ? 'selected' : ''; ?>>Religious</option>
                                <option value="Exhibition" <?php echo (isset($businessCategory) && $businessCategory === 'Exhibition') ? 'selected' : ''; ?>>Exhibition</option>
                                <option value="Corporate" <?php echo (isset($businessCategory) && $businessCategory === 'Corporate') ? 'selected' : ''; ?>>Corporate</option>
                                <option value="Educational" <?php echo (isset($businessCategory) && $businessCa<option value="Educational" <?php echo (isset($businessCategory) && $businessCategory === 'Educational') ? 'selected' : ''; ?>>Educational</option>tegory === 'Educational') ? 'selected' : ''; ?>>Educational</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                 <div class="border-t border-gray-700 pt-8">
                    <h2 class="text-xl font-semibold text-gray-600 mb-6 flex items-center">
                        <i class="fas fa-map-marker-alt text-gray-800 mr-3"></i>
                        Address Information
                    </h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="address">Street Address *</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($streetAddress ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="123 Main St, Apartment 4B" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="city">City *</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="Colombo" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="province">Province *</label>
                            <select id="province" name="province" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all">
                                <option value="">Select Province</option>
                                <option value="Western" <?php echo (isset($province) && $province === 'Western') ? 'selected' : ''; ?>>Western</option>
                                <option value="Central" <?php echo (isset($province) && $province === 'Central') ? 'selected' : ''; ?>>Central</option>
                                <option value="Southern" <?php echo (isset($province) && $province === 'Southern') ? 'selected' : ''; ?>>Southern</option>
                                <option value="Northern" <?php echo (isset($province) && $province === 'Northern') ? 'selected' : ''; ?>>Northern</option>
                                <option value="Eastern" <?php echo (isset($province) && $province === 'Eastern') ? 'selected' : ''; ?>>Eastern</option>
                                <option value="North Western" <?php echo (isset($province) && $province === 'North Western') ? 'selected' : ''; ?>>North Western</option>
                                <option value="North Central" <?php echo (isset($province) && $province === 'North Central') ? 'selected' : ''; ?>>North Central</option>
                                <option value="Uva" <?php echo (isset($province) && $province === 'Uva') ? 'selected' : ''; ?>>Uva</option>
                                <option value="Sabaragamuwa" <?php echo (isset($province) && $province === 'Sabaragamuwa') ? 'selected' : ''; ?>>Sabaragamuwa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="postal_code">Postal Code *</label>
                            <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($postalCode ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="10000" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="country">Country *</label>
                            <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($country ?? ''); ?>" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="Sri Lanka" />
                        </div>
                    </div>
                </div>

                <!-- Password & Terms -->
                <div class="border-t border-gray-700 pt-8 space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="password">Password *</label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="At least 8 characters" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:ring-2 focus:ring-white focus:border-transparent transition-all"
                                placeholder="Re-enter password" />
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <input type="checkbox" id="terms" name="terms" <?php if (!empty($termsAccepted)) echo 'checked'; ?> required
                            class="w-5 h-5 rounded border-gray-600 bg-gray-700/90 text-indigo-600 focus:ring-indigo-500" />
                        <label for="terms" class="text-gray-700 text-sm select-none">I accept the <a href="#" class="underline hover:text-indigo-400">terms and conditions</a></label>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full bg-gradient-to-r from-orange-600 to-red-500 text-white py-3 px-4 rounded-lg font-semibold hover:from-orange-700 hover:to-red-600 transform hover:scale-105 transition-all duration-200 shadow-lg hover:shadow-xl">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
