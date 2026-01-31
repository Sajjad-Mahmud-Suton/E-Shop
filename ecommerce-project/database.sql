-- ============================================
-- E-Shop Modern E-Commerce Database Schema
-- Version: 1.0.0
-- Author: E-Shop Team
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- ============================================
-- USERS TABLE
-- ============================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    zip_code VARCHAR(20) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'banned', 'pending') DEFAULT 'active',
    email_verified TINYINT(1) DEFAULT 0,
    remember_token VARCHAR(255) DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CATEGORIES TABLE
-- ============================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    description TEXT DEFAULT NULL,
    parent_id INT DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(100) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRODUCTS TABLE
-- ============================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500) DEFAULT NULL,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2) DEFAULT NULL,
    cost_price DECIMAL(10, 2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE DEFAULT NULL,
    category_id INT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    gallery TEXT DEFAULT NULL,
    stock INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 5,
    weight DECIMAL(8, 2) DEFAULT NULL,
    dimensions VARCHAR(100) DEFAULT NULL,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    featured TINYINT(1) DEFAULT 0,
    bestseller TINYINT(1) DEFAULT 0,
    new_arrival TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    sales_count INT DEFAULT 0,
    rating_avg DECIMAL(3, 2) DEFAULT 0.00,
    rating_count INT DEFAULT 0,
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price),
    FULLTEXT idx_search (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PRODUCT IMAGES TABLE
-- ============================================
DROP TABLE IF EXISTS product_images;
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDERS TABLE
-- ============================================
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) DEFAULT 0.00,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    tax DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    transaction_id VARCHAR(255) DEFAULT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_email VARCHAR(150) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_country VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    billing_same TINYINT(1) DEFAULT 1,
    billing_address TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    shipped_at TIMESTAMP NULL DEFAULT NULL,
    delivered_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_order_number (order_number),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ORDER ITEMS TABLE
-- ============================================
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_title VARCHAR(200) NOT NULL,
    product_image VARCHAR(255) DEFAULT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CART TABLE
