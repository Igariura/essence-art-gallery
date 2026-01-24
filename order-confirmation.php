<?php
// ===========================================
// SECTION 1: SETUP & VALIDATION
// ===========================================

// Start session
session_start();

// Include database connection
require_once 'includes/db-connect.php';

// Check if order parameter exists in URL
if (!isset($_GET['order']) || empty($_GET['order'])) {
    // No order number provided - redirect to homepage
    header("Location: index.php");
    exit;
}

// Get order number from URL
$order_number = $_GET['order'];

// ===========================================
// SECTION 2: FETCH ORDER DETAILS
// ===========================================

try {
    // Fetch order information from database
    $order_sql = "SELECT 
                    o.OrderID,
                    o.OrderNumber,
                    o.CustomerName,
                    o.CustomerEmail,
                    o.CustomerPhone,
                    o.ShippingAddress,
                    o.ShippingCity,
                    o.ShippingArea,
                    o.ShippingLandmark,
                    o.OrderTotal,
                    o.PaymentMethod,
                    o.PaymentStatus,
                    o.OrderStatus,
                    o.OrderDate,
                    o.Notes
                  FROM Orders o
                  WHERE o.OrderNumber = :order_number
                  LIMIT 1";
    
    $order_stmt = $pdo->prepare($order_sql);
    $order_stmt->execute([':order_number' => $order_number]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);
    
    // If order doesn't exist, redirect to homepage
    if (!$order) {
        $_SESSION['error'] = "Order not found";
        header("Location: index.php");
        exit;
    }
    
    // Fetch order items
    $items_sql = "SELECT 
                    oi.ArtworkID,
                    oi.ArtworkTitle,
                    oi.Price,
                    oi.Quantity,
                    oi.Subtotal,
                    a.MainImageURL
                  FROM OrderItems oi
                  LEFT JOIN Artworks a ON oi.ArtworkID = a.ArtworkID
                  WHERE oi.OrderID = :order_id";
    
    $items_stmt = $pdo->prepare($items_sql);
    $items_stmt->execute([':order_id' => $order['OrderID']]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // If database error, show error message
    $_SESSION['error'] = "Error retrieving order details";
    header("Location: index.php");
    exit;
}

// ===========================================
// SECTION 3: EXTRACT M-PESA PHONE FROM NOTES
// ===========================================

// Initialize M-Pesa phone variable
$mpesa_phone = '';

// If payment method is M-Pesa, extract phone from notes
if ($order['PaymentMethod'] == 'M-Pesa' && !empty($order['Notes'])) {
    // Check if notes contain "M-Pesa Phone:"
    if (preg_match('/M-Pesa Phone:\s*(\d+)/', $order['Notes'], $matches)) {
        // Extract the phone number
        $mpesa_phone = $matches[1];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - <?= htmlspecialchars($order['OrderNumber']) ?></title>
    
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
            --success-color: #27ae60;
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
        
        /* Success Section */
        .success-section {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .success-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .success-section p {
            font-size: 1.2rem;
            opacity: 0.95;
        }
        
        /* Order Details Section */
        .order-details-section {
            padding: 50px 0;
        }
        
        .details-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        .order-number-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .order-number-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .order-number-value {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            text-align: right;
        }
        
        /* Order Items */
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .item-qty {
            font-size: 0.9rem;
            color: #666;
        }
        
        .item-price {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--secondary-color);
            white-space: nowrap;
        }
        
        .order-total-box {
            background: var(--light-bg);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
        }
        
        /* Payment Instructions */
        .payment-instructions {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .payment-instructions h4 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .payment-instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .payment-instructions li {
            margin-bottom: 10px;
            color: #856404;
        }
        
        .mpesa-number {
            background: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.3rem;
            font-weight: 700;
            text-align: center;
            color: var(--success-color);
            margin: 15px 0;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-primary-custom {
            flex: 1;
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .btn-primary-custom:hover {
            background-color: #1a252f;
            color: white;
        }
        
        .btn-secondary-custom {
            flex: 1;
            background-color: var(--secondary-color);
            color: white;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .btn-secondary-custom:hover {
            background-color: #c0392b;
            color: white;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .status-shipped {
            background-color: #d1e7dd;
            color: #0f5132;
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
            .success-section h1 {
                font-size: 2rem;
            }
            
            .order-number-value {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .details-card {
                padding: 20px;
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
            
            <?php include 'includes/navbar.php'; ?>
        </div>
    </nav>

    <!-- ===========================================
         SECTION 6: SUCCESS HEADER
         =========================================== -->
    
    <section class="success-section">
        <div class="container">
            <div class="success-icon">✅</div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your purchase. We've received your order.</p>
        </div>
    </section>

    <!-- ===========================================
         SECTION 7: ORDER DETAILS
         =========================================== -->
    
    <section class="order-details-section">
        <div class="container">
            
            <!-- Order Number Box -->
            <div class="order-number-box">
                <div class="order-number-label">Your Order Number</div>
                <div class="order-number-value"><?= htmlspecialchars($order['OrderNumber']) ?></div>
                <small>Save this number for your records</small>
            </div>
            
            <div class="row">
                
                <!-- Left Column: Order Info & Payment -->
                <div class="col-lg-6">
                    
                    <!-- Order Information -->
                    <div class="details-card">
                        <h3 class="section-title">📋 Order Information</h3>
                        
                        <div class="info-row">
                            <span class="info-label">Order Date:</span>
                            <span class="info-value">
                                <?= date('F j, Y, g:i a', strtotime($order['OrderDate'])) ?>
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Order Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-<?= strtolower($order['OrderStatus']) ?>">
                                    <?= htmlspecialchars($order['OrderStatus']) ?>
                                </span>
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value"><?= htmlspecialchars($order['PaymentMethod']) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Payment Status:</span>
                            <span class="info-value">
                                <span class="status-badge status-<?= strtolower($order['PaymentStatus']) ?>">
                                    <?= htmlspecialchars($order['PaymentStatus']) ?>
                                </span>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Payment Instructions -->
                    <?php if ($order['PaymentMethod'] == 'M-Pesa'): ?>
                        <div class="payment-instructions">
                            <h4>💳 M-Pesa Payment Instructions</h4>
                            <p><strong>Complete your payment using these steps:</strong></p>
                            <ol>
                                <li>Go to M-Pesa on your phone</li>
                                <li>Select Lipa Na M-Pesa → Paybill</li>
                                <li>Enter Business Number: <strong>123456</strong></li>
                                <li>Account Number: <strong><?= htmlspecialchars($order['OrderNumber']) ?></strong></li>
                                <li>Amount: <strong>KES <?= number_format($order['OrderTotal'], 2) ?></strong></li>
                                <li>Enter your M-Pesa PIN and confirm</li>
                            </ol>
                            <?php if ($mpesa_phone): ?>
                                <div class="alert alert-info mt-3">
                                    <small>We'll send payment prompt to: <strong><?= htmlspecialchars($mpesa_phone) ?></strong></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($order['PaymentMethod'] == 'Cash on Delivery'): ?>
                        <div class="payment-instructions">
                            <h4>💵 Cash on Delivery</h4>
                            <p><strong>Payment Details:</strong></p>
                            <ul>
                                <li>You'll pay when your order arrives</li>
                                <li>Amount to pay: <strong>KES <?= number_format($order['OrderTotal'], 2) ?></strong></li>
                                <li>Please have exact cash ready</li>
                                <li>Our delivery person will confirm your order</li>
                            </ul>
                        </div>
                    <?php elseif ($order['PaymentMethod'] == 'Bank Transfer'): ?>
                        <div class="payment-instructions">
                            <h4>🏦 Bank Transfer Instructions</h4>
                            <p><strong>Transfer to this account:</strong></p>
                            <div class="mpesa-number">
                                Bank: ABC Bank<br>
                                Account: 1234567890<br>
                                Amount: KES <?= number_format($order['OrderTotal'], 2) ?>
                            </div>
                            <p><small>Reference: <?= htmlspecialchars($order['OrderNumber']) ?></small></p>
                            <p>After transfer, send confirmation to: <strong>payments@essenceofart.com</strong></p>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Right Column: Shipping & Items -->
                <div class="col-lg-6">
                    
                    <!-- Shipping Address -->
                    <div class="details-card">
                        <h3 class="section-title">📦 Shipping Address</h3>
                        
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?= htmlspecialchars($order['CustomerName']) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?= htmlspecialchars($order['CustomerPhone']) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?= htmlspecialchars($order['CustomerEmail']) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">City:</span>
                            <span class="info-value"><?= htmlspecialchars($order['ShippingCity']) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Area:</span>
                            <span class="info-value"><?= htmlspecialchars($order['ShippingArea']) ?></span>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?= htmlspecialchars($order['ShippingAddress']) ?></span>
                        </div>
                        
                        <?php if (!empty($order['ShippingLandmark'])): ?>
                            <div class="info-row">
                                <span class="info-label">Landmark:</span>
                                <span class="info-value"><?= htmlspecialchars($order['ShippingLandmark']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="details-card">
                        <h3 class="section-title">🎨 Order Items</h3>
                        
                        <?php foreach ($order_items as $item): ?>
                            <div class="order-item">
                                <?php if (!empty($item['MainImageURL'])): ?>
                                    <img src="<?= htmlspecialchars($item['MainImageURL']) ?>" 
                                         alt="<?= htmlspecialchars($item['ArtworkTitle']) ?>" 
                                         class="item-image">
                                <?php endif; ?>
                                
                                <div class="item-details">
                                    <div class="item-title">
                                        <?= htmlspecialchars($item['ArtworkTitle']) ?>
                                    </div>
                                    <div class="item-qty">
                                        Quantity: <?= $item['Quantity'] ?> × KES <?= number_format($item['Price'], 2) ?>
                                    </div>
                                </div>
                                
                                <div class="item-price">
                                    KES <?= number_format($item['Subtotal'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="order-total-box">
                            <div class="total-row">
                                <span>Total:</span>
                                <span>KES <?= number_format($order['OrderTotal'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="index.php" class="btn-primary-custom">
                    🏠 Continue Shopping
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn-secondary-custom">
                        📋 View My Orders
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Email Confirmation Notice -->
            <div class="alert alert-info mt-4 text-center">
                <strong>📧 Confirmation Email Sent!</strong><br>
                A confirmation email has been sent to <strong><?= htmlspecialchars($order['CustomerEmail']) ?></strong>
            </div>
            
        </div>
    </section>

    <!-- ===========================================
         SECTION 8: FOOTER
         =========================================== -->
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Essence of Art Gallery. All rights reserved.</p>
            <p>Need help? Contact us at support@essenceofart.com or call 0712345678</p>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>