<?php
// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Get cart count for navbar
$cart_count = getCartCount();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Essence of Art Gallery</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #f39c12;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
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
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.3rem;
            opacity: 0.9;
        }
        
        /* Content Sections */
        .content-section {
            padding: 80px 0;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .section-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            max-width: 800px;
            margin: 0 auto 40px;
            text-align: center;
        }
        
        /* Story Section */
        .story-section {
            background-color: #f8f9fa;
        }
        
        .story-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }
        
        .story-text {
            flex: 1;
        }
        
        .story-text h2 {
            font-size: 2.2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .story-text p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #666;
            margin-bottom: 20px;
        }
        
        .story-image {
            flex: 1;
            text-align: center;
        }
        
        .story-image-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 100%;
            height: 400px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        /* Values Section */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }
        
        .value-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .value-card:hover {
            transform: translateY(-10px);
        }
        
        .value-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .value-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .value-text {
            color: #666;
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn-cta {
            background-color: white;
            color: var(--primary-color);
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s;
        }
        
        .btn-cta:hover {
            transform: translateY(-3px);
            color: var(--primary-color);
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .story-content {
                flex-direction: column;
            }
            
            .section-title {
                font-size: 2rem;
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>About Essence of Art</h1>
            <p>Discover the story behind our passion for art</p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="content-section story-section">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>Our Story</h2>
                    <p>
                        Essence of Art Gallery was founded with a simple mission: to make unique, 
                        high-quality original artworks accessible to art lovers everywhere. What started 
                        as a passion project has grown into a vibrant community of artists and collectors.
                    </p>
                    <p>
                        We believe that art has the power to transform spaces and inspire emotions. 
                        Every piece in our collection is carefully curated to ensure authenticity, 
                        quality, and uniqueness. We work directly with talented artists to bring 
                        their visions to life and connect them with collectors who appreciate their work.
                    </p>
                    <p>
                        Whether you're an experienced collector or just starting your art journey, 
                        we're here to help you find the perfect piece that speaks to you.
                    </p>
                </div>
                <div class="story-image">
                    <div class="story-image-placeholder">
                        🎨
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="content-section">
        <div class="container">
            <h2 class="section-title">Our Values</h2>
            <p class="section-text">
                These core principles guide everything we do at Essence of Art Gallery
            </p>
            
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">✨</div>
                    <h3 class="value-title">Quality First</h3>
                    <p class="value-text">
                        Every artwork in our collection is carefully inspected to ensure 
                        the highest standards of quality and craftsmanship.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🤝</div>
                    <h3 class="value-title">Artist Support</h3>
                    <p class="value-text">
                        We believe in empowering artists and ensuring they receive fair 
                        compensation for their incredible work.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">💎</div>
                    <h3 class="value-title">Authenticity</h3>
                    <p class="value-text">
                        All our pieces are original artworks with certificates of authenticity. 
                        We never sell prints or reproductions without clear disclosure.
                    </p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🌟</div>
                    <h3 class="value-title">Customer Experience</h3>
                    <p class="value-text">
                        Your satisfaction is our priority. We provide detailed descriptions, 
                        secure shipping, and exceptional customer service.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Find Your Perfect Artwork?</h2>
            <p>Explore our curated collection of original artworks from talented artists</p>
            <a href="gallery.php" class="btn-cta">Browse Gallery</a>
        </div>
    </section>

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