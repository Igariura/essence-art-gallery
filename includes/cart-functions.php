<?php
// ===========================================
// DATABASE-BACKED CART FUNCTIONS
// ===========================================
// This file manages shopping cart with database persistence for logged-in users
// and session storage for guest users
// Store this in: includes/cart-functions.php

// Require database connection
require_once __DIR__ . '/db-connect.php';

// ===========================================
// HELPER: Check if User is Logged In
// ===========================================

function isUserLoggedIn() {
    // Returns true if user is logged in, false otherwise
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ===========================================
// FUNCTION 1: Initialize Cart
// ===========================================
// Makes sure cart exists in session (for guests)

function initCart() {
    // If cart doesn't exist in session, create it
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

// ===========================================
// FUNCTION 2: Add Item to Cart (DATABASE + SESSION)
// ===========================================
// Adds artwork to database if logged in, or session if guest

function addToCart($artwork_id, $title, $price, $image, $quantity = 1) {
    global $pdo;
    
    // Check if user is logged in
    if (isUserLoggedIn()) {
        // USER IS LOGGED IN - Save to database
        
        $user_id = $_SESSION['user_id'];
        
        try {
            // Check if item already exists in database cart
            $check_sql = "SELECT Quantity FROM Cart WHERE UserID = :user_id AND ArtworkID = :artwork_id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([
                ':user_id' => $user_id,
                ':artwork_id' => $artwork_id
            ]);
            
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                // Item EXISTS - Update quantity (add to existing)
                $new_quantity = $existing['Quantity'] + $quantity;
                
                $update_sql = "UPDATE Cart 
                              SET Quantity = :quantity 
                              WHERE UserID = :user_id AND ArtworkID = :artwork_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    ':quantity' => $new_quantity,
                    ':user_id' => $user_id,
                    ':artwork_id' => $artwork_id
                ]);
            } else {
                // Item DOESN'T EXIST - Insert new record
                $insert_sql = "INSERT INTO Cart (UserID, ArtworkID, Quantity, DateAdded) 
                              VALUES (:user_id, :artwork_id, :quantity, NOW())";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    ':user_id' => $user_id,
                    ':artwork_id' => $artwork_id,
                    ':quantity' => $quantity
                ]);
            }
            
            return true;
            
        } catch (PDOException $e) {
            // If database fails, log error
            error_log("Cart Error: " . $e->getMessage());
            return false;
        }
        
    } else {
        // USER IS GUEST - Save to session
        
        initCart();
        
        // Check if item already in session cart
        if (isset($_SESSION['cart'][$artwork_id])) {
            // Item exists - increase quantity
            $_SESSION['cart'][$artwork_id]['quantity'] += $quantity;
        } else {
            // New item - add to session cart
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
}

// ===========================================
// FUNCTION 3: Remove Item from Cart
// ===========================================
// Removes artwork from database or session

function removeFromCart($artwork_id) {
    global $pdo;
    
    if (isUserLoggedIn()) {
        // USER IS LOGGED IN - Delete from database
        
        $user_id = $_SESSION['user_id'];
        
        try {
            $delete_sql = "DELETE FROM Cart WHERE UserID = :user_id AND ArtworkID = :artwork_id";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([
                ':user_id' => $user_id,
                ':artwork_id' => $artwork_id
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Cart Error: " . $e->getMessage());
            return false;
        }
        
    } else {
        // USER IS GUEST - Remove from session
        
        initCart();
        
        if (isset($_SESSION['cart'][$artwork_id])) {
            unset($_SESSION['cart'][$artwork_id]);
            return true;
        }
        
        return false;
    }
}

// ===========================================
// FUNCTION 4: Update Item Quantity
// ===========================================
// Changes quantity in database or session

function updateCartQuantity($artwork_id, $quantity) {
    global $pdo;
    
    // If quantity is 0 or less, remove item
    if ($quantity <= 0) {
        return removeFromCart($artwork_id);
    }
    
    if (isUserLoggedIn()) {
        // USER IS LOGGED IN - Update database
        
        $user_id = $_SESSION['user_id'];
        
        try {
            $update_sql = "UPDATE Cart 
                          SET Quantity = :quantity 
                          WHERE UserID = :user_id AND ArtworkID = :artwork_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([
                ':quantity' => $quantity,
                ':user_id' => $user_id,
                ':artwork_id' => $artwork_id
            ]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Cart Error: " . $e->getMessage());
            return false;
        }
        
    } else {
        // USER IS GUEST - Update session
        
        initCart();
        
        if (isset($_SESSION['cart'][$artwork_id])) {
            $_SESSION['cart'][$artwork_id]['quantity'] = $quantity;
            return true;
        }
        
        return false;
    }
}

// ===========================================
// FUNCTION 5: Get Cart Items
// ===========================================
// Returns all items from database or session

function getCartItems() {
    global $pdo;
    
    if (isUserLoggedIn()) {
        // USER IS LOGGED IN - Get from database
        
        $user_id = $_SESSION['user_id'];
        
        try {
            // Join with Artworks table to get current artwork details
            $sql = "SELECT 
                        c.CartID,
                        c.ArtworkID as id,
                        c.Quantity as quantity,
                        a.Title as title,
                        a.Price as price,
                        a.MainImageURL as image
                    FROM Cart c
                    JOIN Artworks a ON c.ArtworkID = a.ArtworkID
                    WHERE c.UserID = :user_id
                    ORDER BY c.DateAdded DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convert to same format as session cart (keyed by artwork_id)
            $cart = [];
            foreach ($items as $item) {
                $cart[$item['id']] = $item;
            }
            
            return $cart;
            
        } catch (PDOException $e) {
            error_log("Cart Error: " . $e->getMessage());
            return [];
        }
        
    } else {
        // USER IS GUEST - Get from session
        
        initCart();
        return $_SESSION['cart'];
    }
}

// ===========================================
// FUNCTION 6: Get Cart Count
// ===========================================
// Returns total number of items (sum of quantities)

function getCartCount() {
    $items = getCartItems();
    
    $count = 0;
    foreach ($items as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

// ===========================================
// FUNCTION 7: Get Cart Total
// ===========================================
// Returns total price of all items

function getCartTotal() {
    $items = getCartItems();
    
    $total = 0;
    foreach ($items as $item) {
        $total += ($item['price'] * $item['quantity']);
    }
    
    return $total;
}

// ===========================================
// FUNCTION 8: Clear Cart
// ===========================================
// Empties entire cart (useful after checkout)

function clearCart() {
    global $pdo;
    
    if (isUserLoggedIn()) {
        // USER IS LOGGED IN - Clear database
        
        $user_id = $_SESSION['user_id'];
        
        try {
            $delete_sql = "DELETE FROM Cart WHERE UserID = :user_id";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([':user_id' => $user_id]);
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Cart Error: " . $e->getMessage());
            return false;
        }
        
    } else {
        // USER IS GUEST - Clear session
        
        $_SESSION['cart'] = [];
        return true;
    }
}

// ===========================================
// FUNCTION 9: Check if Item in Cart
// ===========================================
// Returns true if artwork is in cart

function isInCart($artwork_id) {
    $items = getCartItems();
    return isset($items[$artwork_id]);
}

// ===========================================
// FUNCTION 10: Get Item Quantity
// ===========================================
// Returns quantity of specific item

function getItemQuantity($artwork_id) {
    $items = getCartItems();
    
    if (isset($items[$artwork_id])) {
        return $items[$artwork_id]['quantity'];
    }
    
    return 0;
}

// ===========================================
// NEW FUNCTION 11: Sync Cart on Login
// ===========================================
// Merges session cart into database when user logs in
// Call this function AFTER successful login

function syncCartOnLogin($user_id) {
    global $pdo;
    
    initCart();
    
    // If session cart is empty, nothing to sync
    if (empty($_SESSION['cart'])) {
        return true;
    }
    
    try {
        // Loop through each item in session cart
        foreach ($_SESSION['cart'] as $artwork_id => $item) {
            
            // Check if item already exists in database cart
            $check_sql = "SELECT Quantity FROM Cart WHERE UserID = :user_id AND ArtworkID = :artwork_id";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([
                ':user_id' => $user_id,
                ':artwork_id' => $artwork_id
            ]);
            
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                // Item already in database - ADD quantities together
                $new_quantity = $existing['Quantity'] + $item['quantity'];
                
                $update_sql = "UPDATE Cart 
                              SET Quantity = :quantity 
                              WHERE UserID = :user_id AND ArtworkID = :artwork_id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    ':quantity' => $new_quantity,
                    ':user_id' => $user_id,
                    ':artwork_id' => $artwork_id
                ]);
            } else {
                // Item NOT in database - Insert it
                $insert_sql = "INSERT INTO Cart (UserID, ArtworkID, Quantity, DateAdded) 
                              VALUES (:user_id, :artwork_id, :quantity, NOW())";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    ':user_id' => $user_id,
                    ':artwork_id' => $artwork_id,
                    ':quantity' => $item['quantity']
                ]);
            }
        }
        
        // Clear session cart after syncing
        $_SESSION['cart'] = [];
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Cart Sync Error: " . $e->getMessage());
        return false;
    }
}

?>