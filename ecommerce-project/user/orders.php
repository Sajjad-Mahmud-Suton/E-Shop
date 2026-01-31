<?php
/**
 * User Orders Page
 * Modern E-Commerce Platform
 */

require_once '../includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php', 'Please login to view your orders.', 'error');
}

$userId = $_SESSION['user_id'];
$orderId = (int)($_GET['id'] ?? 0);

// View single order
if ($orderId > 0) {
    $order = db()->fetch("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $userId]);
    
    if (!$order) {
        redirect(SITE_URL . '/user/orders.php', 'Order not found.', 'error');
    }
    
    $orderItems = db()->fetchAll("
        SELECT oi.*, p.name as product_name, p.image as product_image, p.slug as product_slug
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ", [$orderId]);
}

// Get all orders
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalOrders = db()->fetch("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", [$userId])['count'];
$totalPages = ceil($totalOrders / $perPage);

$orders = db()->fetchAll("
    SELECT o.*,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT $perPage OFFSET $offset
", [$userId]);

$user = db()->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

$pageTitle = $orderId ? 'Order #' . str_pad($orderId, 5, '0', STR_PAD_LEFT) : 'My Orders';
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
                        <a href="orders.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium">
                            <i class="fas fa-shopping-bag w-5 text-center"></i>
                            <span>My Orders</span>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/wishlist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <i class="fas fa-heart w-5 text-center"></i>
                            <span>Wishlist</span>
                        </a>
                        <a href="profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
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
            <div class="lg:col-span-3">
                <?php if ($orderId > 0 && $order): ?>
                <!-- Single Order View -->
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <a href="orders.php" class="text-gray-500 hover:text-primary-500 transition">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl font-display font-bold text-gray-900 dark:text-white">
                            Order #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                        </h1>
                        <?php echo getOrderStatusBadge($order['status']); ?>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500">
                                Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                        
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($orderItems as $item): ?>
                            <div class="p-6 flex items-center gap-6">
                                <img src="<?php echo SITE_URL . '/' . ($item['product_image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                     alt="" 
                                     class="w-20 h-20 rounded-xl object-cover">
                                <div class="flex-1">
                                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $item['product_slug']; ?>" 
                                       class="font-medium text-gray-900 dark:text-white hover:text-primary-500 transition">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <?php echo formatPrice($item['price']); ?> × <?php echo $item['quantity']; ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900 dark:text-white">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Order Totals -->
                        <div class="p-6 bg-gray-50 dark:bg-gray-700/50">
                            <div class="max-w-xs ml-auto space-y-2">
                                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                    <span>Subtotal</span>
                                    <span><?php echo formatPrice($order['subtotal']); ?></span>
                                </div>
                                <div class="flex justify-between text-gray-600 dark:text-gray-300">
                                    <span>Shipping</span>
                                    <span><?php echo $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'Free'; ?></span>
                                </div>
                                <?php if ($order['discount'] > 0): ?>
                                <div class="flex justify-between text-green-600">
                                    <span>Discount</span>
                                    <span>-<?php echo formatPrice($order['discount']); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span>Total</span>
                                    <span><?php echo formatPrice($order['total_amount']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Shipping Address -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-shipping-fast text-primary-500 mr-2"></i>
                                Shipping Address
                            </h3>
                            <div class="text-gray-600 dark:text-gray-300 space-y-1">
                                <p><?php echo htmlspecialchars($order['name']); ?></p>
                                <p><?php echo htmlspecialchars($order['address']); ?></p>
                                <p><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['zip']); ?></p>
                                <p><?php echo htmlspecialchars($order['country']); ?></p>
                                <p class="pt-2"><?php echo htmlspecialchars($order['phone']); ?></p>
                            </div>
                        </div>
                        
                        <!-- Payment Info -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-credit-card text-primary-500 mr-2"></i>
                                Payment Information
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Method</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        <?php echo strtoupper($order['payment_method']); ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Status</span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        <?php echo $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Timeline -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                        <h3 class="font-bold text-gray-900 dark:text-white mb-6">Order Status</h3>
                        
                        <div class="flex items-center justify-between relative">
                            <?php 
                            $statuses = ['pending', 'processing', 'shipped', 'delivered'];
                            $currentIndex = array_search($order['status'], $statuses);
                            if ($order['status'] === 'cancelled') $currentIndex = -1;
                            ?>
                            
                            <div class="absolute top-5 left-0 right-0 h-1 bg-gray-200 dark:bg-gray-700">
                                <div class="h-full bg-primary-500 transition-all" style="width: <?php echo max(0, ($currentIndex / 3) * 100); ?>%"></div>
                            </div>
                            
                            <?php foreach ($statuses as $index => $status): ?>
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $index <= $currentIndex ? 'bg-primary-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'; ?>">
                                    <?php if ($index <= $currentIndex): ?>
                                    <i class="fas fa-check"></i>
                                    <?php else: ?>
                                    <i class="fas fa-<?php echo ['clock', 'cog', 'truck', 'home'][$index]; ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="mt-2 text-sm font-medium <?php echo $index <= $currentIndex ? 'text-primary-500' : 'text-gray-500'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($order['status'] === 'cancelled'): ?>
                        <div class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400">
                            <i class="fas fa-times-circle mr-2"></i>
                            This order has been cancelled.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Orders List -->
                <div class="space-y-6">
                    <h1 class="text-2xl font-display font-bold text-gray-900 dark:text-white">My Orders</h1>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                        <?php if (empty($orders)): ?>
                        <div class="p-12 text-center">
                            <i class="fas fa-shopping-bag text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                            <p class="text-gray-500 mb-4">You haven't placed any orders yet</p>
                            <a href="<?php echo SITE_URL; ?>/products.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-xl font-medium hover:bg-primary-600 transition">
                                <i class="fas fa-shopping-cart"></i>
                                Start Shopping
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Order</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Items</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                        <td class="px-6 py-4 font-bold text-primary-500">
                                            #<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                            <?php echo date('M j, Y', strtotime($ord['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                            <?php echo $ord['item_count']; ?> item<?php echo $ord['item_count'] > 1 ? 's' : ''; ?>
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                            <?php echo formatPrice($ord['total_amount']); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo getOrderStatusBadge($ord['status']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="?id=<?php echo $ord['id']; ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 dark:bg-primary-900/20 text-primary-500 rounded-lg font-medium hover:bg-primary-100 dark:hover:bg-primary-900/30 transition">
                                                <i class="fas fa-eye"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <p class="text-sm text-gray-500">
                                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalOrders); ?> of <?php echo $totalOrders; ?> orders
                            </p>
                            <div class="flex gap-2">
                                <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> transition">
                                    <?php echo $i; ?>
                                </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
