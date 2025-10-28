<?php
include '../config.php';
include __DIR__ . '/_auth.php';

// === LOGIC XỬ LÝ POST: CẬP NHẬT MEMBERSHIP ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_membership'])) {
    $customer_id = intval($_POST['customer_id']);
    $new_level = trim($_POST['membership']);

    // Sử dụng Prepared Statement để cập nhật membership trong bảng customers
    $stmt = $conn->prepare("UPDATE customers SET membership = ? WHERE id = ?");
    if ($stmt && $customer_id > 0) {
        $stmt->bind_param("si", $new_level, $customer_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_users.php");
    exit;
}
// ===============================================

// === LOGIC GET: XÓA USER ===
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Tốt nhất là dùng Prepared Statement, nhưng giữ nguyên logic cũ của bạn
    $conn->query("DELETE FROM customers WHERE user_id=$id");
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_users.php"); exit;
}
// ===============================================

// TRUY VẤN DANH SÁCH USER (JOIN thêm bảng customers và orders để đếm số đơn hàng)
$usersQ = $conn->query("
    SELECT 
        u.*, 
        c.id AS customer_id, 
        c.membership,
        COUNT(o.id) AS order_count /* ĐẾM SỐ ĐƠN HÀNG */
    FROM users u 
    LEFT JOIN customers c ON u.id = c.user_id 
    LEFT JOIN orders o ON c.id = o.customer_id
    GROUP BY u.id, u.username, u.email, u.created_at, c.id, c.membership /* GROUP BY theo các cột không tổng hợp */
    ORDER BY u.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Người dùng</title>
<link rel="stylesheet" href="admin_style.css">
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<div class="page-title">👥 Danh sách người dùng</div>
<div class="table-wrap">
  <div class="akd-card">
    <table class="styled-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Ngày tạo</th>
          <th>Số Đơn Hàng</th> <th>Cấp độ (Membership)</th> 
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
      <?php $membership_levels = ['normal', 'silver', 'gold', 'vip']; ?>
      <?php while($u = $usersQ->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
          
          <td>
            <span style="font-weight: bold; color: <?= $u['order_count'] > 0 ? '#007bff' : '#6c757d' ?>;">
                <?= $u['order_count'] ?>
            </span>
            <?php if ($u['order_count'] > 0): ?>
                <a href="admin_orders.php?customer_id=<?= $u['customer_id'] ?>" style="margin-left: 8px; font-size: 0.9em;">(Xem)</a>
            <?php endif; ?>
          </td>
          
          <td>
            <?php if ($u['customer_id']): // Chỉ hiển thị nếu user có record trong customers ?>
            <form method="post" style="display:flex;gap:6px;align-items:center">
              <input type="hidden" name="customer_id" value="<?= $u['customer_id'] ?>">
              <select name="membership">
                <?php foreach ($membership_levels as $level): ?>
                  <option value="<?= $level ?>" <?= $u['membership']==$level?'selected':'' ?>>
                    <?= ucfirst($level) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button name="update_membership" class="akd-btn akd-btn-primary" style="background-color:#28a745; font-size: 0.9em; padding: 6px 10px;">Cập nhật</button>
            </form>
            <?php else: ?>
                <span style="color:#999; font-style:italic;">Chưa có thông tin KH</span>
            <?php endif; ?>
          </td>
          
          <td><a class="akd-btn akd-btn-delete" href="?delete=<?= $u['id'] ?>" onclick="return confirm('Xóa user?')">Xóa</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>