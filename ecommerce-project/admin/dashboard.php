<?php
/**
 * Admin Dashboard
 * Modern E-Commerce Platform
 */

require_once '../includes/functions.php';

// Check admin access
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/admin/', 'Access denied. Admin login required.', 'error');
}

// Get dashboard statistics
$stats = [
    'total_products' => db()->fetch("SELECT COUNT(*) as count FROM products")['count'],
    'total_orders' => db()->fetch("SELECT COUNT(*) as count FROM orders")['count'],
    'total_users' => db()->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count'],
    'total_categories' => db()->fetch("SELECT COUNT(*) as count FROM categories")['count'],
    'pending_orders' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'],
    'total_revenue' => db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'")['total'],
    'today_orders' => db()->fetch("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")['count'],
    'today_revenue' => db()->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'cancelled'")['total'],
];

// Recent orders
$recentOrders = db()->fetchAll("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
");

// Recent users
$recentUsers = db()->fetchAll("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");

// Low stock products
$lowStockProducts = db()->fetchAll("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 5");

// Recent activity
$recentActivity = db()->fetchAll("
    SELECT a.*, u.name as user_name 
    FROM activity_log a 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
");

// Orders by status for chart
$ordersByStatus = db()->fetchAll("
    SELECT status, COUNT(*) as count 
    FROM orders 
    GROUP BY status
");

// Sales data for last 7 days
$salesData = db()->fetchAll("
    SELECT DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as orders 
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status != 'cancelled'
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
");

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="stat-card bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg shadow-indigo-500/30">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
                <span class="text-sm bg-white/20 px-3 py-1 rounded-full">All Time</span>
            </div>
            <h3 class="text-3xl font-bold"><?php echo formatPrice($stats['total_revenue']); ?></h3>
            <p class="text-indigo-100 mt-1">Total Revenue</p>
            <div class="mt-4 pt-4 border-t border-white/20 flex items-center justify-between text-sm">
                <span>Today: <?php echo formatPrice($stats['today_revenue']); ?></span>
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        
        <!-- Total Orders -->
        <div class="stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg shadow-emerald-500/30">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                </div>
                <span class="text-sm bg-white/20 px-3 py-1 rounded-full"><?php echo $stats['pending_orders']; ?> Pending</span>
            </div>
            <h3 class="text-3xl font-bold"><?php echo number_format($stats['total_orders']); ?></h3>
            <p class="text-emerald-100 mt-1">Total Orders</p>
            <div class="mt-4 pt-4 border-t border-white/20 flex items-center justify-between text-sm">
                <span>Today: <?php echo $stats['today_orders']; ?> orders</span>
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
        
        <!-- Total Products -->
        <div class="stat-card bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl p-6 text-white shadow-lg shadow-amber-500/30">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-box text-2xl"></i>
                </div>
                <a href="products.php" class="text-sm bg-white/20 px-3 py-1 rounded-full hover:bg-white/30 transition">Manage</a>
            </div>
            <h3 class="text-3xl font-bold"><?php echo number_format($stats['total_products']); ?></h3>
            <p class="text-amber-100 mt-1">Total Products</p>
            <div class="mt-4 pt-4 border-t border-white/20 flex items-center justify-between text-sm">
                <span><?php echo count($lowStockProducts); ?> low stock</span>
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        
        <!-- Total Users -->
        <div class="stat-card bg-gradient-to-br from-pink-500 to-rose-500 rounded-2xl p-6 text-white shadow-lg shadow-pink-500/30">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <a href="users.php" class="text-sm bg-white/20 px-3 py-1 rounded-full hover:bg-white/30 transition">View All</a>
            </div>
            <h3 class="text-3xl font-bold"><?php echo number_format($stats['total_users']); ?></h3>
            <p class="text-pink-100 mt-1">Total Customers</p>
            <div class="mt-4 pt-4 border-t border-white/20 flex items-center justify-between text-sm">
                <span><?php echo $stats['total_categories']; ?> categories</span>
                <i class="fas fa-tag"></i>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Sales Overview</h2>
                <span class="text-sm text-gray-500">Last 7 days</span>
            </div>
            <canvas id="salesChart" height="250"></canvas>
        </div>
        
        <!-- Orders by Status -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">Orders by Status</h2>
                <a href="orders.php" class="text-indigo-500 hover:text-indigo-600 text-sm">View All</a>
            </div>
            <canvas id="ordersChart" height="250"></canvas>
        </div>
    </div>
    
    <!-- Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Orders -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Recent Orders</h2>
                    <a href="orders.php" class="text-indigo-500 hover:text-indigo-600 text-sm">View All</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No orders yet</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900 dark:text-white">#<?php echo $order['id']; ?></span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                <?php echo htmlspecialchars($order['customer_name'] ?? $order['name']); ?>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                <?php echo formatPrice($order['total_amount']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo getOrderStatusBadge($order['status']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Low Stock Products -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-2"></i>
                        Low Stock Alert
                    </h2>
                    <a href="products.php" class="text-indigo-500 hover:text-indigo-600 text-sm">View All</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($lowStockProducts)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">All products in stock!</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($lowStockProducts as $product): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="<?php echo SITE_URL . '/' . ($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                         alt="" 
                                         class="w-10 h-10 rounded-lg object-cover">
                                    <span class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($product['name']); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-bold rounded-full <?php echo $product['stock'] == 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'; ?>">
                                    <?php echo $product['stock']; ?> left
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                   class="text-indigo-500 hover:text-indigo-600">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Activity & Users Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-6">Recent Activity</h2>
            <div class="space-y-4">
                <?php if (empty($recentActivity)): ?>
                <p class="text-gray-500 text-center py-4">No recent activity</p>
                <?php else: ?>
                <?php foreach ($recentActivity as $activity): ?>
                <div class="flex items-start gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-history text-indigo-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($activity['action']); ?>
                        </p>
                        <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($activity['description'] ?? ''); ?></p>
                        <p class="text-xs text-gray-400 mt-1"><?php echo timeAgo($activity['created_at']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- New Users -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-800 dark:text-white">New Customers</h2>
                <a href="users.php" class="text-indigo-500 hover:text-indigo-600 text-sm">View All</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($recentUsers)): ?>
                <p class="text-gray-500 text-center py-4">No customers yet</p>
                <?php else: ?>
                <?php foreach ($recentUsers as $user): ?>
                <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                        <p class="text-xs text-gray-400 mt-1"><?php echo timeAgo($user['created_at']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
const salesData = <?php echo json_encode($salesData); ?>;

new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: salesData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-US', { weekday: 'short' });
        }),
        datasets: [{
            label: 'Revenue',
            data: salesData.map(d => d.total),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6366f1',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// Orders by Status Chart
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
const ordersData = <?php echo json_encode($ordersByStatus); ?>;

const statusColors = {
    'pending': '#f59e0b',
    'processing': '#3b82f6',
    'shipped': '#8b5cf6',
    'delivered': '#10b981',
    'cancelled': '#ef4444'
};

new Chart(ordersCtx, {
    type: 'doughnut',
    data: {
        labels: ordersData.map(d => d.status.charAt(0).toUpperCase() + d.status.slice(1)),
        datasets: [{
            data: ordersData.map(d => d.count),
            backgroundColor: ordersData.map(d => statusColors[d.status] || '#6b7280'),
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    usePointStyle: true,
                    padding: 20
                }
            }
        },
        cutout: '70%'
    }
});
</script>

<?php include 'includes/footer.php'; ?>
