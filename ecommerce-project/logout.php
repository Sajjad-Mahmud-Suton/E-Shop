<?php
/**
 * Logout
 * Modern E-Commerce Platform
 */

require_once 'includes/functions.php';

// Log activity before destroying session
if (isLoggedIn()) {
    logActivity('logout', 'User logged out');
}

// Clear remember token
if (isset($_COOKIE['remember_token'])) {
    if (isLoggedIn()) {
        db()->query("UPDATE users SET remember_token = NULL WHERE id = ?", [$_SESSION['user_id']]);
    }
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Destroy session
session_unset();
session_destroy();

// Redirect
header('Location: ' . SITE_URL . '/login.php');
exit;
