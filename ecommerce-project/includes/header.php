<?php
/**
 * Header Component
 * Modern E-Commerce Platform
 */

require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$wishlistCount = getWishlistCount();
$categories = getCategoryWithChildren();
$siteName = getSetting('site_name', 'E-Shop');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo getSetting('site_tagline', 'Your One-Stop Shopping Destination'); ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . $siteName : $siteName; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                        'display': ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        secondary: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Preloader -->
    <div id="preloader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white dark:bg-gray-900">
        <div class="loader">
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <span class="loader-text font-display text-primary-600 dark:text-primary-400">E-Shop</span>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="bg-gradient-to-r from-primary-600 via-primary-500 to-secondary-500 text-white text-sm py-2">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="tel:<?php echo getSetting('site_phone'); ?>" class="flex items-center gap-1 hover:text-primary-100 transition">
                        <i class="fas fa-phone text-xs"></i>
                        <span class="hidden sm:inline"><?php echo getSetting('site_phone', '+1 (555) 123-4567'); ?></span>
                    </a>
                    <a href="mailto:<?php echo getSetting('site_email'); ?>" class="flex items-center gap-1 hover:text-primary-100 transition">
                        <i class="fas fa-envelope text-xs"></i>
                        <span class="hidden sm:inline"><?php echo getSetting('site_email', 'contact@eshop.com'); ?></span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="hidden md:flex items-center gap-1">
                        <i class="fas fa-truck"></i>
                        Free shipping on orders over $<?php echo getSetting('free_shipping_min', '99'); ?>
                    </span>
                    <div class="flex items-center gap-3">
                        <a href="<?php echo getSetting('facebook_url', '#'); ?>" class="hover:text-primary-100 transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo getSetting('twitter_url', '#'); ?>" class="hover:text-primary-100 transition"><i class="fab fa-twitter"></i></a>
                        <a href="<?php echo getSetting('instagram_url', '#'); ?>" class="hover:text-primary-100 transition"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="sticky top-0 z-50 bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-lg transition-all duration-300" id="mainHeader">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <!-- Logo -->
                <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-xl flex items-center justify-center transform group-hover:rotate-12 transition-transform duration-300">
                        <i class="fas fa-shopping-bag text-white text-lg"></i>
                    </div>
                    <span class="text-2xl font-display font-bold bg-gradient-to-r from-primary-600 to-secondary-500 bg-clip-text text-transparent">
                        <?php echo $siteName; ?>
                    </span>
                </a>

                <!-- Search Bar -->
                <div class="hidden lg:flex flex-1 max-w-xl mx-8">
                    <div class="relative w-full group">
                        <input type="text" 
                               id="searchInput"
                               placeholder="Search for products, categories, brands..." 
                               class="w-full pl-12 pr-4 py-3 rounded-full border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 transition-all duration-300 outline-none">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition"></i>
                        <button class="absolute right-2 top-1/2 -translate-y-1/2 bg-primary-500 hover:bg-primary-600 text-white px-4 py-1.5 rounded-full text-sm font-medium transition">
                            Search
                        </button>
                        <!-- Search Results Dropdown -->
                        <div id="searchResults" class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 hidden max-h-96 overflow-y-auto">
                            <!-- Results will be populated via JS -->
                        </div>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Toggle Dark Mode">
                        <i class="fas fa-moon text-gray-600 dark:text-yellow-400 text-xl dark:hidden"></i>
                        <i class="fas fa-sun text-yellow-400 text-xl hidden dark:block"></i>
                    </button>

                    <!-- Wishlist -->
                    <a href="<?php echo SITE_URL; ?>/wishlist.php" class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition group" title="Wishlist">
                        <i class="fas fa-heart text-gray-600 dark:text-gray-300 text-xl group-hover:text-red-500 transition"></i>
                        <?php if ($wishlistCount > 0): ?>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium">
                            <?php echo $wishlistCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>

                    <!-- Cart -->
                    <a href="<?php echo SITE_URL; ?>/cart.php" class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition group" id="cartBtn" title="Cart">
                        <i class="fas fa-shopping-cart text-gray-600 dark:text-gray-300 text-xl group-hover:text-primary-500 transition"></i>
                        <span id="cartBadge" class="absolute -top-1 -right-1 w-5 h-5 bg-primary-500 text-white text-xs rounded-full flex items-center justify-center font-medium <?php echo $cartCount == 0 ? 'hidden' : ''; ?>">
                            <?php echo $cartCount; ?>
                        </span>
                    </a>

                    <!-- User Menu -->
                    <?php if (isLoggedIn()): ?>
                    <div class="relative" id="userMenuContainer">
                        <button id="userMenuBtn" class="flex items-center gap-2 p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-medium text-sm">
                                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                                </span>
                            </div>
                            <span class="hidden md:block text-gray-700 dark:text-gray-300 font-medium">
                                <?php echo $_SESSION['user_name'] ?? 'User'; ?>
                            </span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div id="userMenu" class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 hidden transform opacity-0 scale-95 transition-all duration-200">
                            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                                <p class="font-medium text-gray-800 dark:text-white"><?php echo $_SESSION['user_name'] ?? 'User'; ?></p>
                                <p class="text-sm text-gray-500"><?php echo $_SESSION['user_email'] ?? ''; ?></p>
                            </div>
                            <div class="py-2">
                                <?php if (isAdmin()): ?>
                                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-tachometer-alt text-primary-500 w-5"></i>
                                    <span class="text-gray-700 dark:text-gray-300">Admin Panel</span>
                                </a>
                                <?php endif; ?>
                                <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-user text-primary-500 w-5"></i>
                                    <span class="text-gray-700 dark:text-gray-300">My Account</span>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/user/orders.php" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-box text-primary-500 w-5"></i>
                                    <span class="text-gray-700 dark:text-gray-300">My Orders</span>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/wishlist.php" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-heart text-primary-500 w-5"></i>
                                    <span class="text-gray-700 dark:text-gray-300">Wishlist</span>
                                </a>
                            </div>
                            <div class="border-t border-gray-100 dark:border-gray-700 py-2">
                                <a href="<?php echo SITE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 transition text-red-600">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="flex items-center gap-2">
                        <a href="<?php echo SITE_URL; ?>/login.php" class="hidden sm:flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-primary-500 transition font-medium">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="px-5 py-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-full font-medium shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 transition-all duration-300">
                            Register
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Mobile Menu Toggle -->
                    <button id="mobileMenuBtn" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <i class="fas fa-bars text-gray-600 dark:text-gray-300 text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Category Navigation -->
            <nav class="hidden lg:flex items-center gap-6 py-3 border-t border-gray-100 dark:border-gray-700">
                <div class="relative group" id="categoryDropdown">
                    <button class="flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition">
                        <i class="fas fa-th-large"></i>
                        <span>All Categories</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <!-- Categories Mega Menu -->
                    <div class="absolute left-0 top-full pt-2 w-64 hidden group-hover:block" id="categoryMenu">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <?php foreach ($categories as $category): ?>
                            <div class="relative group/item">
                                <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>" 
                                   class="flex items-center justify-between px-4 py-3 hover:bg-primary-50 dark:hover:bg-gray-700 transition">
                                    <span class="flex items-center gap-3">
                                        <i class="fas <?php echo $category['icon'] ?? 'fa-folder'; ?> text-primary-500 w-5"></i>
                                        <span class="text-gray-700 dark:text-gray-300"><?php echo $category['name']; ?></span>
                                    </span>
                                    <?php if (!empty($category['children'])): ?>
                                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                                    <?php endif; ?>
                                </a>
                                <?php if (!empty($category['children'])): ?>
                                <!-- Subcategories -->
                                <div class="absolute left-full top-0 w-56 hidden group-hover/item:block">
                                    <div class="ml-2 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-2">
                                        <?php foreach ($category['children'] as $child): ?>
                                        <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $child['slug']; ?>" 
                                           class="block px-4 py-2 hover:bg-primary-50 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition">
                                            <?php echo $child['name']; ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <a href="<?php echo SITE_URL; ?>" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 font-medium transition">Home</a>
                <a href="<?php echo SITE_URL; ?>/products.php" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 font-medium transition">Shop</a>
                <a href="<?php echo SITE_URL; ?>/products.php?featured=1" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 font-medium transition">Featured</a>
                <a href="<?php echo SITE_URL; ?>/products.php?sale=1" class="flex items-center gap-1 text-red-500 hover:text-red-600 font-medium transition">
                    <i class="fas fa-fire"></i> Deals
                </a>
                <a href="<?php echo SITE_URL; ?>/products.php?new=1" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 font-medium transition">New Arrivals</a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="text-gray-600 dark:text-gray-300 hover:text-primary-500 font-medium transition">Contact</a>
            </nav>
        </div>
    </header>

    <!-- Mobile Menu Sidebar -->
    <div id="mobileMenu" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="mobileMenuOverlay"></div>
        <div class="absolute left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white dark:bg-gray-800 shadow-2xl transform -translate-x-full transition-transform duration-300" id="mobileMenuContent">
            <div class="p-4 bg-gradient-to-r from-primary-500 to-secondary-500 text-white">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-display font-bold"><?php echo $siteName; ?></span>
                    <button id="closeMobileMenu" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Search -->
            <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                <div class="relative">
                    <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <nav class="py-2 overflow-y-auto max-h-[calc(100vh-200px)]">
                <a href="<?php echo SITE_URL; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-home text-primary-500 w-6"></i>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Home</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/products.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-store text-primary-500 w-6"></i>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Shop</span>
                </a>
                
                <!-- Mobile Categories -->
                <div class="border-t border-gray-100 dark:border-gray-700 mt-2 pt-2">
                    <p class="px-4 py-2 text-sm text-gray-500 font-medium uppercase">Categories</p>
                    <?php foreach ($categories as $category): ?>
                    <a href="<?php echo SITE_URL; ?>/category.php?slug=<?php echo $category['slug']; ?>" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas <?php echo $category['icon'] ?? 'fa-folder'; ?> text-primary-500 w-6"></i>
                        <span class="text-gray-700 dark:text-gray-300"><?php echo $category['name']; ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Mobile User Links -->
                <div class="border-t border-gray-100 dark:border-gray-700 mt-2 pt-2">
                    <?php if (isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-user text-primary-500 w-6"></i>
                        <span class="text-gray-700 dark:text-gray-300">My Account</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="flex items-center gap-3 px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600">
                        <i class="fas fa-sign-out-alt w-6"></i>
                        <span>Logout</span>
                    </a>
                    <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-sign-in-alt text-primary-500 w-6"></i>
                        <span class="text-gray-700 dark:text-gray-300">Login</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/register.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-user-plus text-primary-500 w-6"></i>
                        <span class="text-gray-700 dark:text-gray-300">Register</span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = getFlash()): ?>
    <div id="flashMessage" class="fixed top-24 right-4 z-50 max-w-md animate-slide-in">
        <div class="flex items-center gap-3 px-6 py-4 rounded-xl shadow-lg
            <?php echo $flash['type'] === 'success' ? 'bg-green-500' : ($flash['type'] === 'error' ? 'bg-red-500' : 'bg-yellow-500'); ?> text-white">
            <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : ($flash['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle'); ?> text-xl"></i>
            <p class="font-medium"><?php echo $flash['message']; ?></p>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto p-1 hover:bg-white/20 rounded-lg transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const flash = document.getElementById('flashMessage');
            if (flash) flash.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <!-- Main Content Wrapper -->
    <main id="mainContent">
