<?php
// ===========================================
// SECTION 1: DATABASE CONNECTION & FETCH DATA
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Fetch featured artworks (limit to 6 for homepage)
$featured_query = "SELECT 
                    a.ArtworkID,
                    a.Title,
                    a.Price,
                    a.MainImageURL,
                    c.CategoryName
                FROM Artworks a
                JOIN Categories c ON a.CategoryID = c.CategoryID
                WHERE a.IsFeatured = 1 AND a.IsAvailable = 1
                ORDER BY a.DateAdded DESC
                LIMIT 6";
$featured_artworks = $pdo->query($featured_query)->fetchAll();

// Fetch all active categories
$categories_query = "SELECT CategoryID, CategoryName, Description 
                    FROM Categories 
                    WHERE IsActive = 1 
                    ORDER BY DisplayOrder";
$categories = $pdo->query($categories_query)->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Essence of Art Gallery - Original Artworks</title>
    
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
            --dark-text: #333;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
        }
        
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }
        
        /* Navbar Styling */
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
            color: var(--dark-text) !important;
            font-weight: 500;
            margin: 0 15px;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--secondary-color) !important;
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn-hero {
            background-color: white;
            color: var(--primary-color);
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            color: var(--primary-color);
        }
        
        /* Section Styling */
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            color: var(--primary-color);
        }
        
        /* Artwork Cards */
        .artwork-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
            height: 100%;
        }
        
        .artwork-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .artwork-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .artwork-body {
            padding: 20px;
        }
        
        .artwork-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .artwork-category {
            font-size: 0.9rem;
            color: #777;
            margin-bottom: 10px;
        }
        
        .artwork-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .btn-view {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn-view:hover {
            background-color: #1a252f;
            color: white;
        }
        
        /* Category Cards */
        .category-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s;
            margin-bottom: 30px;
            height: 100%;
        }
        
        .category-card:hover {
            transform: scale(1.05);
        }
        
        .category-card h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .category-card p {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        .footer p {
            margin: 0;
            font-size: 0.95rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .hero-section p {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
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
            
            <!-- Mobile toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation links -->
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
                        <a class="nav-link" href="#">🛒 Cart (0)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===========================================
         SECTION 4: HERO SECTION
         =========================================== -->
    
    <section class="hero-section">
        <div class="container">
            <h1>Discover Unique Art</h1>
            <p>Explore our curated collection of original artworks from talented artists</p>
            <a href="gallery.php" class="btn btn-hero">Browse Gallery</a>
        </div>
    </section>

    <!-- ===========================================
         SECTION 5: FEATURED ARTWORKS
         =========================================== -->
    
    <section class="section" style="background-color: #f8f9fa;">
        <div class="container">
            <h2 class="section-title">Featured Artworks</h2>
            
            <?php if (empty($featured_artworks)): ?>
                <div class="alert alert-info text-center">
                    No featured artworks available at the moment. Check back soon!
                </div>
            <?php else: ?>
                
                <div class="row">
                    <?php foreach ($featured_artworks as $artwork): ?>
                        <div class="col-md-4">
                            <div class="artwork-card">
                                <img src="<?= htmlspecialchars($artwork['MainImageURL']) ?>" 
                                     alt="<?= htmlspecialchars($artwork['Title']) ?>" 
                                     class="artwork-image">
                                
                                <div class="artwork-body">
                                    <h3 class="artwork-title">
                                        <?= htmlspecialchars($artwork['Title']) ?>
                                    </h3>
                                    <p class="artwork-category">
                                        <?= htmlspecialchars($artwork['CategoryName']) ?>
                                    </p>
                                    <div class="artwork-price">
                                        KES <?= number_format($artwork['Price'], 2) ?>
                                    </div>
                                    <a href="artwork-detail.php?id=<?= $artwork['ArtworkID'] ?>" 
                                       class="btn-view">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php endif; ?>
            
            <!-- View All Button -->
            <div class="text-center mt-5">
                <a href="gallery.php" class="btn btn-hero" style="background-color: var(--primary-color); color: white;">
                    View All Artworks
                </a>
            </div>
        </div>
    </section>

    <!-- ===========================================
         SECTION 6: CATEGORIES
         =========================================== -->
    
    <section class="section">
        <div class="container">
            <h2 class="section-title">Browse by Category</h2>
            
            <?php if (empty($categories)): ?>
                <div class="alert alert-info text-center">
                    No categories available at the moment.
                </div>
            <?php else: ?>
                
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-md-4">
                            <a href="gallery.php?category=<?= $category['CategoryID'] ?>" 
                               style="text-decoration: none;">
                                <div class="category-card">
                                    <h3><?= htmlspecialchars($category['CategoryName']) ?></h3>
                                    <p><?= htmlspecialchars($category['Description']) ?></p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            <?php endif; ?>
        </div>
    </section>

    <!-- ===========================================
         SECTION 7: FOOTER
         =========================================== -->
    
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Essence of Art Gallery. All rights reserved.</p>
            <p>Showcasing unique artworks from talented artists around the world.</p>
        </div>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>