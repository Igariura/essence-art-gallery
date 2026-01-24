<?php
// ===========================================
// CART SYNC HANDLER
// ===========================================
// This file syncs guest cart (session) to database when user logs in
// Store this in: includes/syncCartOnLogin.php
// 
// HOW TO USE:
// Include this file AFTER setting $_SESSION['user_id'] on login/register
// 
// Example:
//   $_SESSION['user_id'] = $user['UserID'];
//   require_once 'includes/syncCartOnLogin.php';

// ===========================================
// SECTION 1: SAFETY CHECKS
// ===========================================

// Make sure this file is only included, not accessed directly
if (!defined('CART_SYNC_INCLUDED')) {
    define('CART_SYNC_INCLUDED', true);
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // No user logged in, nothing to sync
    return;
}

// ===========================================
// SECTION 2: LOAD REQUIRED FILES
// ===========================================

// Make sure we have database connection
if (!isset($pdo)) {
    require_once __DIR__ . '/db-connect.php';
}

// Make sure we have cart functions
if (!function_exists('syncCartOnLogin')) {
    require_once __DIR__ . '/cart-functions.php';
}

// ===========================================
// SECTION 3: PERFORM CART SYNC
// ===========================================

try {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Call the sync function from cart-functions.php
    $sync_result = syncCartOnLogin($user_id);
    
    // Optional: Set success message (commented out by default)
    // Uncomment if you want to show a message about synced items
    /*
    if ($sync_result && !empty($_SESSION['cart'])) {
        $_SESSION['cart_synced'] = true;
        $_SESSION['info'] = "Your cart has been restored!";
    }
    */
    
} catch (Exception $e) {
    // If sync fails, log error but don't break the login process
    error_log("Cart sync failed for user {$user_id}: " . $e->getMessage());
    
    // Don't show error to user, they can still use the site
    // Cart will just remain in session until next login attempt
}

// ===========================================
// SECTION 4: CLEANUP
// ===========================================

// Cart sync is complete!
// The syncCartOnLogin() function already cleared the session cart
// User's cart is now loaded from database

?>