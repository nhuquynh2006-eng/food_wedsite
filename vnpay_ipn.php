<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ========== CẤU HÌNH VNPAY (APPLY TRIM() CHO SECRET KEY) ==========
$vnp_TmnCode = "QEA58PR2"; 
$vnp_HashSecret = trim("37T0ZYF78IRJVFJY2T989L6WMVNIR8MF"); // FIX: Loại bỏ ký tự thừa
// ===================================================================

$inputData = [];
// Lấy dữ liệu IPN từ $_GET (VNPAY gửi IPN qua GET)
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

// Mặc định phản hồi là lỗi chung
$response = ['RspCode' => '99', 'Message' => 'Xác thực IPN thất bại']; 

try {
    // --- 1. TÍNH TOÁN VÀ XÁC THỰC CHỮ KÝ ---
    ksort($inputData);
    $hashData = '';
    foreach ($inputData as $key => $value) {
        $hashData .= $key . "=" . $value . "&";
    }
    $hashData = rtrim($hashData, "&");
    $checkHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

    if ($checkHash !== $vnp_SecureHash) {
        // Sai chữ ký (Sai Secret Key hoặc dữ liệu bị sửa đổi)
        $response['RspCode'] = '97';
        $response['Message'] = 'Chữ ký không hợp lệ';
    } else {
        // --- 2. XÁC THỰC GIÁ TRỊ VÀ CẬP NHẬT DB ---
        $orderId = $inputData['vnp_TxnRef'];
        $vnp_Amount = $inputData['vnp_Amount'];
        $vnp_ResponseCode = $inputData['vnp_ResponseCode'];

        // 🚨 BƯỚC CẦN TÙY CHỈNH: KẾT NỐI VÀ KIỂM TRA DB 🚨
        
        // * Lấy thông tin đơn hàng gốc từ DB (ví dụ: trạng thái, số tiền gốc)
        // $orderStatus = lay_trang_thai_don_hang_tu_db($orderId); 
        // $actualAmount = lay_so_tien_goc_tu_db($orderId); 
        
        // Mặc định cho trường hợp không kết nối DB
        $isOrderExist = true; // Giả sử đơn hàng luôn tồn tại
        $isAmountValid = true; // Giả sử số tiền luôn đúng
        $isOrderAlreadyProcessed = false; // Giả sử đơn hàng chưa được xử lý

        // --- Kiểm tra theo quy tắc VNPAY ---
        if (!$isOrderExist) {
             // 01: Đơn hàng không tồn tại
            $response = ['RspCode' => '01', 'Message' => 'Đơn hàng không tồn tại'];
        } 
        else if (!$isAmountValid) {
             // 04: Sai số tiền
            $response = ['RspCode' => '04', 'Message' => 'Sai số tiền'];
        } 
        else if ($isOrderAlreadyProcessed) { 
            // 02: Đơn hàng đã được xử lý (tránh xử lý trùng lặp)
            $response = ['RspCode' => '02', 'Message' => 'Đơn hàng đã được xử lý'];
        }
        
        // --- XỬ LÝ KẾT QUẢ CUỐI CÙNG ---
        else if ($vnp_ResponseCode == '00') {
            // Thanh toán thành công (Mã 00)
            // 🚨 CẬP NHẬT DB: Cập nhật trạng thái đơn hàng là 'Đã thanh toán'
            
            $response = ['RspCode' => '00', 'Message' => 'Confirm Success']; // PHẢI TRẢ VỀ NẾU XỬ LÝ THÀNH CÔNG
        } else {
            // Thanh toán thất bại hoặc bị hủy (Mã khác 00)
            // 🚨 CẬP NHẬT DB: Ghi nhận trạng thái thanh toán thất bại
            
            // Nếu không cập nhật được trạng thái, VNPAY vẫn cần biết bạn đã nhận được IPN
            $response = ['RspCode' => '00', 'Message' => 'Confirm Success']; 
        }
    }
} catch (Exception $e) {
    // 99: Lỗi hệ thống không xác định
    $response = ['RspCode' => '99', 'Message' => 'Lỗi không xác định'];
}

header('Content-Type: application/json');
// Cuối cùng: Trả về JSON cho VNPAY
echo json_encode($response);
?>