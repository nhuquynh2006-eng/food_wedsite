<?php
include '../config.php';
include __DIR__ . '/_auth.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM customers WHERE user_id=$id");
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_users.php"); exit;
}

$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
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
      <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Ngày tạo</th><th>Hành động</th></tr></thead>
      <tbody>
      <?php while($u = $users->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
          <td><a class="akd-btn akd-btn-delete" href="?delete=<?= $u['id'] ?>" onclick="return confirm('Xóa user?')">Xóa</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>