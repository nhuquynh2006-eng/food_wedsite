<?php
include '../config.php';
include __DIR__ . '/_auth.php';

// 1. Lấy và kiểm tra order_id
$order_id = intval($_GET['id'] ?? 0);
if ($order_id <= 0) { 
    header("Location: admin_orders.php"); 
    exit; 
}

// === LOGIC CẬP NHẬT TRẠNG THÁI THANH TOÁN (PAYMENTS.STATUS) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment_status'])) {
    $new_payment_status = trim($_POST['payment_status']);
    
    // Sử dụng Prepared Statement để cập nhật trạng thái thanh toán trong bảng payments
    $stmt_update_payment = $conn->prepare("UPDATE payments SET status = ? WHERE order_id = ?");
    
    if ($stmt_update_payment) {
        $stmt_update_payment->bind_param("si", $new_payment_status, $order_id);
        if ($stmt_update_payment->execute()) {
            // Chuyển hướng về chính file admin_order_detail.php
            header("Location: admin_order_detail.php?id=" . $order_id); 
            exit;
        } else {
             // Tùy chọn: Thêm debug lỗi SQL nếu cần
             // die("Lỗi cập nhật SQL: " . $stmt_update_payment->error);
        }
        $stmt_update_payment->close();
    }
}
// ===============================================================

// 2. TRUY VẤN CHI TIẾT ĐƠN HÀNG VÀ TẤT CẢ THÔNG TIN KHÁCH HÀNG (Dùng LEFT JOIN)
$stmt = $conn->prepare("
    SELECT 
        o.*, 
        u.username,
        c.full_name, 
        c.phone, 
        c.address,
        p.method AS payment_method,
        p.status AS payment_status /* Lấy trạng thái thanh toán */
    FROM orders o 
    JOIN customers c ON o.customer_id=c.id 
    JOIN users u ON c.user_id=u.id 
    LEFT JOIN payments p ON p.order_id=o.id /* Đổi thành LEFT JOIN */
    WHERE o.id = ? LIMIT 1
");

if (!$stmt) { die("Lỗi Prepare Statement: " . $conn->error); }

$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderQ = $stmt->get_result();

if (!$orderQ || $orderQ->num_rows == 0) { 
    echo "Không tìm thấy đơn hàng."; 
    exit; 
}
$order = $orderQ->fetch_assoc();
$stmt->close();

// Nếu không tìm thấy payment (do LEFT JOIN), gán giá trị mặc định để tránh lỗi
if (!isset($order['payment_status'])) {
    $order['payment_method'] = 'Chưa có thông tin';
    $order['payment_status'] = ''; // Gán rỗng để select không bị lỗi
}

// 3. TRUY VẤN CHI TIẾT MÓN HÀNG
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
      <p>
        <strong style="color: #5d4037;">Mã đơn:</strong> #<?= $order['id'] ?> 
        — <strong style="color: #5d4037;">Ngày mua:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
        — <strong style="color: #5d4037;">Trạng thái ĐH:</strong> <span style="font-weight:bold; color:<?= $order['status']=='completed'?'green':'#ffb84d' ?>"><?= $order['status'] ?></span>
      </p>
      <hr>
      
      <div style="display:flex; gap: 40px;">
          <div>
              <h3 style="color: #5d4037;">Thông tin Khách hàng</h3>
              <p><strong style="color: #5d4037;">Username:</strong> <?= htmlspecialchars($order['username']) ?></p>
              <p><strong style="color: #5d4037;">Họ tên:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
              <p><strong style="color: #5d4037;">SĐT:</strong> <?= htmlspecialchars($order['phone']) ?></p>
              <p><strong style="color: #5d4037;">Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>
          </div>
          <div>
              <h3 style="color: #5d4037;">Thông tin Thanh toán</h3>
              <p><strong style="color: #5d4037;">Tổng tiền:</strong> <span style="color:red; font-size:1.1em;"><?= number_format($order['total'],0,',','.') ?>đ</span></p>
              <p><strong style="color: #5d4037;">Phương thức:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>

              <form method="post" style="margin-top: 10px; display:flex; gap: 8px; align-items:center;">
                  <input type="hidden" name="order_id" value="<?= $order['id'] ?>"> 
                  <label for="p_status" style="font-weight: bold; color: #5d4037;">Trạng thái:</label>
                  <select name="payment_status" id="p_status">
                      <option value="pending" <?= $order['payment_status']=='pending'?'selected':'' ?>>Đang chờ</option>
                      <option value="success" <?= $order['payment_status']=='success'?'selected':'' ?>>Thành công</option>
                      <option value="failed" <?= $order['payment_status']=='failed'?'selected':'' ?>>Thất bại</option>
                      <option value="refunded" <?= $order['payment_status']=='refunded'?'selected':'' ?>>Đã hoàn tiền</option>
                      <option value="" <?= $order['payment_status']==''?'selected':'' ?>>-- Chọn trạng thái --</option>
                  </select>
                  <button name="update_payment_status" class="akd-btn akd-btn-primary" style="padding: 8px 12px; font-size: 0.9em;">Cập nhật</button>
              </form>
          </div>
      </div>
      <hr>
      
      <h3 style="color: #5d4037;">Chi tiết món hàng</h3>
      <table class="styled-table">
        <thead><tr><th>Tên</th><th>Số lượng</th><th>Giá/SP</th><th>Tổng</th></tr></thead>
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
      <div style="text-align:right;font-weight:bold;margin-top:10px;color:#3e2723">TỔNG ĐƠN HÀNG: <?= number_format($order['total'],0,',','.') ?>đ</div>
      
      <div style="margin-top:20px">
          <a class="akd-btn akd-btn-primary" href="admin_orders.php">⬅ Quay lại danh sách</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>