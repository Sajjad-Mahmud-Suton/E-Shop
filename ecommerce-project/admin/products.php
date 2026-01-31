<?php
/**
 * Admin Products Management
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
        redirect(SITE_URL . '/admin/products.php', 'Invalid request.', 'error');
    }
    
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'create' || $postAction === 'update') {
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'slug' => createSlug($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'short_description' => sanitize($_POST['short_description'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'stock' => (int)($_POST['stock'] ?? 0),
            'sku' => sanitize($_POST['sku'] ?? ''),
            'featured' => isset($_POST['featured']) ? 1 : 0,
            'status' => sanitize($_POST['status'] ?? 'active'),
        ];
        
        // Validate
        $errors = [];
        if (empty($data['name'])) $errors[] = 'Product name is required.';
        if ($data['price'] <= 0) $errors[] = 'Valid price is required.';
        if ($data['category_id'] <= 0) $errors[] = 'Please select a category.';
        
        if (empty($errors)) {
            // Handle image upload
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = '../assets/images/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $data['image'] = 'assets/images/products/' . $filename;
                    }
                }
            }
            
            if ($postAction === 'create') {
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                
                db()->query("INSERT INTO products ($columns) VALUES ($placeholders)", array_values($data));
                logActivity('product_create', 'Created product: ' . $data['name']);
                redirect(SITE_URL . '/admin/products.php', 'Product created successfully!', 'success');
            } else {
                $productId = (int)$_POST['product_id'];
                $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
                $values = array_values($data);
                $values[] = $productId;
                
                db()->query("UPDATE products SET $sets, updated_at = NOW() WHERE id = ?", $values);
                logActivity('product_update', 'Updated product: ' . $data['name']);
                redirect(SITE_URL . '/admin/products.php', 'Product updated successfully!', 'success');
            }
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $_POST;
        }
    }
    
    if ($postAction === 'delete') {
        $productId = (int)$_POST['product_id'];
        $product = db()->fetch("SELECT name FROM products WHERE id = ?", [$productId]);
        
        db()->query("DELETE FROM products WHERE id = ?", [$productId]);
        logActivity('product_delete', 'Deleted product: ' . ($product['name'] ?? 'Unknown'));
        redirect(SITE_URL . '/admin/products.php', 'Product deleted successfully!', 'success');
    }
    
    if ($postAction === 'bulk_delete') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            db()->query("DELETE FROM products WHERE id IN ($placeholders)", $ids);
            logActivity('product_bulk_delete', 'Deleted ' . count($ids) . ' products');
            redirect(SITE_URL . '/admin/products.php', count($ids) . ' products deleted.', 'success');
        }
    }
}

// Get categories for dropdown
$categories = db()->fetchAll("SELECT id, name FROM categories ORDER BY name ASC");

// Get product for editing
$product = null;
if ($action === 'edit' && $id > 0) {
    $product = db()->fetch("SELECT * FROM products WHERE id = ?", [$id]);
    if (!$product) {
        redirect(SITE_URL . '/admin/products.php', 'Product not found.', 'error');
    }
}

// Get products for listing
$search = sanitize($_GET['search'] ?? '');
$categoryFilter = (int)($_GET['category'] ?? 0);
$statusFilter = sanitize($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter) {
    $where .= " AND p.status = ?";
    $params[] = $statusFilter;
}

$totalProducts = db()->fetch("SELECT COUNT(*) as count FROM products p $where", $params)['count'];
$totalPages = ceil($totalProducts / $perPage);

$products = db()->fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where 
    ORDER BY p.created_at DESC 
    LIMIT $perPage OFFSET $offset
", $params);

$pageTitle = match($action) {
    'create' => 'Add New Product',
    'edit' => 'Edit Product',
    default => 'Products Management'
};

include 'includes/header.php';
?>

<?php if ($action === 'list'): ?>
<!-- Products List -->
<div class="space-y-6">
    <!-- Actions Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <form method="GET" class="flex gap-2">
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search products..." 
                           class="pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
                
                <select name="category" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                
                <button type="submit" class="px-4 py-2.5 bg-gray-200 dark:bg-gray-600 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                    Filter
                </button>
            </form>
        </div>
        
        <div class="flex gap-3">
            <button type="button" 
                    id="bulkDeleteBtn"
                    class="hidden px-4 py-2.5 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">
                <i class="fas fa-trash mr-2"></i>Delete Selected
            </button>
            <a href="?action=create" class="px-6 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-medium hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30">
                <i class="fas fa-plus mr-2"></i>Add Product
            </a>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Product</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Price</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Stock</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-4 opacity-50"></i>
                            <p>No products found</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="product_ids[]" value="<?php echo $prod['id']; ?>" class="product-checkbox rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <img src="<?php echo SITE_URL . '/' . ($prod['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                     alt="" 
                                     class="w-12 h-12 rounded-xl object-cover">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($prod['name']); ?></p>
                                    <p class="text-xs text-gray-500">SKU: <?php echo htmlspecialchars($prod['sku'] ?: 'N/A'); ?></p>
                                </div>
                                <?php if ($prod['featured']): ?>
                                <span class="px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full">Featured</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            <?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <?php if ($prod['sale_price']): ?>
                                <span class="font-medium text-red-500"><?php echo formatPrice($prod['sale_price']); ?></span>
                                <span class="text-sm text-gray-400 line-through ml-1"><?php echo formatPrice($prod['price']); ?></span>
                                <?php else: ?>
                                <span class="font-medium text-gray-900 dark:text-white"><?php echo formatPrice($prod['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php echo $prod['stock'] == 0 ? 'bg-red-100 text-red-700' : ($prod['stock'] <= 5 ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'); ?>">
                                <?php echo $prod['stock']; ?> in stock
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php echo $prod['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                <?php echo ucfirst($prod['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $prod['slug']; ?>" 
                                   target="_blank"
                                   class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                                   title="View">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <a href="?action=edit&id=<?php echo $prod['id']; ?>" 
                                   class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition"
                                   title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <form method="POST" class="inline" onsubmit="return confirmDelete('Delete this product?')">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
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
                Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalProducts); ?> of <?php echo $totalProducts; ?> products
            </p>
            <div class="flex gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="px-4 py-2 rounded-lg <?php echo $i === $page ? 'bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'; ?> transition">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&status=<?php echo $statusFilter; ?>" 
                   class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bulk Delete Form (Hidden) -->
<form id="bulkDeleteForm" method="POST" style="display:none;">
    <?php echo csrfField(); ?>
    <input type="hidden" name="action" value="bulk_delete">
    <div id="bulkDeleteIds"></div>
</form>

<script>
// Select All Checkbox
const selectAll = document.getElementById('selectAll');
const productCheckboxes = document.querySelectorAll('.product-checkbox');
const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
const bulkDeleteForm = document.getElementById('bulkDeleteForm');
const bulkDeleteIds = document.getElementById('bulkDeleteIds');

selectAll?.addEventListener('change', function() {
    productCheckboxes.forEach(cb => cb.checked = this.checked);
    updateBulkDeleteBtn();
});

productCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkDeleteBtn);
});

function updateBulkDeleteBtn() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    if (checked.length > 0) {
        bulkDeleteBtn.classList.remove('hidden');
        bulkDeleteBtn.textContent = `Delete Selected (${checked.length})`;
    } else {
        bulkDeleteBtn.classList.add('hidden');
    }
}

bulkDeleteBtn?.addEventListener('click', function() {
    if (!confirm('Delete all selected products?')) return;
    
    bulkDeleteIds.innerHTML = '';
    document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        bulkDeleteIds.appendChild(input);
    });
    
    bulkDeleteForm.submit();
});
</script>

<?php else: ?>
<!-- Create/Edit Form -->
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="products.php" class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300 hover:text-primary-500 transition">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Products</span>
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
    
    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <?php echo csrfField(); ?>
        <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
        <?php if ($action === 'edit'): ?>
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Product Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Name *</label>
                            <input type="text" 
                                   name="name" 
                                   value="<?php echo htmlspecialchars($_SESSION['old']['name'] ?? $product['name'] ?? ''); ?>"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Short Description</label>
                            <textarea name="short_description" 
                                      rows="2"
                                      class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none resize-none"><?php echo htmlspecialchars($_SESSION['old']['short_description'] ?? $product['short_description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Description</label>
                            <textarea name="description" 
                                      rows="6"
                                      class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none resize-none"><?php echo htmlspecialchars($_SESSION['old']['description'] ?? $product['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Product Image</h3>
                    
                    <div class="flex items-start gap-6">
                        <?php if ($action === 'edit' && $product['image']): ?>
                        <div class="flex-shrink-0">
                            <img src="<?php echo SITE_URL . '/' . $product['image']; ?>" 
                                 alt="" 
                                 class="w-32 h-32 rounded-xl object-cover">
                            <p class="text-xs text-gray-500 mt-2">Current Image</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex-1">
                            <label class="block w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center cursor-pointer hover:border-primary-500 transition">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600 dark:text-gray-300">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP up to 5MB</p>
                                <input type="file" name="image" accept="image/*" class="hidden" id="imageInput">
                            </label>
                            <div id="imagePreview" class="mt-4 hidden">
                                <img src="" alt="" class="w-32 h-32 rounded-xl object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Pricing</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Regular Price *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                <input type="number" 
                                       name="price" 
                                       step="0.01" 
                                       value="<?php echo $_SESSION['old']['price'] ?? $product['price'] ?? ''; ?>"
                                       required
                                       class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sale Price</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                <input type="number" 
                                       name="sale_price" 
                                       step="0.01" 
                                       value="<?php echo $_SESSION['old']['sale_price'] ?? $product['sale_price'] ?? ''; ?>"
                                       class="w-full pl-8 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Organization</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                            <select name="category_id" 
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($_SESSION['old']['category_id'] ?? $product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SKU</label>
                            <input type="text" 
                                   name="sku" 
                                   value="<?php echo htmlspecialchars($_SESSION['old']['sku'] ?? $product['sku'] ?? ''); ?>"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock Quantity</label>
                            <input type="number" 
                                   name="stock" 
                                   value="<?php echo $_SESSION['old']['stock'] ?? $product['stock'] ?? '0'; ?>"
                                   min="0"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Status</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Status</label>
                            <select name="status" 
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                <option value="active" <?php echo ($_SESSION['old']['status'] ?? $product['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($_SESSION['old']['status'] ?? $product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <input type="checkbox" 
                                   name="featured" 
                                   id="featured"
                                   value="1"
                                   <?php echo ($_SESSION['old']['featured'] ?? $product['featured'] ?? false) ? 'checked' : ''; ?>
                                   class="w-5 h-5 rounded border-gray-300 text-primary-500 focus:ring-primary-500">
                            <label for="featured" class="text-sm text-gray-700 dark:text-gray-300">Featured Product</label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="flex gap-3">
                    <a href="products.php" class="flex-1 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-medium text-center hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancel
                    </a>
                    <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-medium hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30">
                        <?php echo $action === 'edit' ? 'Update Product' : 'Create Product'; ?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<?php unset($_SESSION['old']); ?>

<script>
// Image Preview
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');

imageInput?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.querySelector('img').src = e.target.result;
            imagePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
