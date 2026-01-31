<?php
/**
 * Wishlist Page
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Require login
if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php', 'Please login to view your wishlist.', 'error');
}

$userId = $_SESSION['user_id'];

// Get wishlist items
$wishlistItems = db()->fetchAll("
    SELECT p.*, c.name as category_name, c.slug as category_slug, w.created_at as added_at
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
", [$userId]);

$pageTitle = 'My Wishlist';
include 'includes/header.php';
?>

<main class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center gap-2 text-sm">
                <li><a href="<?php echo SITE_URL; ?>" class="text-gray-500 hover:text-primary-500 transition">Home</a></li>
                <li class="text-gray-400"><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 dark:text-white font-medium">Wishlist</li>
            </ol>
        </nav>
        
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-8">
            <div>
                <h1 class="text-3xl font-display font-bold text-gray-900 dark:text-white">My Wishlist</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    <?php echo count($wishlistItems); ?> item<?php echo count($wishlistItems) !== 1 ? 's' : ''; ?> saved
                </p>
            </div>
            
            <?php if (!empty($wishlistItems)): ?>
            <button onclick="addAllToCart()" class="px-6 py-3 bg-primary-500 text-white rounded-xl font-medium hover:bg-primary-600 transition shadow-lg shadow-primary-500/30">
                <i class="fas fa-shopping-cart mr-2"></i>
                Add All to Cart
            </button>
            <?php endif; ?>
        </div>
        
        <?php if (empty($wishlistItems)): ?>
        <!-- Empty Wishlist -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-12 text-center">
            <div class="w-24 h-24 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-heart text-4xl text-pink-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Your wishlist is empty</h2>
            <p class="text-gray-500 mb-6">Start adding items you love to your wishlist</p>
            <a href="<?php echo SITE_URL; ?>/products.php" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-bold hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30">
                <i class="fas fa-shopping-bag"></i>
                Browse Products
            </a>
        </div>
        
        <?php else: ?>
        <!-- Wishlist Items Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach ($wishlistItems as $item): ?>
            <div class="wishlist-item bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden group" data-id="<?php echo $item['id']; ?>">
                <!-- Product Image -->
                <div class="relative aspect-square overflow-hidden">
                    <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $item['slug']; ?>">
                        <img src="<?php echo SITE_URL . '/' . ($item['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    </a>
                    
                    <!-- Remove Button -->
                    <button onclick="removeFromWishlist(<?php echo $item['id']; ?>, this)" 
                            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center text-red-500 hover:bg-red-500 hover:text-white transition">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <!-- Sale Badge -->
                    <?php if ($item['sale_price']): ?>
                    <?php $discount = round((($item['price'] - $item['sale_price']) / $item['price']) * 100); ?>
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-full">
                            -<?php echo $discount; ?>%
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Out of Stock Overlay -->
                    <?php if ($item['stock'] <= 0): ?>
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                        <span class="px-4 py-2 bg-red-500 text-white font-bold rounded-lg">Out of Stock</span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Info -->
                <div class="p-4">
                    <!-- Category -->
                    <?php if ($item['category_name']): ?>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $item['category_slug']; ?>" 
                       class="text-xs text-primary-500 hover:text-primary-600 font-medium uppercase tracking-wider">
                        <?php echo htmlspecialchars($item['category_name']); ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Name -->
                    <h3 class="font-bold text-gray-900 dark:text-white mt-1 mb-2 line-clamp-2">
                        <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $item['slug']; ?>" class="hover:text-primary-500 transition">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </a>
                    </h3>
                    
                    <!-- Price -->
                    <div class="flex items-baseline gap-2 mb-4">
                        <?php if ($item['sale_price']): ?>
                        <span class="text-lg font-bold text-primary-500"><?php echo formatPrice($item['sale_price']); ?></span>
                        <span class="text-sm text-gray-400 line-through"><?php echo formatPrice($item['price']); ?></span>
                        <?php else: ?>
                        <span class="text-lg font-bold text-primary-500"><?php echo formatPrice($item['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <?php if ($item['stock'] > 0): ?>
                    <button onclick="addToCart(<?php echo $item['id']; ?>)" 
                            class="w-full py-2.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl font-medium hover:bg-primary-500 dark:hover:bg-primary-500 dark:hover:text-white transition flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        Add to Cart
                    </button>
                    <?php else: ?>
                    <button disabled class="w-full py-2.5 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl font-medium cursor-not-allowed">
                        Out of Stock
                    </button>
                    <?php endif; ?>
                    
                    <!-- Added Date -->
                    <p class="text-xs text-gray-400 mt-3 text-center">
                        Added <?php echo timeAgo($item['added_at']); ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Remove from wishlist
function removeFromWishlist(productId, btn) {
    fetch('<?php echo SITE_URL; ?>/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = btn.closest('.wishlist-item');
            item.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                item.remove();
                // Check if wishlist is empty
                if (document.querySelectorAll('.wishlist-item').length === 0) {
                    location.reload();
                }
            }, 300);
            showToast('Removed from wishlist', 'success');
        }
    });
}

// Add to cart
function addToCart(productId) {
    fetch('<?php echo SITE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Added to cart!', 'success');
            updateCartCount(data.cart_count);
        } else {
            showToast(data.message || 'Error adding to cart', 'error');
        }
    });
}

// Add all to cart
function addAllToCart() {
    const items = document.querySelectorAll('.wishlist-item');
    items.forEach(item => {
        const id = item.dataset.id;
        addToCart(id);
    });
}

// Update cart count
function updateCartCount(count) {
    const counter = document.getElementById('cart-count');
    if (counter) counter.textContent = count;
}
</script>

<style>
@keyframes fadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.9); }
}
</style>

<?php include 'includes/footer.php'; ?>
