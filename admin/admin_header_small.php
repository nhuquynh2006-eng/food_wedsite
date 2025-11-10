<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<link rel="stylesheet" href="admin_style.css">
<header class="akd-admin-header">
  <div class="akd-header-inner">
    <div class="akd-brand">🍽️ <span>ĂN KHI ĐÓI ADMIN</span></div>
    <nav class="akd-nav">
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="admin_products.php">Sản phẩm</a>
      <a href="admin_orders.php">Quản lý Đơn hàng</a>
      <a href="admin_view_feedback.php">Quản lý Đánh giá</a>
      <a href="admin_contacts.php">Liên hệ</a>
      <a href="admin_users.php">Người dùng</a>
      <a class="akd-logout" href="admin_logout.php">Đăng xuất</a>
    </nav>
  </div>
</header>