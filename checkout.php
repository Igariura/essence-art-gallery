<?php
// ===========================================
// SECTION 1: SETUP & VALIDATION
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Get user info if logged in
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';

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
// SECTION 2: PROCESS NON-MPESA ORDERS
// ===========================================
// M-Pesa orders are now handled by initiate-payment.php via AJAX
// This section only handles Cash on Delivery and Bank Transfer

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['ajax_submit'])) {
    
    // Get form data
    $customer_name = trim($_POST['customer_name']);
    $customer_email = trim($_POST['customer_email']);
    $customer_phone = trim($_POST['customer_phone']);
    $shipping_address = trim($_POST['shipping_address']);
    $shipping_city = trim($_POST['shipping_city']);
    $shipping_area = trim($_POST['shipping_area']);
    $shipping_landmark = trim($_POST['shipping_landmark']);
    $payment_method = $_POST['payment_method'];
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
    
    if (empty($shipping_city)) {
        $errors[] = "City/Town is required";
    }
    
    if (empty($shipping_area)) {
        $errors[] = "Area/Estate is required";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Please select a payment method";
    }
    
    // If no errors, process the order
    if (empty($errors)) {
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Generate unique order number
            $order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Insert into Orders table
            $order_sql = "INSERT INTO Orders (
                            UserID,
                            OrderNumber,
                            CustomerName, 
                            CustomerEmail, 
                            CustomerPhone, 
                            ShippingAddress,
                            ShippingCity,
                            ShippingArea,
                            ShippingLandmark,
                            OrderTotal,
                            PaymentMethod,
                            PaymentStatus,
                            OrderStatus,
                            Notes
                          ) VALUES (
                            :user_id,
                            :order_number,
                            :name, 
                            :email, 
                            :phone, 
                            :address,
                            :city,
                            :area,
                            :landmark,
                            :total,
                            :payment_method,
                            'Pending',
                            'Pending',
                            :notes
                          )";
            
            $order_stmt = $pdo->prepare($order_sql);
            $order_stmt->execute([
                ':user_id' => $user_id,
                ':order_number' => $order_number,
                ':name' => $customer_name,
                ':email' => $customer_email,
                ':phone' => $customer_phone,
                ':address' => $shipping_address,
                ':city' => $shipping_city,
                ':area' => $shipping_area,
                ':landmark' => $shipping_landmark,
                ':total' => $cart_total,
                ':payment_method' => $payment_method,
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
            
            // Store order info in session for confirmation page
            $_SESSION['order_id'] = $order_id;
            $_SESSION['order_number'] = $order_number;
            $_SESSION['success'] = "Order placed successfully!";
            
            // Redirect to confirmation page
            header("Location: order-confirmation.php?order=" . $order_number);
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
        
        /* Payment Method Cards */
        .payment-option {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-option:hover {
            border-color: var(--primary-color);
            background-color: #f8f9fa;
        }
        
        .payment-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-option.selected {
            border-color: var(--primary-color);
            background-color: #e8f4f8;
        }
        
        #mpesaFields {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
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
        
        .btn-place-order:disabled {
            background-color: #ccc;
            cursor: not-allowed;
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
        
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 50px;
        }
        
        /* =========================
           M-PESA PAYMENT MODAL
        ========================= */
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .payment-modal.show {
            display: flex;
        }
        
        .modal-content-payment {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .modal-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .modal-text {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .success-checkmark {
            color: #4caf50;
        }
        
        .error-cross {
            color: var(--secondary-color);
        }
        
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

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎨 Essence of Art</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
           <?php include 'includes/navbar.php'; ?>
        </div>
    </nav>

    <section class="page-header">
        <div class="container">
            <h1>Checkout</h1>
        </div>
    </section>

    <!-- M-PESA PAYMENT MODAL -->
    <div class="payment-modal" id="paymentModal">
        <div class="modal-content-payment">
            <div class="modal-icon" id="modalIcon">📱</div>
            <h3 class="modal-title" id="modalTitle">Processing Payment...</h3>
            <p class="modal-text" id="modalText">Check your phone for M-Pesa prompt</p>
            <div class="spinner" id="modalSpinner"></div>
        </div>
    </div>

    <section class="checkout-section">
        <div class="container">
            
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
                
                <div class="col-lg-7">
                    <div class="checkout-form">
                        <h2 class="section-title">📦 Shipping Information</h2>
                        
                        <form method="POST" action="checkout.php" id="checkoutForm">
                            
                            <!-- Hidden field for total amount -->
                            <input type="hidden" name="total_amount" id="total_amount" value="<?= $cart_total ?>">
                            
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">
                                    Full Name <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="customer_name" 
                                       name="customer_name"
                                       value="<?= isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : htmlspecialchars($user_name) ?>"
                                       required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="customer_email" class="form-label">
                                        Email Address <span class="required">*</span>
                                    </label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="customer_email" 
                                           name="customer_email"
                                           value="<?= isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : htmlspecialchars($user_email) ?>"
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="customer_phone" class="form-label">
                                        Phone Number <span class="required">*</span>
                                    </label>
                                    <input type="tel" 
                                           class="form-control" 
                                           id="customer_phone" 
                                           name="customer_phone"
                                           value="<?= isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : '' ?>"
                                           placeholder="0712345678"
                                           required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="shipping_city" class="form-label">
                                        City/Town <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="shipping_city" 
                                           name="shipping_city"
                                           value="<?= isset($_POST['shipping_city']) ? htmlspecialchars($_POST['shipping_city']) : '' ?>"
                                           placeholder="e.g., Nairobi"
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="shipping_area" class="form-label">
                                        Area/Estate <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="shipping_area" 
                                           name="shipping_area"
                                           value="<?= isset($_POST['shipping_area']) ? htmlspecialchars($_POST['shipping_area']) : '' ?>"
                                           placeholder="e.g., Westlands"
                                           required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">
                                    Street Address <span class="required">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="shipping_address" 
                                          name="shipping_address" 
                                          rows="2"
                                          placeholder="Building name, floor, apartment number"
                                          required><?= isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : '' ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="shipping_landmark" class="form-label">
                                    Landmark (Optional)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="shipping_landmark" 
                                       name="shipping_landmark"
                                       value="<?= isset($_POST['shipping_landmark']) ? htmlspecialchars($_POST['shipping_landmark']) : '' ?>"
                                       placeholder="e.g., Near Sarit Centre, Opposite KFC">
                                <small class="text-muted">Helps delivery person find you easily</small>
                            </div>
                            
                            <h2 class="section-title mt-4">💳 Payment Method</h2>
                            
                            <div class="payment-option" onclick="selectPayment('mpesa')">
                                <input type="radio" name="payment_method" value="M-Pesa" id="payment_mpesa" required>
                                <label for="payment_mpesa" style="cursor: pointer;">
                                    <strong>M-Pesa</strong> - Pay via M-Pesa (Instant Payment)
                                </label>
                            </div>
                            
                            <div id="mpesaFields">
                                <label for="mpesa_phone" class="form-label">
                                    M-Pesa Phone Number <span class="required">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="mpesa_phone" 
                                       name="mpesa_phone"
                                       placeholder="0712345678">
                                <small class="text-muted">📱 You'll receive a payment prompt on this number</small>
                            </div>
                            
                            <div class="payment-option" onclick="selectPayment('cod')">
                                <input type="radio" name="payment_method" value="Cash on Delivery" id="payment_cod">
                                <label for="payment_cod" style="cursor: pointer;">
                                    <strong>Cash on Delivery</strong> - Pay when you receive
                                </label>
                            </div>
                            
                            <div class="payment-option" onclick="selectPayment('bank')">
                                <input type="radio" name="payment_method" value="Bank Transfer" id="payment_bank">
                                <label for="payment_bank" style="cursor: pointer;">
                                    <strong>Bank Transfer</strong> - Transfer to our account
                                </label>
                            </div>
                            
                            <div class="mb-4 mt-3">
                                <label for="notes" class="form-label">
                                    Order Notes (Optional)
                                </label>
                                <textarea class="form-control" 
                                          id="notes" 
                                          name="notes" 
                                          rows="2"
                                          placeholder="Any special instructions"><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn-place-order" id="submitBtn">
                                Place Order - KES <?= number_format($cart_total, 2) ?>
                            </button>
                            
                            <a href="cart.php" class="btn-back">
                                ← Back to Cart
                            </a>
                            
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-5">
                    <div class="order-summary">
                        <h3 class="section-title">Order Summary</h3>
                        
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
                        
                        <div class="summary-total">
                            <span>Total:</span>
                            <span>KES <?= number_format($cart_total, 2) ?></span>
                        </div>
                        
                        <div class="alert alert-success mt-3">
                            <small>
                                <strong>✅ Free Shipping</strong> on all orders!
                            </small>
                        </div>
                    </div>
                </div>
                
            </div>
            
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Essence of Art Gallery. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // ===========================================
    // PAYMENT METHOD SELECTION
    // ===========================================
    function selectPayment(method) {
        // Remove selected class from all
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        
        // Add selected class to clicked option
        event.currentTarget.classList.add('selected');
        
        // Check the radio button
        const radio = event.currentTarget.querySelector('input[type="radio"]');
        radio.checked = true;
        
        // Show/hide M-Pesa fields
        const mpesaFields = document.getElementById('mpesaFields');
        if (method === 'mpesa') {
            mpesaFields.style.display = 'block';
            document.getElementById('mpesa_phone').required = true;
        } else {
            mpesaFields.style.display = 'none';
            document.getElementById('mpesa_phone').required = false;
        }
    }
    
    // ===========================================
    // FORM SUBMISSION WITH M-PESA INTEGRATION
    // ===========================================
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const paymentMethod = formData.get('payment_method');
        const submitBtn = document.getElementById('submitBtn');
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
        
        // If M-Pesa selected, use AJAX to initiate payment
        if (paymentMethod === 'M-Pesa') {
            initiateMpesaPayment(formData);
        } else {
            // For other payment methods, submit form normally
            this.submit();
        }
    });
    
    // ===========================================
    // INITIATE M-PESA PAYMENT
    // ===========================================
    function initiateMpesaPayment(formData) {
        // Show payment modal
        showModal('📱', 'Sending Payment Request...', 'Please wait...');
        
        // Send data to initiate-payment.php
        fetch('initiate-payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // STK Push sent successfully
                showModal('📱', 'Check Your Phone!', 'Enter your M-Pesa PIN to complete payment');
                
                // Start checking payment status
                const orderId = data.order_id;
                checkPaymentStatus(orderId);
                
            } else {
                // Failed to send STK Push
                showModal('❌', 'Payment Failed', data.message, true);
                
                // Re-enable submit button after 3 seconds
                setTimeout(() => {
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('submitBtn').textContent = 'Place Order - KES <?= number_format($cart_total, 2) ?>';
                    hideModal();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModal('❌', 'Error', 'Something went wrong. Please try again.', true);
            
            setTimeout(() => {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').textContent = 'Place Order - KES <?= number_format($cart_total, 2) ?>';
                hideModal();
            }, 3000);
        });
    }
    
    // ===========================================
    // CHECK PAYMENT STATUS (Polls every 3 seconds)
    // ===========================================
    let statusCheckInterval;
    let statusCheckCount = 0;
    const MAX_STATUS_CHECKS = 40; // 40 checks × 3 seconds = 2 minutes max
    
    function checkPaymentStatus(orderId) {
        statusCheckCount = 0;
        
        statusCheckInterval = setInterval(() => {
            statusCheckCount++;
            
            // Stop after 2 minutes
            if (statusCheckCount >= MAX_STATUS_CHECKS) {
                clearInterval(statusCheckInterval);
                showModal('⏱️', 'Payment Timeout', 'Payment is taking longer than expected. Please check your M-Pesa messages.', true);
                
                setTimeout(() => {
                    window.location.href = 'orders.php';
                }, 3000);
                return;
            }
            
            // Check status via AJAX
            fetch('api/check-payment-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (data.payment_complete) {
                    // Payment successful!
                    clearInterval(statusCheckInterval);
                    showModal('✅', 'Payment Successful!', 'Receipt: ' + data.receipt, false);
                    
                    // Redirect to confirmation page
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 2000);
                    
                } else if (data.payment_failed) {
                    // Payment failed
                    clearInterval(statusCheckInterval);
                    showModal('❌', 'Payment Failed', data.message || 'Transaction was cancelled or failed', true);
                    
                    setTimeout(() => {
                        document.getElementById('submitBtn').disabled = false;
                        document.getElementById('submitBtn').textContent = 'Place Order - KES <?= number_format($cart_total, 2) ?>';
                        hideModal();
                    }, 3000);
                }
                // If still pending, continue checking
            })
            .catch(error => {
                console.error('Status check error:', error);
            });
            
        }, 3000); // Check every 3 seconds
    }
    
    // ===========================================
    // MODAL FUNCTIONS
    // ===========================================
    function showModal(icon, title, text, hideSpinner = false) {
        const modal = document.getElementById('paymentModal');
        const modalIcon = document.getElementById('modalIcon');
        const modalTitle = document.getElementById('modalTitle');
        const modalText = document.getElementById('modalText');
        const modalSpinner = document.getElementById('modalSpinner');
        
        modalIcon.textContent = icon;
        modalTitle.textContent = title;
        modalText.textContent = text;
        
        if (hideSpinner) {
            modalSpinner.style.display = 'none';
        } else {
            modalSpinner.style.display = 'block';
        }
        
        modal.classList.add('show');
    }
    
    function hideModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('show');
    }
    </script>

</body>
</html>