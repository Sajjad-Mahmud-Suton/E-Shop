/**
 * E-Shop Main JavaScript
 * Version: 1.0.0
 * 
 * Features:
 * - Preloader
 * - Dark Mode Toggle
 * - Mobile Menu
 * - Search Functionality
 * - Cart Operations
 * - Wishlist Operations
 * - Quick View Modal
 * - GSAP Animations
 * - Swiper Carousels
 */

'use strict';

// ============================================
// Global Variables
// ============================================
const SITE_URL = window.SITE_URL || '';
let cartCount = 0;

// ============================================
// Utility Functions
// ============================================
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

const formatPrice = (price) => {
    return '$' + parseFloat(price).toFixed(2);
};

const showToast = (message, type = 'success') => {
    const toast = document.createElement('div');
    toast.className = `fixed top-24 right-4 z-50 max-w-md animate-slide-in`;
    toast.innerHTML = `
        <div class="flex items-center gap-3 px-6 py-4 rounded-xl shadow-lg
            ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500'} text-white">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle'} text-xl"></i>
            <p class="font-medium">${message}</p>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto p-1 hover:bg-white/20 rounded-lg transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 5000);
};

// ============================================
// Preloader
// ============================================
window.addEventListener('load', () => {
    const preloader = $('#preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.classList.add('hidden');
            // Initialize animations after preloader
            initGSAPAnimations();
        }, 500);
    }
});

// ============================================
// Dark Mode Toggle
// ============================================
const initDarkMode = () => {
    const toggle = $('#darkModeToggle');
    const html = document.documentElement;
    
    // Check for saved preference or system preference
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        html.classList.add('dark');
    }
    
    if (toggle) {
        toggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });
    }
};

// ============================================
// Mobile Menu
// ============================================
const initMobileMenu = () => {
    const mobileMenuBtn = $('#mobileMenuBtn');
    const mobileMenu = $('#mobileMenu');
    const mobileMenuContent = $('#mobileMenuContent');
    const closeMobileMenu = $('#closeMobileMenu');
    const mobileMenuOverlay = $('#mobileMenuOverlay');
    
    const openMenu = () => {
        mobileMenu.classList.remove('hidden');
        setTimeout(() => {
            mobileMenuContent.classList.remove('-translate-x-full');
        }, 10);
        document.body.style.overflow = 'hidden';
    };
    
    const closeMenu = () => {
        mobileMenuContent.classList.add('-translate-x-full');
        setTimeout(() => {
            mobileMenu.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    };
    
    if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMenu);
    if (closeMobileMenu) closeMobileMenu.addEventListener('click', closeMenu);
    if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMenu);
};

// ============================================
// User Menu Dropdown
// ============================================
const initUserMenu = () => {
    const userMenuBtn = $('#userMenuBtn');
    const userMenu = $('#userMenu');
    
    if (userMenuBtn && userMenu) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userMenu.classList.toggle('hidden');
            if (!userMenu.classList.contains('hidden')) {
                setTimeout(() => {
                    userMenu.classList.remove('opacity-0', 'scale-95');
                    userMenu.classList.add('opacity-100', 'scale-100');
                }, 10);
            } else {
                userMenu.classList.add('opacity-0', 'scale-95');
                userMenu.classList.remove('opacity-100', 'scale-100');
            }
        });
        
        document.addEventListener('click', (e) => {
            if (!userMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userMenu.classList.add('hidden', 'opacity-0', 'scale-95');
            }
        });
    }
};

// ============================================
// Header Scroll Effect
// ============================================
const initHeaderScroll = () => {
    const header = $('#mainHeader');
    if (!header) return;
    
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });
};

// ============================================
// Back to Top Button
// ============================================
const initBackToTop = () => {
    const btn = $('#backToTop');
    if (!btn) return;
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 500) {
            btn.classList.remove('opacity-0', 'invisible', 'translate-y-4');
            btn.classList.add('opacity-100', 'visible', 'translate-y-0');
        } else {
            btn.classList.add('opacity-0', 'invisible', 'translate-y-4');
            btn.classList.remove('opacity-100', 'visible', 'translate-y-0');
        }
    });
    
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
};

// ============================================
// Search Functionality
// ============================================
const initSearch = () => {
    const searchInput = $('#searchInput');
    const searchResults = $('#searchResults');
    
    if (!searchInput || !searchResults) return;
    
    const performSearch = debounce(async (query) => {
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }
        
        try {
            const response = await fetch(`${SITE_URL}/api/products.php?search=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.products.length > 0) {
                searchResults.innerHTML = data.products.map(product => `
                    <a href="${SITE_URL}/product.php?slug=${product.slug}" 
                       class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <img src="${product.image ? SITE_URL + '/uploads/' + product.image : SITE_URL + '/assets/images/placeholder.jpg'}" 
                             alt="${product.title}" 
                             class="w-16 h-16 object-cover rounded-lg">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800 dark:text-white">${product.title}</h4>
                            <p class="text-sm text-gray-500">${product.category_name || ''}</p>
                            <p class="font-bold text-primary-600">${formatPrice(product.sale_price || product.price)}</p>
                        </div>
                    </a>
                `).join('');
                searchResults.classList.remove('hidden');
            } else {
                searchResults.innerHTML = `
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-search text-4xl mb-2 opacity-50"></i>
                        <p>No products found for "${query}"</p>
                    </div>
                `;
                searchResults.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300);
    
    searchInput.addEventListener('input', (e) => performSearch(e.target.value));
    
    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.classList.add('hidden');
        }
    });
};

