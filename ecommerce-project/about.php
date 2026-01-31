<?php
/**
 * About Page
 * Modern E-Commerce Platform
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'About Us';
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 py-24 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.4\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>
    </div>
    <div class="container mx-auto px-4 relative">
        <div class="max-w-4xl mx-auto text-center text-white">
            <h1 class="text-4xl md:text-6xl font-bold mb-6" data-aos="fade-down">
                Our Story
            </h1>
            <p class="text-xl md:text-2xl opacity-90 leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                We believe shopping should be a joy, not a chore. That's why we've built a platform that puts you first.
            </p>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-semibold mb-6">
                        Our Mission
                    </span>
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">
                        Making Quality Accessible to Everyone
                    </h2>
                    <p class="text-lg text-gray-600 leading-relaxed mb-6">
                        Founded in 2024, ShopModern started with a simple idea: everyone deserves access to high-quality products at fair prices. We've partnered with hundreds of brands and artisans worldwide to bring you a curated selection of products that combine quality, style, and value.
                    </p>
                    <p class="text-lg text-gray-600 leading-relaxed">
                        Today, we serve millions of customers around the globe, but our mission remains the same – to make shopping enjoyable, sustainable, and accessible to all.
                    </p>
                </div>
                <div class="relative" data-aos="fade-left">
                    <div class="aspect-square bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl overflow-hidden shadow-2xl">
                        <div class="absolute inset-0 flex items-center justify-center text-white">
                            <div class="text-center">
                                <i class="fas fa-shopping-bag text-8xl mb-4 opacity-50"></i>
                                <p class="text-2xl font-semibold">Quality First</p>
                            </div>
                        </div>
                    </div>
                    <!-- Floating Stats -->
                    <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl p-6 shadow-xl" data-aos="zoom-in" data-aos-delay="200">
                        <div class="text-4xl font-bold text-primary-500">10M+</div>
                        <div class="text-gray-600">Happy Customers</div>
                    </div>
                    <div class="absolute -top-6 -right-6 bg-white rounded-2xl p-6 shadow-xl" data-aos="zoom-in" data-aos-delay="300">
                        <div class="text-4xl font-bold text-green-500">50K+</div>
                        <div class="text-gray-600">Products</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-semibold mb-4">
                Our Values
            </span>
            <h2 class="text-4xl font-bold text-gray-800">What We Stand For</h2>
        </div>
        
        <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Value 1 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow text-center" data-aos="fade-up" data-aos-delay="0">
                <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-medal text-primary-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Quality</h3>
                <p class="text-gray-600">We carefully vet every product and partner to ensure you receive only the best.</p>
            </div>
            
            <!-- Value 2 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow text-center" data-aos="fade-up" data-aos-delay="100">
                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-leaf text-green-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Sustainability</h3>
                <p class="text-gray-600">We're committed to reducing our environmental footprint through eco-friendly practices.</p>
            </div>
            
            <!-- Value 3 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow text-center" data-aos="fade-up" data-aos-delay="200">
                <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-handshake text-blue-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Trust</h3>
                <p class="text-gray-600">Transparency and honesty are at the core of everything we do.</p>
            </div>
            
            <!-- Value 4 -->
            <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow text-center" data-aos="fade-up" data-aos-delay="300">
                <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-heart text-purple-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Customer First</h3>
                <p class="text-gray-600">Your satisfaction is our priority. We go above and beyond to serve you.</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-semibold mb-4">
                Our Team
            </span>
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Meet the People Behind ShopModern</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Passionate individuals dedicated to making your shopping experience exceptional.</p>
        </div>
        
        <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Team Member 1 -->
            <div class="text-center group" data-aos="fade-up" data-aos-delay="0">
                <div class="relative mb-6 mx-auto w-48 h-48 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 p-1 group-hover:scale-105 transition-transform">
                    <div class="w-full h-full rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                        <i class="fas fa-user text-6xl text-gray-400"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Sarah Johnson</h3>
                <p class="text-primary-500 font-medium mb-3">CEO & Founder</p>
                <p class="text-gray-600 text-sm mb-4">Visionary leader with 15+ years in e-commerce innovation.</p>
                <div class="flex justify-center gap-3">
                    <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <!-- Team Member 2 -->
            <div class="text-center group" data-aos="fade-up" data-aos-delay="100">
                <div class="relative mb-6 mx-auto w-48 h-48 rounded-full bg-gradient-to-br from-green-400 to-green-600 p-1 group-hover:scale-105 transition-transform">
                    <div class="w-full h-full rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                        <i class="fas fa-user text-6xl text-gray-400"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Michael Chen</h3>
                <p class="text-green-500 font-medium mb-3">CTO</p>
                <p class="text-gray-600 text-sm mb-4">Tech enthusiast building the future of online shopping.</p>
                <div class="flex justify-center gap-3">
                    <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <!-- Team Member 3 -->
            <div class="text-center group" data-aos="fade-up" data-aos-delay="200">
                <div class="relative mb-6 mx-auto w-48 h-48 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 p-1 group-hover:scale-105 transition-transform">
                    <div class="w-full h-full rounded-full bg-gray-200 flex items-center justify-center overflow-hidden">
                        <i class="fas fa-user text-6xl text-gray-400"></i>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">Emily Roberts</h3>
                <p class="text-purple-500 font-medium mb-3">Head of Customer Success</p>
                <p class="text-gray-600 text-sm mb-4">Ensuring every customer has an amazing experience.</p>
                <div class="flex justify-center gap-3">
                    <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-primary-500 transition-colors"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-20 bg-gradient-to-br from-primary-600 to-primary-800 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div data-aos="zoom-in" data-aos-delay="0">
                <div class="text-5xl font-bold mb-2 counter" data-target="10000000">10M+</div>
                <div class="text-primary-200">Happy Customers</div>
            </div>
            <div data-aos="zoom-in" data-aos-delay="100">
                <div class="text-5xl font-bold mb-2">50K+</div>
                <div class="text-primary-200">Products</div>
            </div>
            <div data-aos="zoom-in" data-aos-delay="200">
                <div class="text-5xl font-bold mb-2">100+</div>
                <div class="text-primary-200">Countries</div>
            </div>
            <div data-aos="zoom-in" data-aos-delay="300">
                <div class="text-5xl font-bold mb-2">4.9</div>
                <div class="text-primary-200">Customer Rating</div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
            <h2 class="text-4xl font-bold text-gray-800 mb-6">Ready to Start Shopping?</h2>
            <p class="text-xl text-gray-600 mb-8">Join millions of happy customers and discover amazing products today.</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo SITE_URL; ?>/products.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white px-8 py-4 rounded-xl font-semibold hover:shadow-lg hover:shadow-primary-500/30 transition-all">
                    <i class="fas fa-shopping-bag"></i>
                    Shop Now
                </a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="inline-flex items-center gap-2 bg-white text-gray-700 px-8 py-4 rounded-xl font-semibold border-2 border-gray-200 hover:border-primary-500 hover:text-primary-500 transition-all">
                    <i class="fas fa-envelope"></i>
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
