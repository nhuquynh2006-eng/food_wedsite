<?php
include '../config.php';
session_start();

if(!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Tài khoản của tôi</title>
  <link rel="stylesheet" href="../main.css">
  <style>
    .account-container {
      max-width: 600px;
      margin: 40px auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .account-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #701f1f;
    }
    .account-info p {
      margin: 10px 0;
      font-size: 16px;
    }
    .account-actions a {
      color: #701f1f;               /* Màu đỏ nâu chủ đạo */
     text-decoration: none;        /* Bỏ gạch chân */
     font-weight: 600;
     font-size: 16px;
     transition: 0.3s;
    }

     .account-actions a:hover {
     color: #a83232;               /* Màu đậm hơn khi rê chuột */
     text-decoration: underline;   /* Gạch chân khi hover */
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
      <div class="logo">
        <h1>ĂN KHI ĐÓI</h1>
        <p>Ăn ngon – Sống khỏe</p>
      </div>
      <nav class="menu">
        <div class="item"><a href="../index.php">Trang chủ</a></div>
        <div class="item"><a href="../store.php">Cửa hàng</a></div>
        <div class="item"><a href="../view_cart.php">🛒 Giỏ hàng</a></div>
        <div class="item"><a href="../logout.php">Đăng xuất</a></div>
      </nav>
    </div>
  </header>

  <div class="account-container">
    <h2>👤 Thông tin tài khoản</h2>
    <div class="account-info">
      <p><strong>Tên đăng nhập:</strong> <?= htmlspecialchars($user['username']); ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
      <p><strong>Ngày tạo:</strong> <?= $user['created_at']; ?></p>
    </div>
    <div class="account-actions" style="text-align:center; margin-top:20px;">
     <a href="order.php">📦 Xem đơn hàng</a>  | <a href="order_history.php"> Lịch sử đơn hàng</a> | 
     <a href="edit_profile.php">✏️ Sửa thông tin</a>
    </div>

  </div>
</body>
</html>
