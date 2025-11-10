<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ========== CẤU HÌNH VNPAY (APPLY TRIM() CHO SECRET KEY) ==========
$vnp_HashSecret = trim("37T0ZYF78IRJVFJY2T989L6WMVNIR8MF"); 
// ===================================================================

$inputData = [];
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

// ========== XÁC THỰC CHỮ KÝ ==========
ksort($inputData);
$hashData = '';
foreach ($inputData as $key => $value) {
    // Nối key=value& (RAW data)
    $hashData .= $key . "=" . $value . "&";
}
$hashData = rtrim($hashData, "&");

$checkHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// --- KIỂM TRA KẾT QUẢ ---
if ($checkHash === $vnp_SecureHash) {
    $RspCode = $inputData['vnp_ResponseCode'];
    $TxnRef = $inputData['vnp_TxnRef'];
    $Amount = $inputData['vnp_Amount'] / 100;

    if ($RspCode == '00') {
        $message = "✅ Giao dịch thành công!";
        // 🚨 CẬP NHẬT DB: Cập nhật trạng thái đơn hàng $TxnRef là "Đã thanh toán"
    } else {
        $message = "❌ Giao dịch thất bại. Mã lỗi VNPAY: " . $RspCode;
    }
} else {
    $message = "⚠️ Lỗi xác thực: Chữ ký không hợp lệ! Vui lòng liên hệ quản trị.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kết quả thanh toán VNPAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4">KẾT QUẢ THANH TOÁN</h3>
        <p class="alert alert-<?php echo ($RspCode == '00' && $checkHash === $vnp_SecureHash) ? 'success' : 'danger'; ?>">
            <?php echo $message; ?>
        </p>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr><th>Mã đơn hàng</th><td><?php echo htmlspecialchars($TxnRef ?? 'N/A'); ?></td></tr>
                <tr><th>Số tiền</th><td><?php echo htmlspecialchars(number_format($Amount ?? 0)); ?> VND</td></tr>
                <tr><th>Mã GD VNPAY</th><td><?php echo htmlspecialchars($inputData['vnp_TransactionNo'] ?? 'N/A'); ?></td></tr>
                <tr><th>Mã phản hồi</th><td><?php echo htmlspecialchars($RspCode ?? '99'); ?></td></tr>
            </table>
        </div>
        <p class="text-center mt-3"><a href="/">Quay lại trang chủ</a></p>
    </div>
</div>
</body>
</html>

