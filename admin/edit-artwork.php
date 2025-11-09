<?php
// ===========================================
// SECTION 1: SETUP & GET ARTWORK DATA
// ===========================================

// Start session
session_start();

// Include database connection
require_once '../includes/db-connect.php';

// Check if 'id' exists in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // No ID provided - redirect back
    $_SESSION['error'] = "No artwork ID provided";
    header("Location: manage-artworks.php");
    exit;
}

// Get the ID and make it safe (convert to integer)
$artwork_id = intval($_GET['id']);

// Fetch the artwork from database
$stmt = $pdo->prepare("SELECT * FROM Artworks WHERE ArtworkID = :id");
$stmt->execute([':id' => $artwork_id]);
$artwork = $stmt->fetch();

// Check if artwork exists
if (!$artwork) {
    // Artwork not found
    $_SESSION['error'] = "Artwork not found";
    header("Location: manage-artworks.php");
    exit;
}

// ===========================================
// SECTION 2: PROCESS FORM SUBMISSION
// ===========================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- GET FORM DATA ---
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $medium = trim($_POST['medium']);
    $dimensions = trim($_POST['dimensions']);
    $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $technical_details = trim($_POST['technical_details']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    // --- VALIDATION ---
    $errors = [];
    
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($price) || $price <= 0) {
        $errors[] = "Valid price is required";
    }
    if (empty($category_id)) {
        $errors[] = "Category is required";
    }
    
    // --- HANDLE NEW IMAGE UPLOAD (OPTIONAL) ---
    $image_path = $artwork['MainImageURL']; // Keep old image by default
    
    // Check if user uploaded a new image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            
            // Delete old image first
            $old_image_path = '../' . $artwork['MainImageURL'];
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
            
            // Upload new image
            $new_filename = uniqid('artwork_', true) . '.' . $file_ext;
            $upload_path = '../uploads/artworks/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/artworks/' . $new_filename;
            } else {
                $errors[] = "Failed to upload new image";
            }
            
        } else {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG, GIF allowed";
        }
    }
    
    // --- UPDATE DATABASE ---
    if (empty($errors)) {
        
        try {
            $sql = "UPDATE Artworks SET 
                        Title = :title,
                        Description = :description,
                        Medium = :medium,
                        Dimensions = :dimensions,
                        YearCreated = :year,
                        Price = :price,
                        CategoryID = :category_id,
                        MainImageURL = :image_url,
                        TechnicalDetails = :technical_details,
                        IsFeatured = :is_featured,
                        IsAvailable = :is_available
                    WHERE ArtworkID = :id";
            
            $stmt = $pdo->prepare($sql);
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
                ':is_featured' => $is_featured,
                ':is_available' => $is_available,
                ':id' => $artwork_id
            ]);
            
            $_SESSION['success'] = "Artwork updated successfully!";
            header("Location: manage-artwork.php");
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// ===========================================
// SECTION 3: GET CATEGORIES FOR DROPDOWN
// ===========================================

