<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ========== CẤU HÌNH VNPAY (APPLY TRIM() CHO SECRET KEY) ==========
$vnp_TmnCode = "QEA58PR2"; 
$vnp_HashSecret = trim("37T0ZYF78IRJVFJY2T989L6WMVNIR8MF"); 
$vnp_ApiUrl = "https://sandbox.vnpayment.vn/merchant_webapi/api/transaction"; 
// ===================================================================

// ---- HÀM GỌI API ----
function callAPI($url, $data)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

$response_data = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['order_id'])) {
    $vnp_RequestId = time();
    $vnp_Version = "2.1.0";
    $vnp_Command = "querydr";
    $vnp_TxnRef = $_POST['order_id'];
    $vnp_TransactionDate = $_POST['trans_date'];
    $vnp_CreateDate = date('YmdHis');
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];

    // Dữ liệu gửi đi
    $inputData = [
        "vnp_RequestId" => $vnp_RequestId,
        "vnp_Version" => $vnp_Version,
        "vnp_Command" => $vnp_Command,
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_TxnRef" => $vnp_TxnRef,
        "vnp_TransactionDate" => $vnp_TransactionDate,
        "vnp_CreateDate" => $vnp_CreateDate,
        "vnp_IpAddr" => $vnp_IpAddr
    ];

    // ========== TẠO CHUỖI HASH CHO API (DÙNG DẤU '|') ==========
    ksort($inputData);
    $hashData = "";
    // 🚨 QUY TẮC API: Nối các GIÁ TRỊ (Value) bằng dấu "|" 🚨
    foreach ($inputData as $key => $value) {
        $hashData .= $value . "|";
    }
    $hashData = rtrim($hashData, "|");

    $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
    $inputData["vnp_SecureHash"] = $vnp_SecureHash;

    // Gửi API
    $response_data = callAPI($vnp_ApiUrl, $inputData);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Truy vấn Giao dịch VNPAY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow p-4">
        <h3>TRA CỨU GIAO DỊCH</h3>
        <form method="post" class="mb-4">
            <div class="form-group mb-3">
                <label for="order_id">Mã đơn hàng (vnp_TxnRef)</label>
                <input class="form-control" id="order_id" name="order_id" type="text" value="<?php echo $_POST['order_id'] ?? time(); ?>" required />
            </div>
            <div class="form-group mb-3">
                <label for="trans_date">Ngày GD (yyyyMMddHHmmss)</label>
                <input class="form-control" id="trans_date" name="trans_date" type="text" value="<?php echo $_POST['trans_date'] ?? date('YmdHis'); ?>" required />
            </div>
            <button type="submit" class="btn btn-primary">Tra cứu</button>
        </form>

        <?php if ($response_data): ?>
        <h4 class="mt-4">KẾT QUẢ TRA CỨU</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tr><th>Tham số</th><th>Giá trị</th></tr>
                <?php foreach ($response_data as $key => $value): ?>
                <tr>
                    <td><?php echo htmlspecialchars($key); ?></td>
                    <td><?php echo htmlspecialchars(is_array($value) ? json_encode($value) : $value); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>