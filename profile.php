<?php
// ===========================================
// SECTION 1: AUTHENTICATION CHECK
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to access your profile";
    header("Location: login.php");
    exit;
}

// Get user ID
$user_id = $_SESSION['user_id'];

// ===========================================
// SECTION 2: FETCH USER DATA
// ===========================================

// Get user information
$user_query = "SELECT * FROM Users WHERE UserID = :id";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->execute([':id' => $user_id]);
$user = $user_stmt->fetch();

// Get user's order count and total spent
$stats_query = "SELECT 
                    COUNT(*) as TotalOrders,
                    COALESCE(SUM(OrderTotal), 0) as TotalSpent
                FROM Orders 
                WHERE CustomerEmail = :email";
$stats_stmt = $pdo->prepare($stats_query);
$stats_stmt->execute([':email' => $user['Email']]);
$stats = $stats_stmt->fetch();

// Get recent orders (last 5)
$orders_query = "SELECT 
                    o.OrderID,
                    o.OrderTotal,
                    o.OrderStatus,
                    o.OrderDate,
                    COUNT(oi.OrderItemID) as ItemCount
                FROM Orders o
                LEFT JOIN OrderItems oi ON o.OrderID = oi.OrderID
                WHERE o.CustomerEmail = :email
                GROUP BY o.OrderID
                ORDER BY o.OrderDate DESC
                LIMIT 5";
$orders_stmt = $pdo->prepare($orders_query);
$orders_stmt->execute([':email' => $user['Email']]);
$recent_orders = $orders_stmt->fetchAll();

// Get cart count
$cart_count = getCartCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Essence of Art</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
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
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .profile-welcome {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .profile-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
        }
        
        /* Section Cards */
        .section-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
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
            width: 150px;
            color: #666;
        }
        
        .info-value {
            flex: 1;
            color: #333;
        }
        
        /* Order List */
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: transform 0.3s;
        }
        
        .order-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-number {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .order-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-total {
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cfe2ff; color: #084298; }
        .status-shipped { background-color: #d1e7dd; color: #0f5132; }
        .status-delivered { background-color: #d1e7dd; color: #0a3622; }
        .status-cancelled { background-color: #f8d7da; color: #842029; }
        
        .btn-view-order {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-view-order:hover {
            background-color: #1a252f;
            color: white;
        }
        
        .btn-logout {
            background-color: var(--secondary-color);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        
        .btn-logout:hover {
            background-color: #c0392b;
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 50px;
        }
        
        @media (max-width: 768px) {
            .profile-welcome {
                font-size: 2rem;
            }
            
            .order-item {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎨 Essence of Art</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
           <?php include 'includes/navbar.php'; ?>
           <?php include 'includes/syncCartOnLogin.php';?>
        </div>
    </nav>

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container text-center">
            <h1 class="profile-welcome">Welcome, <?= htmlspecialchars($user['FullName']) ?>! 👋</h1>
            <p class="profile-subtitle">Manage your account and view your orders</p>
        </div>
    </section>

    <!-- Profile Content -->
    <div class="container">
        
        <!-- Success Message -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?= $stats['TotalOrders'] ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value">KES <?= number_format($stats['TotalSpent'], 0) ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎨</div>
                <div class="stat-value"><?= $cart_count ?></div>
                <div class="stat-label">Items in Cart</div>
            </div>
        </div>
        
        <div class="row">
            
            <!-- Account Information (Left Column) -->
            <div class="col-lg-5 mb-4">
                <div class="section-card">
                    <h2 class="section-title">Account Information</h2>
                    
                    <div class="info-row">
                        <div class="info-label">Full Name:</div>
                        <div class="info-value"><?= htmlspecialchars($user['FullName']) ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?= htmlspecialchars($user['Email']) ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Phone:</div>
                        <div class="info-value">
                            <?= !empty($user['Phone']) ? htmlspecialchars($user['Phone']) : '<em class="text-muted">Not provided</em>' ?>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Member Since:</div>
                        <div class="info-value"><?= date('F Y', strtotime($user['DateRegistered'])) ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Last Login:</div>
                        <div class="info-value">
                            <?= $user['LastLogin'] ? date('M d, Y g:i A', strtotime($user['LastLogin'])) : 'First time' ?>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="logout.php" class="btn-logout">Logout</a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders (Right Column) -->
            <div class="col-lg-7">
                <div class="section-card">
                    <h2 class="section-title">Recent Orders</h2>
                    
                    <?php if (empty($recent_orders)): ?>
                        
                        <div class="no-orders">
                            <p>You haven't placed any orders yet.</p>
                            <a href="gallery.php" class="btn-view-order">Browse Gallery</a>
                        </div>
                        
                    <?php else: ?>
                        
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="order-item">
                                <div>
                                    <div class="order-number">
                                        Order #<?= str_pad($order['OrderID'], 6, '0', STR_PAD_LEFT) ?>
                                    </div>
                                    <div class="order-date">
                                        <?= date('M d, Y', strtotime($order['OrderDate'])) ?> • <?= $order['ItemCount'] ?> item(s)
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <?php
                                    $status = $order['OrderStatus'];
                                    $badge_class = 'status-' . strtolower($status);
                                    ?>
                                    <span class="status-badge <?= $badge_class ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                </div>
                                
                                <div class="text-end">
                                    <div class="order-total">
                                        KES <?= number_format($order['OrderTotal'], 2) ?>
                                    </div>
                                    <a href="order-confirmation.php?order=<?= $order['OrderID'] ?>" class="btn-view-order mt-2">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Essence of Art Gallery. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 