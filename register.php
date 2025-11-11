<?php
include 'config.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check_sql = "SELECT * FROM users WHERE username=? OR email=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Tên đăng nhập hoặc email đã tồn tại!";
        } else {
           $sql = "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $hashed_password, $token);
            $token = bin2hex(random_bytes(16));

            if ($stmt->execute()) {
                // Bật hiện lỗi khi test
                error_reporting(E_ALL);
                ini_set('display_errors', 1);

                // === GỬI MAIL CHÀO MỪNG ===
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '2431540078@vaa.edu.vn'; // thay bằng email của bạn
                    $mail->Password   = '';    // mật khẩu ứng dụng Gmail
                    $mail->SMTPSecure = 'tls';
                    $mail->Port       = 587;

                    $mail->setFrom('2431540078@vaa.edu.vn', 'ĂN KHI ĐÓI');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Chào mừng bạn đến với ĂN KHI ĐÓI!';
                    $mail->Body = "<h3>Xin chào $username!</h3>
               <p>Bạn đã đăng ký tài khoản trên <b>ĂN KHI ĐÓI</b>.</p>
               <p>Vui lòng nhấn <a href='http://localhost/food/verify.php?token=$token'>vào đây</a> để kích hoạt tài khoản.</p>";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Không gửi được mail: {$mail->ErrorInfo}");
                }

                $_SESSION['username'] = $username;
                header("Location: index.php");
                exit();
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
  <?php if (!empty($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>
  <form action="register.php" method="POST">
    <input type="text" name="username" placeholder="Tên đăng nhập" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
    <button type="submit">Đăng ký</button>
  </form>
  <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
</div>
</body>
</html>
