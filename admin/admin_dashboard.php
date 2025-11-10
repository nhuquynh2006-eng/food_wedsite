<?php
include '../config.php';
include __DIR__ . '/_auth.php'; // Giả định file này chứa session_start() và kiểm tra đăng nhập

$total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) AS c FROM customers")->fetch_assoc()['c'] ?? 0;
$today = date('Y-m-d');

// Thống kê Doanh thu
$today_revenue = $conn->query("SELECT IFNULL(SUM(total),0) AS s FROM orders WHERE DATE(created_at)='$today'")->fetch_assoc()['s'];
$month_revenue = $conn->query("SELECT IFNULL(SUM(total),0) AS s FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc()['s'];

// Thống kê món bán chạy
$best = $conn->query("SELECT f.name, SUM(oi.quantity) AS sold FROM order_items oi JOIN foods f ON oi.food_id=f.id GROUP BY f.id ORDER BY sold DESC LIMIT 1")->fetch_assoc();
$best_name = $best ? $best['name'] : 'Chưa có';

// === BỔ SUNG TRUY VẤN MỚI CHO DASHBOARD ===
// 1. Số đơn hàng đang chờ xử lý
$pending_orders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'] ?? 0;

// 2. Số lượng feedback mới (chưa có phản hồi)
$new_feedback = $conn->query("SELECT COUNT(*) AS c FROM feedback WHERE response IS NULL OR response = ''")->fetch_assoc()['c'] ?? 0;
// =============================================
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="admin_style.css">
<style>
/* Tùy chỉnh CSS cho các khối mới để làm nổi bật */
.akd-panel .critical { 
    border: 1px solid #dc3545; 
    color: #dc3545 !important; 
}
.akd-panel .info { 
    border: 1px solid #007bff; 
    color: #007bff !important; 
}
.akd-panel .info div:first-child { 
    color: #007bff; /* Màu chữ nhỏ */
}
</style>
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<section class="akd-card">
    <div class="akd-card-title">🧾 <span>Thống kê</span></div>
    <div class="akd-panel">
        <div style="display:flex;gap:14px;flex-wrap:wrap">
            
            <div style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;color:#3e2723;text-align:center">
                <div class="small">Tổng đơn hàng</div>
                <div style="font-weight:800;font-size:20px"><?= $total_orders ?></div>
            </div>
            
            <div style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;color:#3e2723;text-align:center">
                <div class="small">Tổng khách hàng</div>
                <div style="font-weight:800;font-size:20px"><?= $total_customers ?></div>
            </div>
            
            <div style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;color:#3e2723;text-align:center">
                <div class="small">Doanh thu hôm nay</div>
                <div style="font-weight:800;font-size:18px"><?= number_format($today_revenue,0,',','.') ?>đ</div>
            </div>
            
            <div style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;color:#3e2723;text-align:center">
                <div class="small">Doanh thu tháng</div>
                <div style="font-weight:800;font-size:18px"><?= number_format($month_revenue,0,',','.') ?>đ</div>
            </div>
            
            <div style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;color:#3e2723;text-align:center">
                <div class="small">Món bán chạy</div>
                <div style="font-weight:800;font-size:16px"><?= htmlspecialchars($best_name) ?></div>
            </div>

            <div class="critical" style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;text-align:center;font-weight: bold;">
                <div class="small">Đơn chờ xử lý</div>
                <div style="font-weight:800;font-size:20px"><?= $pending_orders ?></div>
            </div>

            <div class="info" style="flex:1;min-width:180px;background:var(--muted);padding:18px;border-radius:8px;text-align:center;font-weight: bold;">
                <div class="small">Feedback mới</div>
                <div style="font-weight:800;font-size:20px"><?= $new_feedback ?></div>
            </div>
        </div>
    </div>
</section>
</body>
</html>