<?php
include '../config.php';
include __DIR__ . '/_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status='$status' WHERE id=$order_id");
    header("Location: admin_orders.php");
    exit;
}

$orders = $conn->query("SELECT o.*, u.username FROM orders o JOIN customers c ON o.customer_id=c.id JOIN users u ON c.user_id=u.id ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Quản lý đơn hàng</title>
<link rel="stylesheet" href="admin_style.css">
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<div class="page-title">📦 Danh sách đơn hàng</div>
<div class="table-wrap">
  <div class="akd-card" style="padding:18px">
    <table class="styled-table">
      <thead><tr><th>ID</th><th>Khách</th><th>Ngày</th><th>Tổng</th><th>Trạng thái</th><th>Hành động</th></tr></thead>
      <tbody>
      <?php while($o = $orders->fetch_assoc()): ?>
        <tr>
          <td>#<?= $o['id'] ?></td>
          <td><?= htmlspecialchars($o['username']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
          <td><?= number_format($o['total'],0,',','.') ?>đ</td>
          <td>
            <form method="post" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
              <select name="status">
                <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>pending</option>
                <option value="processing" <?= $o['status']=='processing'?'selected':'' ?>>processing</option>
                <option value="completed" <?= $o['status']=='completed'?'selected':'' ?>>completed</option>
                <option value="cancelled" <?= $o['status']=='cencelled'?'selected':'' ?>>cancelled</option>
              </select>
              <button name="update_status" class="akd-btn akd-btn-primary">Cập nhật</button>
            </form>
          </td>
          <td><a class="akd-btn" href="admin_order_detail.php?id=<?= $o['id'] ?>">Xem</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>