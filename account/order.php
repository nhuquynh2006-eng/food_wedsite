<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];
$user = $conn->query("SELECT id FROM users WHERE username='$username' LIMIT 1")->fetch_assoc();
$user_id = $user['id'];

$customer = $conn->query("SELECT id FROM customers WHERE user_id=$user_id LIMIT 1")->fetch_assoc();
$customer_id = $customer['id'];

$sql = "SELECT * FROM orders 
        WHERE customer_id=$customer_id 
        AND status IN ('pending','processing')
        ORDER BY id DESC";
$orders = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đơn hàng của tôi</title>
<link rel="stylesheet" href="../main.css">
<style>
/* === Giao diện nền tổng thể === */
body {
  background-color: #F6F2E9;
  font-family: 'Segoe UI', sans-serif;
  color: #2C1A12;
}
/* === Bảng === */
table {
  width: 100%;
  border-collapse: collapse;
  background: #FCFCFA;
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
  border-radius: 12px;
  overflow: hidden;
}
/* === Tiêu đề bảng === */
th {
  background-color: #4E6C3A;
  color: #FDF8F5;
  text-align: left;
  padding: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
}
/* === Nội dung bảng === */
td {
  padding: 12px;
  border-top: 1px solid #ddd;
  color: #2C1A12;
  background-color: #FFF;
}
/* === Hiệu ứng khi rê chuột === */
tr:hover td {
  background-color: #EFE9D9;
}
/* === Tiêu đề trang === */
h2 {
  text-align: center;
  color: #4B3B2B;
  margin-bottom: 20px;
}
/* === Nút === */
.btn {
  padding: 8px 16px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  color: #fff;
  margin: 0 10px;
  transition: 0.3s;
}
.btn-view, .btn-back { background-color: #607D3B; }
.btn-view:hover, .btn-back:hover { background-color: #7EA94B; }
/* === Trạng thái === */
.status.pending { color: #C99200; }
.status.processing { color: #3C91E6; }
/* === Section === */
.order-detail-section {
  background-color: #E9D8C0;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  margin-top: 20px;
}
</style>
</head>
<body>
<header>
  <div class="container">
    <div class="logo"><h1>ĂN KHI ĐÓI</h1><p>Ăn ngon – Sống khỏe</p></div>
    <nav class="menu">
      <div class="item"><a href="../index.php">Trang chủ</a></div>
      <div class="item"><a href="order_history.php">Lịch sử mua hàng</a></div>
      <div class="item"><a href="../view_cart.php">🛒 Giỏ hàng</a></div>
      <div class="item"><a href="../logout.php">Đăng xuất</a></div>
    </nav>
  </div>
</header>

<div class="container order-detail-section">
  <h2>📦 Đơn hàng hiện tại</h2>
  <table>
    <tr><th>Mã đơn</th><th>Trạng thái</th><th>Tổng tiền</th><th>Ngày tạo</th><th>Hành động</th></tr>
    <?php while($row = $orders->fetch_assoc()): ?>
    <tr>
      <td>#<?= $row['id'] ?></td>
      <td class="status <?= $row['status'] ?>"><?= ucfirst($row['status']) ?></td>
      <td><?= number_format($row['total'],0,",",".") ?>đ</td>
      <td><?= $row['created_at'] ?></td>
      <td><a class="btn btn-view" href="order_detail.php?id=<?= $row['id'] ?>">👁️ Xem chi tiết</a></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>

