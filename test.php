<?php
// Test database connection
$host = 'localhost';
$dbname = 'essence_art_gallery';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>✅ Database Connected Successfully!</h1>";
    
    // Get categories from database
    $stmt = $pdo->query("SELECT * FROM Categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Categories in Database:</h2>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>" . $category['CategoryName'] . " - " . $category['Description'] . "</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<h1>❌ Connection Failed!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>