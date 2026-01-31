<?php
/**
 * 404 Error Page
 * Modern E-Commerce Platform
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Page Not Found';
include 'includes/header.php';
?>

<!-- 404 Error Section -->
<section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 py-20 px-4">
    <div class="text-center max-w-2xl mx-auto">
        <!-- Animated 404 -->
        <div class="relative mb-8">
            <h1 class="text-[180px] font-black text-gray-200 leading-none select-none" id="error-number">
                404
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-8 py-4 rounded-2xl shadow-xl transform -rotate-6 error-badge">
                    <i class="fas fa-exclamation-triangle text-2xl mr-2"></i>
                    <span class="font-bold text-xl">Page Not Found</span>
                </div>
            </div>
        </div>
        
        <!-- Message -->
        <h2 class="text-3xl font-bold text-gray-800 mb-4 fade-up">
            Oops! This page doesn't exist
        </h2>
        <p class="text-gray-600 text-lg mb-8 max-w-md mx-auto fade-up" style="animation-delay: 0.1s;">
            The page you're looking for might have been moved, deleted, or perhaps never existed.
        </p>
        
        <!-- Search Box -->
        <div class="max-w-md mx-auto mb-8 fade-up" style="animation-delay: 0.2s;">
            <form action="products.php" method="GET" class="relative">
                <input type="text" name="search" 
                       placeholder="Search for products..." 
                       class="w-full px-6 py-4 rounded-xl border-2 border-gray-200 focus:border-primary-500 focus:ring-4 focus:ring-primary-100 transition-all text-lg">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 bg-primary-500 text-white px-6 py-2 rounded-lg hover:bg-primary-600 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap justify-center gap-4 fade-up" style="animation-delay: 0.3s;">
            <a href="<?php echo SITE_URL; ?>" 
               class="inline-flex items-center gap-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg hover:scale-105 transition-all">
                <i class="fas fa-home"></i>
                Go Home
            </a>
            <a href="<?php echo SITE_URL; ?>/products.php" 
               class="inline-flex items-center gap-2 bg-white text-gray-700 px-8 py-3 rounded-xl font-semibold border-2 border-gray-200 hover:border-primary-500 hover:text-primary-500 transition-all">
                <i class="fas fa-shopping-bag"></i>
                Browse Products
            </a>
            <button onclick="history.back()" 
                    class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </button>
        </div>
        
        <!-- Popular Links -->
        <div class="mt-12 pt-8 border-t border-gray-200 fade-up" style="animation-delay: 0.4s;">
            <p class="text-gray-500 text-sm mb-4">Popular destinations:</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="<?php echo SITE_URL; ?>/products.php?category=1" class="px-4 py-2 bg-white rounded-lg text-gray-600 hover:text-primary-500 hover:bg-primary-50 transition-all text-sm">
                    Electronics
                </a>
                <a href="<?php echo SITE_URL; ?>/products.php?category=2" class="px-4 py-2 bg-white rounded-lg text-gray-600 hover:text-primary-500 hover:bg-primary-50 transition-all text-sm">
                    Fashion
                </a>
                <a href="<?php echo SITE_URL; ?>/products.php?sort=newest" class="px-4 py-2 bg-white rounded-lg text-gray-600 hover:text-primary-500 hover:bg-primary-50 transition-all text-sm">
                    New Arrivals
                </a>
                <a href="<?php echo SITE_URL; ?>/cart.php" class="px-4 py-2 bg-white rounded-lg text-gray-600 hover:text-primary-500 hover:bg-primary-50 transition-all text-sm">
                    Shopping Cart
                </a>
                <a href="<?php echo SITE_URL; ?>/login.php" class="px-4 py-2 bg-white rounded-lg text-gray-600 hover:text-primary-500 hover:bg-primary-50 transition-all text-sm">
                    Login
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(-6deg); }
        50% { transform: translateY(-10px) rotate(-6deg); }
    }
    
    .error-badge {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-up {
        animation: fadeUp 0.6s ease-out forwards;
        opacity: 0;
    }
    
    #error-number {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<script>
// GSAP animations
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gsap !== 'undefined') {
        gsap.from('#error-number', {
            duration: 1,
            scale: 0.5,
            opacity: 0,
            ease: 'back.out(1.7)'
        });
        
        gsap.from('.error-badge', {
            duration: 0.8,
            scale: 0,
            rotation: 15,
            delay: 0.3,
            ease: 'elastic.out(1, 0.5)'
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
