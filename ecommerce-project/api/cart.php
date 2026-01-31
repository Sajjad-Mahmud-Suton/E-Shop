<?php
/**
 * Cart API Endpoint
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
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get';
        
        if ($action === 'get') {
            $items = getCartItems();
            $total = getCartTotal();
            
            successResponse([
                'items' => $items,
                'total' => $total,
                'count' => array_sum(array_column($items, 'quantity'))
            ]);
        }
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $productId = intval($input['product_id'] ?? 0);
                $quantity = max(1, intval($input['quantity'] ?? 1));
                
                if (!$productId) {
                    errorResponse('Product ID is required');
                }
                
                // Check if product exists and is in stock
                $product = db()->fetch("SELECT id, stock, status FROM products WHERE id = ? AND status = 'active'", [$productId]);
                
                if (!$product) {
                    errorResponse('Product not found');
                }
                
                if ($product['stock'] < $quantity) {
                    errorResponse('Insufficient stock');
                }
                
                // Check if item already in cart
                if (isLoggedIn()) {
                    $existing = db()->fetch("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", [$_SESSION['user_id'], $productId]);
                    
                    if ($existing) {
                        $newQuantity = min($product['stock'], $existing['quantity'] + $quantity);
                        db()->query("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", [$newQuantity, $existing['id']]);
                    } else {
                        db()->query("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)", [$_SESSION['user_id'], $productId, $quantity]);
                    }
                } else {
                    $sessionId = session_id();
                    $existing = db()->fetch("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?", [$sessionId, $productId]);
                    
                    if ($existing) {
                        $newQuantity = min($product['stock'], $existing['quantity'] + $quantity);
                        db()->query("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", [$newQuantity, $existing['id']]);
                    } else {
                        db()->query("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)", [$sessionId, $productId, $quantity]);
                    }
                }
                
                successResponse([
                    'cart_count' => getCartCount()
                ], 'Item added to cart');
                break;
                
            case 'update':
                $cartId = intval($input['cart_id'] ?? 0);
                $quantity = max(1, intval($input['quantity'] ?? 1));
                
                if (!$cartId) {
                    errorResponse('Cart item ID is required');
                }
                
                // Verify ownership
                if (isLoggedIn()) {
                    $cartItem = db()->fetch("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?", [$cartId, $_SESSION['user_id']]);
                } else {
                    $cartItem = db()->fetch("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.session_id = ?", [$cartId, session_id()]);
                }
                
                if (!$cartItem) {
                    errorResponse('Cart item not found');
                }
                
                $quantity = min($quantity, $cartItem['stock']);
                
                if ($quantity <= 0) {
                    db()->query("DELETE FROM cart WHERE id = ?", [$cartId]);
                } else {
                    db()->query("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?", [$quantity, $cartId]);
                }
                
                successResponse([
                    'cart_count' => getCartCount(),
                    'cart_total' => getCartTotal()
                ], 'Cart updated');
                break;
                
            case 'remove':
                $cartId = intval($input['cart_id'] ?? 0);
                
                if (!$cartId) {
                    errorResponse('Cart item ID is required');
                }
                
                // Verify ownership
                if (isLoggedIn()) {
                    db()->query("DELETE FROM cart WHERE id = ? AND user_id = ?", [$cartId, $_SESSION['user_id']]);
                } else {
                    db()->query("DELETE FROM cart WHERE id = ? AND session_id = ?", [$cartId, session_id()]);
                }
                
                successResponse([
                    'cart_count' => getCartCount()
                ], 'Item removed from cart');
                break;
                
            case 'clear':
                if (isLoggedIn()) {
                    db()->query("DELETE FROM cart WHERE user_id = ?", [$_SESSION['user_id']]);
                } else {
                    db()->query("DELETE FROM cart WHERE session_id = ?", [session_id()]);
                }
                
                successResponse(['cart_count' => 0], 'Cart cleared');
                break;
                
            case 'apply_coupon':
                $code = strtoupper(sanitize($input['code'] ?? ''));
                
                if (empty($code)) {
                    errorResponse('Coupon code is required');
                }
                
                $coupon = db()->fetch("
                    SELECT * FROM coupons 
                    WHERE code = ? 
                    AND status = 'active'
                    AND (start_date IS NULL OR start_date <= CURDATE())
                    AND (end_date IS NULL OR end_date >= CURDATE())
                    AND (usage_limit IS NULL OR used_count < usage_limit)
                ", [$code]);
                
                if (!$coupon) {
                    errorResponse('Invalid or expired coupon code');
                }
                
                $cartTotal = getCartTotal();
                
                if ($cartTotal < $coupon['min_order']) {
                    errorResponse('Minimum order amount is $' . number_format($coupon['min_order'], 2));
                }
                
                // Calculate discount
                if ($coupon['type'] === 'percentage') {
                    $discount = $cartTotal * ($coupon['value'] / 100);
                    if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                        $discount = $coupon['max_discount'];
                    }
                } else {
                    $discount = $coupon['value'];
                }
                
                $_SESSION['coupon'] = [
                    'id' => $coupon['id'],
                    'code' => $coupon['code'],
                    'discount' => $discount
                ];
                
                successResponse([
                    'discount' => $discount,
                    'coupon_code' => $coupon['code']
                ], 'Coupon applied successfully');
                break;
                
            default:
                errorResponse('Invalid action');
        }
    } else {
        errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    errorResponse('An error occurred', 500);
}
