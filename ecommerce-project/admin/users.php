<?php
/**
 * Admin Users Management
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
        redirect(SITE_URL . '/admin/users.php', 'Invalid request.', 'error');
    }
    
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'update_status') {
        $userId = (int)$_POST['user_id'];
        $newStatus = sanitize($_POST['status'] ?? '');
        
        // Prevent self-ban
        if ($userId == $_SESSION['user_id']) {
            redirect(SITE_URL . '/admin/users.php', 'You cannot change your own status.', 'error');
        }
        
        $validStatuses = ['active', 'banned'];
        
        if (in_array($newStatus, $validStatuses)) {
            db()->query("UPDATE users SET status = ? WHERE id = ?", [$newStatus, $userId]);
            logActivity('user_status_update', "User #$userId status changed to $newStatus");
            redirect(SITE_URL . '/admin/users.php', 'User status updated!', 'success');
        }
    }
    
    if ($postAction === 'update_role') {
        $userId = (int)$_POST['user_id'];
        $newRole = sanitize($_POST['role'] ?? '');
        
        // Prevent self-demotion
        if ($userId == $_SESSION['user_id']) {
            redirect(SITE_URL . '/admin/users.php', 'You cannot change your own role.', 'error');
        }
        
        $validRoles = ['user', 'admin'];
        
        if (in_array($newRole, $validRoles)) {
            db()->query("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
            logActivity('user_role_update', "User #$userId role changed to $newRole");
            redirect(SITE_URL . '/admin/users.php', 'User role updated!', 'success');
        }
    }
    
    if ($postAction === 'delete') {
        $userId = (int)$_POST['user_id'];
        
        // Prevent self-deletion
        if ($userId == $_SESSION['user_id']) {
            redirect(SITE_URL . '/admin/users.php', 'You cannot delete your own account.', 'error');
        }
        
        // Check for orders
        $orderCount = db()->fetch("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", [$userId])['count'];
        
        if ($orderCount > 0) {
            redirect(SITE_URL . '/admin/users.php', "Cannot delete: User has $orderCount orders.", 'error');
        } else {
            $user = db()->fetch("SELECT name FROM users WHERE id = ?", [$userId]);
            db()->query("DELETE FROM users WHERE id = ?", [$userId]);
            logActivity('user_delete', 'Deleted user: ' . ($user['name'] ?? 'Unknown'));
            redirect(SITE_URL . '/admin/users.php', 'User deleted successfully!', 'success');
        }
    }
    
    if ($postAction === 'create') {
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'user'),
            'status' => sanitize($_POST['status'] ?? 'active'),
        ];
        
        $password = $_POST['password'] ?? '';
        
        // Validate
        $errors = [];
        if (empty($data['name'])) $errors[] = 'Name is required.';
        if (empty($data['email'])) $errors[] = 'Email is required.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        
        // Check email exists
        $exists = db()->fetch("SELECT id FROM users WHERE email = ?", [$data['email']]);
        if ($exists) $errors[] = 'Email already exists.';
        
        if (empty($errors)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
            
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            db()->query("INSERT INTO users ($columns) VALUES ($placeholders)", array_values($data));
            logActivity('user_create', 'Created user: ' . $data['name']);
            redirect(SITE_URL . '/admin/users.php', 'User created successfully!', 'success');
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
        }
    }
}

// Get user for viewing
$user = null;
$userOrders = [];
if ($action === 'view' && $id > 0) {
    $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    
    if (!$user) {
        redirect(SITE_URL . '/admin/users.php', 'User not found.', 'error');
    }
    
    $userOrders = db()->fetchAll("
        SELECT * FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ", [$id]);
}

// Get users for listing
$search = sanitize($_GET['search'] ?? '');
$roleFilter = sanitize($_GET['role'] ?? '');
$statusFilter = sanitize($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($roleFilter) {
    $where .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($statusFilter) {
    $where .= " AND status = ?";
    $params[] = $statusFilter;
}

$totalUsers = db()->fetch("SELECT COUNT(*) as count FROM users $where", $params)['count'];
$totalPages = ceil($totalUsers / $perPage);

$users = db()->fetchAll("
    SELECT u.*, 
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
           (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id AND status != 'cancelled') as total_spent
    FROM users u 
    $where 
    ORDER BY u.created_at DESC 
    LIMIT $perPage OFFSET $offset
", $params);

$pageTitle = match($action) {
    'create' => 'Add New User',
    'view' => 'User Details',
    default => 'Users Management'
};

include 'includes/header.php';
?>

<?php if ($action === 'list'): ?>
<!-- Users List -->
<div class="space-y-6">
    <!-- Actions Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Search users..." 
                       class="pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
            
            <select name="role" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Roles</option>
                <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
            
            <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="">All Status</option>
                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="banned" <?php echo $statusFilter === 'banned' ? 'selected' : ''; ?>>Banned</option>
            </select>
            
            <button type="submit" class="px-4 py-2.5 bg-gray-200 dark:bg-gray-600 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                Filter
            </button>
        </form>
        
        <a href="?action=create" class="px-6 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-medium hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30 text-center">
            <i class="fas fa-plus mr-2"></i>Add User
        </a>
    </div>
    
    <!-- Users Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Orders</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Spent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Joined</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users as $usr): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                    <?php echo strtoupper(substr($usr['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($usr['name']); ?></p>
                                    <p class="text-xs text-gray-500">ID: #<?php echo $usr['id']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($usr['email']); ?></p>
                            <?php if ($usr['phone']): ?>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($usr['phone']); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php echo $usr['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300'; ?>">
                                <?php echo ucfirst($usr['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            <?php echo $usr['order_count']; ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            <?php echo formatPrice($usr['total_spent']); ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php echo $usr['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo ucfirst($usr['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($usr['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="?action=view&id=<?php echo $usr['id']; ?>" 
                                   class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition"
                                   title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                
                                <?php if ($usr['id'] != $_SESSION['user_id']): ?>
                                <!-- Toggle Status -->
                                <form method="POST" class="inline">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo $usr['status'] === 'active' ? 'banned' : 'active'; ?>">
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-lg <?php echo $usr['status'] === 'active' ? 'bg-amber-100 text-amber-600 hover:bg-amber-200' : 'bg-green-100 text-green-600 hover:bg-green-200'; ?> flex items-center justify-center transition"
                                            title="<?php echo $usr['status'] === 'active' ? 'Ban User' : 'Activate User'; ?>">
                                        <i class="fas <?php echo $usr['status'] === 'active' ? 'fa-ban' : 'fa-check'; ?> text-sm"></i>
                                    </button>
                                </form>
                                
                                <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this user?')">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $usr['id']; ?>">
                                    <button type="submit" 
                                            class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 hover:bg-red-200 dark:hover:bg-red-900/50 transition"
                                            title="Delete"
                                            <?php echo $usr['order_count'] > 0 ? 'disabled' : ''; ?>>
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
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
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalUsers); ?> of <?php echo $totalUsers; ?> users
            </p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $roleFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $roleFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> transition">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $roleFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php elseif ($action === 'create'): ?>
<!-- Create User Form -->
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="users.php" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-primary-500 transition">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Users</span>
        </a>
    </div>
    
    <?php if (!empty($_SESSION['errors'])): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-200 rounded-xl text-red-700">
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>
    
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Create New User</h2>
        
        <form method="POST" class="space-y-6">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="create">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                    <input type="text" 
                           name="name" 
                           value="<?php echo htmlspecialchars($_SESSION['old']['name'] ?? ''); ?>"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                    <input type="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($_SESSION['old']['email'] ?? ''); ?>"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                    <input type="tel" 
                           name="phone" 
                           value="<?php echo htmlspecialchars($_SESSION['old']['phone'] ?? ''); ?>"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password *</label>
                    <input type="password" 
                           name="password" 
                           required
                           minlength="6"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                    <select name="role" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="active">Active</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
            </div>
            
            <div class="flex gap-4 pt-4">
                <a href="users.php" class="flex-1 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-medium hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
<?php unset($_SESSION['old']); ?>

<?php else: ?>
<!-- View User Details -->
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="users.php" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-primary-500 transition">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Users</span>
        </a>
        
        <?php if ($user['id'] != $_SESSION['user_id']): ?>
        <div class="flex gap-2">
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <input type="hidden" name="status" value="<?php echo $user['status'] === 'active' ? 'banned' : 'active'; ?>">
                <button type="submit" 
                        class="px-4 py-2 rounded-xl font-medium <?php echo $user['status'] === 'active' ? 'bg-red-500 text-white hover:bg-red-600' : 'bg-green-500 text-white hover:bg-green-600'; ?> transition">
                    <i class="fas <?php echo $user['status'] === 'active' ? 'fa-ban' : 'fa-check'; ?> mr-2"></i>
                    <?php echo $user['status'] === 'active' ? 'Ban User' : 'Activate User'; ?>
                </button>
            </form>
            
            <form method="POST">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <input type="hidden" name="role" value="<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>">
                <button type="submit" 
                        class="px-4 py-2 rounded-xl font-medium bg-purple-500 text-white hover:bg-purple-600 transition">
                    <i class="fas fa-user-shield mr-2"></i>
                    Make <?php echo $user['role'] === 'admin' ? 'User' : 'Admin'; ?>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Info -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 text-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <div class="flex justify-center gap-2 mt-4">
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                        <?php echo ucfirst($user['status']); ?>
                    </span>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Contact Information</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                        <i class="fas fa-envelope w-5 text-center text-gray-400"></i>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <?php if ($user['phone']): ?>
                    <div class="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                        <i class="fas fa-phone w-5 text-center text-gray-400"></i>
                        <span><?php echo htmlspecialchars($user['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($user['address']): ?>
                    <div class="flex items-start gap-3 text-gray-600 dark:text-gray-300">
                        <i class="fas fa-map-marker-alt w-5 text-center text-gray-400 mt-1"></i>
                        <span><?php echo htmlspecialchars($user['address']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-4">Account Details</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">User ID</span>
                        <span class="font-medium text-gray-900 dark:text-white">#<?php echo $user['id']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Joined</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Last Login</span>
                        <span class="font-medium text-gray-900 dark:text-white">
                            <?php echo $user['last_login'] ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Orders -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Order History</h2>
                </div>
                
                <?php if (empty($userOrders)): ?>
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-shopping-bag text-4xl mb-4 opacity-50"></i>
                    <p>No orders yet</p>
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
                            <?php foreach ($userOrders as $ord): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 font-bold text-primary-500">
                                    #<?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    <?php echo date('M j, Y', strtotime($ord['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                    <?php echo formatPrice($ord['total_amount']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo getOrderStatusBadge($ord['status']); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="orders.php?action=view&id=<?php echo $ord['id']; ?>" 
                                       class="text-primary-500 hover:text-primary-600">
                                        View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
