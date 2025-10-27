<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name']; 
    $email = $_POST['email']; 
    $message = $_POST['message'];
    $conn->query("INSERT INTO contacts (name,email,message) VALUES ('$name','$email','$message')");
    $success = "Gửi liên hệ thành công!";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Liên hệ</title>
  <link rel="stylesheet" href="main.css">
</head>
<body>
  <!-- Header -->
<header>
  <div class="container">
    <div class="logo">
      <h1>ĂN KHI ĐÓI</h1>
      <p>Ăn ngon – Sống khỏe</p>
    </div>
    <nav>
      <a href="index.php">TRANG CHỦ</a>
      <a href="store.php">CỬA HÀNG</a>
      <a href="shop.php">SẢN PHẨM</a>
      <a href="about_store.php">VỀ CHÚNG TÔI</a>
      <a href="contact.php">LIÊN HỆ</a>
      <a href="view_cart.php">🛒 Giỏ hàng</a>

      <?php if(isset($_SESSION['username'])): ?>
  <a href="account/account.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a>
  <a href="logout.php">Đăng xuất</a>
<?php else: ?>
  <a href="login.php">Đăng nhập</a>
  <a href="register.php">Đăng ký</a>
<?php endif; ?>

    </nav>
  </div>
</header>

<h1>Liên hệ với chúng tôi</h1>
<section class="contact-section" id="contact">
  <h2>📩 Để lại thông tin để được tư vấn</h2>
  <form class="contact-form" action="send_contact.php" method="POST">
    <input type="text" name="name" placeholder="Họ và tên" required>
    <input type="email" name="email" placeholder="Email của bạn" required>
    <input type="tel" name="phone" placeholder="Số điện thoại">
    <textarea name="message" placeholder="Nội dung cần tư vấn..." rows="5" required></textarea>
    <button type="submit">Gửi thông tin</button>
  </form>
</section>
<?php if (isset($success)) echo "<p>$success</p>"; ?>
<?php include_once "footer.php"; ?>
</body>
</html>
