<?php
require_once '../includes/mpesa-config.php';
require_once '../includes/mpesa-functions.php';
require_once '../includes/db-connect.php';

$callback_json = file_get_contents('php://input');
mpesa_log("=== CALLBACK RECEIVED ===");
mpesa_log("Raw Data: $callback_json");

$callback_data = json_decode($callback_json, true);

if (!$callback_data) {
    mpesa_log("ERROR: Invalid JSON");
    exit('Invalid data');
}

$result_code = $callback_data['Body']['stkCallback']['ResultCode'] ?? null;
$result_desc = $callback_data['Body']['stkCallback']['ResultDesc'] ?? '';
$checkout_request_id = $callback_data['Body']['stkCallback']['CheckoutRequestID'] ?? '';

mpesa_log("Result Code: $result_code");
mpesa_log("Checkout Request ID: $checkout_request_id");

try {
    $stmt = $pdo->prepare("SELECT OrderID FROM Orders WHERE MpesaCheckoutRequestID = ?");
    $stmt->execute([$checkout_request_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        mpesa_log("ERROR: Order not found");
        exit('Order not found');
    }
    
    $order_id = $order['OrderID'];
    mpesa_log("Found Order ID: $order_id");
    
    if ($result_code == 0) {
        mpesa_log("✅ PAYMENT SUCCESS");
        
        $callback_metadata = $callback_data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
        $mpesa_receipt = '';
        $amount = 0;
        $phone = '';
        $transaction_date = '';
        
        foreach ($callback_metadata as $item) {
            if ($item['Name'] == 'MpesaReceiptNumber') $mpesa_receipt = $item['Value'];
            if ($item['Name'] == 'Amount') $amount = $item['Value'];
            if ($item['Name'] == 'PhoneNumber') $phone = $item['Value'];
            if ($item['Name'] == 'TransactionDate') {
                $date_str = (string)$item['Value'];
                $transaction_date = date('Y-m-d H:i:s', strtotime(
                    substr($date_str, 0, 4) . '-' . substr($date_str, 4, 2) . '-' . 
                    substr($date_str, 6, 2) . ' ' . substr($date_str, 8, 2) . ':' . 
                    substr($date_str, 10, 2) . ':' . substr($date_str, 12, 2)
                ));
            }
        }
        
        mpesa_log("Receipt: $mpesa_receipt");
        
        $update_stmt = $pdo->prepare("
            UPDATE Orders 
            SET PaymentStatus = 'Paid',
                MpesaReceiptNumber = ?,
                MpesaTransactionDate = ?,
                MpesaPhoneNumber = ?,
                MpesaResultDesc = ?
            WHERE OrderID = ?
        ");
        
        $success_msg = "Payment successful - Receipt: $mpesa_receipt";
        $update_stmt->execute([$mpesa_receipt, $transaction_date, $phone, $success_msg, $order_id]);
        
        mpesa_log("✅ Order updated");
        
    } else {
        mpesa_log("❌ PAYMENT FAILED");
        $update_stmt = $pdo->prepare("UPDATE Orders SET PaymentStatus = 'Payment Failed', MpesaResultDesc = ? WHERE OrderID = ?");
        $update_stmt->execute([$result_desc, $order_id]);
    }
    
} catch (PDOException $e) {
    mpesa_log("ERROR: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
mpesa_log("=== CALLBACK PROCESSED ===");
?>