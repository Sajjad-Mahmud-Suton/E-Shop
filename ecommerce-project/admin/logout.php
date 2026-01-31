<?php
/**
 * Admin Logout
 * Modern E-Commerce Platform
 */

require_once '../includes/functions.php';

logActivity('admin_logout', 'Admin logged out');

session_destroy();

header('Location: ' . SITE_URL . '/admin/');
exit;
