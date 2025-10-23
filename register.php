<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Kiểm tra password nhập lại
    if ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        // Mã hóa mật khẩu
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Chuẩn bị query kiểm tra username/email trùng
        $check_sql = "SELECT * FROM users WHERE username=? OR email=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Tên đăng nhập hoặc email đã tồn tại!";
        } else {
            // Insert user mới
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                header("Location: index.php"); // đăng ký xong chuyển về trang chủ
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
