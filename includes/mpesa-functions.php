<?php
// ==============================================================
// M-PESA HELPER FUNCTIONS
// ==============================================================
// Contains reusable functions for M-Pesa integration
// ==============================================================

require_once 'mpesa-config.php';

// =====================
// FUNCTION 1: Get Access Token
// =====================
// Authenticates with Safaricom API and returns access token
// Returns: string (access token) or false on failure
// =====================
function mpesa_get_access_token() {
    $url = MPESA_AUTH_URL;
    
    // Create authorization header: Base64(ConsumerKey:ConsumerSecret)
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);
    
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // For sandbox testing
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($http_code == 200) {
        $result = json_decode($response);
        return $result->access_token ?? false;
    }
    
    // Log error
    mpesa_log("Access Token Error: HTTP $http_code - $response");
    return false;
}

// =====================
// FUNCTION 2: Generate Password
// =====================
// Creates the password for STK Push request
// Password = Base64(Shortcode + Passkey + Timestamp)
// Returns: string (base64 encoded password)
// =====================
function mpesa_generate_password($timestamp) {
    $password = MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp;
    return base64_encode($password);
}

// =====================
// FUNCTION 3: Initiate STK Push
// =====================
// Triggers the payment prompt on customer's phone
// Parameters:
//   $phone - Customer phone number (254XXXXXXXXX format)
//   $amount - Payment amount
//   $order_id - Your order reference number
// Returns: array with success status and data
// =====================
function mpesa_stk_push($phone, $amount, $order_id) {
    // Step 1: Get access token
    $access_token = mpesa_get_access_token();
    if (!$access_token) {
        return ['success' => false, 'message' => 'Failed to authenticate with M-Pesa'];
    }
    
    // Step 2: Prepare timestamp and password
    $timestamp = date('YmdHis'); // Format: 20231216143045
    $password = mpesa_generate_password($timestamp);
    
    // Step 3: Format phone number (remove leading 0, add 254)
    $phone = mpesa_format_phone($phone);
    
    // Step 4: Prepare request data
    $data = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline', // Payment type
        'Amount' => (int)$amount, // Must be integer
        'PartyA' => $phone, // Customer phone number
        'PartyB' => MPESA_SHORTCODE, // Your business shortcode
        'PhoneNumber' => $phone, // Customer phone number (again)
        'CallBackURL' => MPESA_CALLBACK_URL, // Where Safaricom sends result
        'AccountReference' => MPESA_ACCOUNT_REFERENCE . '-' . $order_id, // Your reference
        'TransactionDesc' => MPESA_TRANSACTION_DESC // Description
    ];
    
    // Step 5: Make API request
    $curl = curl_init(MPESA_STK_PUSH_URL);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    // Step 6: Process response
    $result = json_decode($response, true);
    
    // Log the request
    mpesa_log("STK Push Request - Order: $order_id, Phone: $phone, Amount: $amount");
    mpesa_log("Response: $response");
    
    if ($http_code == 200 && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
        // Success! Payment prompt sent to phone
        return [
            'success' => true,
            'message' => 'Payment request sent to phone',
            'CheckoutRequestID' => $result['CheckoutRequestID'] ?? '',
            'MerchantRequestID' => $result['MerchantRequestID'] ?? ''
        ];
    } else {
        // Failed
        return [
            'success' => false,
            'message' => $result['errorMessage'] ?? $result['ResponseDescription'] ?? 'Payment request failed',
            'response' => $result
        ];
    }
}

// =====================
// FUNCTION 4: Format Phone Number
// =====================
// Converts phone to 254XXXXXXXXX format
// Examples:
//   0712345678 → 254712345678
//   712345678 → 254712345678
//   254712345678 → 254712345678
// =====================
function mpesa_format_phone($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove leading zero
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    
    // Add 254 if not present
    if (substr($phone, 0, 3) !== '254') {
        $phone = '254' . $phone;
    }
    
    return $phone;
}

// =====================
// FUNCTION 5: Validate Phone Number
// =====================
// Checks if phone number is valid Kenyan number
// Returns: boolean
// =====================
function mpesa_validate_phone($phone) {
    $phone = mpesa_format_phone($phone);
    
    // Must be 12 digits and start with 254
    if (strlen($phone) === 12 && substr($phone, 0, 3) === '254') {
        return true;
    }
    
    return false;
}

// =====================
// FUNCTION 6: Log to File
// =====================
// Writes logs to file for debugging
// =====================
function mpesa_log($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message" . PHP_EOL;
    file_put_contents(MPESA_LOG_FILE, $log_message, FILE_APPEND);
}

// =====================
// FUNCTION 7: Sanitize Input
// =====================
// Cleans user input to prevent SQL injection
// =====================
function mpesa_sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>