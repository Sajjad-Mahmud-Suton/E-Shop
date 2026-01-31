<?php
/**
 * Admin Header Component
 * Modern E-Commerce Platform
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin'; ?> - E-Shop Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
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
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Poppins', sans-serif; }
        
        .sidebar-link {
            @apply flex items-center gap-3 px-4 py-3 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all;
        }
        .sidebar-link.active {
            @apply bg-primary-500 text-white hover:bg-primary-600;
        }
        .sidebar-link i {
            @apply w-5 text-center;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-xl z-50 transform transition-transform lg:translate-x-0 -translate-x-full">
            <!-- Logo -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-gray-200 dark:border-gray-700">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-display font-bold text-gray-900 dark:text-white">E-Shop</h1>
                        <p class="text-xs text-gray-500">Admin Panel</p>
                    </div>
                </a>
                <button id="closeSidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-5rem)]">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mb-2">Main</p>
                
                <a href="dashboard.php" class="sidebar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mt-6 mb-2">Catalog</p>
                
                <a href="products.php" class="sidebar-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                
                <a href="categories.php" class="sidebar-link <?php echo $currentPage === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mt-6 mb-2">Sales</p>
                
                <a href="orders.php" class="sidebar-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                    <?php 
                    $pendingCount = db()->fetch("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
                    if ($pendingCount > 0):
                    ?>
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
                
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mt-6 mb-2">Users</p>
                
                <a href="users.php" class="sidebar-link <?php echo $currentPage === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-4 mt-6 mb-2">Settings</p>
                
                <a href="settings.php" class="sidebar-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                
                <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="<?php echo SITE_URL; ?>" target="_blank" class="sidebar-link">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Website</span>
                    </a>
                    
                    <a href="logout.php" class="sidebar-link text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 lg:ml-64">
            <!-- Top Bar -->
            <header class="sticky top-0 h-20 bg-white dark:bg-gray-800 shadow-sm z-40 flex items-center justify-between px-6">
                <!-- Mobile Menu Toggle -->
                <button id="openSidebar" class="lg:hidden text-gray-600 dark:text-gray-300 hover:text-primary-500">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Page Title -->
                <div class="hidden lg:block">
                    <h1 class="text-xl font-display font-bold text-gray-900 dark:text-white"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button id="darkModeToggle" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <button class="relative w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                    </button>
                    
                    <!-- Admin Profile -->
                    <div class="relative" x-data="{ open: false }">
                        <div class="flex items-center gap-3 cursor-pointer">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?>
                            </div>
                            <div class="hidden md:block">
                                <p class="font-medium text-gray-900 dark:text-white text-sm"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-6">
                <?php if (hasFlash()): ?>
                <?php $flash = getFlash(); ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                    <div class="flex items-center gap-3">
                        <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                        <span><?php echo $flash['message']; ?></span>
                    </div>
                </div>
                <?php endif; ?>
