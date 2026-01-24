<?php
// ===========================================
// LOGOUT SCRIPT
// ===========================================

// Start session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Set success message in new session
session_start();
$_SESSION['success'] = "You have been logged out successfully!";

// Redirect to login page
header("Location: login.php");
exit;

?>