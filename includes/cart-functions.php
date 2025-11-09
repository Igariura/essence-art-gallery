<?php
// ===========================================
// CART HELPER FUNCTIONS
// ===========================================
// This file contains reusable functions for managing the shopping cart
// Store this in: includes/cart-functions.php

// ===========================================
// FUNCTION 1: Initialize Cart
// ===========================================
// Makes sure cart exists in session

function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// ===========================================
// FUNCTION 2: Add Item to Cart
// ===========================================
// Adds an artwork to the cart or increases quantity if already exists

function addToCart($artwork_id, $title, $price, $image, $quantity = 1) {
    initCart();
    
    // Check if item already in cart
    if (isset($_SESSION['cart'][$artwork_id])) {
        // Item exists - increase quantity
        $_SESSION['cart'][$artwork_id]['quantity'] += $quantity;
    } else {
        // New item - add to cart
        $_SESSION['cart'][$artwork_id] = [
            'id' => $artwork_id,
            'title' => $title,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        ];
    }
    
    return true;
}

// ===========================================
// FUNCTION 3: Remove Item from Cart
// ===========================================
// Removes an artwork from the cart completely

function removeFromCart($artwork_id) {
    initCart();
    
    if (isset($_SESSION['cart'][$artwork_id])) {
        unset($_SESSION['cart'][$artwork_id]);
        return true;
    }
    
    return false;
}

// ===========================================
// FUNCTION 4: Update Item Quantity
// ===========================================
// Changes the quantity of an item in cart

function updateCartQuantity($artwork_id, $quantity) {
    initCart();
    
    // If quantity is 0 or less, remove item
    if ($quantity <= 0) {
        return removeFromCart($artwork_id);
    }
    
    // Update quantity
    if (isset($_SESSION['cart'][$artwork_id])) {
        $_SESSION['cart'][$artwork_id]['quantity'] = $quantity;
        return true;
    }
    
    return false;
}

// ===========================================
// FUNCTION 5: Get Cart Items
// ===========================================
// Returns all items in cart

function getCartItems() {
    initCart();
    return $_SESSION['cart'];
}

// ===========================================
// FUNCTION 6: Get Cart Count
// ===========================================
// Returns total number of items in cart (sum of quantities)

function getCartCount() {
    initCart();
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

// ===========================================
// FUNCTION 7: Get Cart Total
// ===========================================
// Returns total price of all items in cart

function getCartTotal() {
    initCart();
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += ($item['price'] * $item['quantity']);
    }
    
    return $total;
}

// ===========================================
// FUNCTION 8: Clear Cart
// ===========================================
// Empties the entire cart (useful after checkout)

function clearCart() {
    $_SESSION['cart'] = [];
    return true;
}

// ===========================================
// FUNCTION 9: Check if Item in Cart
// ===========================================
// Returns true if artwork is already in cart

function isInCart($artwork_id) {
    initCart();
    return isset($_SESSION['cart'][$artwork_id]);
}

// ===========================================
// FUNCTION 10: Get Item Quantity
// ===========================================
// Returns quantity of specific item in cart

function getItemQuantity($artwork_id) {
    initCart();
    
    if (isset($_SESSION['cart'][$artwork_id])) {
        return $_SESSION['cart'][$artwork_id]['quantity'];
    }
    
    return 0;
}

?>