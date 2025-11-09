<?php
// ===========================================
// SECTION 1: GET ARTWORK ID & FETCH DATA
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Check if artwork ID is provided in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gallery.php");
    exit;
}

// Get the artwork ID and make it safe
$artwork_id = intval($_GET['id']);

// Fetch the specific artwork with category info
$artwork_query = "SELECT 
                    a.*,
                    c.CategoryName
                FROM Artworks a
                JOIN Categories c ON a.CategoryID = c.CategoryID
                WHERE a.ArtworkID = :id AND a.IsAvailable = 1";

$stmt = $pdo->prepare($artwork_query);
$stmt->execute([':id' => $artwork_id]);
$artwork = $stmt->fetch();

// If artwork not found, redirect to gallery
if (!$artwork) {
    header("Location: gallery.php");
    exit;
}

// Fetch related artworks (same category, excluding current)
$related_query = "SELECT 
                    ArtworkID,
                    Title,
                    Price,
                    MainImageURL
                FROM Artworks
                WHERE CategoryID = :category_id 
                AND ArtworkID != :current_id
                AND IsAvailable = 1
                ORDER BY RAND()
                LIMIT 3";

$related_stmt = $pdo->prepare($related_query);
$related_stmt->execute([
    ':category_id' => $artwork['CategoryID'],
    ':current_id' => $artwork_id
]);
$related_artworks = $related_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artwork['Title']) ?> - Essence of Art Gallery</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        /* ===========================================
           SECTION 2: STYLING
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
        
        /* Navbar (same as homepage) */
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
        
        /* Breadcrumb */
        .breadcrumb-section {
            padding: 20px 0;
            background-color: white;
        }
        
        /* Artwork Detail Section */
        .artwork-detail {
            padding: 50px 0;
        }
        
        .artwork-image-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .artwork-main-image {
            max-width: 100%;
            max-height: 600px;
            border-radius: 10px;
            object-fit: contain;
        }
        
        .artwork-info {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .artwork-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .category-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .artwork-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin: 20px 0;
        }
        
        .availability-badge {
            display: inline-block;
            background-color: #27ae60;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        
        .artwork-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin: 25px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-left: 4px solid var(--accent-color);
            border-radius: 5px;
        }
        
        .technical-details {
            margin: 30px 0;
        }
        
        .technical-details h3 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .detail-item {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-label {
            font-weight: 600;
            width: 150px;
            color: #666;
        }
        
        .detail-value {
            flex: 1;
            color: #333;
        }
        
        .btn-add-cart {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px 50px;
            font-size: 1.2rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            transition: all 0.3s;
            margin-top: 20px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-add-cart:hover {
            background-color: #c0392b;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        /* Related Artworks Section */
        .related-section {
            padding: 60px 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 40px;
            color: var(--primary-color);
        }
        
        .related-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        
        .related-card:hover {
            transform: translateY(-5px);
        }
        
        .related-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .related-body {
            padding: 15px;
            text-align: center;
        }
        
        .related-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .related-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .btn-view-small {
            background-color: var(--primary-color);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .btn-view-small:hover {
            background-color: #1a252f;
            color: white;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .artwork-title {
                font-size: 2rem;
            }
            
            .artwork-price {
                font-size: 2rem;
            }
            
            .btn-add-cart {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- ===========================================
         SECTION 3: NAVIGATION BAR
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
                        <a class="nav-link" href="cart.php">🛒 Cart (<?= getCartCount() ?>)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===========================================
         SECTION 4: BREADCRUMB
         =========================================== -->
    
    <section class="breadcrumb-section">
        <div class="container">
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="gallery.php">Gallery</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($artwork['Title']) ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- ===========================================
         SECTION 5: ARTWORK DETAIL
         =========================================== -->
    
    <section class="artwork-detail">
        <div class="container">
            <div class="row">
                
                <!-- Left Column: Image -->
                <div class="col-lg-6 mb-4">
                    <div class="artwork-image-container">
                        <img src="<?= htmlspecialchars($artwork['MainImageURL']) ?>" 
                             alt="<?= htmlspecialchars($artwork['Title']) ?>" 
                             class="artwork-main-image">
                    </div>
                </div>
                
                <!-- Right Column: Information -->
                <div class="col-lg-6">
                    <div class="artwork-info">
                        
                        <!-- Title -->
                        <h1 class="artwork-title"><?= htmlspecialchars($artwork['Title']) ?></h1>
                        
                        <!-- Category Badge -->
                        <span class="category-badge">
                            <?= htmlspecialchars($artwork['CategoryName']) ?>
                        </span>
                        
                        <!-- Price & Availability -->
                        <div class="artwork-price">
                            KES <?= number_format($artwork['Price'], 2) ?>
                            <span class="availability-badge">✓ Available</span>
                        </div>
                        
                        <!-- Description -->
                        <?php if (!empty($artwork['Description'])): ?>
                            <div class="artwork-description">
                                <strong>About this artwork:</strong><br>
                                <?= nl2br(htmlspecialchars($artwork['Description'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Technical Details -->
                        <div class="technical-details">
                            <h3>Technical Details</h3>
                            
                            <?php if (!empty($artwork['Medium'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Medium:</div>
                                    <div class="detail-value"><?= htmlspecialchars($artwork['Medium']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($artwork['Dimensions'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Dimensions:</div>
                                    <div class="detail-value"><?= htmlspecialchars($artwork['Dimensions']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($artwork['YearCreated'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Year Created:</div>
                                    <div class="detail-value"><?= htmlspecialchars($artwork['YearCreated']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($artwork['TechnicalDetails'])): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Additional Info:</div>
                                    <div class="detail-value"><?= htmlspecialchars($artwork['TechnicalDetails']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <div class="detail-label">Artwork ID:</div>
                                <div class="detail-value">#<?= $artwork['ArtworkID'] ?></div>
                            </div>
                        </div>
                        
                        <!-- Add to Cart Button -->
                        <a href="add-to-cart.php?id=<?= $artwork['ArtworkID'] ?>" class="btn-add-cart">
                            🛒 Add to Cart
                        </a>
                        
                        <div class="mt-3">
                            <a href="gallery.php" class="btn btn-outline-secondary">← Back to Gallery</a>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <!-- ===========================================
         SECTION 6: RELATED ARTWORKS
         =========================================== -->
    
    <?php if (!empty($related_artworks)): ?>
    <section class="related-section">
        <div class="container">
            <h2 class="section-title">You May Also Like</h2>
            
            <div class="row">
                <?php foreach ($related_artworks as $related): ?>
                    <div class="col-md-4">
                        <div class="related-card">
                            <img src="<?= htmlspecialchars($related['MainImageURL']) ?>" 
                                 alt="<?= htmlspecialchars($related['Title']) ?>" 
                                 class="related-image">
                            
                            <div class="related-body">
                                <h3 class="related-title">
                                    <?= htmlspecialchars($related['Title']) ?>
                                </h3>
                                <div class="related-price">
                                    KES <?= number_format($related['Price'], 2) ?>
                                </div>
                                <a href="artwork-detail.php?id=<?= $related['ArtworkID'] ?>" 
                                   class="btn-view-small">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

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