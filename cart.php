<?php
// ===========================================
// SECTION 1: SETUP & HANDLE ACTIONS
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// ===========================================
// SECTION 2: HANDLE CART ACTIONS
// ===========================================

// Handle Remove Item
if (isset($_GET['remove'])) {
    $artwork_id = intval($_GET['remove']);
    removeFromCart($artwork_id);
    $_SESSION['success'] = "Item removed from cart";
    header("Location: cart.php");
    exit;
}

// Handle Update Quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $artwork_id => $quantity) {
        $artwork_id = intval($artwork_id);
        $quantity = intval($quantity);
        updateCartQuantity($artwork_id, $quantity);
    }
    $_SESSION['success'] = "Cart updated successfully";
    header("Location: cart.php");
    exit;
}

// Handle Clear Cart
if (isset($_GET['clear'])) {
    clearCart();
    $_SESSION['success'] = "Cart cleared";
    header("Location: cart.php");
    exit;
}

// ===========================================
// SECTION 3: GET CART ITEMS
// ===========================================

$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = getCartCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Essence of Art</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* ===========================================
           SECTION 4: STYLING
           =========================================== */
        
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-color: var(--light-bg);
        }
        
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }
        
        /* Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            color: #333 !important;
            font-weight: 500;
            margin: 0 15px;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
        }
        
        /* Cart Section */
        .cart-section {
            padding: 40px 0;
        }
        
        .cart-table {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .item-price {
            font-size: 1.2rem;
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .quantity-input {
            width: 80px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }
        
        .item-subtotal {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
            min-width: 150px;
            text-align: right;
        }
        
        .btn-remove {
            background-color: var(--secondary-color);
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        
        .btn-remove:hover {
            background-color: #c0392b;
            color: white;
        }
        
        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1.1rem;
        }
        
        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            margin-top: 15px;
            border-top: 2px solid #eee;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        .btn-checkout {
            width: 100%;
            background-color: var(--secondary-color);
            color: white;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        
        .btn-checkout:hover {
            background-color: #c0392b;
        }
        
        .btn-continue {
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .btn-continue:hover {
            background-color: #1a252f;
            color: white;
        }
        
        .btn-update {
            background-color: var(--accent-color);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        
        .btn-update:hover {
            background-color: #e67e22;
        }
        
        .btn-clear {
            background-color: #6c757d;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-clear:hover {
            background-color: #5a6268;
            color: white;
        }
        
        /* Empty Cart */
        .empty-cart {
            background: white;
            border-radius: 10px;
            padding: 60px 30px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .empty-cart h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .empty-cart p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
        }
        
        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 30px;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 50px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .item-quantity {
                justify-content: center;
                margin: 15px 0;
            }
            
            .item-subtotal {
                text-align: center;
                margin-top: 10px;
            }
            
            .cart-summary {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>

    <!-- ===========================================
         SECTION 5: NAVIGATION BAR
         =========================================== -->
    
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎨 Essence of Art</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gallery.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">🛒 Cart (<?= $cart_count ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===========================================
         SECTION 6: PAGE HEADER
         =========================================== -->
    
    <section class="page-header">
        <div class="container">
            <h1>🛒 Shopping Cart</h1>
        </div>
    </section>

    <!-- ===========================================
         SECTION 7: CART CONTENT
         =========================================== -->
    
    <section class="cart-section">
        <div class="container">
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (empty($cart_items)): ?>
                
                <!-- Empty Cart -->
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h2>Your cart is empty</h2>
                    <p>Looks like you haven't added any artworks to your cart yet.</p>
                    <a href="gallery.php" class="btn-checkout">Browse Gallery</a>
                </div>
                
            <?php else: ?>
                
                <div class="row">
                    
                    <!-- Cart Items (Left Column) -->
                    <div class="col-lg-8">
                        <div class="cart-table">
                            <h2 class="summary-title">Cart Items (<?= $cart_count ?>)</h2>
                            
                            <form method="POST" action="cart.php">
                                
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="cart-item">
                                        
                                        <!-- Item Image -->
                                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                                             alt="<?= htmlspecialchars($item['title']) ?>" 
                                             class="item-image">
                                        
                                        <!-- Item Details -->
                                        <div class="item-details">
                                            <div class="item-title">
                                                <?= htmlspecialchars($item['title']) ?>
                                            </div>
                                            <div class="item-price">
                                                KES <?= number_format($item['price'], 2) ?>
                                            </div>
                                            
                                            <!-- Quantity Input -->
                                            <div class="item-quantity mt-3">
                                                <label>Quantity:</label>
                                                <input type="number" 
                                                       name="quantity[<?= $item['id'] ?>]" 
                                                       value="<?= $item['quantity'] ?>" 
                                                       min="1" 
                                                       max="10"
                                                       class="quantity-input">
                                                
                                                <a href="cart.php?remove=<?= $item['id'] ?>" 
                                                   class="btn-remove"
                                                   onclick="return confirm('Remove this item from cart?')">
                                                    Remove
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Item Subtotal -->
                                        <div class="item-subtotal">
                                            KES <?= number_format($item['price'] * $item['quantity'], 2) ?>
                                        </div>
                                        
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Update Cart Button -->
                                <div class="mt-4">
                                    <button type="submit" name="update_cart" class="btn-update">
                                        Update Cart
                                    </button>
                                    <a href="cart.php?clear=1" 
                                       class="btn-clear ms-2"
                                       onclick="return confirm('Clear entire cart?')">
                                        Clear Cart
                                    </a>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                    
                    <!-- Cart Summary (Right Column) -->
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h3 class="summary-title">Order Summary</h3>
                            
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>KES <?= number_format($cart_total, 2) ?></span>
                            </div>
                            
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>Calculated at checkout</span>
                            </div>
                            
                            <div class="summary-total">
                                <span>Total:</span>
                                <span>KES <?= number_format($cart_total, 2) ?></span>
                            </div>
                            
                            <button class="btn-checkout" onclick="alert('Checkout functionality coming soon!')">
                                Proceed to Checkout
                            </button>
                            
                            <a href="gallery.php" class="btn-continue">
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                    
                </div>
                
            <?php endif; ?>
            
        </div>
    </section>

    <!-- ===========================================
         SECTION 8: FOOTER
         =========================================== -->
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Essence of Art Gallery. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>