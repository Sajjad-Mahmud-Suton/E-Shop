<?php
/**
 * Homepage
 * Modern E-Commerce Platform
 */

$pageTitle = 'Home';
require_once 'includes/header.php';

// Fetch data
$sliders = getSliders();
$featuredProducts = getFeaturedProducts(8);
$bestsellerProducts = getBestsellerProducts(8);
$newArrivals = getNewArrivals(8);
$allCategories = getCategoryWithChildren();
?>

<!-- Hero Slider Section -->
<section class="hero-slider swiper relative">
    <div class="swiper-wrapper">
        <?php foreach ($sliders as $slide): ?>
        <div class="swiper-slide">
            <div class="slide-bg" style="background-image: linear-gradient(135deg, rgba(99,102,241,0.9), rgba(245,158,11,0.8)), url('<?php echo imageUrl($slide['image'], 'banners/default-banner.jpg'); ?>');"></div>
            <div class="slide-content container mx-auto px-4">
                <div class="max-w-2xl text-white">
                    <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur rounded-full text-sm font-medium mb-4 animate-fade-in-down">
                        <?php echo $slide['subtitle'] ?? 'Special Offer'; ?>
                    </span>
                    <h1 class="text-4xl md:text-6xl font-display font-bold mb-6 leading-tight animate-fade-in-up">
                        <?php echo $slide['title']; ?>
                    </h1>
                    <p class="text-lg md:text-xl opacity-90 mb-8 animate-fade-in-up" style="animation-delay: 0.2s;">
                        <?php echo $slide['description'] ?? 'Discover amazing products at unbeatable prices'; ?>
                    </p>
                    <?php if ($slide['button_text']): ?>
                    <a href="<?php echo $slide['button_link'] ?? '#'; ?>" 
                       class="inline-flex items-center gap-2 px-8 py-4 bg-white text-primary-600 rounded-full font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 animate-fade-in-up" style="animation-delay: 0.4s;">
                        <?php echo $slide['button_text']; ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($sliders)): ?>
        <!-- Default slide if no sliders -->
        <div class="swiper-slide">
            <div class="slide-bg" style="background: linear-gradient(135deg, #6366f1, #f59e0b);"></div>
            <div class="slide-content container mx-auto px-4">
                <div class="max-w-2xl text-white">
                    <span class="inline-block px-4 py-2 bg-white/20 backdrop-blur rounded-full text-sm font-medium mb-4">
                        Welcome to E-Shop
                    </span>
                    <h1 class="text-4xl md:text-6xl font-display font-bold mb-6 leading-tight">
                        Discover Amazing Products
                    </h1>
                    <p class="text-lg md:text-xl opacity-90 mb-8">
                        Shop the latest trends with exclusive deals and free shipping on orders over $99
                    </p>
                    <a href="<?php echo SITE_URL; ?>/products.php" 
                       class="inline-flex items-center gap-2 px-8 py-4 bg-white text-primary-600 rounded-full font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                        Shop Now
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
</section>

<!-- Features Bar -->
<section class="bg-white dark:bg-gray-800 shadow-lg relative z-10 -mt-6 mx-4 lg:mx-auto lg:max-w-6xl rounded-2xl">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6">
        <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-primary-50 dark:hover:bg-gray-700 transition">
            <div class="w-14 h-14 bg-primary-100 dark:bg-primary-900/50 rounded-xl flex items-center justify-center">
                <i class="fas fa-truck text-primary-600 dark:text-primary-400 text-2xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-800 dark:text-white">Free Shipping</h4>
                <p class="text-sm text-gray-500">On orders over $99</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-primary-50 dark:hover:bg-gray-700 transition">
            <div class="w-14 h-14 bg-green-100 dark:bg-green-900/50 rounded-xl flex items-center justify-center">
                <i class="fas fa-shield-alt text-green-600 dark:text-green-400 text-2xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-800 dark:text-white">Secure Payment</h4>
                <p class="text-sm text-gray-500">100% protected</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-primary-50 dark:hover:bg-gray-700 transition">
            <div class="w-14 h-14 bg-yellow-100 dark:bg-yellow-900/50 rounded-xl flex items-center justify-center">
                <i class="fas fa-undo text-yellow-600 dark:text-yellow-400 text-2xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-800 dark:text-white">Easy Returns</h4>
                <p class="text-sm text-gray-500">30-day return policy</p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-primary-50 dark:hover:bg-gray-700 transition">
            <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/50 rounded-xl flex items-center justify-center">
                <i class="fas fa-headset text-purple-600 dark:text-purple-400 text-2xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-gray-800 dark:text-white">24/7 Support</h4>
                <p class="text-sm text-gray-500">Dedicated support</p>
            </div>
        </div>
    </div>
