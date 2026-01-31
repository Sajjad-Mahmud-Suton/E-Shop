<?php
/**
 * Wishlist API Endpoint
 * Modern E-Commerce Platform
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Require login for wishlist
    if (!isLoggedIn()) {
        errorResponse('Please login to manage your wishlist', 401);
    }
    
    $userId = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        // Get wishlist items
        $items = db()->fetchAll("
            SELECT w.id, w.created_at, p.id as product_id, p.title, p.slug, p.price, p.sale_price, p.image, p.stock, c.name as category_name
            FROM wishlist w
            JOIN products p ON w.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE w.user_id = ? AND p.status = 'active'
            ORDER BY w.created_at DESC
        ", [$userId]);
        
        successResponse([
            'items' => $items,
            'count' => count($items)
        ]);
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $productId = intval($input['product_id'] ?? 0);
        
        if (!$productId) {
            errorResponse('Product ID is required');
        }
        
        // Check if product exists
        $product = db()->fetch("SELECT id FROM products WHERE id = ? AND status = 'active'", [$productId]);
        
        if (!$product) {
            errorResponse('Product not found', 404);
        }
        
        switch ($action) {
            case 'add':
                // Check if already in wishlist
                $existing = db()->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
                
                if ($existing) {
                    successResponse([
                        'in_wishlist' => true,
                        'count' => getWishlistCount()
                    ], 'Product already in wishlist');
                }
                
                db()->query("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", [$userId, $productId]);
                
                successResponse([
                    'in_wishlist' => true,
                    'count' => getWishlistCount()
                ], 'Added to wishlist');
                break;
                
            case 'remove':
                db()->query("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
                
                successResponse([
                    'in_wishlist' => false,
                    'count' => getWishlistCount()
                ], 'Removed from wishlist');
                break;
                
            case 'toggle':
                $existing = db()->fetch("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
                
                if ($existing) {
                    db()->query("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
                    successResponse([
                        'in_wishlist' => false,
                        'count' => getWishlistCount()
                    ], 'Removed from wishlist');
                } else {
                    db()->query("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", [$userId, $productId]);
                    successResponse([
                        'in_wishlist' => true,
                        'count' => getWishlistCount()
                    ], 'Added to wishlist');
                }
                break;
                
            default:
                errorResponse('Invalid action');
        }
    } else {
        errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Wishlist API Error: " . $e->getMessage());
    errorResponse('An error occurred', 500);
}
