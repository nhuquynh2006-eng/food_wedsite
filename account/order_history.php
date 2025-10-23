<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) { header("Location: ../login.php"); exit; }

$username = $_SESSION['username'];
$user = $conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1")->fetch_assoc();
$user_id = $user['id'];

$customer = $conn->query("SELECT id FROM customers WHERE user_id=$user_id LIMIT 1")->fetch_assoc();
$customer_id = $customer['id'];

$sql = "SELECT * FROM orders 
        WHERE customer_id=$customer_id 
        AND status IN ('completed','cancelled')
        ORDER BY id DESC";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Lịch sử mua hàng</title>
<link rel="stylesheet" href="../main.css">
<style>
<?php include 'order_style.css'; ?>
</style>
</head>
<body>
<header>
  <div class="container">
    <div class="logo"><h1>ĂN KHI ĐÓI</h1><p>Ăn ngon – Sống khỏe</p></div>
    <nav class="menu">
      <div class="item"><a href="../index.php">Trang chủ</a></div>
      <div class="item"><a href="order.php">Đơn hàng hiện tại</a></div>
      <div class="item"><a href="../logout.php">Đăng xuất</a></div>
    </nav>
  </div>
</header>

<div class="container order-detail-section">
  <h2>📜 Lịch sử mua hàng</h2>
  <table>
    <tr><th>Mã đơn</th><th>Trạng thái</th><th>Tổng tiền</th><th>Ngày mua</th></tr>
    <?php while($row = $orders->fetch_assoc()): ?>
    <tr>
      <td>#<?= $row['id'] ?></td>
      <td class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
      <td><?= number_format($row['total'],0,",",".") ?>đ</td>
      <td><?= $row['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html> 