</section>

<!-- Main Content with Sidebar -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Categories -->
            <aside class="lg:w-72 flex-shrink-0">
                <div class="category-sidebar sticky top-24">
                    <h3 class="text-lg font-display font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                        <i class="fas fa-th-large text-primary-500"></i>
                        Categories
                    </h3>
                    <nav class="space-y-1">
                        <?php foreach ($allCategories as $category): ?>
                        <div class="category-item-wrapper">
                            <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>" class="category-item">
                                <span class="flex items-center">
                                    <span class="icon"><i class="fas <?php echo $category['icon'] ?? 'fa-folder'; ?>"></i></span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium"><?php echo $category['name']; ?></span>
                                </span>
                                <span class="count"><?php echo $category['product_count']; ?></span>
                            </a>
                            <?php if (!empty($category['children'])): ?>
                            <div class="ml-12 mt-1 space-y-1">
                                <?php foreach ($category['children'] as $child): ?>
                                <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $child['slug']; ?>" 
                                   class="block py-2 px-3 text-sm text-gray-500 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition">
                                    <?php echo $child['name']; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </nav>
                    
                    <!-- Promo Banner -->
                    <div class="mt-8 rounded-2xl overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-primary-600 to-secondary-500"></div>
                        <div class="relative p-6 text-white">
                            <span class="text-sm font-medium opacity-90">Limited Time</span>
                            <h4 class="text-2xl font-display font-bold mt-1">Flash Sale!</h4>
                            <p class="text-sm opacity-90 mt-2">Up to 50% off on selected items</p>
                            <a href="<?php echo SITE_URL; ?>/products.php?sale=1" 
                               class="inline-block mt-4 px-6 py-2 bg-white text-primary-600 rounded-full font-bold text-sm hover:bg-gray-100 transition">
                                Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Featured Products -->
                <div class="mb-16">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-3xl font-display font-bold text-gray-800 dark:text-white">Featured Products</h2>
                            <p class="text-gray-500 mt-1">Hand-picked products just for you</p>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/products.php?featured=1" 
                           class="hidden md:flex items-center gap-2 text-primary-500 hover:text-primary-600 font-medium transition">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($featuredProducts as $product): ?>
                        <?php include 'includes/components/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($featuredProducts)): ?>
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-2xl">
                        <i class="fas fa-box-open text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500">No featured products available</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Categories Showcase -->
                <div class="mb-16 p-8 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-3xl text-white">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
                        <div>
                            <h2 class="text-3xl font-display font-bold">Shop by Category</h2>
                            <p class="opacity-90 mt-1">Find exactly what you're looking for</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <?php foreach ($allCategories as $category): ?>
                        <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>" 
                           class="group bg-white/10 backdrop-blur hover:bg-white/20 rounded-2xl p-6 text-center transition-all duration-300 hover:scale-105">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition">
                                <i class="fas <?php echo $category['icon'] ?? 'fa-folder'; ?> text-2xl"></i>
                            </div>
                            <h4 class="font-bold"><?php echo $category['name']; ?></h4>
                            <p class="text-sm opacity-75 mt-1"><?php echo $category['product_count']; ?> Products</p>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Best Sellers -->
                <div class="mb-16">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-3xl font-display font-bold text-gray-800 dark:text-white">Best Sellers</h2>
                            <p class="text-gray-500 mt-1">Most popular products this week</p>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/products.php?bestseller=1" 
                           class="hidden md:flex items-center gap-2 text-primary-500 hover:text-primary-600 font-medium transition">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($bestsellerProducts as $product): ?>
                        <?php include 'includes/components/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- New Arrivals -->
                <div class="mb-16">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-3xl font-display font-bold text-gray-800 dark:text-white">New Arrivals</h2>
                            <p class="text-gray-500 mt-1">Fresh products just added</p>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/products.php?new=1" 
                           class="hidden md:flex items-center gap-2 text-primary-500 hover:text-primary-600 font-medium transition">
                            View All <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($newArrivals as $product): ?>
                        <?php include 'includes/components/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Promotional Banners -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-2 gap-6">
            <div class="relative overflow-hidden rounded-3xl group">
                <div class="absolute inset-0 bg-gradient-to-r from-primary-600/90 to-primary-800/90"></div>
                <div class="relative p-8 md:p-12 text-white">
                    <span class="inline-block px-4 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">Electronics</span>
                    <h3 class="text-3xl font-display font-bold mb-4">Smart Home Devices</h3>
                    <p class="opacity-90 mb-6 max-w-sm">Transform your home with our latest IoT devices and smart electronics</p>
                    <a href="<?php echo SITE_URL; ?>/category.php?slug=electronics" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-600 rounded-full font-bold hover:bg-gray-100 transition">
                        Shop Now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="relative overflow-hidden rounded-3xl group">
                <div class="absolute inset-0 bg-gradient-to-r from-secondary-500/90 to-secondary-700/90"></div>
                <div class="relative p-8 md:p-12 text-white">
                    <span class="inline-block px-4 py-1 bg-white/20 rounded-full text-sm font-medium mb-4">Beauty</span>
                    <h3 class="text-3xl font-display font-bold mb-4">Beauty Essentials</h3>
                    <p class="opacity-90 mb-6 max-w-sm">Premium skincare and cosmetics for your daily beauty routine</p>
                    <a href="<?php echo SITE_URL; ?>/category.php?slug=cosmetics" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white text-secondary-600 rounded-full font-bold hover:bg-gray-100 transition">
                        Shop Now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="py-16 bg-gray-50 dark:bg-gray-800">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-display font-bold text-gray-800 dark:text-white">What Our Customers Say</h2>
            <p class="text-gray-500 mt-2">Real reviews from real customers</p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white dark:bg-gray-700 rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
                <div class="flex items-center gap-1 text-yellow-400 mb-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">"Amazing quality products and super fast shipping! The customer service team was incredibly helpful when I had questions about my order."</p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-bold">
                        JD
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white">John Doe</h4>
                        <p class="text-sm text-gray-500">Verified Buyer</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-700 rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
                <div class="flex items-center gap-1 text-yellow-400 mb-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">"I've been shopping here for months and never had a bad experience. The products are exactly as described and the prices are unbeatable!"</p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-secondary-400 to-secondary-600 rounded-full flex items-center justify-center text-white font-bold">
                        SA
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white">Sarah Anderson</h4>
                        <p class="text-sm text-gray-500">Verified Buyer</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-700 rounded-2xl p-8 shadow-lg hover:shadow-xl transition">
                <div class="flex items-center gap-1 text-yellow-400 mb-4">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">"The best online shopping experience I've had. Easy navigation, secure checkout, and my package arrived earlier than expected!"</p>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold">
                        MK
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 dark:text-white">Michael Kim</h4>
                        <p class="text-sm text-gray-500">Verified Buyer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Brands/Partners -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-display font-bold text-gray-800 dark:text-white">Trusted by Top Brands</h2>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-12 opacity-60 grayscale hover:grayscale-0 hover:opacity-100 transition-all duration-500">
            <div class="text-4xl font-display font-bold text-gray-400">Apple</div>
            <div class="text-4xl font-display font-bold text-gray-400">Samsung</div>
            <div class="text-4xl font-display font-bold text-gray-400">Sony</div>
            <div class="text-4xl font-display font-bold text-gray-400">Nike</div>
            <div class="text-4xl font-display font-bold text-gray-400">Adidas</div>
            <div class="text-4xl font-display font-bold text-gray-400">L'Oreal</div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
