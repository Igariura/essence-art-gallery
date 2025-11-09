<?php
// ===========================================
// SECTION 1: SETUP & INITIALIZATION
// ===========================================

// Start session (so we can store success messages)
session_start();

// Include database connection file
// This gives us access to $pdo (database connection)
require_once '../includes/db-connect.php';

// ===========================================
// SECTION 2: PROCESS FORM SUBMISSION
// ===========================================

// Check if form was submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- 2A: GET FORM DATA ---
    // Retrieve all form values and clean them up
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $medium = trim($_POST['medium']);
    $dimensions = trim($_POST['dimensions']);
    $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $technical_details = trim($_POST['technical_details']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // --- 2B: VALIDATION ---
    // Create empty array to store any errors
    $errors = [];
    
    // Check required fields
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($price) || $price <= 0) {
        $errors[] = "Valid price is required";
    }
    if (empty($category_id)) {
        $errors[] = "Category is required";
    }
    
    // --- 2C: HANDLE IMAGE UPLOAD ---
    $image_uploaded = false;
    $image_path = '';
    
    // Check if image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        
        // Define allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Get uploaded file info
        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Check if file type is allowed
        if (in_array($file_ext, $allowed)) {
            
            // Create unique filename to avoid conflicts
            $new_filename = uniqid('artwork_', true) . '.' . $file_ext;
            
            // Define where to save the file
            $upload_path = '../uploads/artworks/' . $new_filename;
            
            // Move uploaded file from temp location to our folder
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Success! Save the path (relative to root)
                $image_path = 'uploads/artworks/' . $new_filename;
                $image_uploaded = true;
            } else {
                $errors[] = "Failed to upload image";
            }
            
        } else {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG, GIF allowed";
        }
        
    } else {
        $errors[] = "Please select an image";
    }
    
    // --- 2D: INSERT INTO DATABASE ---
    // Only proceed if there are no errors
    if (empty($errors)) {
        
        try {
            // Prepare SQL query
            $sql = "INSERT INTO Artworks (
                Title, Description, Medium, Dimensions, YearCreated, 
                Price, CategoryID, MainImageURL, TechnicalDetails, 
                IsFeatured, IsAvailable
            ) VALUES (
                :title, :description, :medium, :dimensions, :year,
                :price, :category_id, :image_url, :technical_details,
                :is_featured, 1
            )";
            
            // Prepare statement
            $stmt = $pdo->prepare($sql);
            
            // Execute with actual values
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':medium' => $medium,
                ':dimensions' => $dimensions,
                ':year' => $year,
                ':price' => $price,
                ':category_id' => $category_id,
                ':image_url' => $image_path,
                ':technical_details' => $technical_details,
                ':is_featured' => $is_featured
            ]);
            
            // Success! Store message in session
            $_SESSION['success'] = "Artwork added successfully!";
            
            // Redirect to same page (clears form)
            header("Location: add-artwork.php");
            exit;
            
        } catch (PDOException $e) {
            // Database error occurred
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// ===========================================
// SECTION 3: GET CATEGORIES FOR DROPDOWN
// ===========================================

// Query to get all active categories
$categories_query = "SELECT CategoryID, CategoryName FROM Categories WHERE IsActive = 1 ORDER BY DisplayOrder";
$categories = $pdo->query($categories_query)->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Artwork - Essence of Art Gallery Admin</title>
    
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Custom styles for the admin page */
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .admin-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .btn-primary {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }
        .btn-primary:hover {
            background-color: #1a252f;
            border-color: #1a252f;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="admin-container">
    
    <!-- Page Title -->
    <h1>🎨 Add New Artwork</h1>
    
    <!-- ===========================================
         SECTION 4: DISPLAY MESSAGES
         =========================================== -->
    
    <?php
    // Display success message if exists
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . $_SESSION['success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['success']);  // Delete message so it doesn't show again
    }
    
    // Display errors if any
    if (!empty($errors)) {
        echo '<div class="alert alert-danger"><ul class="mb-0">';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul></div>';
    }
    ?>
    
    <!-- ===========================================
         SECTION 5: THE FORM
         =========================================== -->
    
    <form method="POST" enctype="multipart/form-data">
        
        <!-- Title Input -->
        <div class="mb-3">
            <label for="title" class="form-label">Artwork Title *</label>
            <input type="text" 
                   class="form-control" 
                   id="title" 
                   name="title" 
                   value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
                   required>
        </div>
        
        <!-- Description Textarea -->
        <div class="mb-3">
            <label for="description" class="form-label">Description / Story</label>
            <textarea class="form-control" 
                      id="description" 
                      name="description" 
                      rows="4"
                      placeholder="Tell the story behind this piece..."><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
        </div>
        
        <!-- Medium Input -->
        <div class="mb-3">
            <label for="medium" class="form-label">Medium</label>
            <input type="text" 
                   class="form-control" 
                   id="medium" 
                   name="medium" 
                   value="<?= isset($_POST['medium']) ? htmlspecialchars($_POST['medium']) : '' ?>"
                   placeholder="e.g., Charcoal on canvas, Oil paint, Acrylic">
        </div>
        
        <!-- Dimensions Input -->
        <div class="mb-3">
            <label for="dimensions" class="form-label">Dimensions</label>
            <input type="text" 
                   class="form-control" 
                   id="dimensions" 
                   name="dimensions" 
                   value="<?= isset($_POST['dimensions']) ? htmlspecialchars($_POST['dimensions']) : '' ?>"
                   placeholder="e.g., 24x36 inches, 60x90 cm">
        </div>
        
        <!-- Year Input -->
        <div class="mb-3">
            <label for="year" class="form-label">Year Created</label>
            <input type="number" 
                   class="form-control" 
                   id="year" 
                   name="year" 
                   value="<?= isset($_POST['year']) ? htmlspecialchars($_POST['year']) : date('Y') ?>"
                   min="1900" 
                   max="<?= date('Y') ?>">
        </div>
        
        <!-- Price Input -->
        <div class="mb-3">
            <label for="price" class="form-label">Price (KES) *</label>
            <input type="number" 
                   class="form-control" 
                   id="price" 
                   name="price" 
                   value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>"
                   step="0.01" 
                   min="0" 
                   required>
        </div>
        
        <!-- Category Dropdown -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Category *</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['CategoryID'] ?>"
                            <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['CategoryID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['CategoryName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Image Upload -->
        <div class="mb-3">
            <label for="image" class="form-label">Artwork Image *</label>
            <input type="file" 
                   class="form-control" 
                   id="image" 
                   name="image" 
                   accept="image/jpeg,image/png,image/jpg,image/gif" 
                   required>
            <div class="form-text">Accepted formats: JPG, JPEG, PNG, GIF. Max size: 5MB</div>
        </div>
        
        <!-- Technical Details -->
        <div class="mb-3">
            <label for="technical_details" class="form-label">Technical Details</label>
            <textarea class="form-control" 
                      id="technical_details" 
                      name="technical_details" 
                      rows="3"
                      placeholder="Frame type, condition, materials, etc."><?= isset($_POST['technical_details']) ? htmlspecialchars($_POST['technical_details']) : '' ?></textarea>
        </div>
        
        <!-- Featured Checkbox -->
        <div class="mb-3 form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   id="is_featured" 
                   name="is_featured"
                   <?= (isset($_POST['is_featured'])) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_featured">
                Feature on homepage
            </label>
        </div>
        
        <!-- Submit Button -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                Add Artwork
            </button>
        </div>
        
    </form>
    
    <!-- Link to view artworks (we'll build this later) -->
    <div class="mt-3 text-center">
        <a href="manage-artworks.php" class="btn btn-outline-secondary">View All Artworks</a>
    </div>
    
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>