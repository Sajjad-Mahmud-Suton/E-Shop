<?php
/**
 * Admin Categories Management
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
        redirect(SITE_URL . '/admin/categories.php', 'Invalid request.', 'error');
    }
    
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'create' || $postAction === 'update') {
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'slug' => createSlug($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'status' => sanitize($_POST['status'] ?? 'active'),
        ];
        
        // Validate
        if (empty($data['name'])) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Category name is required.'];
        } else {
            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = '../assets/images/categories/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $filename = 'category_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $data['image'] = 'assets/images/categories/' . $filename;
                    }
                }
            }
            
            if ($postAction === 'create') {
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                
                db()->query("INSERT INTO categories ($columns) VALUES ($placeholders)", array_values($data));
                logActivity('category_create', 'Created category: ' . $data['name']);
                redirect(SITE_URL . '/admin/categories.php', 'Category created successfully!', 'success');
            } else {
                $categoryId = (int)$_POST['category_id'];
                
                // Prevent self-parent
                if ($data['parent_id'] == $categoryId) {
                    $data['parent_id'] = null;
                }
                
                $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
                $values = array_values($data);
                $values[] = $categoryId;
                
                db()->query("UPDATE categories SET $sets WHERE id = ?", $values);
                logActivity('category_update', 'Updated category: ' . $data['name']);
                redirect(SITE_URL . '/admin/categories.php', 'Category updated successfully!', 'success');
            }
        }
    }
    
    if ($postAction === 'delete') {
        $categoryId = (int)$_POST['category_id'];
        $category = db()->fetch("SELECT name FROM categories WHERE id = ?", [$categoryId]);
        
        // Check for products in category
        $productCount = db()->fetch("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$categoryId])['count'];
        
        if ($productCount > 0) {
            redirect(SITE_URL . '/admin/categories.php', "Cannot delete: $productCount products in this category.", 'error');
        } else {
            // Update child categories to have no parent
            db()->query("UPDATE categories SET parent_id = NULL WHERE parent_id = ?", [$categoryId]);
            db()->query("DELETE FROM categories WHERE id = ?", [$categoryId]);
            logActivity('category_delete', 'Deleted category: ' . ($category['name'] ?? 'Unknown'));
            redirect(SITE_URL . '/admin/categories.php', 'Category deleted successfully!', 'success');
        }
    }
}

// Get category for editing
$category = null;
if ($action === 'edit' && $id > 0) {
    $category = db()->fetch("SELECT * FROM categories WHERE id = ?", [$id]);
    if (!$category) {
        redirect(SITE_URL . '/admin/categories.php', 'Category not found.', 'error');
    }
}

// Get all categories
$categories = db()->fetchAll("
    SELECT c.*, 
           p.name as parent_name,
           (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    ORDER BY c.name ASC
");

$pageTitle = match($action) {
    'create' => 'Add New Category',
    'edit' => 'Edit Category',
    default => 'Categories Management'
};

include 'includes/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Categories List -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">All Categories</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Parent</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Products</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-tags text-4xl mb-4 opacity-50"></i>
                                <p>No categories found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <?php if ($cat['image']): ?>
                                    <img src="<?php echo SITE_URL . '/' . $cat['image']; ?>" 
                                         alt="" 
                                         class="w-12 h-12 rounded-xl object-cover">
                                    <?php else: ?>
                                    <div class="w-12 h-12 rounded-xl bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($cat['name']); ?></p>
                                        <p class="text-xs text-gray-500">/<?php echo $cat['slug']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                <?php echo $cat['parent_name'] ? htmlspecialchars($cat['parent_name']) : '<span class="text-gray-400">—</span>'; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                                    <?php echo $cat['product_count']; ?> products
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    <?php echo $cat['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo ucfirst($cat['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="?action=edit&id=<?php echo $cat['id']; ?>" 
                                       class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition"
                                       title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this category?')">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" 
                                                class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 hover:bg-red-200 dark:hover:bg-red-900/50 transition"
                                                title="Delete"
                                                <?php echo $cat['product_count'] > 0 ? 'disabled' : ''; ?>>
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
        </div>
    </div>
    
    <!-- Create/Edit Form -->
    <div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-28">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6">
                <?php echo $action === 'edit' ? 'Edit Category' : 'Add New Category'; ?>
            </h2>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                <?php if ($action === 'edit'): ?>
                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category Name *</label>
                    <input type="text" 
                           name="name" 
                           value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>"
                           required
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Parent Category</label>
                    <select name="parent_id" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="">None (Top Level)</option>
                        <?php foreach ($categories as $cat): ?>
                        <?php if ($action !== 'edit' || $cat['id'] != $category['id']): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category['parent_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                    <textarea name="description" 
                              rows="3"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none resize-none"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" 
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        <option value="active" <?php echo ($category['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($category['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category Image</label>
                    
                    <?php if ($action === 'edit' && !empty($category['image'])): ?>
                    <div class="mb-3">
                        <img src="<?php echo SITE_URL . '/' . $category['image']; ?>" 
                             alt="" 
                             class="w-20 h-20 rounded-xl object-cover">
                    </div>
                    <?php endif; ?>
                    
                    <input type="file" 
                           name="image" 
                           accept="image/*"
                           class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-500 file:text-white file:cursor-pointer">
                </div>
                
                <div class="flex gap-3 pt-4">
                    <?php if ($action === 'edit'): ?>
                    <a href="categories.php" class="flex-1 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancel
                    </a>
                    <?php endif; ?>
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-medium hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30">
                        <?php echo $action === 'edit' ? 'Update' : 'Create'; ?> Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
