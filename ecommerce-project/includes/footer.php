    </main>
    <!-- End Main Content -->

    <!-- Quick View Modal -->
    <div id="quickViewModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" id="quickViewOverlay"></div>
        <div class="absolute inset-4 md:inset-10 lg:inset-20 flex items-center justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-4xl w-full max-h-full overflow-y-auto transform scale-95 opacity-0 transition-all duration-300" id="quickViewContent">
                <!-- Content loaded via AJAX -->
                <div class="p-8 text-center">
                    <div class="animate-spin w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full mx-auto"></div>
                    <p class="mt-4 text-gray-500">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar -->
    <div id="cartSidebar" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="cartOverlay"></div>
        <div class="absolute right-0 top-0 bottom-0 w-96 max-w-[90vw] bg-white dark:bg-gray-800 shadow-2xl transform translate-x-full transition-transform duration-300" id="cartContent">
            <div class="flex flex-col h-full">
                <div class="p-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="font-display font-bold text-lg">Your Cart</span>
                        <span class="bg-white/20 px-2 py-0.5 rounded-full text-sm" id="cartSidebarCount">0</span>
                    </div>
                    <button id="closeCartSidebar" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-4" id="cartItemsContainer">
                    <!-- Cart items loaded via AJAX -->
                </div>
                
                <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                        <span class="text-xl font-bold text-gray-800 dark:text-white" id="cartSidebarTotal">$0.00</span>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="block w-full py-3 text-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-xl font-medium text-gray-800 dark:text-white transition mb-2">
                        View Cart
                    </a>
                    <a href="<?php echo SITE_URL; ?>/checkout.php" class="block w-full py-3 text-center bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 rounded-xl font-medium text-white shadow-lg shadow-primary-500/25 transition">
                        Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-20">
        <!-- Newsletter Section -->
        <div class="bg-gradient-to-r from-primary-600 to-secondary-500 py-12">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="text-center md:text-left">
                        <h3 class="text-2xl font-display font-bold text-white mb-2">Subscribe to Our Newsletter</h3>
                        <p class="text-primary-100">Get the latest deals and exclusive offers delivered to your inbox</p>
                    </div>
                    <form class="flex w-full md:w-auto gap-2" id="newsletterForm">
                        <input type="email" name="email" placeholder="Enter your email" required
                               class="flex-1 md:w-80 px-6 py-3 rounded-full bg-white/10 border border-white/20 text-white placeholder-white/60 focus:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 transition">
                        <button type="submit" class="px-8 py-3 bg-white text-primary-600 rounded-full font-bold hover:bg-gray-100 transition shadow-lg">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Footer -->
        <div class="container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <!-- About -->
                <div>
                    <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-shopping-bag text-white text-lg"></i>
                        </div>
                        <span class="text-2xl font-display font-bold text-white"><?php echo $siteName; ?></span>
                    </a>
                    <p class="text-gray-400 mb-6 leading-relaxed">
                        <?php echo getSetting('site_tagline', 'Your One-Stop Shopping Destination. We provide the best products at the most affordable prices with fast shipping worldwide.'); ?>
                    </p>
                    <div class="flex gap-4">
                        <a href="<?php echo getSetting('facebook_url', '#'); ?>" class="w-10 h-10 bg-gray-800 hover:bg-primary-500 rounded-full flex items-center justify-center transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="<?php echo getSetting('twitter_url', '#'); ?>" class="w-10 h-10 bg-gray-800 hover:bg-primary-500 rounded-full flex items-center justify-center transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="<?php echo getSetting('instagram_url', '#'); ?>" class="w-10 h-10 bg-gray-800 hover:bg-primary-500 rounded-full flex items-center justify-center transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="<?php echo getSetting('youtube_url', '#'); ?>" class="w-10 h-10 bg-gray-800 hover:bg-primary-500 rounded-full flex items-center justify-center transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-display font-bold text-white mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="<?php echo SITE_URL; ?>" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/products.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Contact</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faq.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> FAQ</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h4 class="text-lg font-display font-bold text-white mb-6">Customer Service</h4>
                    <ul class="space-y-3">
                        <li><a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> My Account</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/user/orders.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Order Tracking</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/wishlist.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Wishlist</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/returns.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Returns & Refunds</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/privacy.php" class="hover:text-primary-400 transition flex items-center gap-2"><i class="fas fa-chevron-right text-xs text-primary-500"></i> Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-display font-bold text-white mb-6">Contact Us</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-primary-500"></i>
                            </div>
                            <span class="text-gray-400"><?php echo getSetting('site_address', '123 Commerce Street, New York, NY 10001'); ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-primary-500"></i>
                            </div>
                            <a href="tel:<?php echo getSetting('site_phone'); ?>" class="hover:text-primary-400 transition"><?php echo getSetting('site_phone', '+1 (555) 123-4567'); ?></a>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-primary-500"></i>
                            </div>
                            <a href="mailto:<?php echo getSetting('site_email'); ?>" class="hover:text-primary-400 transition"><?php echo getSetting('site_email', 'contact@eshop.com'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment Methods & Trust -->
        <div class="border-t border-gray-800 py-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <span class="text-gray-500 text-sm">We Accept:</span>
                        <div class="flex items-center gap-3">
                            <i class="fab fa-cc-visa text-3xl text-gray-400 hover:text-blue-500 transition"></i>
                            <i class="fab fa-cc-mastercard text-3xl text-gray-400 hover:text-red-500 transition"></i>
                            <i class="fab fa-cc-amex text-3xl text-gray-400 hover:text-blue-400 transition"></i>
                            <i class="fab fa-cc-paypal text-3xl text-gray-400 hover:text-blue-600 transition"></i>
                            <i class="fab fa-cc-apple-pay text-3xl text-gray-400 hover:text-white transition"></i>
                            <i class="fab fa-google-pay text-3xl text-gray-400 hover:text-white transition"></i>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 text-gray-500 text-sm">
                        <span class="flex items-center gap-2"><i class="fas fa-shield-alt text-green-500"></i> Secure Checkout</span>
                        <span class="flex items-center gap-2"><i class="fas fa-lock text-green-500"></i> SSL Encrypted</span>
                        <span class="flex items-center gap-2"><i class="fas fa-undo text-green-500"></i> Easy Returns</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-800 py-6">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-gray-500 text-sm">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo $siteName; ?>. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <a href="<?php echo SITE_URL; ?>/terms.php" class="hover:text-primary-400 transition">Terms of Service</a>
                        <a href="<?php echo SITE_URL; ?>/privacy.php" class="hover:text-primary-400 transition">Privacy Policy</a>
                        <a href="<?php echo SITE_URL; ?>/cookies.php" class="hover:text-primary-400 transition">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="fixed bottom-6 right-6 w-12 h-12 bg-primary-500 hover:bg-primary-600 text-white rounded-full shadow-lg shadow-primary-500/30 flex items-center justify-center opacity-0 invisible translate-y-4 transition-all duration-300 z-50">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

    <script>
        // Pass PHP variables to JavaScript
        window.SITE_URL = '<?php echo SITE_URL; ?>';
        window.isLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
</body>
</html>
