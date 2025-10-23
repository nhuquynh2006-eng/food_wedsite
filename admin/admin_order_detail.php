<?php
include '../config.php';
include __DIR__ . '/_auth.php';

$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) { header("Location: admin_orders.php"); exit; }

$orderQ = $conn->query("SELECT o.*, u.username FROM orders o JOIN customers c ON o.customer_id=c.id JOIN users u ON c.user_id=u.id WHERE o.id=$order_id LIMIT 1");
if (!$orderQ || $orderQ->num_rows == 0) { echo "Không tìm thấy đơn hàng."; exit; }
$order = $orderQ->fetch_assoc();

$items = $conn->query("SELECT oi.*, f.name FROM order_items oi JOIN foods f ON oi.food_id=f.id WHERE oi.order_id=$order_id");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Chi tiết đơn #<?= $order_id ?></title>
<link rel="stylesheet" href="admin_style.css">
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<div class="page-title">🧾 Chi tiết đơn hàng #<?= $order['id'] ?></div>
<div class="table-wrap">
  <div class="akd-card">
    <div class="akd-panel">
      <p><strong>Khách:</strong> <?= htmlspecialchars($order['username']) ?> — <strong>Ngày:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
      <table class="styled-table">
        <thead><tr><th>Tên</th><th>Số lượng</th><th>Giá</th><th>Tổng</th></tr></thead>
        <tbody>
        <?php while($it = $items->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($it['name']) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td><?= number_format($it['price'],0,',','.') ?>đ</td>
            <td><?= number_format($it['price']*$it['quantity'],0,',','.') ?>đ</td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
      <div style="text-align:right;font-weight:bold;margin-top:10px;color:#3e2723">Tổng: <?= number_format($order['total'],0,',','.') ?>đ</div>
      <div style="margin-top:12px"><a class="akd-btn akd-btn-primary" href="admin_orders.php">⬅ Quay lại</a></div>
    </div>
  </div>
</div>
</body>
</html>