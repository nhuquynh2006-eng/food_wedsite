<?php
include '../config.php';
include __DIR__ . '/_auth.php';

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM contacts WHERE id=$id");
    header("Location: admin_contacts.php"); exit;
}

$contacts = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Liên hệ</title>
<link rel="stylesheet" href="admin_style.css">
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<div class="page-title">💬 Phản hồi khách hàng</div>
<div class="table-wrap">
  <div class="akd-card">
    <table class="styled-table">
      <thead><tr><th>ID</th><th>Tên</th><th>Email</th><th>Message</th><th>Ngày</th><th>Hành động</th></tr></thead>
      <tbody>
      <?php while($c = $contacts->fetch_assoc()): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td style="max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?= htmlspecialchars($c['message']) ?>"><?= htmlspecialchars($c['message']) ?></td>
          <td><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
          <td><a class="akd-btn akd-btn-delete" href="?delete=<?= $c['id'] ?>" onclick="return confirm('Xóa phản hồi?')">Xóa</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>