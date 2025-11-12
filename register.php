<?php
include 'config.php';
// Kiểm tra và khởi động session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đường dẫn include PHPMailer
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Xác thực phía máy chủ cơ bản
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ tất cả các trường.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // 2. Kiểm tra tên đăng nhập hoặc email đã tồn tại
        $check_sql = "SELECT username, email FROM users WHERE username=? OR email=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Kiểm tra chính xác tên đăng nhập hay email bị trùng
            $row = $result->fetch_assoc();
            if ($row['username'] === $username) {
                $error = "Tên đăng nhập đã tồn tại!";
            } else {
                $error = "Email đã tồn tại!";
            }
        } else {
            // 3. Khắc phục lỗi Logic SQL: Khai báo token trước khi bind_param
            $token = bin2hex(random_bytes(16));
            
            // Thêm is_verified = 0 (Chưa kích hoạt)
            $sql = "INSERT INTO users (username, email, password, token, is_verified) VALUES (?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $token);

            if ($stmt->execute()) {
                
                // === GỬI MAIL KÍCH HOẠT ===
                $mail = new PHPMailer(true);
                try {
                    // Cấu hình SMTP (Giữ nguyên)
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    // THAY THẾ CHUỖI NÀY BẰNG MẬT KHẨU ỨNG DỤNG CỦA BẠN
                    $mail->Username   = '2431540078@vaa.edu.vn'; 
                    $mail->Password   = 'YOUR_GMAIL_APP_PASSWORD'; 
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Sử dụng PHPMailer constant
                    $mail->Port       = 587;

                    // Cấu hình nội dung Email
                    $mail->setFrom('2431540078@vaa.edu.vn', 'ĂN KHI ĐÓI - Xác thực');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Kích hoạt tài khoản tại ĂN KHI ĐÓI!';
                    
                    // Link xác thực. Lưu ý: Thay 'http://localhost/food/' bằng domain thực tế khi deploy
                    $verification_link = "http://localhost/food/verify.php?token=$token";
                    
                    $mail->Body = "<h3>Xin chào $username!</h3>
                        <p>Bạn đã đăng ký tài khoản trên <b>ĂN KHI ĐÓI</b>.</p>
                        <p>Vui lòng nhấn vào liên kết bên dưới để kích hoạt tài khoản của bạn:</p>
                        <p><a href='$verification_link'>KÍCH HOẠT TÀI KHOẢN NGAY</a></p>
                        <p>Nếu bạn không đăng ký, vui lòng bỏ qua email này.</p>";

                    $mail->send();
                    
                    // 4. Thay đổi luồng: Thông báo thành công và yêu cầu kiểm tra email
                    $success_message = "Đăng ký thành công! Vui lòng kiểm tra email (bao gồm cả thư mục Spam) để kích hoạt tài khoản.";

                } catch (Exception $e) {
                    $error = "Đăng ký thành công nhưng không gửi được email xác thực. Lỗi Mailer: {$mail->ErrorInfo}";
                    error_log("Mailer Error: {$mail->ErrorInfo}");
                }

            } else {
                $error = "Đăng ký thất bại: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng ký tài khoản</title>
  <link rel="stylesheet" href="main.css">
  <link rel="stylesheet" href="auth.css">

</head>
<body>
  <header>
    <div class="container">
      <div class="logo"><h1>ĂN KHI ĐÓI</h1></div>
      <nav class="menu">
        <div class="item"><a href="index.php">Trang chủ</a></div>
        <div class="item"><a href="login.php">Đăng nhập</a></div>
      </nav>
    </div>
  </header>

 <div class="auth-container">
  <h2> Đăng ký tài khoản</h2>
  
    <?php 
    // Hiển thị thông báo LỖI hoặc THÀNH CÔNG
    if (!empty($error)) {
        echo "<p style='color:red; text-align:center;'>$error</p>";
    } elseif (!empty($success_message)) {
        echo "<p style='color:green; text-align:center;'>$success_message</p>";
    }
    ?>

    <?php if (empty($success_message)): ?>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Tên đăng nhập" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
            <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            <button type="submit">Đăng ký</button>
        </form>
    <?php endif; ?>
  
  <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
</div>
</body>
</html>
