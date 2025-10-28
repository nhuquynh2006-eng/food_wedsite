<?php
include '../config.php';
include __DIR__ . '/_auth.php';

// Khai báo các biến lọc
$filter_customer_id = intval($_GET['customer_id'] ?? 0);
$filter_status = trim($_GET['status'] ?? '');

// Mảng trạng thái đơn hàng có thể có
$valid_statuses = ['pending', 'processing', 'completed', 'canceled'];

// Khởi tạo WHERE clause và tham số cho Prepared Statement
$where_clauses = [];
$params = '';
$bind_values = [];

// 1. Lọc theo Customer ID (từ trang admin_users.php)
if ($filter_customer_id > 0) {
    $where_clauses[] = "o.customer_id = ?";
    $params .= 'i';
    $bind_values[] = $filter_customer_id;
}

// 2. Lọc theo Status (từ form lọc)
if (!empty($filter_status) && in_array($filter_status, $valid_statuses)) {
    $where_clauses[] = "o.status = ?";
    $params .= 's';
    $bind_values[] = $filter_status;
}

// Xây dựng câu truy vấn
$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

$query = "
    SELECT 
        o.*, 
        u.username 
    FROM orders o 
    JOIN customers c ON o.customer_id=c.id 
    JOIN users u ON c.user_id=u.id 
    " . $where_sql . " 
    ORDER BY o.created_at DESC
";

// Thực thi truy vấn
if (count($where_clauses) > 0) {
    // Dùng Prepared Statement nếu có điều kiện lọc
    $stmt = $conn->prepare($query);
    if ($stmt) {
        // Gắn các tham số
        $stmt->bind_param($params, ...$bind_values);
        $stmt->execute();
        $ordersQ = $stmt->get_result();
        $stmt->close();
    } else {
        die("Lỗi Prepared Statement: " . $conn->error);
    }
} else {
    // Dùng query thông thường nếu không có điều kiện lọc
    $ordersQ = $conn->query($query);
}

// Lấy tổng số đơn hàng hiện tại
$total_orders = $ordersQ ? $ordersQ->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Quản lý Đơn hàng</title>
<link rel="stylesheet" href="admin_style.css">
<style>
/* Tùy chỉnh nhỏ cho form lọc */
.filter-form {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
}
.filter-form label { font-weight: bold; color: #5d4037; }
.filter-form select, .filter-form input[type="text"] {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.status-pending { color: #ffc107; font-weight: bold; }
.status-processing { color: #007bff; font-weight: bold; }
.status-completed { color: #28a745; font-weight: bold; }
.status-canceled { color: #dc3545; font-weight: bold; }
</style>
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<div class="page-title">🛍️ Quản lý Đơn hàng (<?= $total_orders ?> đơn)</div>
<div class="table-wrap">

  <form method="get" class="filter-form">
    <label for="f_status">Lọc theo Trạng thái:</label>
    <select name="status" id="f_status">
        <option value="">-- Tất cả --</option>
        <?php foreach ($valid_statuses as $s): ?>
            <option value="<?= $s ?>" <?= $filter_status == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
    </select>
    
    <label for="f_customer_id">Lọc theo ID Khách hàng:</label>
    <input type="text" name="customer_id" id="f_customer_id" placeholder="Nhập Customer ID" value="<?= $filter_customer_id > 0 ? $filter_customer_id : '' ?>">
    
    <button type="submit" class="akd-btn akd-btn-primary" style="padding: 8px 15px;">Lọc</button>
    <a href="admin_orders.php" class="akd-btn" style="padding: 8px 15px; background-color: #6c757d; color: white;">Đặt lại</a>
  </form>

  <div class="akd-card">
    <table class="styled-table">
      <thead>
        <tr>
          <th>Mã Đơn</th>
          <th>Username</th>
          <th>Tổng tiền</th>
          <th>Ngày đặt</th>
          <th>Trạng thái ĐH</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($ordersQ && $ordersQ->num_rows > 0): ?>
        <?php while($o = $ordersQ->fetch_assoc()): ?>
          <tr>
            <td>#<?= $o['id'] ?></td>
            <td><?= htmlspecialchars($o['username']) ?></td>
            <td><?= number_format($o['total'], 0, ',', '.') ?>đ</td>
            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td>
                <span class="status-<?= strtolower($o['status']) ?>">
                    <?= ucfirst($o['status']) ?>
                </span>
            </td>
            <td>
              <a class="akd-btn" href="admin_order_detail.php?id=<?= $o['id'] ?>" style="padding: 6px 10px; background-color: #007bff; color: white;">Chi tiết</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center; padding: 20px;">Không tìm thấy đơn hàng nào phù hợp.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>