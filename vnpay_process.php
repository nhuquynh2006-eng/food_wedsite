<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ========== CẤU HÌNH VNPAY (APPLY TRIM() CHO SECRET KEY) ==========
$vnp_TmnCode = "QEA58PR2";
$vnp_HashSecret = trim("37T0ZYF78IRJVFJY2T989L6WMVNIR8MF"); // Loại bỏ ký tự thừa
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
// 🚨 CẬP NHẬT ĐƯỜNG DẪN CỦA BẠN 🚨
$vnp_ReturnUrl = "http://localhost/food_website/vnpay_return.php"; 

// --- DỮ LIỆU MẪU (BẠN THAY BẰNG DỮ LIỆU THỰC TẾ CỦA MÌNH) ---
$amount = 100000; 
$order_id = time(); // Mã đơn hàng duy nhất
// -----------------------------------------------------------------

$vnp_Amount = $amount * 100;
$vnp_TxnRef = $order_id;
$vnp_OrderInfo = "Thanh toan don hang #" . $vnp_TxnRef;

// Xử lý IP: Chuẩn hóa IPv6 (::1) về IPv4 (127.0.0.1) nếu chạy local
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if ($vnp_IpAddr === '::1' || $vnp_IpAddr === '127.0.0.1') {
    $vnp_IpAddr = '127.0.0.1'; 
}

$vnp_CreateDate = date('YmdHis');
// 🚨 FIX: THÊM vnp_ExpireDate (15 phút)
$vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', time())); 

$inputData = array(
    "vnp_Version"    => "2.1.0",
    "vnp_TmnCode"    => $vnp_TmnCode,
    "vnp_Amount"     => $vnp_Amount,
    "vnp_Command"    => "pay",
    "vnp_CreateDate" => $vnp_CreateDate,
    "vnp_CurrCode"   => "VND",
    "vnp_IpAddr"     => $vnp_IpAddr,
    "vnp_Locale"     => 'vn',
    "vnp_OrderInfo"  => $vnp_OrderInfo,
    "vnp_OrderType"  => 'other',
    "vnp_ReturnUrl"  => $vnp_ReturnUrl,
    "vnp_TxnRef"     => $vnp_TxnRef,
    "vnp_ExpireDate" => $vnp_ExpireDate,
);

// ========== SẮP XẾP VÀ TẠO CHUỖI HASH ==========
ksort($inputData);
$query = "";
$hashData = "";
foreach ($inputData as $key => $value) {
    // 1. Dùng cho URL: encode key và value
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
    // 2. Dùng để Hash (RAW data): KHÔNG encode để hash
    $hashData .= $key . "=" . $value . "&";
}
$hashData = rtrim($hashData, "&");
$query = rtrim($query, '&');

$vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$vnp_Url .= "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

header("Location: " . $vnp_Url);
exit();
?>