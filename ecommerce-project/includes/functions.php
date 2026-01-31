<?php
/**
 * Core Functions Library
 * Modern E-Commerce Platform
 */

// Security constant
define('SECURE_ACCESS', true);

// Start session with security settings
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        session_start();
    }
}

initSession();

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Redirect with optional message
 */
function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    }
    header("Location: $url");
    exit;
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user
 */
function currentUser() {
    if (!isLoggedIn()) return null;
    
    return db()->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php', 'Please login to continue', 'warning');
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        redirect('/admin/login.php', 'Admin access required', 'error');
    }
}

/**
 * Format price
 */
function formatPrice($price) {
    return '$' . number_format((float)$price, 2);
}

/**
 * Generate slug
 */
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Generate order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Upload file
 */
function uploadFile($file, $folder = 'products') {
    $targetDir = UPLOAD_PATH . $folder . '/';
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $folder . '/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Upload failed'];
}

/**
 * Delete file
 */
function deleteFile($filename) {
    $filepath = UPLOAD_PATH . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Get image URL
 */
function imageUrl($path, $default = 'placeholder.jpg') {
    if (empty($path)) {
        return SITE_URL . '/assets/images/' . $default;
    }
    return SITE_URL . '/uploads/' . $path;
}

/**
 * Get cart count
 */
function getCartCount() {
    if (isLoggedIn()) {
        $result = db()->fetch("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
    } else {
        $sessionId = session_id();
        $result = db()->fetch("SELECT SUM(quantity) as count FROM cart WHERE session_id = ?", [$sessionId]);
    }
    return $result['count'] ?? 0;
}

/**
 * Get cart items
 */
function getCartItems() {
    if (isLoggedIn()) {
        return db()->fetchAll("
            SELECT c.*, p.title, p.price, p.sale_price, p.image, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ", [$_SESSION['user_id']]);
    } else {
        $sessionId = session_id();
        return db()->fetchAll("
            SELECT c.*, p.title, p.price, p.sale_price, p.image, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.session_id = ?
        ", [$sessionId]);
    }
}

/**
 * Get cart total
 */
function getCartTotal() {
    $items = getCartItems();
    $total = 0;
    foreach ($items as $item) {
        $price = $item['sale_price'] ?? $item['price'];
        $total += $price * $item['quantity'];
    }
    return $total;
}

/**
 * Get wishlist count
 */
function getWishlistCount() {
    if (!isLoggedIn()) return 0;
    $result = db()->fetch("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?", [$_SESSION['user_id']]);
    return $result['count'] ?? 0;
}

/**
 * Check if product is in wishlist
 */
function isInWishlist($productId) {
    if (!isLoggedIn()) return false;
    $result = db()->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
    return !empty($result);
}

/**
 * Get all categories with hierarchy
 */
function getCategories($parentId = 0) {
    return db()->fetchAll("
        SELECT c.*, 
               (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'active') as product_count
        FROM categories c
        WHERE c.parent_id = ? AND c.status = 'active'
        ORDER BY c.sort_order, c.name
    ", [$parentId]);
}

/**
 * Get category with children
 */
function getCategoryWithChildren($parentId = 0) {
    $categories = getCategories($parentId);
    foreach ($categories as &$category) {
        $category['children'] = getCategoryWithChildren($category['id']);
    }
    return $categories;
}

/**
 * Get featured products
 */
function getFeaturedProducts($limit = 8) {
    return db()->fetchAll("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.featured = 1 AND p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT ?
    ", [$limit]);
}

/**
 * Get bestseller products
 */
function getBestsellerProducts($limit = 8) {
    return db()->fetchAll("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.bestseller = 1 AND p.status = 'active'
        ORDER BY p.sales_count DESC
        LIMIT ?
    ", [$limit]);
}

/**
 * Get new arrival products
 */
function getNewArrivals($limit = 8) {
    return db()->fetchAll("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.new_arrival = 1 AND p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT ?
    ", [$limit]);
}

/**
 * Get sliders
 */
function getSliders() {
    return db()->fetchAll("SELECT * FROM sliders WHERE status = 'active' ORDER BY sort_order");
}

/**
 * Get setting
 */
function getSetting($key, $default = null) {
    $result = db()->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    return $result['setting_value'] ?? $default;
}

/**
 * Update setting
 */
function updateSetting($key, $value) {
    $existing = db()->fetch("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    if ($existing) {
        db()->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
    } else {
        db()->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
    }
}

/**
 * Log activity
 */
function logActivity($action, $description = null) {
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    db()->query("
        INSERT INTO activity_log (user_id, action, description, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?)
    ", [$userId, $action, $description, $ip, $userAgent]);
}

/**
 * Pagination helper
 */
function paginate($total, $perPage = 12, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M d, Y', $time);
}

/**
 * Get order status badge
 */
function getOrderStatusBadge($status) {
    $badges = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'processing' => 'bg-indigo-100 text-indigo-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'delivered' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'refunded' => 'bg-gray-100 text-gray-800'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800';
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Calculate discount percentage
 */
function discountPercentage($original, $sale) {
    if (!$sale || $sale >= $original) return 0;
    return round((($original - $sale) / $original) * 100);
}

/**
 * Get product price (sale or regular)
 */
function getProductPrice($product) {
    return $product['sale_price'] ?? $product['price'];
}

/**
 * JSON response helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Error response
 */
function errorResponse($message, $status = 400) {
    jsonResponse(['success' => false, 'error' => $message], $status);
}

/**
 * Success response
 */
function successResponse($data = [], $message = 'Success') {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}
