<?php
// ==============================================================
// INITIATE M-PESA PAYMENT (FIXED FOR YOUR FORM)
// ==============================================================
session_start();
require_once 'includes/mpesa-config.php';
require_once 'includes/mpesa-functions.php';
require_once 'includes/db-connect.php'; // Changed to match YOUR file name

header('Content-Type: application/json');

// =====================
// STEP 1: Validate Request Method
// =====================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// =====================
// STEP 2: Get Form Data (MATCHING YOUR FORM FIELD NAMES)
// =====================
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$shipping_address = trim($_POST['shipping_address'] ?? '');
$shipping_city = trim($_POST['shipping_city'] ?? '');
$shipping_area = trim($_POST['shipping_area'] ?? '');
$shipping_landmark = trim($_POST['shipping_landmark'] ?? '');
$mpesa_phone = trim($_POST['mpesa_phone'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$total_amount = floatval($_POST['total_amount'] ?? 0);

// Get cart items for order items table
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// =====================
// STEP 3: Validation
// =====================
$errors = [];

if (empty($customer_name)) {
    $errors[] = "Full name is required";
}

if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required";
}

if (empty($customer_phone)) {
    $errors[] = "Phone number is required";
}

if (empty($shipping_address)) {
    $errors[] = "Shipping address is required";
}

if (empty($shipping_city)) {
    $errors[] = "City is required";
}

if (empty($shipping_area)) {
    $errors[] = "Area is required";
}

if (empty($mpesa_phone)) {
    $errors[] = "M-Pesa phone number is required";
}

if ($total_amount <= 0) {
    $errors[] = "Invalid cart amount";
}

// Return errors if any
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Sanitize inputs
$customer_name = mpesa_sanitize($customer_name);
$customer_email = mpesa_sanitize($customer_email);
$customer_phone = mpesa_sanitize($customer_phone);
$shipping_address = mpesa_sanitize($shipping_address);
$shipping_city = mpesa_sanitize($shipping_city);
$shipping_area = mpesa_sanitize($shipping_area);
$shipping_landmark = mpesa_sanitize($shipping_landmark);
$mpesa_phone = mpesa_sanitize($mpesa_phone);

// =====================
// STEP 4: Validate M-Pesa Phone
// =====================
if (!mpesa_validate_phone($mpesa_phone)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid M-Pesa phone number. Use format: 0712345678 or 254712345678'
    ]);
    exit;
}

$formatted_phone = mpesa_format_phone($mpesa_phone);

// =====================
// STEP 5: Create Order in Database (PDO VERSION)
// =====================
try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Generate unique order number
    $order_number = 'ORD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Get user ID if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Prepare notes with M-Pesa phone
    $full_notes = "M-Pesa Phone: " . $mpesa_phone . "\n" . $notes;
    
    // Insert order
    $order_sql = "INSERT INTO Orders (
                    UserID,
                    OrderNumber,
                    CustomerName, 
                    CustomerEmail, 
                    CustomerPhone, 
                    ShippingAddress,
                    ShippingCity,
                    ShippingArea,
                    ShippingLandmark,
                    OrderTotal,
                    PaymentMethod,
                    PaymentStatus,
                    OrderStatus,
                    Notes,
                    OrderDate
                  ) VALUES (
                    :user_id,
                    :order_number,
                    :name, 
                    :email, 
                    :phone, 
                    :address,
                    :city,
                    :area,
                    :landmark,
                    :total,
                    'M-Pesa',
                    'Pending Payment',
                    'Pending',
                    :notes,
                    NOW()
                  )";
    
    $order_stmt = $pdo->prepare($order_sql);
    $order_stmt->execute([
        ':user_id' => $user_id,
        ':order_number' => $order_number,
        ':name' => $customer_name,
        ':email' => $customer_email,
        ':phone' => $customer_phone,
        ':address' => $shipping_address,
        ':city' => $shipping_city,
        ':area' => $shipping_area,
        ':landmark' => $shipping_landmark,
        ':total' => $total_amount,
        ':notes' => $full_notes
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Insert cart items
    if (!empty($cart_items)) {
        $item_sql = "INSERT INTO OrderItems (
                        OrderID, 
                        ArtworkID, 
                        ArtworkTitle, 
                        Price, 
                        Quantity, 
                        Subtotal
                     ) VALUES (
                        :order_id, 
                        :artwork_id, 
                        :title, 
                        :price, 
                        :quantity, 
                        :subtotal
                     )";
        
        $item_stmt = $pdo->prepare($item_sql);
        
        foreach ($cart_items as $item) {
            $item_stmt->execute([
                ':order_id' => $order_id,
                ':artwork_id' => $item['id'],
                ':title' => $item['title'],
                ':price' => $item['price'],
                ':quantity' => $item['quantity'],
                ':subtotal' => ($item['price'] * $item['quantity'])
            ]);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    mpesa_log("Created Order ID: $order_id for $customer_name ($customer_email)");
    
} catch (PDOException $e) {
    $pdo->rollBack();
    mpesa_log("ERROR: Failed to create order - " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()]);
    exit;
}

// =====================
// STEP 6: Initiate M-Pesa STK Push
// =====================
$stk_result = mpesa_stk_push($formatted_phone, $total_amount, $order_id);

if (!$stk_result['success']) {
    // STK Push failed - update order status
    $pdo->query("UPDATE Orders SET PaymentStatus = 'Payment Failed' WHERE OrderID = $order_id");
    
    echo json_encode([
        'success' => false,
        'message' => $stk_result['message']
    ]);
    exit;
}

// =====================
// STEP 7: Save CheckoutRequestID
// =====================
$checkout_request_id = $stk_result['CheckoutRequestID'];

$update_stmt = $pdo->prepare("
    UPDATE Orders 
    SET MpesaCheckoutRequestID = ?,
        MpesaPhoneNumber = ?
    WHERE OrderID = ?
");

$update_stmt->execute([$checkout_request_id, $formatted_phone, $order_id]);

mpesa_log("STK Push initiated for Order $order_id - CheckoutRequestID: $checkout_request_id");

// =====================
// STEP 8: Clear Cart
// =====================
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

// =====================
// STEP 9: Return Success
// =====================
echo json_encode([
    'success' => true,
    'message' => 'Payment request sent! Check your phone to complete payment.',
    'order_id' => $order_id,
    'order_number' => $order_number,
    'checkout_request_id' => $checkout_request_id
]);
?>