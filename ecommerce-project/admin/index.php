<?php
/**
 * Admin Login Page
 * Modern E-Commerce Platform
 */

require_once '../includes/functions.php';

// Redirect if already logged in as admin
if (isLoggedIn() && isAdmin()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        } else {
            $user = db()->fetch("SELECT * FROM users WHERE email = ? AND role = 'admin'", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'banned') {
                    $error = 'Your account has been suspended.';
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    db()->query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                    logActivity('admin_login', 'Admin logged in');
                    
                    redirect(SITE_URL . '/admin/dashboard.php', 'Welcome back, ' . $user['name'] . '!', 'success');
                }
            } else {
                $error = 'Invalid credentials or insufficient permissions.';
            }
        }
    }
}

$pageTitle = 'Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - E-Shop Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-3 mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <div class="text-left">
                    <h1 class="text-2xl font-display font-bold text-white">E-Shop</h1>
                    <p class="text-gray-400 text-sm">Admin Panel</p>
                </div>
            </div>
        </div>
        
        <!-- Login Card -->
        <div class="bg-gray-800 rounded-3xl shadow-2xl p-8 border border-gray-700">
            <div class="text-center mb-8">
                <h2 class="text-xl font-display font-bold text-white">Admin Login</h2>
                <p class="text-gray-400 mt-2">Enter your credentials to access the dashboard</p>
            </div>
            
            <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-900/50 border border-red-700 rounded-xl flex items-center gap-3 text-red-400">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="space-y-6">
                <?php echo csrfField(); ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="admin@admin.com" 
                               required
                               class="w-full pl-12 pr-4 py-3 rounded-xl bg-gray-700 border-2 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition outline-none">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" 
                               name="password" 
                               id="password"
                               placeholder="••••••••" 
                               required
                               class="w-full pl-12 pr-12 py-3 rounded-xl bg-gray-700 border-2 border-gray-600 text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition outline-none">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" 
                        class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-indigo-500/30 transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>
        </div>
        
        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="<?php echo SITE_URL; ?>" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Website
            </a>
        </div>
        
        <!-- Demo Credentials -->
        <div class="mt-6 p-4 bg-gray-800/50 rounded-xl border border-gray-700 text-center">
            <p class="text-gray-400 text-sm mb-2">Demo Credentials:</p>
            <p class="text-gray-300 text-sm"><strong>Email:</strong> admin@admin.com</p>
            <p class="text-gray-300 text-sm"><strong>Password:</strong> password</p>
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
