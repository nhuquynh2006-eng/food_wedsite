<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username=? OR email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username_or_email, $username_or_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Sai tài khoản hoặc mật khẩu!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đăng nhập</title>
  <link rel="stylesheet" href="main.css">
  <link rel="stylesheet" href="auth.css">

</head>
<body>
  
  <div class="auth-container">
  <h2>🔑 Đăng nhập</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="POST" action="">
    <input type="text" name="username" placeholder="Tên đăng nhập hoặc Email" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <button type="submit">Đăng nhập</button>
  </form>
  <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
</div>
</body>
</html>
