<?php
/**
 * Admin Orders Management
 * Modern E-Commerce Platform
 */

require_once '../includes/functions.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/admin/', 'Access denied.', 'error');
}

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirect(SITE_URL . '/admin/orders.php', 'Invalid request.', 'error');
    }
    
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'update_status') {
        $orderId = (int)$_POST['order_id'];
        $newStatus = sanitize($_POST['status'] ?? '');
        
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (in_array($newStatus, $validStatuses)) {
            db()->query("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $orderId]);
            logActivity('order_status_update', "Order #$orderId status changed to $newStatus");
            
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Status updated']);
                exit;
            }
            
            redirect(SITE_URL . '/admin/orders.php?action=view&id=' . $orderId, 'Order status updated!', 'success');
        }
    }
    
    if ($postAction === 'delete') {
        $orderId = (int)$_POST['order_id'];
        
        db()->query("DELETE FROM order_items WHERE order_id = ?", [$orderId]);
        db()->query("DELETE FROM orders WHERE id = ?", [$orderId]);
        logActivity('order_delete', "Deleted order #$orderId");
        
        redirect(SITE_URL . '/admin/orders.php', 'Order deleted successfully!', 'success');
    }
}

// Get order for viewing
$order = null;
$orderItems = [];
if ($action === 'view' && $id > 0) {
    $order = db()->fetch("
        SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ", [$id]);
    
    if (!$order) {
        redirect(SITE_URL . '/admin/orders.php', 'Order not found.', 'error');
    }
    
    $orderItems = db()->fetchAll("
        SELECT oi.*, p.name as product_name, p.image as product_image, p.slug as product_slug
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ", [$id]);
}

// Get orders for listing
$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];

if ($statusFilter) {
    $where .= " AND o.status = ?";
    $params[] = $statusFilter;
}

if ($search) {
    $where .= " AND (o.id LIKE ? OR o.name LIKE ? OR o.email LIKE ? OR u.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$totalOrders = db()->fetch("SELECT COUNT(*) as count FROM orders o LEFT JOIN users u ON o.user_id = u.id $where", $params)['count'];
$totalPages = ceil($totalOrders / $perPage);

$orders = db()->fetchAll("
    SELECT o.*, u.name as customer_name,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    $where 
    ORDER BY o.created_at DESC 
    LIMIT $perPage OFFSET $offset
", $params);

// Order statistics
$orderStats = [
    'pending' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'],
    'processing' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")['count'],
    'shipped' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'shipped'")['count'],
    'delivered' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'")['count'],
    'cancelled' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")['count'],
];

$pageTitle = match($action) {
    'view' => 'Order #' . $id,
    default => 'Orders Management'
};

include 'includes/header.php';
?>

<?php if ($action === 'list'): ?>
<!-- Orders List -->
<div class="space-y-6">
    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2">
        <a href="orders.php" 
           class="px-4 py-2 rounded-xl font-medium transition <?php echo !$statusFilter ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
            All Orders
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?php echo !$statusFilter ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-600'; ?>">
                <?php echo $totalOrders; ?>
            </span>
        </a>
        
        <?php foreach ($orderStats as $status => $count): ?>
        <a href="?status=<?php echo $status; ?>" 
           class="px-4 py-2 rounded-xl font-medium transition <?php echo $statusFilter === $status ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
            <?php echo ucfirst($status); ?>
            <span class="ml-1 px-2 py-0.5 rounded-full text-xs <?php echo $statusFilter === $status ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-600'; ?>">
                <?php echo $count; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Search -->
    <div class="flex gap-4">
        <form method="GET" class="flex gap-2 flex-1 max-w-md">
            <?php if ($statusFilter): ?>
            <input type="hidden" name="status" value="<?php echo $statusFilter; ?>">
            <?php endif; ?>
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search by order ID, name, or email..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 transition">
                Search
            </button>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Order</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Items</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Payment</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-4 opacity-50"></i>
                            <p>No orders found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4">
                            <a href="?action=view&id=<?php echo $ord['id']; ?>" class="font-bold text-primary-500 hover:text-primary-600">
                                #<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($ord['customer_name'] ?? $ord['name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($ord['email']); ?></p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            <?php echo $ord['item_count']; ?> item<?php echo $ord['item_count'] > 1 ? 's' : ''; ?>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            <?php echo formatPrice($ord['total_amount']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <?php echo strtoupper($ord['payment_method']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php echo getOrderStatusBadge($ord['status']); ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($ord['created_at'])); ?>
                            <br>
                            <span class="text-xs"><?php echo date('g:i A', strtotime($ord['created_at'])); ?></span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="?action=view&id=<?php echo $ord['id']; ?>" 
                                   class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition"
                                   title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this order?')">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 hover:bg-red-200 dark:hover:bg-red-900/50 transition"
                                            title="Delete">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
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
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>" 
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>" 
                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> transition">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>" 
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<!-- View Order Details -->
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="orders.php" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-primary-500 transition">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Orders</span>
        </a>
        
        <div class="flex items-center gap-3">
            <span class="text-gray-500">Status:</span>
            <form method="POST" class="flex items-center gap-2">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <select name="status" 
                        onchange="this.form.submit()"
                        class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </form>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Order Items -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Order #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
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
                               target="_blank"
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
                    <div class="space-y-2">
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
            
            <!-- Order Notes -->
            <?php if (!empty($order['notes'])): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Order Notes</h3>
                <p class="text-gray-600 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Customer Details</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white font-bold">
                            <?php echo strtoupper(substr($order['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($order['name']); ?></p>
                            <?php if ($order['user_id']): ?>
                            <a href="users.php?action=view&id=<?php echo $order['user_id']; ?>" class="text-xs text-primary-500">View Profile</a>
                            <?php else: ?>
                            <span class="text-xs text-gray-500">Guest</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <i class="fas fa-envelope w-5 text-center text-gray-400"></i>
                            <a href="mailto:<?php echo $order['email']; ?>" class="hover:text-primary-500"><?php echo htmlspecialchars($order['email']); ?></a>
                        </div>
                        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                            <i class="fas fa-phone w-5 text-center text-gray-400"></i>
                            <span><?php echo htmlspecialchars($order['phone']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Shipping Address</h3>
                <div class="text-gray-600 dark:text-gray-300 space-y-1">
                    <p><?php echo htmlspecialchars($order['name']); ?></p>
                    <p><?php echo htmlspecialchars($order['address']); ?></p>
                    <p><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> <?php echo htmlspecialchars($order['zip']); ?></p>
                    <p><?php echo htmlspecialchars($order['country']); ?></p>
                </div>
            </div>
            
            <!-- Payment Info -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Payment Information</h3>
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
                    <?php if (!empty($order['transaction_id'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Transaction ID</span>
                        <span class="font-mono text-sm text-gray-900 dark:text-white"><?php echo $order['transaction_id']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Timeline -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Order Timeline</h3>
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-3 h-3 rounded-full bg-primary-500 mt-1.5"></div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Order Placed</p>
                            <p class="text-sm text-gray-500"><?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($order['updated_at'] !== $order['created_at']): ?>
                    <div class="flex gap-3">
                        <div class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 mt-1.5"></div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">Last Updated</p>
                            <p class="text-sm text-gray-500"><?php echo date('M j, Y \a\t g:i A', strtotime($order['updated_at'])); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
