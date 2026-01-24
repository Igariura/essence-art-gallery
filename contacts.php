<?php
// ===========================================
// SECTION 1: SETUP & PROCESS FORM
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Get cart count for navbar
$cart_count = getCartCount();

// Process contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO ContactMessages (Name, Email, Phone, Subject, Message) 
                    VALUES (:name, :email, :phone, :subject, :message)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':subject' => $subject,
                ':message' => $message
            ]);
            
            $_SESSION['success'] = "Thank you! Your message has been sent. We'll get back to you soon!";
            header("Location: contact.php");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error sending message. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Essence of Art Gallery</title>
    
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
        
        /* Contact Section */
        .contact-section {
            padding: 80px 0;
        }
        
        .contact-form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 30px;
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
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
        }
        
        .btn-submit {
            background-color: var(--secondary-color);
            color: white;
            padding: 15px 50px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
        }
        
        /* Contact Info Cards */
        .contact-info {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }
        
        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .info-content h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .info-content p {
            color: #666;
            margin: 0;
        }
        
        .info-content a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        
        .info-content a:hover {
            text-decoration: underline;
        }
        
        /* Footer */
        .footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: 50px;
        }
        
        /* Required asterisk */
        .required {
            color: var(--secondary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5rem;
            }
            
            .contact-form-container,
            .contact-info {
                padding: 25px;
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
           
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Get in Touch</h1>
            <p>We'd love to hear from you! Send us a message.</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
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
                
                <!-- Contact Form (Left Column) -->
                <div class="col-lg-7 mb-4">
                    <div class="contact-form-container">
                        <h2 class="section-title">Send us a Message</h2>
                        
                        <form method="POST" action="contact.php">
                            
                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Full Name <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name"
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                                       required>
                            </div>
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email Address <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                       required>
                            </div>
                            
                            <!-- Phone (Optional) -->
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    Phone Number (Optional)
                                </label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone"
                                       value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"
                                       placeholder="e.g., 0712345678">
                            </div>
                            
                            <!-- Subject (Optional) -->
                            <div class="mb-3">
                                <label for="subject" class="form-label">
                                    Subject (Optional)
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="subject" 
                                       name="subject"
                                       value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>"
                                       placeholder="What is your message about?">
                            </div>
                            
                            <!-- Message -->
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    Message <span class="required">*</span>
                                </label>
                                <textarea class="form-control" 
                                          id="message" 
                                          name="message" 
                                          rows="6"
                                          placeholder="Tell us how we can help you..."
                                          required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    Send Message
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>
                
                <!-- Contact Information (Right Column) -->
                <div class="col-lg-5">
                    <div class="contact-info">
                        <h2 class="section-title">Contact Information</h2>
                        
                        <!-- Email -->
                        <div class="info-item">
                            <div class="info-icon">📧</div>
                            <div class="info-content">
                                <h3>Email</h3>
                                <p>
                                    <a href="mailto:info@essenceofart.com">igariuramuraguri@gmail.com</a>
                                    <a href="mailto:support@essenceofart.com">support@essenceofart.com</a>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Phone -->
                        <div class="info-item">
                            <div class="info-icon">📱</div>
                            <div class="info-content">
                                <h3>Phone</h3>
                                <p>
                                    +254 712 345 678<br>
                                    +254 733 456 789
                                </p>
                            </div>
                        </div>
                        
                        <!-- Location -->
                        <div class="info-item">
                            <div class="info-icon">📍</div>
                            <div class="info-content">
                                <h3>Location</h3>
                                <p>
                                    Nairobi, Kenya<br>
                                    Open Mon-Sat: 9AM - 6PM
                                </p>
                            </div>
                        </div>
                        
                        <!-- Business Hours -->
                        <div class="info-item">
                            <div class="info-icon">🕐</div>
                            <div class="info-content">
                                <h3>Business Hours</h3>
                                <p>
                                    Monday - Friday: 9:00 AM - 6:00 PM<br>
                                    Saturday: 10:00 AM - 4:00 PM<br>
                                    Sunday: Closed
                                </p>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>
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