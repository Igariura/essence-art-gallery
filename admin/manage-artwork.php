<?php
// ===========================================
// SECTION 1: SETUP & INITIALIZATION
// ===========================================

// Start session for success/error messages
session_start();

// Include database connection
require_once '../includes/db-connect.php';

// ===========================================
// SECTION 2: HANDLE DELETE ACTION
// ===========================================

// Check if there's a 'delete' parameter in the URL
if (isset($_GET['delete'])) {
    
    // Get the artwork ID from URL and make sure it's a number
    $artwork_id = intval($_GET['delete']);
    
    try {
        // First, let's GET the image path so we can delete the file too
        $get_image = $pdo->prepare("SELECT MainImageURL FROM Artworks WHERE ArtworkID = :id");
        $get_image->execute([':id' => $artwork_id]);
        $artwork = $get_image->fetch();
        
        // Delete the artwork from database
        $delete_stmt = $pdo->prepare("DELETE FROM Artworks WHERE ArtworkID = :id");
        $delete_stmt->execute([':id' => $artwork_id]);
        
        // Also delete the physical image file from server
        if ($artwork && !empty($artwork['MainImageURL'])) {
            $file_path = '../' . $artwork['MainImageURL'];
            if (file_exists($file_path)) {
                unlink($file_path);  // unlink = delete file
            }
        }
        
        // Success message
        $_SESSION['success'] = "Artwork deleted successfully!";
        
    } catch (PDOException $e) {
        // If something goes wrong
        $_SESSION['error'] = "Error deleting artwork: " . $e->getMessage();
    }
    
    // Redirect back to same page (without ?delete in URL)
    header("Location: manage-artworks.php");
    exit;
}

// ===========================================
// SECTION 3: FETCH ALL ARTWORKS
// ===========================================

// SQL Query with JOIN to get category names
$sql = "SELECT 
            a.ArtworkID,
            a.Title,
            a.Price,
            a.MainImageURL,
            a.Medium,
            a.IsAvailable,
            a.IsFeatured,
            a.DateAdded,
            c.CategoryName
        FROM Artworks a
        JOIN Categories c ON a.CategoryID = c.CategoryID
        ORDER BY a.DateAdded DESC";

// Execute query and get all results
$artworks = $pdo->query($sql)->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Artworks - Admin Panel</title>
    
    <!-- Bootstrap for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .artwork-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
        .btn-sm {
            font-size: 0.875rem;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .badge {
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="admin-container">
    
    <!-- ===========================================
         SECTION 4: PAGE HEADER
         =========================================== -->
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>🎨 Manage Artworks</h1>
        <a href="add-artwork.php" class="btn btn-primary">+ Add New Artwork</a>
    </div>
    
    <!-- ===========================================
         SECTION 5: DISPLAY MESSAGES
         =========================================== -->
    
    <?php
    // Success message
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show">
                ' . htmlspecialchars($_SESSION['success']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['success']);
    }
    
    // Error message
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show">
                ' . htmlspecialchars($_SESSION['error']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        unset($_SESSION['error']);
    }
    ?>
    
    <!-- ===========================================
         SECTION 6: ARTWORKS TABLE
         =========================================== -->
    
    <!-- Check if there are any artworks -->
    <?php if (empty($artworks)): ?>
        
        <div class="alert alert-info">
            No artworks found. <a href="add-artwork.php">Add your first artwork</a>
        </div>
        
    <?php else: ?>
    
    <!-- Artworks Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Medium</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($artworks as $artwork): ?>
                <tr>
                    <!-- Image Thumbnail -->
                    <td>
                        <img src="../<?= htmlspecialchars($artwork['MainImageURL']) ?>" 
                             alt="<?= htmlspecialchars($artwork['Title']) ?>" 
                             class="artwork-img">
                    </td>
                    
                    <!-- Title -->
                    <td><strong><?= htmlspecialchars($artwork['Title']) ?></strong></td>
                    
                    <!-- Category -->
                    <td><?= htmlspecialchars($artwork['CategoryName']) ?></td>
                    
                    <!-- Medium -->
                    <td><?= htmlspecialchars($artwork['Medium']) ?></td>
                    
                    <!-- Price -->
                    <td>KES <?= number_format($artwork['Price'], 2) ?></td>
                    
                    <!-- Status Badges -->
                    <td>
                        <?php if ($artwork['IsAvailable']): ?>
                            <span class="badge bg-success">Available</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Sold</span>
                        <?php endif; ?>
                        
                        <?php if ($artwork['IsFeatured']): ?>
                            <span class="badge bg-warning text-dark">Featured</span>
                        <?php endif; ?>
                    </td>
                    
                    <!-- Date Added -->
                    <td><?= date('M d, Y', strtotime($artwork['DateAdded'])) ?></td>
                    
                    <!-- Action Buttons -->
                    <td>
                        <a href="edit-artwork.php?id=<?= $artwork['ArtworkID'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            Edit
                        </a>
                        <a href="?delete=<?= $artwork['ArtworkID'] ?>" 
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Are you sure you want to delete this artwork?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php endif; ?>
    
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>