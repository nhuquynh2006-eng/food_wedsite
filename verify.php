<?php
include 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE token=? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Kích hoạt: xóa token hoặc thêm cột `is_active`
        $stmt = $conn->prepare("UPDATE users SET token=NULL WHERE token=?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        echo "Tài khoản đã được kích hoạt! <a href='login.php'>Đăng nhập ngay</a>";
    } else {
        echo "Token không hợp lệ hoặc đã kích hoạt rồi.";
    }
} else {
    echo "Không tìm thấy token.";
}
?>