// ============================================
// Cart Operations
// ============================================
const updateCartBadge = (count) => {
    const badge = $('#cartBadge');
    const sidebarCount = $('#cartSidebarCount');
    
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    
    if (sidebarCount) {
        sidebarCount.textContent = count;
    }
};

const addToCart = async (productId, quantity = 1) => {
    try {
        const response = await fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge(data.cart_count);
            showToast('Product added to cart!', 'success');
            // Optionally open cart sidebar
            openCartSidebar();
            loadCartItems();
        } else {
            showToast(data.error || 'Failed to add to cart', 'error');
        }
    } catch (error) {
        console.error('Cart error:', error);
        showToast('An error occurred', 'error');
    }
};

const removeFromCart = async (cartItemId) => {
    try {
        const response = await fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'remove',
                cart_id: cartItemId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge(data.cart_count);
            loadCartItems();
            showToast('Item removed from cart', 'success');
        }
    } catch (error) {
        console.error('Cart error:', error);
    }
};

const updateCartQuantity = async (cartItemId, quantity) => {
    try {
        const response = await fetch(`${SITE_URL}/api/cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                cart_id: cartItemId,
                quantity: quantity
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge(data.cart_count);
            loadCartItems();
        }
    } catch (error) {
        console.error('Cart error:', error);
    }
};

const loadCartItems = async () => {
    const container = $('#cartItemsContainer');
    const totalEl = $('#cartSidebarTotal');
    
    if (!container) return;
    
    try {
        const response = await fetch(`${SITE_URL}/api/cart.php?action=get`);
        const data = await response.json();
        
        if (data.success) {
            if (data.items.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-shopping-cart text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">Your cart is empty</p>
                        <a href="${SITE_URL}/products.php" class="inline-block mt-4 text-primary-500 hover:text-primary-600 font-medium">
                            Continue Shopping
                        </a>
                    </div>
                `;
            } else {
                container.innerHTML = data.items.map(item => `
                    <div class="cart-item mb-4" data-id="${item.id}">
                        <div class="item-image">
                            <img src="${item.image ? SITE_URL + '/uploads/' + item.image : SITE_URL + '/assets/images/placeholder.jpg'}" 
                                 alt="${item.title}">
                        </div>
                        <div class="item-info">
                            <h4 class="item-title line-clamp-2">${item.title}</h4>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="quantity-selector">
                                    <button onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input type="text" value="${item.quantity}" readonly>
                                    <button onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                                <button onclick="removeFromCart(${item.id})" class="text-red-500 hover:text-red-600 ml-auto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <p class="item-price mt-2">${formatPrice(item.sale_price || item.price)}</p>
                        </div>
                    </div>
                `).join('');
            }
            
            if (totalEl) {
                totalEl.textContent = formatPrice(data.total);
            }
        }
    } catch (error) {
        console.error('Load cart error:', error);
    }
};

// Cart Sidebar
const openCartSidebar = () => {
    const sidebar = $('#cartSidebar');
    const content = $('#cartContent');
    
    if (sidebar && content) {
        sidebar.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('translate-x-full');
        }, 10);
        document.body.style.overflow = 'hidden';
        loadCartItems();
    }
};

