<?php
/**
 * User Registration Page
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/user/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);
        
        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $error = 'Please fill in all required fields.';
        } elseif (strlen($name) < 2) {
            $error = 'Name must be at least 2 characters.';
        } elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (!$terms) {
            $error = 'You must agree to the Terms of Service.';
        } else {
            // Check if email exists
            $existing = db()->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            
            if ($existing) {
                $error = 'An account with this email already exists.';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    db()->query("
                        INSERT INTO users (name, email, password, role, status, created_at)
                        VALUES (?, ?, ?, 'user', 'active', NOW())
                    ", [$name, $email, $hashedPassword]);
                    
                    $userId = db()->lastInsertId();
                    
                    // Log activity
                    $_SESSION['user_id'] = $userId;
                    logActivity('register', 'New user registration');
                    unset($_SESSION['user_id']);
                    
                    $success = 'Account created successfully! You can now login.';
                    
                    // Optionally auto-login
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'user';
                    
                    redirect(SITE_URL . '/user/dashboard.php', 'Welcome to E-Shop, ' . $name . '!', 'success');
                    
                } catch (Exception $e) {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Register';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Shop</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-500 via-pink-500 to-red-500">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="<?php echo SITE_URL; ?>" class="inline-flex items-center gap-2">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shopping-bag text-purple-600 text-2xl"></i>
                    </div>
                    <span class="text-3xl font-display font-bold text-white">E-Shop</span>
                </a>
            </div>
            
            <!-- Register Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-display font-bold text-gray-800">Create Account</h1>
                    <p class="text-gray-500 mt-2">Join us and start shopping</p>
                </div>
                
                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-600">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 text-green-600">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-5">
                    <?php echo csrfField(); ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <div class="relative">
                            <input type="text" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                   placeholder="John Doe" 
                                   required
                                   class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 transition outline-none">
                            <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   placeholder="you@example.com" 
                                   required
                                   class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 transition outline-none">
                            <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="••••••••" 
                                   required
                                   minlength="6"
                                   class="w-full pl-12 pr-12 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 transition outline-none">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">At least 6 characters</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <div class="relative">
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password"
                                   placeholder="••••••••" 
                                   required
                                   class="w-full pl-12 pr-12 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/20 transition outline-none">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <button type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="terms" required class="w-5 h-5 mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span class="text-sm text-gray-600">
                                I agree to the 
                                <a href="<?php echo SITE_URL; ?>/terms.php" class="text-purple-600 hover:text-purple-700 font-medium">Terms of Service</a> 
                                and 
                                <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-purple-600 hover:text-purple-700 font-medium">Privacy Policy</a>
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" 
                            class="w-full py-3 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all duration-300">
                        Create Account
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Or sign up with</span>
                    </div>
                </div>
                
                <!-- Social Register -->
                <div class="grid grid-cols-2 gap-4">
                    <button class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        <i class="fab fa-google text-red-500"></i>
                        <span class="font-medium text-gray-700">Google</span>
                    </button>
                    <button class="flex items-center justify-center gap-2 px-4 py-3 border-2 border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        <i class="fab fa-facebook text-blue-600"></i>
                        <span class="font-medium text-gray-700">Facebook</span>
                    </button>
                </div>
                
                <!-- Login Link -->
                <p class="text-center mt-8 text-gray-600">
                    Already have an account? 
                    <a href="<?php echo SITE_URL; ?>/login.php" class="text-purple-600 hover:text-purple-700 font-bold">
                        Sign In
                    </a>
                </p>
            </div>
            
            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="<?php echo SITE_URL; ?>" class="text-white/80 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
