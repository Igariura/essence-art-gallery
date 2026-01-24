<?php
session_start();
require_once '../includes/db-connect.php';
header('Content-Type: application/json');

if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

$order_id = (int)$_POST['order_id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            PaymentStatus,
            MpesaReceiptNumber,
            MpesaResultDesc,
            OrderNumber
        FROM Orders 
        WHERE OrderID = ?
    ");
    
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $response = [
        'success' => true,
        'status' => $order['PaymentStatus'],
        'receipt' => $order['MpesaReceiptNumber'],
        'message' => $order['MpesaResultDesc']
    ];
    
    if ($order['PaymentStatus'] === 'Paid') {
        $response['payment_complete'] = true;
        $response['redirect_url'] = '../order-confirmation.php?order=' . $order['OrderNumber'];
    } elseif (strpos($order['PaymentStatus'], 'Failed') !== false) {
        $response['payment_failed'] = true;
    } else {
        $response['payment_pending'] = true;
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>