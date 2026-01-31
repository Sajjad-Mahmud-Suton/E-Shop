<?php
/**
 * Product Card Component
 * Modern E-Commerce Platform
 * 
 * Required variable: $product
 */

$discount = 0;
if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']) {
    $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
}

$inWishlist = isInWishlist($product['id']);
$isOutOfStock = $product['stock'] <= 0;
?>

<div class="product-card" data-product-id="<?php echo $product['id']; ?>">
    <!-- Product Image -->
    <div class="product-image">
        <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>">
            <img src="<?php echo imageUrl($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                 loading="lazy">
        </a>
        
        <!-- Badges -->
        <div class="product-badge">
            <?php if ($discount > 0): ?>
            <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
            <?php endif; ?>
            <?php if ($product['new_arrival']): ?>
            <span class="badge badge-new">New</span>
            <?php endif; ?>
            <?php if ($product['featured']): ?>
            <span class="badge badge-featured">Featured</span>
            <?php endif; ?>
            <?php if ($isOutOfStock): ?>
            <span class="badge badge-soldout">Sold Out</span>
            <?php endif; ?>
        </div>
        
        <!-- Overlay -->
        <div class="product-overlay"></div>
        
        <!-- Quick Actions -->
        <div class="product-actions">
            <button class="action-btn <?php echo $inWishlist ? 'active' : ''; ?>" 
                    data-wishlist="<?php echo $product['id']; ?>" 
                    title="Add to Wishlist">
                <i class="<?php echo $inWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
            </button>
            <button class="action-btn" 
                    data-quick-view="<?php echo $product['id']; ?>" 
                    title="Quick View">
                <i class="fas fa-eye"></i>
            </button>
            <?php if (!$isOutOfStock): ?>
            <button class="action-btn" 
                    data-add-to-cart="<?php echo $product['id']; ?>" 
                    title="Add to Cart">
                <i class="fas fa-shopping-cart"></i>
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product Info -->
    <div class="product-info">
        <!-- Category -->
        <a href="<?php echo SITE_URL; ?>/category.php?id=<?php echo $product['category_id']; ?>" 
           class="product-category">
            <?php echo $product['category_name'] ?? 'Product'; ?>
        </a>
        
        <!-- Title -->
        <h3 class="product-title">
            <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $product['slug']; ?>">
                <?php echo htmlspecialchars($product['title']); ?>
            </a>
        </h3>
        
        <!-- Rating -->
        <div class="product-rating">
            <?php 
            $rating = round($product['rating_avg'] ?? 4);
            for ($i = 1; $i <= 5; $i++): 
            ?>
            <i class="fas fa-star <?php echo $i <= $rating ? '' : 'opacity-30'; ?>"></i>
            <?php endfor; ?>
            <span>(<?php echo $product['rating_count'] ?? 0; ?>)</span>
        </div>
        
        <!-- Price -->
        <div class="product-price">
            <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
            <span class="current-price"><?php echo formatPrice($product['sale_price']); ?></span>
            <span class="original-price"><?php echo formatPrice($product['price']); ?></span>
            <span class="discount-badge">Save <?php echo $discount; ?>%</span>
            <?php else: ?>
            <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