$categories_query = "SELECT CategoryID, CategoryName FROM Categories WHERE IsActive = 1 ORDER BY DisplayOrder";
$categories = $pdo->query($categories_query)->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artwork - Essence of Art Gallery Admin</title>
    
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
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
        .current-image {
            max-width: 200px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="admin-container">
    
    <!-- ===========================================
         SECTION 4: PAGE TITLE & BREADCRUMB
         =========================================== -->
    
    <div class="mb-4">
        <a href="manage-artworks.php" class="btn btn-sm btn-outline-secondary mb-3">← Back to Artworks</a>
        <h1>✏️ Edit Artwork</h1>
    </div>
    
    <!-- ===========================================
         SECTION 5: DISPLAY ERRORS
         =========================================== -->
    
    <?php
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
         SECTION 6: THE FORM (PRE-FILLED)
         =========================================== -->
    
    <form method="POST" enctype="multipart/form-data">
        
        <!-- Title Input (Pre-filled) -->
        <div class="mb-3">
            <label for="title" class="form-label">Artwork Title *</label>
            <input type="text" 
                   class="form-control" 
                   id="title" 
                   name="title" 
                   value="<?= htmlspecialchars($artwork['Title']) ?>" 
                   required>
        </div>
        
        <!-- Description Textarea (Pre-filled) -->
        <div class="mb-3">
            <label for="description" class="form-label">Description / Story</label>
            <textarea class="form-control" 
                      id="description" 
                      name="description" 
                      rows="4"><?= htmlspecialchars($artwork['Description']) ?></textarea>
        </div>
        
        <!-- Medium Input (Pre-filled) -->
        <div class="mb-3">
            <label for="medium" class="form-label">Medium</label>
            <input type="text" 
                   class="form-control" 
                   id="medium" 
                   name="medium" 
                   value="<?= htmlspecialchars($artwork['Medium']) ?>">
        </div>
        
        <!-- Dimensions Input (Pre-filled) -->
        <div class="mb-3">
            <label for="dimensions" class="form-label">Dimensions</label>
            <input type="text" 
                   class="form-control" 
                   id="dimensions" 
                   name="dimensions" 
                   value="<?= htmlspecialchars($artwork['Dimensions']) ?>">
        </div>
        
        <!-- Year Input (Pre-filled) -->
        <div class="mb-3">
            <label for="year" class="form-label">Year Created</label>
            <input type="number" 
                   class="form-control" 
                   id="year" 
                   name="year" 
                   value="<?= htmlspecialchars($artwork['YearCreated']) ?>"
                   min="1900" 
                   max="<?= date('Y') ?>">
        </div>
        
        <!-- Price Input (Pre-filled) -->
        <div class="mb-3">
            <label for="price" class="form-label">Price (KES) *</label>
            <input type="number" 
                   class="form-control" 
                   id="price" 
                   name="price" 
                   value="<?= htmlspecialchars($artwork['Price']) ?>"
                   step="0.01" 
                   min="0" 
                   required>
        </div>
        
        <!-- Category Dropdown (Pre-selected) -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Category *</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['CategoryID'] ?>"
                            <?= ($artwork['CategoryID'] == $category['CategoryID']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['CategoryName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Current Image Display -->
        <div class="mb-3">
            <label class="form-label">Current Image</label>
            <div>
                <img src="../<?= htmlspecialchars($artwork['MainImageURL']) ?>" 
                     alt="Current artwork" 
                     class="current-image img-thumbnail">
            </div>
        </div>
        
        <!-- New Image Upload (Optional) -->
        <div class="mb-3">
            <label for="image" class="form-label">Upload New Image (Optional)</label>
            <input type="file" 
                   class="form-control" 
                   id="image" 
                   name="image" 
                   accept="image/jpeg,image/png,image/jpg,image/gif">
            <div class="form-text">Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG, GIF</div>
        </div>
        
        <!-- Technical Details (Pre-filled) -->
        <div class="mb-3">
            <label for="technical_details" class="form-label">Technical Details</label>
            <textarea class="form-control" 
                      id="technical_details" 
                      name="technical_details" 
                      rows="3"><?= htmlspecialchars($artwork['TechnicalDetails']) ?></textarea>
        </div>
        
        <!-- Featured Checkbox (Pre-checked if featured) -->
        <div class="mb-3 form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   id="is_featured" 
                   name="is_featured"
                   <?= $artwork['IsFeatured'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_featured">
                Feature on homepage
            </label>
        </div>
        
        <!-- Available Checkbox (Pre-checked if available) -->
        <div class="mb-3 form-check">
            <input type="checkbox" 
                   class="form-check-input" 
                   id="is_available" 
                   name="is_available"
                   <?= $artwork['IsAvailable'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_available">
                Available for purchase
            </label>
        </div>
        
        <!-- Submit Buttons -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                Update Artwork
            </button>
            <a href="manage-artworks.php" class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>
        
    </form>
    
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>