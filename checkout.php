<?php
// ===========================================
// SECTION 1: SETUP & VALIDATION
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Get cart items and total
$cart_items = getCartItems();
$cart_total = getCartTotal();
$cart_count = getCartCount();

// Redirect if cart is empty
if (empty($cart_items)) {
    $_SESSION['error'] = "Your cart is empty. Add items before checking out.";
    header("Location: cart.php");
    exit;
}

// ===========================================
// SECTION 2: PROCESS CHECKOUT FORM
// ===========================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $shipping_address = trim($_POST['shipping_address']);
    $notes = trim($_POST['notes']);
    
    // Validation
    $errors = [];
    
    if (empty($customer_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($customer_phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required";
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert into Orders table
            $order_sql = "INSERT INTO Orders (
                            CustomerName, 
                            CustomerEmail, 
                            CustomerPhone, 
                            ShippingAddress, 
                            OrderTotal, 
                            OrderStatus,
                            Notes
                          ) VALUES (
                            :name, 
                            :email, 
                            :phone, 
                            :address, 
                            :total, 
                            'Pending',
                            :notes
                          )";
            
            $order_stmt = $pdo->prepare($order_sql);
            $order_stmt->execute([
                ':name' => $customer_name,
                ':email' => $customer_email,
                ':phone' => $customer_phone,
                ':address' => $shipping_address,
                ':total' => $cart_total,
                ':notes' => $notes
            ]);
            
            // Get the OrderID that was just created
            $order_id = $pdo->lastInsertId();
            
            // Insert each cart item into OrderItems table
            $item_sql = "INSERT INTO OrderItems (
                            OrderID, 
                            ArtworkID, 
                            ArtworkTitle, 
                            Price, 
                            Quantity, 
                            Subtotal
                         ) VALUES (
                            :order_id, 
                            :artwork_id, 
                            :title, 
                            :price, 
                            :quantity, 
                            :subtotal
                         )";
            
            $item_stmt = $pdo->prepare($item_sql);
            
            foreach ($cart_items as $item) {
                $item_stmt->execute([
                    ':order_id' => $order_id,
                    ':artwork_id' => $item['id'],
                    ':title' => $item['title'],
                    ':price' => $item['price'],
                    ':quantity' => $item['quantity'],
                    ':subtotal' => ($item['price'] * $item['quantity'])
                ]);
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Clear the cart
            clearCart();
            
            // Store order ID in session for confirmation page
            $_SESSION['order_id'] = $order_id;
            $_SESSION['success'] = "Order placed successfully!";
            
            // Redirect to confirmation page
            header("Location: order-confirmation.php?order=" . $order_id);
            exit;
            
        } catch (PDOException $e) {
            // Rollback on error
            $pdo->rollBack();
            $errors[] = "Error processing order: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Essence of Art</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* ===========================================
           SECTION 3: STYLING
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
        
        /* Checkout Section */
        .checkout-section {
            padding: 40px 0 60px;
        }
        
        .checkout-form {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
        }
        
        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .summary-details {
            flex: 1;
        }
        
        .summary-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .summary-qty {
            font-size: 0.85rem;
            color: #666;
        }
        
        .summary-price {
            font-weight: 700;
            color: var(--secondary-color);
            white-space: nowrap;
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
        
        .btn-place-order {
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
        
        .btn-place-order:hover {
            background-color: #c0392b;
        }
        
        .btn-back {
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
        
        .btn-back:hover {
            background-color: #1a252f;
            color: white;
        }
        
        .required {
            color: var(--secondary-color);
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
            .page-header h1 {
                font-size: 2rem;
            }
            
            .checkout-form {
                padding: 25px;
            }
            
            .order-summary {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>

    <!-- ===========================================
         SECTION 4: NAVIGATION BAR
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
                        <a class="nav-link" href="cart.php">🛒 Cart (<?= $cart_count ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===========================================
         SECTION 5: PAGE HEADER
         =========================================== -->
    
    <section class="page-header">
        <div class="container">
            <h1>💳 Checkout</h1>
        </div>
    </section>

    <!-- ===========================================
         SECTION 6: CHECKOUT CONTENT
         =========================================== -->
    
    <section class="checkout-section">
        <div class="container">
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                
                <!-- Checkout Form (Left Column) -->
                <div class="col-lg-7">
                    <div class="checkout-form">
                        <h2 class="section-title">Billing & Shipping Information</h2>
                        
                        <form method="POST" action="checkout.php">
                            
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">
                                    Full Name <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="customer_name" 
                                       name="customer_name"
                                       value="<?= isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : '' ?>"
                                       required>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="customer_email" class="form-label">
                                    Email Address <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="customer_email" 
                                       name="customer_email"
                                       value="<?= isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : '' ?>"
                                       required>
                                <small class="text-muted">Order confirmation will be sent to this email</small>
                            </div>
                            
                            <!-- Phone -->
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">
                                    Phone Number <span class="required">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="customer_phone" 
                                       name="customer_phone"
                                       value="<?= isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : '' ?>"
                                       placeholder="e.g., 0712345678"
                                       required>
                            </div>
                            
                            <!-- Shipping Address -->
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">
                                    Shipping Address <span class="required">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="shipping_address" 
                                          name="shipping_address" 
                                          rows="4"
                                          placeholder="Enter your complete address including city and postal code"
                                          required><?= isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : '' ?></textarea>
                            </div>
                            
                            <!-- Order Notes (Optional) -->
                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    Order Notes (Optional)
                                </label>
                                <textarea class="form-control" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3"
                                          placeholder="Any special instructions or notes about your order"><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="btn-place-order">
                                Place Order
                            </button>
                            
                            <a href="cart.php" class="btn-back">
                                ← Back to Cart
                            </a>
                            
                        </form>
                    </div>
                </div>
                
                <!-- Order Summary (Right Column) -->
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h3 class="section-title">Order Summary</h3>
                        
                        <!-- Cart Items -->
                        <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <img src="<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['title']) ?>" 
                                     class="summary-image">
                                
                                <div class="summary-details">
                                    <div class="summary-title">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </div>
                                    <div class="summary-qty">
                                        Qty: <?= $item['quantity'] ?>
                                    </div>
                                </div>
                                
                                <div class="summary-price">
                                    KES <?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Total -->
                        <div class="summary-total">
                            <span>Total:</span>
                            <span>KES <?= number_format($cart_total, 2) ?></span>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <small>
                                <strong>📦 Shipping:</strong> We'll contact you to confirm shipping details and costs.
                            </small>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </section>

    <!-- ===========================================
         SECTION 7: FOOTER
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