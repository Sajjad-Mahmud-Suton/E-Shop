<?php
/**
 * Products Listing Page
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Get filter parameters
$categorySlug = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$where = "WHERE p.status = 'active'";
$params = [];

// Category filter
$category = null;
if ($categorySlug) {
    $category = db()->fetch("SELECT * FROM categories WHERE slug = ?", [$categorySlug]);
    if ($category) {
        $where .= " AND p.category_id = ?";
        $params[] = $category['id'];
    }
}

// Search filter
if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Price filter
if ($minPrice > 0) {
    $where .= " AND COALESCE(p.sale_price, p.price) >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where .= " AND COALESCE(p.sale_price, p.price) <= ?";
    $params[] = $maxPrice;
}

// Sort order
$orderBy = match($sort) {
    'price_low' => 'COALESCE(p.sale_price, p.price) ASC',
    'price_high' => 'COALESCE(p.sale_price, p.price) DESC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC',
    'oldest' => 'p.created_at ASC',
    default => 'p.created_at DESC'
};

// Get total count
$totalProducts = db()->fetch("SELECT COUNT(*) as count FROM products p $where", $params)['count'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$products = db()->fetchAll("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY $orderBy
    LIMIT $perPage OFFSET $offset
", $params);

// Get all categories for filter
$categories = db()->fetchAll("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
    WHERE c.status = 'active'
    GROUP BY c.id
    ORDER BY c.name ASC
");

// Get price range
$priceRange = db()->fetch("
    SELECT MIN(COALESCE(sale_price, price)) as min_price, MAX(COALESCE(sale_price, price)) as max_price
    FROM products WHERE status = 'active'
");

$pageTitle = $category ? $category['name'] : ($search ? "Search: $search" : 'All Products');
include 'includes/header.php';
?>

<main class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center gap-2 text-sm">
                <li><a href="<?php echo SITE_URL; ?>" class="text-gray-500 hover:text-primary-500 transition">Home</a></li>
                <li class="text-gray-400"><i class="fas fa-chevron-right text-xs"></i></li>
                <?php if ($category): ?>
                <li><a href="<?php echo SITE_URL; ?>/products.php" class="text-gray-500 hover:text-primary-500 transition">Products</a></li>
                <li class="text-gray-400"><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 dark:text-white font-medium"><?php echo htmlspecialchars($category['name']); ?></li>
                <?php else: ?>
                <li class="text-gray-900 dark:text-white font-medium">Products</li>
                <?php endif; ?>
            </ol>
        </nav>
        
        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center mb-8">
            <div>
                <h1 class="text-3xl font-display font-bold text-gray-900 dark:text-white"><?php echo $pageTitle; ?></h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1"><?php echo number_format($totalProducts); ?> products found</p>
            </div>
            
            <div class="flex flex-wrap gap-4">
                <!-- Search -->
                <form method="GET" class="flex">
                    <?php if ($categorySlug): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>">
                    <?php endif; ?>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search products..." 
                               class="pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none w-64">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </form>
                
                <!-- Sort -->
                <select id="sortSelect" 
                        class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                    <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 sticky top-28">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-gray-900 dark:text-white">Filters</h3>
                        <?php if ($categorySlug || $search || $minPrice || $maxPrice): ?>
                        <a href="<?php echo SITE_URL; ?>/products.php" class="text-sm text-primary-500 hover:text-primary-600">Clear All</a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Categories</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            <a href="<?php echo SITE_URL; ?>/products.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" 
                               class="flex items-center justify-between py-2 px-3 rounded-lg <?php echo !$categorySlug ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> transition">
                                <span>All Products</span>
                                <span class="text-sm"><?php echo $totalProducts; ?></span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                            <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['slug']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                               class="flex items-center justify-between py-2 px-3 rounded-lg <?php echo $categorySlug === $cat['slug'] ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-600' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> transition">
                                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                <span class="text-sm"><?php echo $cat['product_count']; ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Price Range</h4>
                        <form method="GET" id="priceFilterForm">
                            <?php if ($categorySlug): ?>
                            <input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>">
                            <?php endif; ?>
                            <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <?php if ($sort): ?>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                            <?php endif; ?>
                            
                            <div class="flex items-center gap-2 mb-3">
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                    <input type="number" 
                                           name="min_price" 
                                           value="<?php echo $minPrice ?: ''; ?>"
                                           placeholder="Min"
                                           min="0"
                                           class="w-full pl-7 pr-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                </div>
                                <span class="text-gray-400">-</span>
                                <div class="relative flex-1">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">$</span>
                                    <input type="number" 
                                           name="max_price" 
                                           value="<?php echo $maxPrice ?: ''; ?>"
                                           placeholder="Max"
                                           min="0"
                                           class="w-full pl-7 pr-2 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
                                </div>
                            </div>
                            <button type="submit" class="w-full py-2 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition text-sm">
                                Apply Filter
                            </button>
                        </form>
                        
                        <?php if ($priceRange): ?>
                        <p class="text-xs text-gray-500 mt-2">
                            Range: <?php echo formatPrice($priceRange['min_price']); ?> - <?php echo formatPrice($priceRange['max_price']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Active Filters -->
                    <?php if ($minPrice || $maxPrice): ?>
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Active Filters</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php if ($minPrice): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full text-sm">
                                Min: <?php echo formatPrice($minPrice); ?>
                                <a href="<?php echo removeQueryParam('min_price'); ?>" class="hover:text-primary-800"><i class="fas fa-times"></i></a>
                            </span>
                            <?php endif; ?>
                            <?php if ($maxPrice): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-600 rounded-full text-sm">
                                Max: <?php echo formatPrice($maxPrice); ?>
                                <a href="<?php echo removeQueryParam('max_price'); ?>" class="hover:text-primary-800"><i class="fas fa-times"></i></a>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="lg:col-span-3">
                <?php if (empty($products)): ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 text-center">
                    <i class="fas fa-box-open text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No products found</h3>
                    <p class="text-gray-500 mb-6">Try adjusting your filters or search term</p>
                    <a href="<?php echo SITE_URL; ?>/products.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 text-white rounded-xl font-medium hover:bg-primary-600 transition">
                        <i class="fas fa-redo"></i>
                        Clear Filters
                    </a>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                    <?php include 'includes/components/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="mt-8 flex items-center justify-center gap-2">
                    <?php if ($page > 1): ?>
                    <a href="<?php echo updateQueryParam('page', $page - 1); ?>" 
                       class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-md transition">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                    <a href="<?php echo updateQueryParam('page', 1); ?>" 
                       class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-md transition">
                        1
                    </a>
                    <?php if ($startPage > 2): ?>
                    <span class="text-gray-400">...</span>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?php echo updateQueryParam('page', $i); ?>" 
                       class="w-10 h-10 rounded-xl <?php echo $i === $page ? 'bg-primary-500 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> flex items-center justify-center shadow-md transition font-medium">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                    <span class="text-gray-400">...</span>
                    <?php endif; ?>
                    <a href="<?php echo updateQueryParam('page', $totalPages); ?>" 
                       class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-md transition">
                        <?php echo $totalPages; ?>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="<?php echo updateQueryParam('page', $page + 1); ?>" 
                       class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 shadow-md transition">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
// Sort select change
document.getElementById('sortSelect')?.addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('sort', this.value);
    url.searchParams.delete('page');
    window.location = url;
});
</script>

<?php include 'includes/footer.php'; ?>
