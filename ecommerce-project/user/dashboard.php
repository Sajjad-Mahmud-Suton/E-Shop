<?php
/**
 * User Dashboard
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php', 'Please login to access your dashboard.', 'error');
}

$userId = $_SESSION['user_id'];

// Get user data
$user = db()->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

// Get user statistics
$stats = [
    'total_orders' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", [$userId])['count'],
    'pending_orders' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE user_id = ? AND status = 'pending'", [$userId])['count'],
    'total_spent' => db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id = ? AND status != 'cancelled'", [$userId])['total'],
    'wishlist_items' => db()->fetch("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?", [$userId])['count'],
];

// Get recent orders
$recentOrders = db()->fetchAll("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
", [$userId]);

// Get wishlist items
$wishlistItems = db()->fetchAll("
    SELECT p.* FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
    LIMIT 4
", [$userId]);

$pageTitle = 'My Dashboard';
include 'includes/header.php';
?>

<main class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-display font-bold text-gray-900 dark:text-white">
                Welcome back, <?php echo htmlspecialchars($user['name']); ?>!
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Manage your account and view your orders</p>
        </div>
        
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
                        <a href="user/dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium">
                            <i class="fas fa-home w-5 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="user/orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-shopping-bag w-5 text-center"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="wishlist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-heart w-5 text-center"></i>
                            <span>Wishlist</span>
                        </a>
                        <a href="user/profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-user w-5 text-center"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i>
                            <span>Logout</span>
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500 mb-4">
                            <i class="fas fa-shopping-bag text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['total_orders']; ?></p>
                        <p class="text-sm text-gray-500">Total Orders</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-500 mb-4">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['pending_orders']; ?></p>
                        <p class="text-sm text-gray-500">Pending Orders</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-500 mb-4">
                            <i class="fas fa-dollar-sign text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo formatPrice($stats['total_spent']); ?></p>
                        <p class="text-sm text-gray-500">Total Spent</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-pink-500 mb-4">
                            <i class="fas fa-heart text-xl"></i>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $stats['wishlist_items']; ?></p>
                        <p class="text-sm text-gray-500">Wishlist Items</p>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Recent Orders</h2>
                        <a href="user/orders.php" class="text-primary-500 hover:text-primary-600 text-sm font-medium">View All</a>
                    </div>
                    
                    <?php if (empty($recentOrders)): ?>
                    <div class="p-12 text-center">
                        <i class="fas fa-shopping-bag text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500">You haven't placed any orders yet</p>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="inline-flex items-center gap-2 mt-4 px-6 py-2 bg-primary-500 text-white rounded-xl font-medium hover:bg-primary-600 transition">
                            Start Shopping
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($recentOrders as $order): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-6 py-4 font-bold text-primary-500">
                                        #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                        <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        <?php echo formatPrice($order['total_amount']); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo getOrderStatusBadge($order['status']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="user/orders.php?id=<?php echo $order['id']; ?>" class="text-primary-500 hover:text-primary-600 font-medium">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Wishlist Preview -->
                <?php if (!empty($wishlistItems)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Your Wishlist</h2>
                        <a href="wishlist.php" class="text-primary-500 hover:text-primary-600 text-sm font-medium">View All</a>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($wishlistItems as $item): ?>
                        <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $item['slug']; ?>" class="group">
                            <div class="aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 mb-2">
                                <img src="<?php echo SITE_URL . '/' . ($item['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                            <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate group-hover:text-primary-500 transition">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </h4>
                            <p class="text-primary-500 font-bold text-sm">
                                <?php echo formatPrice($item['sale_price'] ?: $item['price']); ?>
                            </p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
