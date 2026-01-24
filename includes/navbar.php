<?php
// Make sure session is started and cart functions are loaded
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('getCartCount')) {
    require_once __DIR__ . '/cart-functions.php';
}

$cart_count = getCartCount();
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
$user_email = $is_logged_in ? $_SESSION['user_email'] : '';
$user_initials = $is_logged_in ? strtoupper(substr($user_name, 0, 2)) : '';
?>

<!-- User Dropdown Styles -->
<style>
/* User Dropdown Container */
.user-dropdown-container {
    position: relative;
    display: inline-block;
    margin-left: 15px;
}

/* User Profile Button */
.user-profile-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.user-profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* User Avatar Circle */
.user-avatar {
    width: 35px;
    height: 35px;
    background: white;
    color: #667eea;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

/* Email Text */
.user-email-text {
    color: white;
    font-size: 14px;
    font-weight: 500;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Dropdown Arrow */
.dropdown-arrow {
    color: white;
    font-size: 12px;
    transition: transform 0.3s ease;
}

.user-profile-btn.active .dropdown-arrow {
    transform: rotate(180deg);
}

/* Dropdown Menu Panel */
.user-dropdown-menu {
    position: absolute;
    top: 55px;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    min-width: 280px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
    overflow: hidden;
}

.user-dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* Dropdown Header */
.user-dropdown-header {
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-dropdown-header-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.user-dropdown-header-email {
    font-size: 13px;
    opacity: 0.9;
}

/* Dropdown Divider */
.user-dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 8px 0;
}

/* Dropdown Items */
.user-dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}

.user-dropdown-item:hover {
    background: #f3f4f6;
    color: #667eea;
    text-decoration: none;
}

.user-dropdown-item-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.user-dropdown-item-text {
    font-size: 14px;
    font-weight: 500;
}

.user-dropdown-item.logout {
    color: #dc2626;
}

.user-dropdown-item.logout:hover {
    background: #fee2e2;
    color: #dc2626;
}

/* Notification Badge */
.notification-badge {
    background: #ef4444;
    color: white;
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: auto;
    font-weight: 600;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .user-email-text {
        display: none;
    }
    
    .user-dropdown-menu {
        right: -10px;
        min-width: 260px;
    }
    
    .user-dropdown-container {
        margin-left: 0;
        margin-top: 10px;
    }
}

/* Integration with Bootstrap Navbar */
.navbar-nav .user-dropdown-container {
    display: flex;
    align-items: center;
}

@media (max-width: 991px) {
    .navbar-nav .user-dropdown-container {
        width: 100%;
        justify-content: center;
        padding: 10px 0;
    }
    
    .user-profile-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gallery.php">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contacts.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">🛒 Cart (<?= $cart_count ?>)</a>
                </li>
                
                <?php if ($is_logged_in): ?>
                    <!-- USER DROPDOWN MENU - Replaces simple profile link -->
                    <li class="nav-item">
                        <div class="user-dropdown-container">
                            <button class="user-profile-btn" id="userProfileBtn" onclick="toggleUserDropdown()">
                                <div class="user-avatar"><?php echo $user_initials; ?></div>
                                <span class="user-email-text"><?php echo htmlspecialchars($user_email); ?></span>
                                <span class="dropdown-arrow">▼</span>
                            </button>

                            <div class="user-dropdown-menu" id="userDropdownMenu">
                                <!-- User Info Header -->
                                <div class="user-dropdown-header">
                                    <div class="user-dropdown-header-name"><?php echo htmlspecialchars($user_name); ?></div>
                                    <div class="user-dropdown-header-email"><?php echo htmlspecialchars($user_email); ?></div>
                                </div>

                                <!-- Menu Items -->
                                <a href="profile.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">👤</span>
                                    <span class="user-dropdown-item-text">My Profile</span>
                                </a>

                                <a href="orders.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">📦</span>
                                    <span class="user-dropdown-item-text">My Orders</span>
                                </a>

                                <a href="wishlist.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">❤️</span>
                                    <span class="user-dropdown-item-text">Wishlist</span>
                                </a>

                                <a href="notifications.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">🔔</span>
                                    <span class="user-dropdown-item-text">Notifications</span>
                                    <span class="notification-badge">3</span>
                                </a>

                                <div class="user-dropdown-divider"></div>

                                <a href="settings.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">⚙️</span>
                                    <span class="user-dropdown-item-text">Settings</span>
                                </a>

                                <a href="addresses.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">📍</span>
                                    <span class="user-dropdown-item-text">Addresses</span>
                                </a>

                                <a href="payment-methods.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">💳</span>
                                    <span class="user-dropdown-item-text">Payment Methods</span>
                                </a>

                                <div class="user-dropdown-divider"></div>

                                <a href="switch-account.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">🔄</span>
                                    <span class="user-dropdown-item-text">Switch Account</span>
                                </a>

                                <a href="help.php" class="user-dropdown-item">
                                    <span class="user-dropdown-item-icon">❓</span>
                                    <span class="user-dropdown-item-text">Help & Support</span>
                                </a>

                                <div class="user-dropdown-divider"></div>

                                <a href="logout.php" class="user-dropdown-item logout">
                                    <span class="user-dropdown-item-icon">🚪</span>
                                    <span class="user-dropdown-item-text">Logout</span>
                                </a>
                            </div>
                        </div>
                    </li>
                <?php else: ?>
                    <!-- Not Logged In Menu -->
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- JavaScript for User Dropdown -->
<script>
function toggleUserDropdown() {
    // Get the dropdown menu and button elements
    const dropdown = document.getElementById('userDropdownMenu');
    const btn = document.getElementById('userProfileBtn');
    
    // Toggle the 'show' class on dropdown (makes it visible/hidden)
    dropdown.classList.toggle('show');
    // Toggle the 'active' class on button (rotates the arrow)
    btn.classList.toggle('active');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    // Find the container element
    const container = document.querySelector('.user-dropdown-container');
    const dropdown = document.getElementById('userDropdownMenu');
    const btn = document.getElementById('userProfileBtn');
    
    // Check if container exists (user is logged in)
    if (!container) return;
    
    // If click was outside the container
    if (!container.contains(event.target)) {
        // Remove the 'show' class (hides dropdown)
        dropdown.classList.remove('show');
        // Remove the 'active' class (resets arrow rotation)
        btn.classList.remove('active');
    }
});

// Close dropdown on escape key
document.addEventListener('keydown', function(event) {
    // Check if ESC key was pressed
    if (event.key === 'Escape') {
        const dropdown = document.getElementById('userDropdownMenu');
        const btn = document.getElementById('userProfileBtn');
        
        // Check if elements exist
        if (dropdown && btn) {
            // Close the dropdown
            dropdown.classList.remove('show');
            btn.classList.remove('active');
        }
    }
});
</script>