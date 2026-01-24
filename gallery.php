<?php
// ===========================================
// SECTION 1: DATABASE & GET FILTERS
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Get filter parameters from URL
$selected_category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// ===========================================
// SECTION 1.5: GET FEATURED ARTWORKS FOR 3D CAROUSEL
// ===========================================

// Get 8 featured/newest artworks for the carousel
$featured_sql = "SELECT 
                    a.ArtworkID,
                    a.Title,
                    a.MainImageURL
                FROM Artworks a
                WHERE a.IsAvailable = 1
                ORDER BY a.DateAdded DESC
                LIMIT 8";
$featured_artworks = $pdo->query($featured_sql)->fetchAll();

// ===========================================
// SECTION 2: BUILD SQL QUERY WITH FILTERS
// ===========================================

// Start building the SQL query
$sql = "SELECT 
            a.ArtworkID,
            a.Title,
            a.Price,
            a.MainImageURL,
            a.DateAdded,
            a.ShowPrice,
            c.CategoryName
        FROM Artworks a
        JOIN Categories c ON a.CategoryID = c.CategoryID
        WHERE a.IsAvailable = 1";

// Array to hold parameters for prepared statement
$params = [];

// Add category filter if selected
if ($selected_category > 0) {
    $sql .= " AND a.CategoryID = :category_id";
    $params[':category_id'] = $selected_category;
}

// Add search filter if provided
if (!empty($search_query)) {
    $sql .= " AND a.Title LIKE :search";
    $params[':search'] = '%' . $search_query . '%';
}

// Add sorting
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY a.Price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY a.Price DESC";
        break;
    case 'title_asc':
        $sql .= " ORDER BY a.Title ASC";
        break;
    case 'oldest':
        $sql .= " ORDER BY a.DateAdded ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY a.DateAdded DESC";
        break;
}

// Execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$artworks = $stmt->fetchAll();

