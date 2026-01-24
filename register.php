<?php
// ===========================================
// SECTION 1: SETUP & PROCESS REGISTRATION
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

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email = $pdo->prepare("SELECT UserID FROM Users WHERE Email = :email");
        $check_email->execute([':email' => $email]);
        
        if ($check_email->fetch()) {
            $errors[] = "Email already registered. Please login instead.";
        }
    }
    
    // If no errors, create account
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO Users (FullName, Email, Phone, Password, UserType) 
                    VALUES (:name, :email, :phone, :password, 'customer')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':password' => $hashed_password
            ]);
            
            // Get new user ID
            $user_id = $pdo->lastInsertId();
            
            // Log them in automatically
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = 'customer';
            
            $_SESSION['success'] = "Account created successfully! Welcome, " . $full_name . "!";
            header("Location: profile.php");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error creating account. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Essence of Art</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        /* Registration Container */
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 20px;
        }
        
        .register-box {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        
        .register-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            text-align: center;
        }
        
        .register-subtitle {
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
        
        .btn-register {
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
        
        .btn-register:hover {
            transform: translateY(-3px);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .required {
            color: var(--secondary-color);
        }
        
        @media (max-width: 576px) {
            .register-box {
                padding: 30px 20px;
            }
            
            .register-title {
                font-size: 2rem;
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

    <!-- Registration Container -->
    <div class="register-container">
        <div class="register-box">
            
            <h1 class="register-title">Create Account</h1>
            <p class="register-subtitle">Join Essence of Art Gallery</p>
            
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
            
            <!-- Registration Form -->
            <form method="POST" action="register.php">
                
                <!-- Full Name -->
                <div class="mb-3">
                    <label for="full_name" class="form-label">
                        Full Name <span class="required">*</span>
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="full_name" 
                           name="full_name"
                           value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>"
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
                
                <!-- Phone -->
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
                
                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password <span class="required">*</span>
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password"
                           required>
                    <small class="text-muted">At least 6 characters</small>
                </div>
                
                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">
                        Confirm Password <span class="required">*</span>
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password"
                           required>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-register">
                    Create Account
                </button>
                
            </form>
            
            <!-- Login Link -->
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
            
        </div>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>