<?php
/**
 * User Profile Page
 * Modern E-Commerce Platform
 */

require_once '../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php', 'Please login to access your profile.', 'error');
}

$userId = $_SESSION['user_id'];
$user = db()->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $address = sanitize($_POST['address'] ?? '');
            $city = sanitize($_POST['city'] ?? '');
            $state = sanitize($_POST['state'] ?? '');
            $zip = sanitize($_POST['zip'] ?? '');
            $country = sanitize($_POST['country'] ?? '');
            
            // Validation
            if (empty($name)) $errors[] = 'Name is required.';
            if (empty($email)) $errors[] = 'Email is required.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
            
            // Check if email is taken by another user
            $exists = db()->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
            if ($exists) $errors[] = 'Email is already taken.';
            
            if (empty($errors)) {
                db()->query("
                    UPDATE users SET 
                        name = ?, email = ?, phone = ?, address = ?, 
                        city = ?, state = ?, zip = ?, country = ?
                    WHERE id = ?
                ", [$name, $email, $phone, $address, $city, $state, $zip, $country, $userId]);
                
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $success = 'Profile updated successfully!';
                $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            }
        }
        
        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword)) $errors[] = 'Current password is required.';
            if (strlen($newPassword) < 6) $errors[] = 'New password must be at least 6 characters.';
            if ($newPassword !== $confirmPassword) $errors[] = 'Passwords do not match.';
            
            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = 'Current password is incorrect.';
            }
            
            if (empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                db()->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);
                $success = 'Password changed successfully!';
            }
        }
    }
}

$pageTitle = 'Profile Settings';
include '../includes/header.php';
?>

<main class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-28">
                    <div class="text-center mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h3 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <nav class="space-y-2">
                        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-home w-5 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-shopping-bag w-5 text-center"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/wishlist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-heart w-5 text-center"></i>
                            <span>Wishlist</span>
                        </a>
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium">
                            <i class="fas fa-user w-5 text-center"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-6">
                <h1 class="text-2xl font-display font-bold text-gray-900 dark:text-white">Profile Settings</h1>
                
                <?php if ($success): ?>
                <div class="p-4 bg-green-100 border border-green-200 rounded-xl text-green-700">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="p-4 bg-red-100 border border-red-200 rounded-xl text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Profile Information -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
                        <i class="fas fa-user text-primary-500 mr-2"></i>
                        Personal Information
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                                <input type="text" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>"
                                       required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                                <input type="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country</label>
                                <select name="country" 
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                    <option value="">Select Country</option>
                                    <option value="US" <?php echo ($user['country'] ?? '') === 'US' ? 'selected' : ''; ?>>United States</option>
                                    <option value="CA" <?php echo ($user['country'] ?? '') === 'CA' ? 'selected' : ''; ?>>Canada</option>
                                    <option value="UK" <?php echo ($user['country'] ?? '') === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="AU" <?php echo ($user['country'] ?? '') === 'AU' ? 'selected' : ''; ?>>Australia</option>
                                    <option value="DE" <?php echo ($user['country'] ?? '') === 'DE' ? 'selected' : ''; ?>>Germany</option>
                                    <option value="FR" <?php echo ($user['country'] ?? '') === 'FR' ? 'selected' : ''; ?>>France</option>
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Street Address</label>
                                <input type="text" 
                                       name="address" 
                                       value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City</label>
                                <input type="text" 
                                       name="city" 
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">State / Province</label>
                                <input type="text" 
                                       name="state" 
                                       value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ZIP / Postal Code</label>
                                <input type="text" 
                                       name="zip" 
                                       value="<?php echo htmlspecialchars($user['zip'] ?? ''); ?>"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                        </div>
                        
                        <div class="pt-4">
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-bold hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30">
                                <i class="fas fa-save mr-2"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
                        <i class="fas fa-lock text-primary-500 mr-2"></i>
                        Change Password
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Password</label>
                                <input type="password" 
                                       name="current_password" 
                                       required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">New Password</label>
                                <input type="password" 
                                       name="new_password" 
                                       required
                                       minlength="6"
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Confirm Password</label>
                                <input type="password" 
                                       name="confirm_password" 
                                       required
                                       class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="px-8 py-3 bg-gray-800 dark:bg-gray-700 text-white rounded-xl font-bold hover:bg-gray-900 dark:hover:bg-gray-600 transition">
                                <i class="fas fa-key mr-2"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Account Info -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
                        <i class="fas fa-info-circle text-primary-500 mr-2"></i>
                        Account Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Member Since</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Last Login</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                <?php echo $user['last_login'] ? date('F j, Y', strtotime($user['last_login'])) : 'N/A'; ?>
                            </p>
                        </div>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Account Status</p>
                            <p class="font-medium text-green-600">
                                <i class="fas fa-check-circle mr-1"></i>
                                Active
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
