<?php
/**
 * Checkout Page
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Require login for checkout
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = '/checkout.php';
    redirect(SITE_URL . '/login.php', 'Please login to continue with checkout', 'warning');
}

$user = currentUser();
$cartItems = getCartItems();
$cartTotal = getCartTotal();

// Redirect if cart is empty
if (empty($cartItems)) {
    redirect(SITE_URL . '/cart.php', 'Your cart is empty', 'warning');
}

$shippingCost = floatval(getSetting('shipping_cost', 9.99));
$freeShippingMin = floatval(getSetting('free_shipping_min', 99));
$taxRate = floatval(getSetting('tax_rate', 8.875)) / 100;

$shipping = $cartTotal >= $freeShippingMin ? 0 : $shippingCost;
$tax = $cartTotal * $taxRate;
$grandTotal = $cartTotal + $shipping + $tax;

$error = '';
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $shippingName = sanitize($_POST['shipping_name'] ?? '');
        $shippingEmail = sanitize($_POST['shipping_email'] ?? '');
        $shippingPhone = sanitize($_POST['shipping_phone'] ?? '');
        $shippingAddress = sanitize($_POST['shipping_address'] ?? '');
        $shippingCity = sanitize($_POST['shipping_city'] ?? '');
        $shippingCountry = sanitize($_POST['shipping_country'] ?? '');
        $shippingZip = sanitize($_POST['shipping_zip'] ?? '');
        $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');
        $notes = sanitize($_POST['notes'] ?? '');
        
        // Validation
        if (empty($shippingName) || empty($shippingEmail) || empty($shippingPhone) || 
            empty($shippingAddress) || empty($shippingCity) || empty($shippingCountry) || empty($shippingZip)) {
            $error = 'Please fill in all required fields.';
        } elseif (!isValidEmail($shippingEmail)) {
            $error = 'Please enter a valid email address.';
        } else {
            try {
                // Create order
                $orderNumber = generateOrderNumber();
                
                db()->query("
                    INSERT INTO orders (
                        order_number, user_id, subtotal, shipping_cost, tax, total,
                        status, payment_method, payment_status,
                        shipping_name, shipping_email, shipping_phone, shipping_address,
                        shipping_city, shipping_country, shipping_zip, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $orderNumber, $user['id'], $cartTotal, $shipping, $tax, $grandTotal,
                    $paymentMethod, $shippingName, $shippingEmail, $shippingPhone, $shippingAddress,
                    $shippingCity, $shippingCountry, $shippingZip, $notes
                ]);
                
                $orderId = db()->lastInsertId();
                
                // Add order items
                foreach ($cartItems as $item) {
                    $itemPrice = $item['sale_price'] ?? $item['price'];
                    $itemTotal = $itemPrice * $item['quantity'];
                    
                    db()->query("
                        INSERT INTO order_items (order_id, product_id, product_title, product_image, quantity, price, total)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ", [$orderId, $item['product_id'], $item['title'], $item['image'], $item['quantity'], $itemPrice, $itemTotal]);
                    
                    // Update product stock
                    db()->query("UPDATE products SET stock = stock - ?, sales_count = sales_count + ? WHERE id = ?", 
                               [$item['quantity'], $item['quantity'], $item['product_id']]);
                }
                
                // Clear cart
                db()->query("DELETE FROM cart WHERE user_id = ?", [$user['id']]);
                
                // Update user address if empty
                if (empty($user['address'])) {
                    db()->query("UPDATE users SET address = ?, city = ?, country = ?, zip_code = ?, phone = ? WHERE id = ?",
                               [$shippingAddress, $shippingCity, $shippingCountry, $shippingZip, $shippingPhone, $user['id']]);
                }
                
                // Log activity
                logActivity('order_placed', 'Order #' . $orderNumber . ' placed');
                
                // Redirect to success page
                $_SESSION['order_success'] = [
                    'order_number' => $orderNumber,
                    'total' => $grandTotal
                ];
                redirect(SITE_URL . '/order-success.php');
                
            } catch (Exception $e) {
                $error = 'Failed to process order. Please try again.';
                error_log("Checkout error: " . $e->getMessage());
            }
        }
    }
}

$pageTitle = 'Checkout';
require_once 'includes/header.php';
?>

<section class="py-12">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-gray-500 mb-8">
            <a href="<?php echo SITE_URL; ?>" class="hover:text-primary-500 transition">Home</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="<?php echo SITE_URL; ?>/cart.php" class="hover:text-primary-500 transition">Cart</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-800 dark:text-white font-medium">Checkout</span>
        </nav>

        <h1 class="text-3xl font-display font-bold text-gray-800 dark:text-white mb-8">Checkout</h1>

        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl flex items-center gap-3 text-red-600 dark:text-red-400">
            <i class="fas fa-exclamation-circle text-xl"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>

        <!-- Checkout Steps -->
        <div class="flex items-center justify-center mb-12">
            <div class="flex items-center">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">
                        <i class="fas fa-check"></i>
                    </div>
                    <span class="ml-2 font-medium text-gray-800 dark:text-white">Cart</span>
                </div>
                <div class="w-20 h-1 bg-primary-500 mx-4"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-500 text-white flex items-center justify-center font-bold">2</div>
                    <span class="ml-2 font-medium text-gray-800 dark:text-white">Checkout</span>
                </div>
                <div class="w-20 h-1 bg-gray-300 dark:bg-gray-600 mx-4"></div>
                <div class="flex items-center opacity-50">
                    <div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-400 flex items-center justify-center font-bold">3</div>
                    <span class="ml-2 font-medium text-gray-600 dark:text-gray-400">Complete</span>
                </div>
            </div>
        </div>

        <form method="POST" action="" class="grid lg:grid-cols-3 gap-8">
            <?php echo csrfField(); ?>
            
            <div class="lg:col-span-2 space-y-6">
                <!-- Shipping Information -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-8">
                    <h2 class="text-xl font-display font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-truck text-primary-500"></i>
                        Shipping Information
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                            <input type="text" 
                                   name="shipping_name" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_name'] ?? $user['name']); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address *</label>
                            <input type="email" 
                                   name="shipping_email" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_email'] ?? $user['email']); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                            <input type="tel" 
                                   name="shipping_phone" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_phone'] ?? $user['phone']); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country *</label>
                            <select name="shipping_country" 
                                    required
                                    class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                                <option value="">Select Country</option>
                                <option value="USA" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'USA' ? 'selected' : ''; ?>>United States</option>
                                <option value="UK" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="Canada" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'Canada' ? 'selected' : ''; ?>>Canada</option>
                                <option value="Australia" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'Australia' ? 'selected' : ''; ?>>Australia</option>
                                <option value="Germany" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'Germany' ? 'selected' : ''; ?>>Germany</option>
                                <option value="France" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'France' ? 'selected' : ''; ?>>France</option>
                                <option value="Other" <?php echo ($_POST['shipping_country'] ?? $user['country']) === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Street Address *</label>
                            <input type="text" 
                                   name="shipping_address" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_address'] ?? $user['address']); ?>"
                                   required
                                   placeholder="House number and street name"
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">City *</label>
                            <input type="text" 
                                   name="shipping_city" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_city'] ?? $user['city']); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ZIP / Postal Code *</label>
                            <input type="text" 
                                   name="shipping_zip" 
                                   value="<?php echo htmlspecialchars($_POST['shipping_zip'] ?? $user['zip_code']); ?>"
                                   required
                                   class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-8">
                    <h2 class="text-xl font-display font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-credit-card text-primary-500"></i>
                        Payment Method
                    </h2>
                    
                    <div class="space-y-4">
                        <label class="flex items-center gap-4 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-500 transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-900/20">
                            <input type="radio" name="payment_method" value="cod" checked class="w-5 h-5 text-primary-500">
                            <div class="flex-1">
                                <span class="font-medium text-gray-800 dark:text-white">Cash on Delivery</span>
                                <p class="text-sm text-gray-500">Pay when you receive your order</p>
                            </div>
                            <i class="fas fa-money-bill-wave text-2xl text-green-500"></i>
                        </label>
                        
                        <label class="flex items-center gap-4 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-500 transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-900/20">
                            <input type="radio" name="payment_method" value="card" class="w-5 h-5 text-primary-500">
                            <div class="flex-1">
                                <span class="font-medium text-gray-800 dark:text-white">Credit/Debit Card</span>
                                <p class="text-sm text-gray-500">Pay securely with your card</p>
                            </div>
                            <div class="flex gap-2">
                                <i class="fab fa-cc-visa text-2xl text-blue-600"></i>
                                <i class="fab fa-cc-mastercard text-2xl text-red-500"></i>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-4 p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer hover:border-primary-500 transition has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 dark:has-[:checked]:bg-primary-900/20">
                            <input type="radio" name="payment_method" value="paypal" class="w-5 h-5 text-primary-500">
                            <div class="flex-1">
                                <span class="font-medium text-gray-800 dark:text-white">PayPal</span>
                                <p class="text-sm text-gray-500">Pay with your PayPal account</p>
                            </div>
                            <i class="fab fa-paypal text-2xl text-blue-700"></i>
                        </label>
                    </div>
                </div>

                <!-- Order Notes -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-8">
                    <h2 class="text-xl font-display font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-primary-500"></i>
                        Order Notes (Optional)
                    </h2>
                    <textarea name="notes" 
                              rows="4" 
                              placeholder="Special instructions for delivery..."
                              class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-primary-500 outline-none transition dark:bg-gray-700 dark:text-white resize-none"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-lg p-6 sticky top-24">
                    <h2 class="text-xl font-display font-bold text-gray-800 dark:text-white mb-6">Order Summary</h2>
                    
                    <!-- Order Items -->
                    <div class="max-h-64 overflow-y-auto space-y-4 mb-6">
                        <?php foreach ($cartItems as $item): 
                            $itemPrice = $item['sale_price'] ?? $item['price'];
                        ?>
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <img src="<?php echo imageUrl($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     class="w-16 h-16 object-cover rounded-lg">
                                <span class="absolute -top-2 -right-2 w-6 h-6 bg-primary-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                                    <?php echo $item['quantity']; ?>
                                </span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-white line-clamp-1"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <p class="text-sm text-gray-500"><?php echo formatPrice($itemPrice); ?> × <?php echo $item['quantity']; ?></p>
                            </div>
                            <span class="font-bold text-gray-800 dark:text-white"><?php echo formatPrice($itemPrice * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
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
                            <span>Tax</span>
                            <span class="font-medium text-gray-800 dark:text-white"><?php echo formatPrice($tax); ?></span>
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="border-t border-gray-100 dark:border-gray-700 mt-6 pt-6">
                        <div class="flex justify-between items-center mb-6">
                            <span class="text-lg font-bold text-gray-800 dark:text-white">Total</span>
                            <span class="text-2xl font-display font-bold text-primary-600"><?php echo formatPrice($grandTotal); ?></span>
                        </div>
                        
                        <button type="submit" 
                                class="w-full py-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-xl font-bold text-lg shadow-lg shadow-primary-500/30 transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i>
                            Place Order
                        </button>
                        
                        <p class="text-xs text-gray-500 text-center mt-4">
                            By placing your order, you agree to our 
                            <a href="<?php echo SITE_URL; ?>/terms.php" class="text-primary-500 hover:underline">Terms</a> 
                            and 
                            <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-primary-500 hover:underline">Privacy Policy</a>
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
