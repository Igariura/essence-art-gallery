<?php
// ===========================================
// SECTION 1: SETUP & GET ORDER
// ===========================================

// Start session
session_start();

// Include database connection
require_once '../includes/db-connect.php';

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// ===========================================
// SECTION 2: HANDLE STATUS UPDATE
// ===========================================

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    
    try {
        $update_query = "UPDATE Orders SET OrderStatus = :status WHERE OrderID = :id";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([
            ':status' => $new_status,
            ':id' => $order_id
        ]);
        
        $_SESSION['success'] = "Order status updated successfully!";
        header("Location: order-details.php?id=" . $order_id);
        exit;
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating status: " . $e->getMessage();
    }
}

// ===========================================
// SECTION 3: FETCH ORDER DETAILS
// ===========================================

// Get order information
$order_query = "SELECT * FROM Orders WHERE OrderID = :id";
$order_stmt = $pdo->prepare($order_query);
$order_stmt->execute([':id' => $order_id]);
$order = $order_stmt->fetch();

// If order not found, redirect
if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: orders.php");
    exit;
}

// Get order items
$items_query = "SELECT * FROM OrderItems WHERE OrderID = :id";
$items_stmt = $pdo->prepare($items_query);
$items_stmt->execute([':id' => $order_id]);
$order_items = $items_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        
        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .order-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .order-meta {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .meta-value {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .info-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            width: 200px;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        /* Status Update Form */
        .status-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cfe2ff; color: #084298; }
        .status-shipped { background-color: #d1e7dd; color: #0f5132; }
        .status-delivered { background-color: #d1e7dd; color: #0a3622; }
        .status-cancelled { background-color: #f8d7da; color: #842029; }
        
        /* Order Items */
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .item-meta {
            font-size: 0.9rem;
            color: #666;
        }
        
        .item-price {
            font-weight: 700;
            color: #e74c3c;
            font-size: 1.1rem;
        }
        
        .order-total {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .btn-update {
            background-color: #667eea;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .btn-update:hover {
            background-color: #5568d3;
        }
    </style>
</head>
<body>

<div class="admin-container">
    
    <!-- Back Button -->
    <div class="mb-3">
        <a href="orders.php" class="btn-back">← Back to Orders</a>
    </div>
    
    <!-- ===========================================
         SECTION 4: SUCCESS/ERROR MESSAGES
         =========================================== -->
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- ===========================================
         SECTION 5: ORDER HEADER
         =========================================== -->
    
    <div class="order-header">
        <div class="order-number">
            Order #<?= str_pad($order['OrderID'], 6, '0', STR_PAD_LEFT) ?>
        </div>
        
        <div class="order-meta">
            <div class="meta-item">
                <div class="meta-label">Order Date</div>
                <div class="meta-value"><?= date('F j, Y, g:i a', strtotime($order['OrderDate'])) ?></div>
            </div>
            
            <div class="meta-item">
                <div class="meta-label">Total Amount</div>
                <div class="meta-value">KES <?= number_format($order['OrderTotal'], 2) ?></div>
            </div>
            
            <div class="meta-item">
                <div class="meta-label">Items</div>
                <div class="meta-value"><?= count($order_items) ?> item(s)</div>
            </div>
        </div>
    </div>
    
    <div class="row">
        
        <!-- Left Column: Customer & Items -->
        <div class="col-lg-8">
            
            <!-- ===========================================
                 SECTION 6: CUSTOMER INFORMATION
                 =========================================== -->
            
            <div class="info-section">
                <h2 class="section-title">Customer Information</h2>
                
                <div class="info-row">
                    <div class="info-label">Name:</div>
                    <div class="info-value"><?= htmlspecialchars($order['CustomerName']) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= htmlspecialchars($order['CustomerEmail']) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value"><?= htmlspecialchars($order['CustomerPhone']) ?></div>
                </div>
                
                <div class="info-row">
                    <div class="info-label">Shipping Address:</div>
                    <div class="info-value"><?= nl2br(htmlspecialchars($order['ShippingAddress'])) ?></div>
                </div>
                
                <?php if (!empty($order['Notes'])): ?>
                    <div class="info-row">
                        <div class="info-label">Order Notes:</div>
                        <div class="info-value"><?= nl2br(htmlspecialchars($order['Notes'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- ===========================================
                 SECTION 7: ORDER ITEMS
                 =========================================== -->
            
            <div class="info-section">
                <h2 class="section-title">Order Items</h2>
                
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-details">
                            <div class="item-title">
                                <?= htmlspecialchars($item['ArtworkTitle']) ?>
                            </div>
                            <div class="item-meta">
                                Quantity: <?= $item['Quantity'] ?> × KES <?= number_format($item['Price'], 2) ?>
                            </div>
                        </div>
                        <div class="item-price">
                            KES <?= number_format($item['Subtotal'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Order Total -->
                <div class="order-total">
                    <div class="total-row">
                        <span>Total:</span>
                        <span>KES <?= number_format($order['OrderTotal'], 2) ?></span>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Right Column: Status Update -->
        <div class="col-lg-4">
            
            <!-- ===========================================
                 SECTION 8: STATUS UPDATE FORM
                 =========================================== -->
            
            <div class="info-section">
                <h2 class="section-title">Order Status</h2>
                
                <div class="mb-3">
                    <strong>Current Status:</strong><br>
                    <?php
                    $status = $order['OrderStatus'];
                    $badge_class = 'status-' . strtolower($status);
                    ?>
                    <span class="status-badge <?= $badge_class ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>
                </div>
                
                <div class="status-form">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="order_status" class="form-label">
                                <strong>Update Status:</strong>
                            </label>
                            <select class="form-select" id="order_status" name="order_status" required>
                                <option value="Pending" <?= $order['OrderStatus'] == 'Pending' ? 'selected' : '' ?>>
                                    Pending
                                </option>
                                <option value="Processing" <?= $order['OrderStatus'] == 'Processing' ? 'selected' : '' ?>>
                                    Processing
                                </option>
                                <option value="Shipped" <?= $order['OrderStatus'] == 'Shipped' ? 'selected' : '' ?>>
                                    Shipped
                                </option>
                                <option value="Delivered" <?= $order['OrderStatus'] == 'Delivered' ? 'selected' : '' ?>>
                                    Delivered
                                </option>
                                <option value="Cancelled" <?= $order['OrderStatus'] == 'Cancelled' ? 'selected' : '' ?>>
                                    Cancelled
                                </option>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_status" class="btn-update w-100">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
        
    </div>
    
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>