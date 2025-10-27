<?php
// Đường dẫn chính xác: Tìm file config.php ở thư mục cha (food/)
include '../config.php'; 
session_start();

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) { 
    header("Location: ../login.php"); // Quay lại login.php ở thư mục cha
    exit; 
}

$username = $_SESSION['username'];
$user_id = null;
$customer_id = null;

// 2. Lấy user_id và customer_id bằng Prepared Statements (Bảo mật)
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
if ($stmt_user) {
    $stmt_user->bind_param("s", $username);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($user = $result_user->fetch_assoc()) {
        $user_id = $user['id'];
        
        $stmt_customer = $conn->prepare("SELECT id FROM customers WHERE user_id = ? LIMIT 1");
        if ($stmt_customer) {
            $stmt_customer->bind_param("i", $user_id);
            $stmt_customer->execute();
            $result_customer = $stmt_customer->get_result();
            if ($customer = $result_customer->fetch_assoc()) {
                $customer_id = $customer['id'];
            }
            $stmt_customer->close();
        }
    }
    $stmt_user->close();
}

// 3. Lấy lịch sử đơn hàng
$orders = null;
if ($customer_id) {
    $stmt_orders = $conn->prepare("SELECT id, total, status, created_at FROM orders 
                                 WHERE customer_id = ? 
                                 AND status IN ('completed','cancelled')
                                 ORDER BY id DESC");
    if ($stmt_orders) {
        $stmt_orders->bind_param("i", $customer_id);
        $stmt_orders->execute();
        $orders = $stmt_orders->get_result();
        // Không đóng stmt_orders ở đây vì ta cần $orders->fetch_assoc()
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lịch sử mua hàng</title>
<link rel="stylesheet" href="../main.css"> 
<style>
/* Nhúng CSS định dạng bảng: order_style.css nằm trong cùng thư mục */
<?php include 'order_style.css'; ?>
</style>
</head>
<body>
<header>
  <div class="container">
    <div class="logo"><h1>ĂN KHI ĐÓI</h1><p>Ăn ngon – Sống khỏe</p></div>
    <nav class="menu">
      <a href="../index.php">Trang chủ</a>
      <a href="order.php">Đơn hàng hiện tại</a>
      <a href="../logout.php">Đăng xuất</a>
    </nav>
  </div>
</header>

<div class="container order-detail-section">
  <h2>📜 Lịch sử mua hàng</h2>
  
  <?php if (!$customer_id): ?>
    <p class="warning-message">Tài khoản này chưa có thông tin khách hàng hoặc bạn cần đăng nhập lại.</p>
  <?php elseif (!$orders || $orders->num_rows === 0): ?>
    <p class="empty-message">Bạn chưa có đơn hàng nào đã hoàn tất hoặc bị hủy.</p>
  <?php else: ?>
  <table>
    <tr><th>Mã đơn</th><th>Trạng thái</th><th>Tổng tiền</th><th>Ngày mua</th></tr>
    <?php while($row = $orders->fetch_assoc()): ?>
    <tr>
      <td>#<?= htmlspecialchars($row['id']) ?></td>
      <td class="status <?= htmlspecialchars($row['status']) ?>"><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
      <td><?= number_format($row['total'],0,",",".") ?>đ</td>
      <td><?= date('d-m-Y H:i', strtotime($row['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php endif; ?>
</div>
</body>
</html>