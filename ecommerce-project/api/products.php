<?php
/**
 * Products API Endpoint
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
        // Get single product
        if (isset($_GET['id'])) {
            $productId = intval($_GET['id']);
            
            $product = db()->fetch("
                SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ? AND p.status = 'active'
            ", [$productId]);
            
            if (!$product) {
                errorResponse('Product not found', 404);
            }
            
            // Check wishlist status
            $product['in_wishlist'] = isInWishlist($product['id']);
            
            // Get product images
            $product['images'] = db()->fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order", [$productId]);
            
            successResponse(['product' => $product]);
        }
        // Get product by slug
        elseif (isset($_GET['slug'])) {
            $slug = sanitize($_GET['slug']);
            
            $product = db()->fetch("
                SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = ? AND p.status = 'active'
            ", [$slug]);
            
            if (!$product) {
                errorResponse('Product not found', 404);
            }
            
            // Increment view count
            db()->query("UPDATE products SET views = views + 1 WHERE id = ?", [$product['id']]);
            
            $product['in_wishlist'] = isInWishlist($product['id']);
            $product['images'] = db()->fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order", [$product['id']]);
            
            successResponse(['product' => $product]);
        }
        // Search products
        elseif (isset($_GET['search'])) {
            $search = sanitize($_GET['search']);
            $limit = min(20, intval($_GET['limit'] ?? 10));
            
            $products = db()->fetchAll("
                SELECT p.id, p.title, p.slug, p.price, p.sale_price, p.image, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND (p.title LIKE ? OR p.description LIKE ?)
                ORDER BY p.featured DESC, p.sales_count DESC
                LIMIT ?
            ", ["%$search%", "%$search%", $limit]);
            
            successResponse(['products' => $products]);
        }
        // List products with filters
        else {
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = min(50, intval($_GET['per_page'] ?? 12));
            $categoryId = intval($_GET['category'] ?? 0);
            $featured = isset($_GET['featured']);
            $bestseller = isset($_GET['bestseller']);
            $newArrival = isset($_GET['new']);
            $onSale = isset($_GET['sale']);
            $minPrice = floatval($_GET['min_price'] ?? 0);
            $maxPrice = floatval($_GET['max_price'] ?? 0);
            $sort = sanitize($_GET['sort'] ?? 'newest');
            
            $where = ["p.status = 'active'"];
            $params = [];
            
            if ($categoryId) {
                // Include subcategories
                $categoryIds = [$categoryId];
                $subcategories = db()->fetchAll("SELECT id FROM categories WHERE parent_id = ?", [$categoryId]);
                foreach ($subcategories as $sub) {
                    $categoryIds[] = $sub['id'];
                }
                $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
                $where[] = "p.category_id IN ($placeholders)";
                $params = array_merge($params, $categoryIds);
            }
            
            if ($featured) {
                $where[] = "p.featured = 1";
            }
            
            if ($bestseller) {
                $where[] = "p.bestseller = 1";
            }
            
            if ($newArrival) {
                $where[] = "p.new_arrival = 1";
            }
            
            if ($onSale) {
                $where[] = "p.sale_price IS NOT NULL AND p.sale_price < p.price";
            }
            
            if ($minPrice > 0) {
                $where[] = "COALESCE(p.sale_price, p.price) >= ?";
                $params[] = $minPrice;
            }
            
            if ($maxPrice > 0) {
                $where[] = "COALESCE(p.sale_price, p.price) <= ?";
                $params[] = $maxPrice;
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Sort order
            switch ($sort) {
                case 'price_low':
                    $orderBy = "COALESCE(p.sale_price, p.price) ASC";
                    break;
                case 'price_high':
                    $orderBy = "COALESCE(p.sale_price, p.price) DESC";
                    break;
                case 'popular':
                    $orderBy = "p.sales_count DESC";
                    break;
                case 'rating':
                    $orderBy = "p.rating_avg DESC";
                    break;
                case 'oldest':
                    $orderBy = "p.created_at ASC";
                    break;
                default:
                    $orderBy = "p.created_at DESC";
            }
            
            // Get total count
            $totalResult = db()->fetch("SELECT COUNT(*) as total FROM products p WHERE $whereClause", $params);
            $total = $totalResult['total'];
            
            // Get products
            $offset = ($page - 1) * $perPage;
            $params[] = $perPage;
            $params[] = $offset;
            
            $products = db()->fetchAll("
                SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT ? OFFSET ?
            ", $params);
            
            successResponse([
                'products' => $products,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => ceil($total / $perPage),
                    'has_more' => ($page * $perPage) < $total
                ]
            ]);
        }
    } else {
        errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    errorResponse('An error occurred', 500);
}
