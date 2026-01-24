<?php
// ===========================================
// SECTION 1: SETUP & PROCESS LOGIN
// ===========================================

// Start session
session_start();

// Include database connection and cart functions
require_once 'includes/db-connect.php';
require_once 'includes/cart-functions.php';

// Get cart count for navbar
$cart_count = getCartCount();

// If already logged in, redirect to profile
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, check credentials
    if (empty($errors)) {
        try {
            // Get user by email
            $sql = "SELECT * FROM Users WHERE Email = :email AND IsActive = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            // Verify password
            if ($user && password_verify($password, $user['Password'])) {
                
                // Update last login
                $update_login = $pdo->prepare("UPDATE Users SET LastLogin = NOW() WHERE UserID = :id");
                $update_login->execute([':id' => $user['UserID']]);
                
                // Set session variables
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['user_name'] = $user['FullName'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_type'] = $user['UserType'];
                
                $_SESSION['success'] = "Welcome back, " . $user['FullName'] . "!";
                
                // Redirect based on user type
                if ($user['UserType'] == 'admin') {
                    header("Location: admin/manage-artwork.php");
                } else {
                    header("Location: profile.php");
                }
                exit;
                
            } else {
                $errors[] = "Invalid email or password";
            }
            
        } catch (PDOException $e) {
            $errors[] = "Error logging in. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Essence of Art</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --cord: #333;
            --tongue: #ff6b6b;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background 0.5s ease;
        }
        
        body.light-on {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        h1, h2 {
            font-family: 'Playfair Display', serif;
        }
        
        /* Navbar */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        /* Login Container */
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
        }

        .login-layout {
            display: flex;
            align-items: center;
            gap: 4rem;
            max-width: 1200px;
            width: 100%;
        }
        
        /* Welcome Section */
        .welcome-section {
            flex: 1;
            color: white;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        body.light-on .welcome-section {
            opacity: 0.95;
        }

        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #f4d03f 0%, #e74c3c 50%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-tagline {
            font-size: 1.3rem;
            color: #ccc;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .welcome-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #ddd;
            margin-bottom: 2rem;
        }

        .art-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .features-list {
            list-style: none;
            padding: 0;
        }

        .features-list li {
            padding: 0.5rem 0;
            color: #bbb;
            font-size: 0.95rem;
        }

        .features-list li:before {
            content: "✓ ";
            color: #f4d03f;
            font-weight: bold;
            margin-right: 0.5rem;
        }
        
        /* Lamp Section */
        .lamp-section {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .lamp {
            height: 40vmin;
            overflow: visible !important;
            margin: 0 auto 2rem;
            max-height: 250px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cord {
            stroke: var(--cord);
            stroke-width: 2;
            fill: none;
        }

        .lamp-body {
            fill: #d4af37;
            transition: all 0.5s ease;
        }

        body.light-on .lamp-body {
            fill: #f4d03f;
            filter: drop-shadow(0 0 30px rgba(244, 208, 63, 0.8));
        }

        .lamp_tongue {
            fill: var(--tongue);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .lamp_tongue:hover {
            transform: translateY(5px);
        }

        .light-glow {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        body.light-on .light-glow {
            opacity: 0.6;
        }

        .hint {
            color: #888;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            opacity: 1;
            transition: opacity 0.5s ease;
            text-align: center;
        }

        body.light-on .hint {
            opacity: 0;
        }

        @keyframes swing {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(5deg); }
            75% { transform: rotate(-5deg); }
        }
        
        /* Login Box */
        .login-box {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 450px;
            width: 100%;
            opacity: 0;
            transform: translateY(-20px);
            pointer-events: none;
            transition: all 0.5s ease;
        }
        
        body.light-on .login-box {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        
        .login-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            text-align: center;
        }
        
        .login-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #555;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.15);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            width: 100%;
            transition: transform 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            color: #999;
        }
        
        @media (max-width: 992px) {
            .login-layout {
                flex-direction: column;
                gap: 2rem;
            }

            .welcome-section {
                text-align: center;
            }

            .brand-logo {
                font-size: 3rem;
            }

            .art-icon {
                font-size: 4rem;
            }
        }

        @media (max-width: 576px) {
            .login-box {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 2rem;
            }

            .brand-logo {
                font-size: 2.5rem;
            }

            .brand-tagline {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎨 Essence of Art</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <?php include 'includes/navbar.php'; ?>
            <?php include 'includes/syncCartOnLogin.php';?>
        </div>
    </nav>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-layout">
            
            <!-- Welcome Section (Left) -->
            <div class="welcome-section">
                <div class="art-icon">🎨</div>
                <h1 class="brand-logo">Essence<br>of Art</h1>
                <p class="brand-tagline">Where Creativity Meets Passion</p>
                <p class="welcome-text">
                    Discover, collect, and celebrate the beauty of art. Join our community of artists and art lovers from around the world.
                </p>
                <ul class="features-list">
                    <li>Explore unique artworks from talented artists</li>
                    <li>Build your personal art collection</li>
                    <li>Connect with a creative community</li>
                    <li>Support artists you love</li>
                </ul>
            </div>

            <!-- Lamp & Login Section (Right) -->
            <div class="lamp-section">
                
                <!-- Hanging Lamp -->
                <svg class="lamp" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" id="lampSvg">
                    <!-- Ceiling -->
                    <rect x="85" y="0" width="30" height="5" fill="#555" />
                    
                    <!-- Cord -->
                    <line class="cord" x1="100" y1="5" x2="100" y2="60" />
                    
                    <!-- Lamp shade -->
                    <ellipse class="lamp-body" cx="100" cy="80" rx="35" ry="20" />
                    <path class="lamp-body" d="M 65 80 Q 65 100 100 110 Q 135 100 135 80 Z" />
                    
                    <!-- Lamp shine -->
                    <ellipse fill="#fff9e6" opacity="0.6" cx="90" cy="75" rx="15" ry="8" />
                    
                    <!-- Light beam -->
                    <defs>
                        <radialGradient id="lightBeam">
                            <stop offset="0%" style="stop-color:#fff9e6;stop-opacity:0.3" />
                            <stop offset="100%" style="stop-color:#fff9e6;stop-opacity:0" />
                        </radialGradient>
                    </defs>
                    <ellipse class="light-glow" fill="url(#lightBeam)" cx="100" cy="150" rx="60" ry="40" />
                    
                    <!-- Pull cord tongue -->
                    <g class="lamp_tongue" id="pullChain">
                        <line stroke="var(--cord)" stroke-width="1.5" x1="100" y1="110" x2="100" y2="130" />
                        <circle cx="100" cy="135" r="5" />
                    </g>
                </svg>

                <p class="hint">💡 Pull the chain to login</p>
                
                <!-- Login Box -->
                <div class="login-box">
                    
                    <h1 class="login-title">Welcome Back</h1>
                    <p class="login-subtitle">Login to your account</p>
                    
                    <!-- Success Message -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <!-- Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="login.php">
                        
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email"
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                                   required
                                   autofocus>
                        </div>
                        
                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password"
                                   required>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" class="btn-login">
                            Login
                        </button>
                        
                    </form>
                    
                    <div class="divider">───────  or  ───────</div>
                    
                    <!-- Register Link -->
                    <div class="register-link">
                        Don't have an account? <a href="register.php">Create one</a>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Lamp Animation Script -->
    <script>
        const pullChain = document.getElementById('pullChain');
        const body = document.body;
        const lampTongue = document.querySelector('.lamp_tongue');
        let isLightOn = false;

        pullChain.addEventListener('click', function() {
            isLightOn = !isLightOn;
            body.classList.toggle('light-on');
            
            // Add pull animation
            lampTongue.style.animation = 'swing 1s ease-in-out';
            setTimeout(() => {
                lampTongue.style.animation = '';
            }, 1000);
        });

        // Also allow clicking the entire lamp
        document.getElementById('lampSvg').addEventListener('click', function(e) {
            if (e.target !== pullChain && !pullChain.contains(e.target)) {
                pullChain.click();
            }
        });
    </script>

</body>
</html>