<?php
// =============== CẤU HÌNH VNPay ===============
date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TmnCode    = "QEA58PR2";  // Mã website do VNPay cung cấp
$vnp_HashSecret = "37T0ZYF78IRJVFJY2T989L6WMVNIR8MF"; // Chuỗi bí mật
$vnp_Url        = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl  = "http://localhost/project/vnpay_return.php"; // URL nhận kết quả thanh toán

// =============== THÔNG TIN THANH TOÁN ===============
$amount = 100000; // số tiền thanh toán (VND)
$order_id = time(); // mã đơn hàng (duy nhất)
$order_info = "Thanh toan don hang #" . $order_id;
$ipAddr = $_SERVER['REMOTE_ADDR'];

// =============== TẠO MẢNG DỮ LIỆU ===============
$inputData = array(
    "vnp_Version"   => "2.1.0",
    "vnp_TmnCode"   => $vnp_TmnCode,
    "vnp_Amount"    => $amount * 100,
    "vnp_Command"   => "pay",
    "vnp_CreateDate"=> date('YmdHis'),
    "vnp_CurrCode"  => "VND",
    "vnp_IpAddr"    => $ipAddr,
    "vnp_Locale"    => "vn",
    "vnp_OrderInfo" => $order_info,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef"    => $order_id
);

// =============== TẠO CHUỖI HASH ===============
ksort($inputData);
$query = [];
$hashData = "";
foreach ($inputData as $key => $value) {
    $hashData .= $key . '=' . $value . '&';
    $query[] = urlencode($key) . "=" . urlencode($value);
}
$hashData = rtrim($hashData, '&');
$vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$query[] = "vnp_SecureHash=" . $vnp_SecureHash;

$vnp_Url .= "?" . implode('&', $query);

// =============== CHUYỂN HƯỚNG SANG VNPay ===============
header('Location: ' . $vnp_Url);
exit;
?>
