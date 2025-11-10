<?php
// =============== CẤU HÌNH VNPay (giống bên trên) ===============
date_default_timezone_set('Asia/Ho_Chi_Minh');

$vnp_TmnCode    = "QEA58PR2";
$vnp_HashSecret = "37T0ZYF78IRJVFJY2T989L6WMVNIR8MF";

// =============== NHẬN DỮ LIỆU TRẢ VỀ ===============
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = [];

foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_" && $key != "vnp_SecureHash") {
        $inputData[$key] = $value;
    }
}

// =============== KIỂM TRA TÍNH TOÀN VẸN DỮ LIỆU ===============
ksort($inputData);
$hashData = "";
foreach ($inputData as $key => $value) {
    $hashData .= $key . '=' . $value . '&';
}
$hashData = rtrim($hashData, '&');

$secureHashCheck = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// =============== XỬ LÝ KẾT QUẢ ===============
echo "<h2>Kết quả thanh toán</h2>";

if ($secureHashCheck === $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        echo "<p style='color:green;'>✅ Thanh toán thành công!</p>";
        echo "<p>Mã đơn hàng: " . $_GET['vnp_TxnRef'] . "</p>";
        echo "<p>Số tiền: " . number_format($_GET['vnp_Amount'] / 100) . " VND</p>";
    } else {
        echo "<p style='color:red;'>❌ Thanh toán thất bại. Mã lỗi: " . $_GET['vnp_ResponseCode'] . "</p>";
    }
} else {
    echo "<p style='color:red;'>⚠️ Dữ liệu không hợp lệ (sai checksum)</p>";
}
?>
