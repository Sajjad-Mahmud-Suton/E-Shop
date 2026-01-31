<?php
/**
 * User Login Page
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? '/admin/dashboard.php' : '/user/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Fetch user
            $user = db()->fetch("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if user is banned
                if ($user['status'] === 'banned') {
                    $error = 'Your account has been suspended. Please contact support.';
                } else {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login
                    db()->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                    
                    // Log activity
                    logActivity('login', 'User logged in');
                    
                    // Remember me
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        db()->query("UPDATE users SET remember_token = ? WHERE id = ?", [$token, $user['id']]);
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                    }
                    
                    // Redirect
                    $redirectUrl = $_SESSION['redirect_after_login'] ?? ($user['role'] === 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php');
                    unset($_SESSION['redirect_after_login']);
                    redirect(SITE_URL . $redirectUrl, 'Welcome back, ' . $user['name'] . '!', 'success');
                }
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Login';
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
<body class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="<?php echo SITE_URL; ?>" class="inline-flex items-center gap-2">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-shopping-bag text-indigo-600 text-2xl"></i>
                    </div>
                    <span class="text-3xl font-display font-bold text-white">E-Shop</span>
                </a>
            </div>
            
            <!-- Login Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-display font-bold text-gray-800">Welcome Back!</h1>
                    <p class="text-gray-500 mt-2">Sign in to continue shopping</p>
                </div>
                
                <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-red-600">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6">
                    <?php echo csrfField(); ?>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   placeholder="you@example.com" 
                                   required
                                   class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition outline-none">
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
                                   class="w-full pl-12 pr-12 py-3 rounded-xl border-2 border-gray-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition outline-none">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="<?php echo SITE_URL; ?>/forgot-password.php" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button type="submit" 
                            class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all duration-300">
                        Sign In
                    </button>
                </form>
                
                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Or continue with</span>
                    </div>
                </div>
                
                <!-- Social Login -->
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
                
                <!-- Register Link -->
                <p class="text-center mt-8 text-gray-600">
                    Don't have an account? 
                    <a href="<?php echo SITE_URL; ?>/register.php" class="text-indigo-600 hover:text-indigo-700 font-bold">
                        Sign Up
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
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
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