-- ============================================
DROP TABLE IF EXISTS cart;
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WISHLIST TABLE
-- ============================================
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- REVIEWS TABLE
-- ============================================
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200) DEFAULT NULL,
    comment TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (product_id, user_id),
    INDEX idx_product (product_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- COUPONS TABLE
-- ============================================
DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    min_order DECIMAL(10, 2) DEFAULT 0.00,
    max_discount DECIMAL(10, 2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SLIDERS TABLE
-- ============================================
DROP TABLE IF EXISTS sliders;
CREATE TABLE sliders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    subtitle VARCHAR(300) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    button_text VARCHAR(100) DEFAULT NULL,
    button_link VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SETTINGS TABLE
-- ============================================
DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT DEFAULT NULL,
    setting_type VARCHAR(50) DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NEWSLETTER SUBSCRIBERS TABLE
-- ============================================
DROP TABLE IF EXISTS newsletter;
CREATE TABLE newsletter (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) UNIQUE NOT NULL,
    status ENUM('subscribed', 'unsubscribed') DEFAULT 'subscribed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ACTIVITY LOG TABLE
-- ============================================
DROP TABLE IF EXISTS activity_log;
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert Admin User (Password: admin123)
INSERT INTO users (name, email, password, role, phone, status, email_verified) VALUES
('Admin User', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+1234567890', 'active', 1);

-- Insert Regular User (Password: user123)
INSERT INTO users (name, email, password, role, phone, address, city, country, status, email_verified) VALUES
('John Doe', 'john@example.com', '$2y$10$HZV0T8z9uD8/5j6xq5q5Oe8z5Q8z5Q8z5Q8z5Q8z5Q8z5Q8z5Q8z5Q', 'user', '+1987654321', '123 Main Street, Apt 4B', 'New York', 'USA', 'active', 1);

-- Insert Categories
INSERT INTO categories (name, slug, description, parent_id, icon, sort_order, status) VALUES
('Electronics', 'electronics', 'Electronic devices and gadgets', 0, 'fa-laptop', 1, 'active'),
('Cosmetics', 'cosmetics', 'Beauty and skincare products', 0, 'fa-spa', 2, 'active'),
('Fashion', 'fashion', 'Clothing and accessories', 0, 'fa-tshirt', 3, 'active'),
('Home & Living', 'home-living', 'Home decor and furniture', 0, 'fa-home', 4, 'active'),
('Sports & Outdoors', 'sports-outdoors', 'Sports equipment and outdoor gear', 0, 'fa-futbol', 5, 'active');

-- Insert Sub-categories
INSERT INTO categories (name, slug, description, parent_id, icon, sort_order, status) VALUES
('Lights', 'lights', 'LED and smart lights', 1, 'fa-lightbulb', 1, 'active'),
('Fans', 'fans', 'Ceiling and table fans', 1, 'fa-fan', 2, 'active'),
('TVs', 'tvs', 'Smart TVs and displays', 1, 'fa-tv', 3, 'active'),
('Mobile Phones', 'mobile-phones', 'Smartphones and accessories', 1, 'fa-mobile-alt', 4, 'active'),
('Facial Care', 'facial-care', 'Face creams and serums', 2, 'fa-smile', 1, 'active'),
('Skincare', 'skincare', 'Body lotions and moisturizers', 2, 'fa-hand-sparkles', 2, 'active'),
('Lipsticks', 'lipsticks', 'Lip colors and glosses', 2, 'fa-kiss', 3, 'active'),
('Men\'s Clothing', 'mens-clothing', 'Men\'s fashion wear', 3, 'fa-male', 1, 'active'),
('Women\'s Clothing', 'womens-clothing', 'Women\'s fashion wear', 3, 'fa-female', 2, 'active'),
('Furniture', 'furniture', 'Home furniture', 4, 'fa-couch', 1, 'active');

-- Insert Products
INSERT INTO products (title, slug, description, short_description, price, sale_price, sku, category_id, image, stock, featured, bestseller, new_arrival, status) VALUES
('Smart LED Bulb 12W RGB', 'smart-led-bulb-12w-rgb', 'Experience the future of lighting with our Smart LED Bulb. Control colors and brightness with your smartphone. Compatible with Alexa and Google Home. Energy efficient with 25,000 hours lifespan.', 'Smart RGB LED bulb with app control', 29.99, 24.99, 'LED-001', 6, 'products/led-bulb.jpg', 150, 1, 1, 1, 'active'),
('Premium Ceiling Fan 52"', 'premium-ceiling-fan-52', 'Elegant ceiling fan with remote control. 5 speed settings, reversible motor, and energy-efficient DC technology. Silent operation perfect for bedrooms and living rooms.', 'Elegant 52 inch ceiling fan with remote', 189.99, 159.99, 'FAN-001', 7, 'products/ceiling-fan.jpg', 45, 1, 0, 0, 'active'),
('4K Ultra HD Smart TV 55"', '4k-ultra-hd-smart-tv-55', 'Immerse yourself in stunning 4K picture quality. Built-in streaming apps, voice control, and Dolby Atmos sound. Perfect for gaming and movie nights.', '55 inch 4K Smart TV with HDR', 699.99, 599.99, 'TV-001', 8, 'products/smart-tv.jpg', 30, 1, 1, 0, 'active'),
('iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', 'The most advanced iPhone ever. A17 Pro chip, titanium design, and the most powerful camera system. 256GB storage in stunning Natural Titanium.', 'Apple iPhone 15 Pro Max', 1199.99, NULL, 'MOB-001', 9, 'products/iphone.jpg', 25, 1, 1, 1, 'active'),
('Vitamin C Brightening Serum', 'vitamin-c-brightening-serum', 'Professional-grade Vitamin C serum for radiant, youthful skin. Reduces dark spots, fine lines, and boosts collagen production. Suitable for all skin types.', 'Brightening face serum with Vitamin C', 45.99, 39.99, 'COS-001', 10, 'products/serum.jpg', 200, 1, 1, 0, 'active'),
('Hydrating Body Lotion 500ml', 'hydrating-body-lotion-500ml', 'Luxurious body lotion with shea butter and hyaluronic acid. 24-hour moisture lock, fast-absorbing, and non-greasy formula. Delicate floral fragrance.', 'Moisturizing body lotion', 28.99, 24.99, 'COS-002', 11, 'products/lotion.jpg', 180, 0, 1, 0, 'active'),
('Matte Velvet Lipstick Set', 'matte-velvet-lipstick-set', 'Collection of 6 stunning matte lipsticks. Long-lasting formula, moisturizing, and transfer-proof. Perfect shades from nude to bold red.', 'Set of 6 matte lipsticks', 34.99, 29.99, 'COS-003', 12, 'products/lipstick.jpg', 120, 1, 0, 1, 'active'),
('Men\'s Slim Fit Casual Shirt', 'mens-slim-fit-casual-shirt', 'Premium cotton casual shirt with modern slim fit. Breathable fabric, easy care, and versatile design. Available in multiple colors.', 'Cotton slim fit shirt for men', 49.99, 42.99, 'FSH-001', 13, 'products/mens-shirt.jpg', 100, 0, 0, 1, 'active'),
('Women\'s Summer Floral Dress', 'womens-summer-floral-dress', 'Beautiful floral print dress perfect for summer. Lightweight, flowing fabric with adjustable straps. Available in sizes XS to XXL.', 'Floral summer dress', 69.99, 54.99, 'FSH-002', 14, 'products/womens-dress.jpg', 80, 1, 1, 1, 'active'),
('Modern Leather Sofa Set', 'modern-leather-sofa-set', 'Luxurious 3-piece leather sofa set. Premium genuine leather, solid wood frame, and high-density foam cushions. Contemporary design fits any living space.', 'Premium leather sofa set', 1999.99, 1799.99, 'HOM-001', 15, 'products/sofa.jpg', 10, 1, 0, 0, 'active');

-- Insert Sliders
INSERT INTO sliders (title, subtitle, description, image, button_text, button_link, sort_order, status) VALUES
('Summer Sale 2024', 'Up to 50% Off', 'Discover amazing deals on electronics, fashion, and more. Limited time offer!', 'banners/slider-1.jpg', 'Shop Now', '/products.php?sale=1', 1, 'active'),
('New Arrivals', 'Fresh Collection', 'Check out the latest products just added to our store.', 'banners/slider-2.jpg', 'Explore', '/products.php?new=1', 2, 'active'),
('Smart Home Devices', 'Transform Your Home', 'Make your home smarter with our IoT devices and smart electronics.', 'banners/slider-3.jpg', 'Learn More', '/category.php?slug=electronics', 3, 'active'),
('Beauty Essentials', 'Glow Up Season', 'Premium skincare and cosmetics for your daily routine.', 'banners/slider-4.jpg', 'Shop Beauty', '/category.php?slug=cosmetics', 4, 'active'),
('Fashion Forward', 'Style That Speaks', 'Trending fashion pieces for every occasion.', 'banners/slider-5.jpg', 'View Collection', '/category.php?slug=fashion', 5, 'active');

-- Insert Settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'E-Shop', 'text'),
('site_tagline', 'Your One-Stop Shopping Destination', 'text'),
('site_email', 'contact@eshop.com', 'email'),
('site_phone', '+1 (555) 123-4567', 'text'),
('site_address', '123 Commerce Street, New York, NY 10001', 'textarea'),
('currency', 'USD', 'text'),
('currency_symbol', '$', 'text'),
('shipping_cost', '9.99', 'number'),
('free_shipping_min', '99.00', 'number'),
('tax_rate', '8.875', 'number'),
('facebook_url', 'https://facebook.com/eshop', 'url'),
('twitter_url', 'https://twitter.com/eshop', 'url'),
('instagram_url', 'https://instagram.com/eshop', 'url'),
('youtube_url', 'https://youtube.com/eshop', 'url');

-- Insert Sample Coupon
INSERT INTO coupons (code, type, value, min_order, max_discount, usage_limit, start_date, end_date, status) VALUES
('WELCOME10', 'percentage', 10.00, 50.00, 100.00, 1000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 'active'),
('FLAT20', 'fixed', 20.00, 100.00, NULL, 500, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 'active');
