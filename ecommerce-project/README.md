# Modern E-Commerce Platform 🛒

A complete, production-ready e-commerce website built with PHP 8+, MySQL 8+, Tailwind CSS, and GSAP animations.

![PHP Version](https://img.shields.io/badge/PHP-8%2B-777BB4?logo=php)
![MySQL Version](https://img.shields.io/badge/MySQL-8%2B-4479A1?logo=mysql)
![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.4-06B6D4?logo=tailwindcss)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### 🛍️ Customer Features
- **Product Catalog** - Browse products with category filters, price range, and sorting
- **Product Details** - Image gallery, reviews, related products
- **Shopping Cart** - Add/remove items, quantity management
- **Wishlist** - Save products for later
- **User Authentication** - Register, login, logout with session management
- **User Dashboard** - View order history, manage profile, update addresses
- **Checkout** - Secure checkout process with order confirmation
- **Responsive Design** - Optimized for all devices

### 👔 Admin Features
- **Dashboard** - Overview with statistics, charts, and recent orders
- **Product Management** - Full CRUD operations with image upload
- **Category Management** - Organize products by categories
- **Order Management** - View and update order statuses
- **User Management** - Manage customers and admin users
- **Secure Access** - Protected admin area with role-based access

### 🎨 UI/UX Features
- **Modern Design** - Clean, professional look with Tailwind CSS
- **Dark Mode Support** - Toggle between light and dark themes
- **GSAP Animations** - Smooth, engaging animations
- **Toast Notifications** - User-friendly feedback system
- **Lazy Loading** - Optimized image loading

## 📁 Project Structure

```
ecommerce-project/
├── admin/                  # Admin panel
│   ├── includes/           # Admin header, footer
│   ├── index.php           # Admin login
│   ├── dashboard.php       # Admin dashboard
│   ├── products.php        # Product management
│   ├── categories.php      # Category management
│   ├── orders.php          # Order management
│   ├── users.php           # User management
│   └── logout.php          # Admin logout
│
├── api/                    # API endpoints
│   ├── cart.php            # Cart operations
│   ├── products.php        # Product data
│   └── wishlist.php        # Wishlist operations
│
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css       # Custom styles
│   └── js/
│       └── main.js         # JavaScript functions
│
├── config/                 # Configuration
│   └── database.php        # Database connection
│
├── includes/               # Shared components
│   ├── components/
│   │   └── product-card.php
│   ├── header.php          # Site header
│   ├── footer.php          # Site footer
│   └── functions.php       # Helper functions
│
├── uploads/                # User uploads
│   ├── products/           # Product images
│   └── categories/         # Category images
│
├── user/                   # User panel
│   ├── dashboard.php       # User dashboard
│   ├── orders.php          # Order history
│   └── profile.php         # Profile management
│
├── index.php               # Homepage
├── products.php            # Product listing
├── product.php             # Single product
├── cart.php                # Shopping cart
├── checkout.php            # Checkout page
├── wishlist.php            # Wishlist page
├── login.php               # User login
├── register.php            # User registration
├── logout.php              # User logout
├── database.sql            # Database schema
├── .htaccess               # Apache configuration
└── README.md               # This file
```

## 🚀 Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- Composer (optional)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/ecommerce-project.git
   cd ecommerce-project
   ```

2. **Create the database**
   ```bash
   mysql -u root -p < database.sql
   ```
   Or import `database.sql` using phpMyAdmin.

3. **Configure database connection**
   
   Edit `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'ecommerce_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set up uploads directory**
   ```bash
   mkdir -p uploads/products uploads/categories
   chmod 755 uploads
   ```

5. **Configure Apache**
   
   Ensure `mod_rewrite` is enabled and `.htaccess` is allowed:
   ```apache
   <Directory /path/to/ecommerce-project>
       AllowOverride All
   </Directory>
   ```

6. **Access the site**
   - Frontend: `http://localhost/ecommerce-project`
   - Admin Panel: `http://localhost/ecommerce-project/admin`

## 🔐 Default Login Credentials

### Admin Account
- **Email:** admin@admin.com
- **Password:** admin123

### Test Customer Account
- **Email:** customer@test.com
- **Password:** password123

> ⚠️ **Important:** Change these credentials in production!

## 🛠️ Configuration

### Environment Settings

Edit `config/database.php` to configure:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site
define('SITE_URL', 'http://localhost/ecommerce-project');
define('SITE_NAME', 'ShopModern');
```

### Security Settings

The `.htaccess` file includes:
- Security headers (XSS, MIME sniffing, clickjacking protection)
- Directory listing prevention
- Sensitive file protection
- GZIP compression
- Browser caching
- URL rewriting

## 📦 Database Schema

### Tables
- `users` - Customer and admin accounts
- `categories` - Product categories
- `products` - Product catalog
- `orders` - Customer orders
- `order_items` - Order line items
- `wishlist` - User wishlists
- `reviews` - Product reviews

## 🎯 API Endpoints

### Cart API (`/api/cart.php`)
- `POST` - Add/update/remove cart items
- Actions: `add`, `update`, `remove`, `clear`

### Products API (`/api/products.php`)
- `GET` - Retrieve products with filters
- Parameters: `category`, `search`, `min_price`, `max_price`, `sort`

### Wishlist API (`/api/wishlist.php`)
- `POST` - Toggle wishlist items
- `GET` - Get user's wishlist

## 🔧 Development

### Tech Stack
- **Backend:** PHP 8+ with PDO
- **Database:** MySQL 8+
- **Frontend:** Tailwind CSS 3.4 (CDN)
- **Animations:** GSAP 3.12
- **Icons:** Font Awesome 6.5
- **Charts:** Chart.js 4.0 (Admin)
- **Tables:** DataTables (Admin)

### Coding Standards
- PSR-4 autoloading compatible
- PDO with prepared statements
- CSRF protection on forms
- Password hashing with bcrypt
- Session-based authentication

## 🔒 Security Features

- CSRF token validation
- Prepared statements (SQL injection prevention)
- Password hashing (bcrypt)
- XSS protection (htmlspecialchars)
- Role-based access control
- Secure session handling
- Input validation and sanitization

## 📱 Responsive Design

The platform is fully responsive with breakpoints:
- Mobile: 320px - 640px
- Tablet: 640px - 1024px
- Desktop: 1024px+

## 🙏 Credits

- [Tailwind CSS](https://tailwindcss.com/)
- [GSAP](https://greensock.com/gsap/)
- [Font Awesome](https://fontawesome.com/)
- [Chart.js](https://www.chartjs.org/)
- [DataTables](https://datatables.net/)

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

Made with ❤️ for modern e-commerce

