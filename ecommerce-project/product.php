<?php
/**
 * Single Product Page
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');

if (!$slug) {
    redirect(SITE_URL . '/products.php');
}

// Get product
$product = db()->fetch("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.status = 'active'
", [$slug]);

if (!$product) {
    redirect(SITE_URL . '/products.php', 'Product not found.', 'error');
}

// Update view count (if column exists)
// db()->query("UPDATE products SET views = views + 1 WHERE id = ?", [$product['id']]);

// Check if in wishlist
$inWishlist = false;
if (isLoggedIn()) {
    $inWishlist = db()->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $product['id']]);
}

// Get related products
$relatedProducts = db()->fetchAll("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? AND status = 'active'
    ORDER BY RAND()
    LIMIT 4
", [$product['category_id'], $product['id']]);

// Calculate discount percentage
$discount = 0;
if ($product['sale_price']) {
    $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
}

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<main class="py-12 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="mb-8">
            <ol class="flex items-center gap-2 text-sm flex-wrap">
                <li><a href="<?php echo SITE_URL; ?>" class="text-gray-500 hover:text-primary-500 transition">Home</a></li>
                <li class="text-gray-400"><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?php echo SITE_URL; ?>/products.php" class="text-gray-500 hover:text-primary-500 transition">Products</a></li>
                <?php if ($product['category_name']): ?>
                <li class="text-gray-400"><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_slug']; ?>" class="text-gray-500 hover:text-primary-500 transition"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
                <?php endif; ?>
                <li class="text-gray-400"><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-gray-900 dark:text-white font-medium truncate max-w-xs"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>
        
        <!-- Product Details -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Product Image -->
                <div class="relative bg-gray-100 dark:bg-gray-700 p-8 lg:p-12">
                    <?php if ($discount > 0): ?>
                    <div class="absolute top-6 left-6 z-10">
                        <span class="px-4 py-2 bg-red-500 text-white text-sm font-bold rounded-full shadow-lg">
                            -<?php echo $discount; ?>% OFF
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product['featured']): ?>
                    <div class="absolute top-6 right-6 z-10">
                        <span class="px-4 py-2 bg-amber-500 text-white text-sm font-bold rounded-full shadow-lg">
                            <i class="fas fa-star mr-1"></i> Featured
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="aspect-square rounded-2xl overflow-hidden">
                        <img src="<?php echo SITE_URL . '/' . ($product['image'] ?: 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             class="w-full h-full object-cover product-image"
                             id="mainProductImage">
                    </div>
                    
                    <!-- Image Zoom Hint -->
                    <p class="text-center text-sm text-gray-500 mt-4">
                        <i class="fas fa-search-plus mr-1"></i>
                        Hover to zoom
                    </p>
                </div>
                
                <!-- Product Info -->
                <div class="p-8 lg:p-12">
                    <!-- Category -->
                    <?php if ($product['category_name']): ?>
                    <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_slug']; ?>" 
                       class="inline-block px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-600 text-sm font-medium rounded-full mb-4 hover:bg-primary-200 dark:hover:bg-primary-900/50 transition">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <h1 class="text-3xl lg:text-4xl font-display font-bold text-gray-900 dark:text-white mb-4">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>
                    
                    <!-- SKU -->
                    <?php if ($product['sku']): ?>
                    <p class="text-sm text-gray-500 mb-4">
                        SKU: <span class="font-mono"><?php echo htmlspecialchars($product['sku']); ?></span>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Short Description -->
                    <?php if ($product['short_description']): ?>
                    <p class="text-gray-600 dark:text-gray-300 mb-6 text-lg">
                        <?php echo htmlspecialchars($product['short_description']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Price -->
                    <div class="flex items-baseline gap-4 mb-6">
                        <?php if ($product['sale_price']): ?>
                        <span class="text-4xl font-bold text-primary-500"><?php echo formatPrice($product['sale_price']); ?></span>
                        <span class="text-2xl text-gray-400 line-through"><?php echo formatPrice($product['price']); ?></span>
                        <span class="px-3 py-1 bg-red-100 text-red-600 text-sm font-bold rounded-full">Save <?php echo formatPrice($product['price'] - $product['sale_price']); ?></span>
                        <?php else: ?>
                        <span class="text-4xl font-bold text-primary-500"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mb-8">
                        <?php if ($product['stock'] > 0): ?>
                        <div class="flex items-center gap-2 text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-medium">In Stock</span>
                            <span class="text-gray-500">(<?php echo $product['stock']; ?> available)</span>
                        </div>
                        <?php else: ?>
                        <div class="flex items-center gap-2 text-red-500">
                            <i class="fas fa-times-circle"></i>
                            <span class="font-medium">Out of Stock</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quantity & Add to Cart -->
                    <?php if ($product['stock'] > 0): ?>
                    <div class="flex flex-wrap gap-4 mb-8">
                        <!-- Quantity -->
                        <div class="flex items-center border-2 border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden">
                            <button type="button" onclick="updateQty(-1)" class="w-12 h-12 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" 
                                   id="productQty" 
                                   value="1" 
                                   min="1" 
                                   max="<?php echo $product['stock']; ?>"
                                   class="w-16 h-12 text-center border-0 bg-transparent text-gray-900 dark:text-white font-bold text-lg outline-none">
                            <button type="button" onclick="updateQty(1)" class="w-12 h-12 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <!-- Add to Cart -->
                        <button type="button" 
                                onclick="addToCart(<?php echo $product['id']; ?>)"
                                class="flex-1 min-w-[200px] py-3 px-8 bg-gradient-to-r from-primary-500 to-purple-600 text-white rounded-xl font-bold text-lg hover:from-primary-600 hover:to-purple-700 transition shadow-lg shadow-primary-500/30 flex items-center justify-center gap-3">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                        
                        <!-- Wishlist -->
                        <button type="button" 
                                onclick="toggleWishlist(<?php echo $product['id']; ?>, this)"
                                class="w-14 h-14 rounded-xl border-2 <?php echo $inWishlist ? 'border-red-500 bg-red-50 dark:bg-red-900/20 text-red-500' : 'border-gray-200 dark:border-gray-600 text-gray-400 hover:border-red-500 hover:text-red-500'; ?> flex items-center justify-center transition">
                            <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart text-xl"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="mb-8">
                        <button type="button" disabled class="w-full py-4 bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 rounded-xl font-bold text-lg cursor-not-allowed">
                            <i class="fas fa-ban mr-2"></i>
                            Out of Stock
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Features -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">Free Shipping</p>
                                <p class="text-xs text-gray-500">On orders over $100</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">Easy Returns</p>
                                <p class="text-xs text-gray-500">30-day return policy</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">Secure Payment</p>
                                <p class="text-xs text-gray-500">100% protected</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-500">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm">24/7 Support</p>
                                <p class="text-xs text-gray-500">Dedicated support</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Share -->
                    <div class="flex items-center gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-300 font-medium">Share:</span>
                        <div class="flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/product.php?slug=' . $product['slug']); ?>" 
                               target="_blank"
                               class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/product.php?slug=' . $product['slug']); ?>&text=<?php echo urlencode($product['name']); ?>" 
                               target="_blank"
                               class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(SITE_URL . '/product.php?slug=' . $product['slug']); ?>&description=<?php echo urlencode($product['name']); ?>" 
                               target="_blank"
                               class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center hover:bg-red-700 transition">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                            <button onclick="copyToClipboard('<?php echo SITE_URL . '/product.php?slug=' . $product['slug']; ?>')" 
                                    class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Description Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden mb-12">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200 dark:border-gray-700">
                <button onclick="showTab('description')" id="tab-description" class="tab-btn px-8 py-4 font-medium text-primary-500 border-b-2 border-primary-500">
                    Description
                </button>
                <button onclick="showTab('shipping')" id="tab-shipping" class="tab-btn px-8 py-4 font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    Shipping & Returns
                </button>
                <button onclick="showTab('reviews')" id="tab-reviews" class="tab-btn px-8 py-4 font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    Reviews
                </button>
            </div>
            
            <!-- Tab Content -->
            <div class="p-8 lg:p-12">
                <!-- Description Tab -->
                <div id="content-description" class="tab-content">
                    <div class="prose dark:prose-invert max-w-none">
                        <?php if ($product['description']): ?>
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        <?php else: ?>
                        <p class="text-gray-500">No detailed description available for this product.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Shipping Tab -->
                <div id="content-shipping" class="tab-content hidden">
                    <div class="prose dark:prose-invert max-w-none">
                        <h3>Shipping Information</h3>
                        <ul>
                            <li>Standard Shipping: 5-7 business days</li>
                            <li>Express Shipping: 2-3 business days</li>
                            <li>Free shipping on orders over $100</li>
                            <li>Tracking number provided for all orders</li>
                        </ul>
                        
                        <h3>Return Policy</h3>
                        <ul>
                            <li>30-day return policy</li>
                            <li>Items must be unused and in original packaging</li>
                            <li>Free returns on defective items</li>
                            <li>Refunds processed within 5-7 business days</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div id="content-reviews" class="tab-content hidden">
                    <div class="text-center py-12">
                        <i class="fas fa-star text-5xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500">No reviews yet. Be the first to review this product!</p>
                        <button class="mt-4 px-6 py-2 bg-primary-500 text-white rounded-xl font-medium hover:bg-primary-600 transition">
                            Write a Review
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <section>
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-display font-bold text-gray-900 dark:text-white">Related Products</h2>
                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $product['category_slug']; ?>" class="text-primary-500 hover:text-primary-600 font-medium">
                    View All <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $product): ?>
                <?php include 'includes/components/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</main>

<script>
// Quantity update
function updateQty(change) {
    const input = document.getElementById('productQty');
    const max = parseInt(input.max);
    let value = parseInt(input.value) + change;
    value = Math.max(1, Math.min(value, max));
    input.value = value;
}

// Add to cart with quantity
function addToCart(productId) {
    const qty = parseInt(document.getElementById('productQty').value);
    
    fetch('<?php echo SITE_URL; ?>/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId, quantity: qty })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            updateCartCount(data.cart_count);
        } else {
            showToast(data.message || 'Error adding to cart', 'error');
        }
    })
    .catch(() => showToast('Error adding to cart', 'error'));
}

// Toggle wishlist
function toggleWishlist(productId, btn) {
    fetch('<?php echo SITE_URL; ?>/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                btn.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/20', 'text-red-500');
                btn.classList.remove('border-gray-200', 'dark:border-gray-600', 'text-gray-400');
                icon.classList.replace('far', 'fas');
            } else {
                btn.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/20', 'text-red-500');
                btn.classList.add('border-gray-200', 'dark:border-gray-600', 'text-gray-400');
                icon.classList.replace('fas', 'far');
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Please login to add to wishlist', 'error');
        }
    });
}

// Tab switching
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    // Reset all tabs
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('text-primary-500', 'border-b-2', 'border-primary-500');
        el.classList.add('text-gray-500');
    });
    
    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    // Activate selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.add('text-primary-500', 'border-b-2', 'border-primary-500');
    activeTab.classList.remove('text-gray-500');
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Link copied to clipboard!', 'success');
    });
}

// Update cart count
function updateCartCount(count) {
    const counter = document.getElementById('cart-count');
    if (counter) counter.textContent = count;
}
</script>

<?php include 'includes/footer.php'; ?>
