<?php
// ===========================================
// SECTION 1: SETUP
// ===========================================

// Start session
session_start();

// Include database connection
require_once '../includes/db-connect.php';


// ===========================================
// SECTION 2: FETCH ALL ORDERS
// ===========================================

// Get all orders with customer info
$orders_query = "SELECT 
                    o.OrderID,
                    o.CustomerName,
                    o.CustomerEmail,
                    o.OrderTotal,
                    o.OrderStatus,
                    o.OrderDate,
                    COUNT(oi.OrderItemID) as ItemCount
                FROM Orders o
                LEFT JOIN OrderItems oi ON o.OrderID = oi.OrderID
                GROUP BY o.OrderID
                ORDER BY o.OrderDate DESC";

$orders = $pdo->query($orders_query)->fetchAll();

// Get order statistics
$stats_query = "SELECT 
                    COUNT(*) as TotalOrders,
                    SUM(CASE WHEN OrderStatus = 'Pending' THEN 1 ELSE 0 END) as PendingOrders,
                    SUM(OrderTotal) as TotalRevenue
                FROM Orders";
$stats = $pdo->query($stats_query)->fetch();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }
        
        /* Statistics Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card.pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.revenue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        /* Table Styling */
        .orders-table {
            margin-top: 20px;
        }
        
        .table {
            background: white;
        }
        
        .table thead {
            background-color: #2c3e50;
            color: white;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
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
        
        .status-delivered {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .btn-view {
            background-color: #2c3e50;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-view:hover {
            background-color: #1a252f;
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-orders h3 {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        /* Navigation */
        .admin-nav {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        
        .admin-nav a {
            color: #2c3e50;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
        }
        
        .admin-nav a:hover {
            color: #667eea;
        }
    </style>
</head>
<body>

<div class="admin-container">
    
    <!-- ===========================================
         SECTION 3: NAVIGATION
         =========================================== -->
    
    <div class="admin-nav">
        <a href="manage-artwork.php">← Back to Artworks</a>
        <a href="add-artwork.php">Add Artwork</a>
        <a href="orders.php">Orders</a>
    </div>
    
    <!-- Page Title -->
    <h1>📦 Customer Orders Management</h1>
    
    <!-- ===========================================
         SECTION 4: STATISTICS CARDS
         =========================================== -->
    
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= $stats['TotalOrders'] ?></div>
        </div>
        
        <div class="stat-card pending">
            <div class="stat-label">Pending Orders</div>
            <div class="stat-value"><?= $stats['PendingOrders'] ?></div>
        </div>
        
        <div class="stat-card revenue">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">KES <?= number_format($stats['TotalRevenue'], 0) ?></div>
        </div>
    </div>
    
    <!-- ===========================================
         SECTION 5: ORDERS TABLE
         =========================================== -->
    
    <?php if (empty($orders)): ?>
        
        <div class="no-orders">
            <h3>No orders yet</h3>
            <p>Orders will appear here when customers make purchases.</p>
        </div>
        
    <?php else: ?>
        
        <div class="orders-table">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <!-- Order ID -->
                                <td>
                                    <strong>#<?= str_pad($order['OrderID'], 6, '0', STR_PAD_LEFT) ?></strong>
                                </td>
                                
                                <!-- Customer Name -->
                                <td><?= htmlspecialchars($order['CustomerName']) ?></td>
                                
                                <!-- Email -->
                                <td><?= htmlspecialchars($order['CustomerEmail']) ?></td>
                                
                                <!-- Item Count -->
                                <td><?= $order['ItemCount'] ?> item(s)</td>
                                
                                <!-- Total -->
                                <td>
                                    <strong>KES <?= number_format($order['OrderTotal'], 2) ?></strong>
                                </td>
                                
                                <!-- Status Badge -->
                                <td>
                                    <?php
                                    $status = $order['OrderStatus'];
                                    $badge_class = 'status-' . strtolower($status);
                                    ?>
                                    <span class="status-badge <?= $badge_class ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </td>
                                
                                <!-- Date -->
                                <td><?= date('M d, Y', strtotime($order['OrderDate'])) ?></td>
                                
                                <!-- Actions -->
                                <td>
                                    <a href="order-details.php?id=<?= $order['OrderID'] ?>" 
                                       class="btn-view">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    <?php endif; ?>
    
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>