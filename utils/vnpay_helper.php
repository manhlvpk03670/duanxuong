<!-- vnpay helper.php -->
<?php
function vnpay_hash_secure($params, $secretKey) {
    ksort($params);
    $hashData = "";
    $i = 0;
    foreach ($params as $key => $value) {
        if ($i == 1) {
            $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    return hash_hmac('sha512', $hashData, $secretKey);
}

function vnpay_verify_response($vnpayData, $secretKey) {
    // Lấy secure hash từ dữ liệu trả về
    $vnp_SecureHash = $vnpayData['vnp_SecureHash'];
    
    // Xóa secure hash khỏi mảng dữ liệu
    unset($vnpayData['vnp_SecureHash']);
    unset($vnpayData['vnp_SecureHashType']);
    
    // Tính toán secure hash
    $secureHash = vnpay_hash_secure($vnpayData, $secretKey);
    
    // So sánh secure hash
    return $secureHash == $vnp_SecureHash;
}
?>