const closeCartSidebar = () => {
    const sidebar = $('#cartSidebar');
    const content = $('#cartContent');
    
    if (sidebar && content) {
        content.classList.add('translate-x-full');
        setTimeout(() => {
            sidebar.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }
};

const initCartSidebar = () => {
    const overlay = $('#cartOverlay');
    const closeBtn = $('#closeCartSidebar');
    
    if (overlay) overlay.addEventListener('click', closeCartSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeCartSidebar);
};

// ============================================
// Wishlist Operations
// ============================================
const toggleWishlist = async (productId, button) => {
    if (!window.isLoggedIn) {
        showToast('Please login to add items to wishlist', 'warning');
        return;
    }
    
    try {
        const response = await fetch(`${SITE_URL}/api/wishlist.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'toggle',
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (button) {
                button.classList.toggle('active', data.in_wishlist);
                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fas', data.in_wishlist);
                    icon.classList.toggle('far', !data.in_wishlist);
                }
            }
            showToast(data.message, 'success');
        }
    } catch (error) {
        console.error('Wishlist error:', error);
    }
};

// ============================================
// Quick View Modal
// ============================================
const openQuickView = async (productId) => {
    const modal = $('#quickViewModal');
    const content = $('#quickViewContent');
    const overlay = $('#quickViewOverlay');
    
    if (!modal || !content) return;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    try {
        const response = await fetch(`${SITE_URL}/api/products.php?id=${productId}`);
        const data = await response.json();
        
        if (data.success) {
            const product = data.product;
            const discount = product.sale_price ? Math.round((1 - product.sale_price / product.price) * 100) : 0;
            
            content.innerHTML = `
                <button onclick="closeQuickView()" class="absolute top-4 right-4 w-10 h-10 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full flex items-center justify-center transition z-10">
                    <i class="fas fa-times text-gray-600 dark:text-gray-300"></i>
                </button>
                <div class="grid md:grid-cols-2 gap-8 p-8">
                    <div class="relative">
                        <img src="${product.image ? SITE_URL + '/uploads/' + product.image : SITE_URL + '/assets/images/placeholder.jpg'}" 
                             alt="${product.title}" 
                             class="w-full aspect-square object-cover rounded-2xl">
                        ${product.sale_price ? `<span class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-bold">-${discount}%</span>` : ''}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-primary-500 font-medium text-sm uppercase tracking-wider">${product.category_name || 'Product'}</span>
                        <h2 class="text-2xl font-display font-bold text-gray-800 dark:text-white mt-2">${product.title}</h2>
                        
                        <div class="flex items-center gap-2 mt-4">
                            <div class="flex text-yellow-400">
                                ${[1,2,3,4,5].map(i => `<i class="fas fa-star ${i <= Math.round(product.rating_avg || 4) ? '' : 'opacity-30'}"></i>`).join('')}
                            </div>
                            <span class="text-gray-500">(${product.rating_count || 0} reviews)</span>
                        </div>
                        
                        <div class="flex items-center gap-4 mt-6">
                            <span class="text-3xl font-display font-bold text-primary-600">${formatPrice(product.sale_price || product.price)}</span>
                            ${product.sale_price ? `<span class="text-xl text-gray-400 line-through">${formatPrice(product.price)}</span>` : ''}
                        </div>
                        
                        <p class="text-gray-600 dark:text-gray-400 mt-6 leading-relaxed">${product.short_description || product.description || ''}</p>
                        
                        <div class="flex items-center gap-4 mt-6">
                            <span class="text-gray-600 dark:text-gray-400">Quantity:</span>
                            <div class="quantity-selector">
                                <button onclick="this.nextElementSibling.value = Math.max(1, parseInt(this.nextElementSibling.value) - 1)">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <input type="number" id="quickViewQty" value="1" min="1" max="${product.stock}">
                                <button onclick="this.previousElementSibling.value = Math.min(${product.stock}, parseInt(this.previousElementSibling.value) + 1)">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                            <span class="text-sm text-gray-500">${product.stock} in stock</span>
                        </div>
                        
                        <div class="flex gap-4 mt-8">
                            <button onclick="addToCart(${product.id}, parseInt($('#quickViewQty').value)); closeQuickView();" 
                                    class="flex-1 btn btn-primary btn-lg">
                                <i class="fas fa-shopping-cart"></i>
                                Add to Cart
                            </button>
                            <button onclick="toggleWishlist(${product.id}, this)" 
                                    class="btn btn-outline btn-icon ${product.in_wishlist ? 'active' : ''}">
                                <i class="${product.in_wishlist ? 'fas' : 'far'} fa-heart"></i>
                            </button>
                        </div>
                        
                        <a href="${SITE_URL}/product.php?slug=${product.slug}" 
                           class="text-center text-primary-500 hover:text-primary-600 font-medium mt-6">
                            View Full Details <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    } catch (error) {
        console.error('Quick view error:', error);
        content.innerHTML = `
            <div class="p-8 text-center">
                <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
                <p class="text-gray-600">Failed to load product details</p>
            </div>
        `;
    }
};

const closeQuickView = () => {
    const modal = $('#quickViewModal');
    const content = $('#quickViewContent');
    
    if (modal && content) {
        content.classList.add('scale-95', 'opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }
};

const initQuickView = () => {
    const overlay = $('#quickViewOverlay');
    if (overlay) {
        overlay.addEventListener('click', closeQuickView);
    }
    
    // Escape key to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeQuickView();
            closeCartSidebar();
        }
    });
};

// ============================================
// GSAP Animations
// ============================================
const initGSAPAnimations = () => {
    if (typeof gsap === 'undefined') return;
    
    // Register ScrollTrigger
    gsap.registerPlugin(ScrollTrigger);
    
    // Animate elements on scroll
    gsap.utils.toArray('.animate-on-scroll').forEach(element => {
        gsap.from(element, {
            y: 50,
            opacity: 0,
            duration: 0.8,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: element,
                start: 'top 85%',
                toggleActions: 'play none none reverse'
            }
        });
    });
    
    // Stagger animations for product cards
    gsap.utils.toArray('.products-grid').forEach(grid => {
        const cards = grid.querySelectorAll('.product-card');
        gsap.from(cards, {
            y: 50,
            opacity: 0,
            duration: 0.6,
            stagger: 0.1,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: grid,
                start: 'top 85%'
            }
        });
    });
    
    // Animate stats counters
    gsap.utils.toArray('.stat-number').forEach(stat => {
        const target = parseInt(stat.dataset.count);
        gsap.to(stat, {
            innerHTML: target,
            duration: 2,
            ease: 'power2.out',
            snap: { innerHTML: 1 },
            scrollTrigger: {
                trigger: stat,
                start: 'top 90%'
            }
        });
    });
};

