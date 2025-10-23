<?php
include '../config.php';
session_start();

if (!isset($_SESSION['username'])) { header("Location: ../login.php"); exit; }

$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) die("Không tìm thấy đơn hàng.");

$orderQ = $conn->query("SELECT * FROM orders WHERE id=$order_id");
if (!$orderQ || !$orderQ->num_rows) die("Đơn hàng không tồn tại.");
$order = $orderQ->fetch_assoc();

$sql = "SELECT f.name, f.image, oi.quantity, oi.price
        FROM order_items oi
        JOIN foods f ON oi.food_id = f.id
        WHERE oi.order_id = $order_id";
$items = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chi tiết đơn hàng</title>
<link rel="stylesheet" href="../main.css">
<style>
<?php include 'order_style.css'; // hoặc dán đoạn CSS ở trên trực tiếp ?>
</style>
</head>
<body>
<header>
  <div class="container">
    <div class="logo"><h1>ĂN KHI ĐÓI</h1><p>Ăn ngon – Sống khỏe</p></div>
    <nav class="menu">
      <div class="item"><a href="order.php">⬅ Quay lại đơn hàng</a></div>
      <div class="item"><a href="../index.php">Trang chủ</a></div>
    </nav>
  </div>
</header>

<div class="container order-detail-section">
  <h2>🧾 Chi tiết đơn hàng #<?= $order['id'] ?></h2>
  <table>
    <tr><th>Ảnh</th><th>Tên món</th><th>Số lượng</th><th>Giá</th><th>Tổng</th></tr>
    <?php $total = 0; while($row = $items->fetch_assoc()): $subtotal = $row['quantity'] * $row['price']; $total += $subtotal; ?>
    <tr>
      <td><img src="../ảnh/<?= $row['image'] ?>" width="70"></td>
      <td><?= htmlspecialchars($row['name']) ?></td>
      <td><?= $row['quantity'] ?></td>
      <td><?= number_format($row['price'],0,",",".") ?>đ</td>
      <td><?= number_format($subtotal,0,",",".") ?>đ</td>
    </tr>
    <?php endwhile; ?>
  </table>
  
  <div class="total">Tổng cộng: <?= number_format($total,0,",",".") ?>đ</div>
</div>
</body>
</html>
