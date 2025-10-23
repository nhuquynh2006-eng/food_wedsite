<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$result = $conn->query("SELECT * FROM contacts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Danh sách liên hệ</title>
  <link rel="stylesheet" href="../main.css">
</head>
<body>
  <h1 style="text-align:center;">Danh sách liên hệ</h1>
  <table border="1" cellpadding="10" cellspacing="0" style="margin:20px auto; width:80%;">
    <tr>
      <th>ID</th>
      <th>Họ tên</th>
      <th>Email</th>
      <th>Nội dung</th>
      <th>Ngày gửi</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?php echo $row['id']; ?></td>
      <td><?php echo $row['name']; ?></td>
      <td><?php echo $row['email']; ?></td>
      <td><?php echo $row['message']; ?></td>
      <td><?php echo $row['created_at']; ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</body>
</html>
