<?php
/**
 * Shopping Cart Page
 * Modern E-Commerce Platform
 */

$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

$cartItems = getCartItems();
$cartTotal = getCartTotal();
$shippingCost = floatval(getSetting('shipping_cost', 9.99));
$freeShippingMin = floatval(getSetting('free_shipping_min', 99));
$taxRate = floatval(getSetting('tax_rate', 8.875)) / 100;

// Calculate shipping
$shipping = $cartTotal >= $freeShippingMin ? 0 : $shippingCost;
$tax = $cartTotal * $taxRate;
$grandTotal = $cartTotal + $shipping + $tax;
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="<?php echo SITE_URL; ?>" class="hover:text-primary-500 transition">Home</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-800 dark:text-white font-medium">Shopping Cart</span>
        </nav>

        <h1 class="text-3xl font-display font-bold text-gray-800 dark:text-white mb-8">
            Shopping Cart
            <span class="text-lg font-normal text-gray-500">(<?php echo count($cartItems); ?> items)</span>
        </h1>

        <?php if (empty($cartItems)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-3xl shadow-lg">
            <div class="w-32 h-32 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 dark:text-gray-500"></i>
            </div>
            <h2 class="text-2xl font-display font-bold text-gray-800 dark:text-white mb-4">Your cart is empty</h2>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">Looks like you haven't added any items to your cart yet. Start shopping and discover amazing products!</p>
            <a href="<?php echo SITE_URL; ?>/products.php" 
               class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-full font-bold shadow-lg hover:shadow-xl transition-all duration-300">
                <i class="fas fa-shopping-bag"></i>
                Start Shopping
            </a>
        </div>
        <?php else: ?>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Cart Header -->
                <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-4 bg-gray-100 dark:bg-gray-800 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400">
                    <div class="col-span-6">Product</div>
                    <div class="col-span-2 text-center">Price</div>
                    <div class="col-span-2 text-center">Quantity</div>
                    <div class="col-span-2 text-right">Total</div>
                </div>

                <?php foreach ($cartItems as $item): 
                    $itemPrice = $item['sale_price'] ?? $item['price'];
                    $itemTotal = $itemPrice * $item['quantity'];
                ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 cart-row" data-cart-id="<?php echo $item['id']; ?>">
                    <div class="grid md:grid-cols-12 gap-4 items-center">
                        <!-- Product Info -->
                        <div class="md:col-span-6 flex items-center gap-4">
                            <div class="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0">
                                <img src="<?php echo imageUrl($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 dark:text-white line-clamp-2 hover:text-primary-500 transition">
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                </h3>
                                <?php if ($item['stock'] <= 5 && $item['stock'] > 0): ?>
                                <p class="text-sm text-yellow-600 mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> Only <?php echo $item['stock']; ?> left
                                </p>
                                <?php endif; ?>
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" 
                                        class="text-sm text-red-500 hover:text-red-600 mt-2 flex items-center gap-1 transition">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="md:col-span-2 text-center">
                            <span class="md:hidden text-sm text-gray-500">Price: </span>
                            <span class="font-bold text-gray-800 dark:text-white"><?php echo formatPrice($itemPrice); ?></span>
                            <?php if ($item['sale_price']): ?>
                            <span class="block text-sm text-gray-400 line-through"><?php echo formatPrice($item['price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Quantity -->
                        <div class="md:col-span-2 flex justify-center">
                            <div class="quantity-selector">
                                <button onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo max(1, $item['quantity'] - 1); ?>)">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <input type="number" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['stock']; ?>"
                                       onchange="updateCartQuantity(<?php echo $item['id']; ?>, this.value)"
                                       class="qty-input">
                                <button onclick="updateCartQuantity(<?php echo $item['id']; ?>, <?php echo min($item['stock'], $item['quantity'] + 1); ?>)">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="md:col-span-2 text-right">
                            <span class="md:hidden text-sm text-gray-500">Total: </span>
                            <span class="text-lg font-bold text-primary-600"><?php echo formatPrice($itemTotal); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Cart Actions -->
                <div class="flex flex-wrap items-center justify-between gap-4 mt-6">
                    <a href="<?php echo SITE_URL; ?>/products.php" 
                       class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary-500 transition">
                        <i class="fas fa-arrow-left"></i>
                        Continue Shopping
                    </a>
                    <button onclick="clearCart()" class="flex items-center gap-2 text-red-500 hover:text-red-600 transition">
                        <i class="fas fa-trash"></i>
                        Clear Cart
                    </button>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-6 sticky top-24">
                    <h2 class="text-xl font-display font-bold text-gray-800 dark:text-white mb-6">Order Summary</h2>
                    
                    <!-- Coupon Code -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Coupon Code</label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   id="couponCode" 
                                   placeholder="Enter code" 
                                   class="flex-1 px-4 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                            <button onclick="applyCoupon()" 
                                    class="px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-xl font-medium text-gray-700 dark:text-white transition">
                                Apply
                            </button>
                        </div>
                    </div>
                    
                    <!-- Summary Details -->
                    <div class="space-y-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white"><?php echo formatPrice($cartTotal); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Shipping</span>
                            <?php if ($shipping == 0): ?>
                            <span class="text-green-500 font-medium">Free</span>
                            <?php else: ?>
                            <span class="font-medium text-gray-800 dark:text-white"><?php echo formatPrice($shipping); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Tax (<?php echo $taxRate * 100; ?>%)</span>
                            <span class="font-medium text-gray-800 dark:text-white"><?php echo formatPrice($tax); ?></span>
                        </div>
                        
                        <?php if ($cartTotal < $freeShippingMin && $shipping > 0): ?>
                        <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                <i class="fas fa-info-circle mr-1"></i>
                                Add <?php echo formatPrice($freeShippingMin - $cartTotal); ?> more for free shipping!
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Total -->
                    <div class="border-t border-gray-100 dark:border-gray-700 mt-6 pt-6">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-lg font-bold text-gray-800 dark:text-white">Total</span>
                            <span class="text-2xl font-display font-bold text-primary-600"><?php echo formatPrice($grandTotal); ?></span>
                        </div>
                        
                        <a href="<?php echo SITE_URL; ?>/checkout.php" 
                           class="block w-full py-4 text-center bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-primary-500/30 transition-all duration-300">
                            Proceed to Checkout
                        </a>
                        
                        <!-- Payment Methods -->
                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-sm text-gray-500 text-center mb-3">Secure Checkout</p>
                            <div class="flex items-center justify-center gap-3 text-gray-400">
                                <i class="fab fa-cc-visa text-2xl hover:text-blue-600 transition"></i>
                                <i class="fab fa-cc-mastercard text-2xl hover:text-red-500 transition"></i>
                                <i class="fab fa-cc-amex text-2xl hover:text-blue-500 transition"></i>
                                <i class="fab fa-cc-paypal text-2xl hover:text-blue-700 transition"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
async function clearCart() {
    if (!confirm('Are you sure you want to clear your cart?')) return;
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'clear' })
        });
        
        const data = await response.json();
        if (data.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function applyCoupon() {
    const code = document.getElementById('couponCode').value.trim();
    if (!code) {
        showToast('Please enter a coupon code', 'warning');
        return;
    }
    
    try {
        const response = await fetch('<?php echo SITE_URL; ?>/api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'apply_coupon', code: code })
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('Coupon applied successfully!', 'success');
            location.reload();
        } else {
            showToast(data.error || 'Invalid coupon code', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Failed to apply coupon', 'error');
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