// ============================================
// Swiper Carousels
// ============================================
const initSwipers = () => {
    // Hero Slider
    if ($('.hero-slider')) {
        new Swiper('.hero-slider', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    }
    
    // Products Carousel
    $$('.products-carousel').forEach((carousel, index) => {
        new Swiper(carousel, {
            slidesPerView: 1,
            spaceBetween: 20,
            navigation: {
                nextEl: carousel.querySelector('.swiper-button-next') || `.products-carousel-${index} .swiper-button-next`,
                prevEl: carousel.querySelector('.swiper-button-prev') || `.products-carousel-${index} .swiper-button-prev`,
            },
            pagination: {
                el: carousel.querySelector('.swiper-pagination'),
                clickable: true,
            },
            breakpoints: {
                480: { slidesPerView: 2 },
                768: { slidesPerView: 3 },
                1024: { slidesPerView: 4 },
                1280: { slidesPerView: 5 },
            }
        });
    });
    
    // Category Carousel
    if ($('.category-carousel')) {
        new Swiper('.category-carousel', {
            slidesPerView: 2,
            spaceBetween: 16,
            freeMode: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                480: { slidesPerView: 3 },
                768: { slidesPerView: 4 },
                1024: { slidesPerView: 6 },
                1280: { slidesPerView: 8 },
            }
        });
    }
};

// ============================================
// Newsletter Form
// ============================================
const initNewsletter = () => {
    const form = $('#newsletterForm');
    if (!form) return;
    
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = form.querySelector('input[name="email"]').value;
        
        try {
            const response = await fetch(`${SITE_URL}/api/newsletter.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('Successfully subscribed to newsletter!', 'success');
                form.reset();
            } else {
                showToast(data.error || 'Failed to subscribe', 'error');
            }
        } catch (error) {
            showToast('An error occurred', 'error');
        }
    });
};

// ============================================
// Form Validation
// ============================================
const validateForm = (form) => {
    let isValid = true;
    const inputs = form.querySelectorAll('[required]');
    
    inputs.forEach(input => {
        const error = input.parentElement.querySelector('.form-error');
        
        if (!input.value.trim()) {
            input.classList.add('error');
            if (error) error.textContent = 'This field is required';
            isValid = false;
        } else if (input.type === 'email' && !isValidEmail(input.value)) {
            input.classList.add('error');
            if (error) error.textContent = 'Please enter a valid email';
            isValid = false;
        } else {
            input.classList.remove('error');
            if (error) error.textContent = '';
        }
    });
    
    return isValid;
};

const isValidEmail = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

// ============================================
// Lazy Loading Images
// ============================================
const initLazyLoading = () => {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        $$('img.lazy').forEach(img => imageObserver.observe(img));
    }
};

// ============================================
// Product Card Interactions
// ============================================
const initProductCards = () => {
    // Add to cart buttons
    $$('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.dataset.addToCart;
            addToCart(productId);
        });
    });
    
    // Quick view buttons
    $$('[data-quick-view]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.dataset.quickView;
            openQuickView(productId);
        });
    });
    
    // Wishlist buttons
    $$('[data-wishlist]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.dataset.wishlist;
            toggleWishlist(productId, btn);
        });
    });
};

// ============================================
// Initialize All
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    initMobileMenu();
    initUserMenu();
    initHeaderScroll();
    initBackToTop();
    initSearch();
    initCartSidebar();
    initQuickView();
    initSwipers();
    initNewsletter();
    initLazyLoading();
    initProductCards();
});

// Make functions globally available
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateCartQuantity;
window.toggleWishlist = toggleWishlist;
window.openQuickView = openQuickView;
window.closeQuickView = closeQuickView;
window.openCartSidebar = openCartSidebar;
window.closeCartSidebar = closeCartSidebar;