// Get all categories for filter dropdown
$categories_query = "SELECT CategoryID, CategoryName FROM Categories WHERE IsActive = 1 ORDER BY DisplayOrder";
$categories = $pdo->query($categories_query)->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Essence of Art</title>
    
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
            margin-bottom: 15px;
        }
        
        .page-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* ===========================================
           3D CAROUSEL STYLES
           =========================================== */
        
        .carousel-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 80px 0;
            margin-bottom: 50px;
            overflow: hidden;
            position: relative;
        }

        .carousel-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(102, 126, 234, 0.1), transparent);
            pointer-events: none;
        }

        .carousel-title {
            text-align: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .carousel-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.8);
            font-size: 1.1rem;
            margin-bottom: 60px;
        }

        .carousel-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 500px;
            perspective: 1500px;
        }
        
        .box {
            position: relative;
            width: 280px;
            height: 280px;
            transform-style: preserve-3d;
            animation: rotate3d 25s linear infinite;
            cursor: pointer;
        }

        .box:hover {
            animation-play-state: paused;
        }

        @keyframes rotate3d {
            0% {
                transform: perspective(1500px) rotateY(0deg);
            }
            100% {
                transform: perspective(1500px) rotateY(360deg);
            }
        }

        .box span {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform-origin: center;
            transform-style: preserve-3d;
            transform: rotateY(calc(var(--i) * 45deg)) translateZ(450px);
            -webkit-box-reflect: below 0px linear-gradient(transparent, transparent, #0004);
        }
        
        .box span img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 3px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            object-fit: cover;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: border-color 0.3s;
        }

        .box span:hover img {
            border-color: #667eea;
        }

        .carousel-controls {
            text-align: center;
            margin-top: 40px;
        }

        .carousel-hint {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
            font-style: italic;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        
        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: none;
        }
        
        .btn-filter {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn-filter:hover {
            background-color: #1a252f;
        }
        
        .btn-clear {
            background-color: #6c757d;
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-clear:hover {
            background-color: #5a6268;
            color: white;
        }
        
        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px;
            background: white;
            border-radius: 8px;
        }
        
        .results-count {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        /* Artwork Cards */
        .gallery-grid {
            margin-bottom: 50px;
        }
        
        .artwork-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
            height: 100%;
            display: flex;
            flex-direction: column;
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
            flex: 1;
            display: flex;
            flex-direction: column;
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
            margin-top: auto;
        }
        
        .btn-view {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s;
        }
        
        .btn-view:hover {
            background-color: #1a252f;
            color: white;
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .no-results h3 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .no-results p {
            font-size: 1.1rem;
            color: #666;
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
            .page-header h1 {
                font-size: 2rem;
            }

            .carousel-title {
                font-size: 1.8rem;
            }

            .box {
                width: 200px;
                height: 200px;
            }

            .box span {
                transform: rotateY(calc(var(--i) * 45deg)) translateZ(300px);
            }

            .carousel-section {
                padding: 50px 0;
            }
            
            .results-info {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .filter-section {
                padding: 20px;
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
            
            <?php include 'includes/navbar.php'; ?>
        </div>
    </nav>

    <!-- ===========================================
         SECTION 5: PAGE HEADER
         =========================================== -->
    
    <section class="page-header">
        <div class="container">
            <h1>Art Gallery</h1>
            <p>Explore our complete collection of original artworks</p>
        </div>
    </section>

    <!-- ===========================================
         SECTION 5.5: 3D CAROUSEL - FEATURED ARTWORKS
         =========================================== -->
    
    <?php if (count($featured_artworks) > 0): ?>
    <section class="carousel-section">
        <div class="container">
            <h2 class="carousel-title">✨ Featured Collection</h2>
            <p class="carousel-subtitle">Discover our newest masterpieces in stunning 3D</p>
            
            <div class="carousel-container">
                <div class="box">
                    <?php 
                    $carousel_index = 1;
                    foreach ($featured_artworks as $featured): 
                    ?>
                        <span style="--i: <?= $carousel_index ?>">
                            <a href="artwork-detail.php?id=<?= $featured['ArtworkID'] ?>">
                                <img src="<?= htmlspecialchars($featured['MainImageURL']) ?>" 
                                     alt="<?= htmlspecialchars($featured['Title']) ?>" />
                            </a>
                        </span>
                    <?php 
                    $carousel_index++;
                    endforeach; 
                    ?>
                </div>
            </div>
            
            <div class="carousel-controls">
                <p class="carousel-hint">💡 Hover to pause • Click any artwork to view details</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ===========================================
         SECTION 6: FILTER & SEARCH
         =========================================== -->
    
    <div class="container">
        <div class="filter-section">
            <h2 class="filter-title">🔍 Find Your Perfect Artwork</h2>
            
            <form method="GET" action="gallery.php">
                <div class="row g-3">
                    
                    <!-- Search Input -->
                    <div class="col-md-4">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Search by title..."
                               value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['CategoryID'] ?>"
                                        <?= ($selected_category == $category['CategoryID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Sort Options -->
                    <div class="col-md-3">
                        <select class="form-select" name="sort">
                            <option value="newest" <?= ($sort_by == 'newest') ? 'selected' : '' ?>>
                                Newest First
                            </option>
                            <option value="oldest" <?= ($sort_by == 'oldest') ? 'selected' : '' ?>>
                                Oldest First
                            </option>
                            <option value="price_asc" <?= ($sort_by == 'price_asc') ? 'selected' : '' ?>>
                                Price: Low to High
                            </option>
                            <option value="price_desc" <?= ($sort_by == 'price_desc') ? 'selected' : '' ?>>
                                Price: High to Low
                            </option>
                            <option value="title_asc" <?= ($sort_by == 'title_asc') ? 'selected' : '' ?>>
                                Title: A to Z
                            </option>
                        </select>
                    </div>
                    
                    <!-- Buttons -->
                    <div class="col-md-2">
                        <button type="submit" class="btn-filter w-100">Apply</button>
                    </div>
                    
                </div>
                
                <!-- Clear Filters Button -->
                <?php if ($selected_category > 0 || !empty($search_query) || $sort_by != 'newest'): ?>
                    <div class="mt-3">
                        <a href="gallery.php" class="btn-clear">Clear All Filters</a>
                    </div>
                <?php endif; ?>
                
            </form>
        </div>

        <!-- ===========================================
             SECTION 7: RESULTS INFO
             =========================================== -->
        
        <div class="results-info">
            <div class="results-count">
                Showing <?= count($artworks) ?> artwork<?= count($artworks) != 1 ? 's' : '' ?>
                <?php if ($selected_category > 0): ?>
                    <?php 
                    $cat_name = '';
                    foreach ($categories as $cat) {
                        if ($cat['CategoryID'] == $selected_category) {
                            $cat_name = $cat['CategoryName'];
                            break;
                        }
                    }
                    ?>
                    in <strong><?= htmlspecialchars($cat_name) ?></strong>
                <?php endif; ?>
                <?php if (!empty($search_query)): ?>
                    for <strong>"<?= htmlspecialchars($search_query) ?>"</strong>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===========================================
             SECTION 8: ARTWORKS GRID
             =========================================== -->
        
        <?php if (empty($artworks)): ?>
            
            <!-- No Results Found -->
            <div class="no-results">
                <h3>😔 No artworks found</h3>
                <p>Try adjusting your filters or search terms</p>
                <a href="gallery.php" class="btn-filter">View All Artworks</a>
            </div>
            
        <?php else: ?>
            
            <!-- Artworks Grid -->
            <div class="row gallery-grid">
                <?php foreach ($artworks as $artwork): ?>
                    <div class="col-lg-4 col-md-6">
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
                                
                                <?php if ($artwork['ShowPrice']): ?>
                                    <div class="artwork-price">
                                        KES <?= number_format($artwork['Price'], 2) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="artwork-price" style="color: #999;">
                                        Price on Request
                                    </div>
                                <?php endif; ?>
                                
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
        
    </div>

    <!-- ===========================================
         SECTION 9: FOOTER
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