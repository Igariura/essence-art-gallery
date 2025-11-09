<?php
// ===========================================
// ADD TO CART HANDLER
// ===========================================
// This file processes "Add to Cart" requests
// Store this in: add-to-cart.php (root folder)

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// ===========================================
// SECTION 1: VALIDATE REQUEST
// ===========================================

// Check if artwork ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid artwork ID";
    header("Location: gallery.php");
    exit;
}

// Get artwork ID and make it safe
$artwork_id = intval($_GET['id']);

// ===========================================
// SECTION 2: FETCH ARTWORK FROM DATABASE
// ===========================================

// Get artwork details
$stmt = $pdo->prepare("SELECT ArtworkID, Title, Price, MainImageURL, IsAvailable 
                       FROM Artworks 
                       WHERE ArtworkID = :id");
$stmt->execute([':id' => $artwork_id]);
$artwork = $stmt->fetch();

// Check if artwork exists and is available
if (!$artwork) {
    $_SESSION['error'] = "Artwork not found";
    header("Location: gallery.php");
    exit;
}

if (!$artwork['IsAvailable']) {
    $_SESSION['error'] = "Sorry, this artwork is no longer available";
    header("Location: artwork-detail.php?id=" . $artwork_id);
    exit;
}

// ===========================================
// SECTION 3: ADD TO CART
// ===========================================

// Check if item already in cart
if (isInCart($artwork_id)) {
    // Already in cart - just increase quantity
    $current_qty = getItemQuantity($artwork_id);
    updateCartQuantity($artwork_id, $current_qty + 1);
    $_SESSION['success'] = "Quantity updated! '{$artwork['Title']}' is now in your cart";
} else {
    // Add new item to cart
    addToCart(
        $artwork['ArtworkID'],
        $artwork['Title'],
        $artwork['Price'],
        $artwork['MainImageURL'],
        1  // Initial quantity
    );
    $_SESSION['success'] = "'{$artwork['Title']}' added to cart!";
}

// ===========================================
// SECTION 4: REDIRECT BACK
// ===========================================

// Determine where to redirect back to
$redirect_to = isset($_GET['redirect']) ? $_GET['redirect'] : 'artwork-detail.php?id=' . $artwork_id;

// Redirect with success message
header("Location: " . $redirect_to);
exit;

?>