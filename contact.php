<?php
session_start();
include 'config.php';

// Logic hiển thị thông báo
$message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = '<p style="color: green; font-weight: bold; text-align: center; margin-top: 15px;">✅ Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất.</p>';
    } elseif ($_GET['status'] === 'error') {
        $error_msg = htmlspecialchars($_GET['msg'] ?? 'Đã xảy ra lỗi không xác định.');
        $message = '<p style="color: red; font-weight: bold; text-align: center; margin-top: 15px;">❌ Lỗi: ' . $error_msg . '</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Liên hệ</title>
  <link rel="stylesheet" href="main.css">
    <style>
        /* Thêm style cơ bản cho form và thông tin liên hệ */
        .contact-section {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background: #fff;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .contact-info, .contact-form-wrapper {
            flex: 1;
            min-width: 300px;
        }
        .contact-info h3 {
            color: #701f1f;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .contact-info p, 
        .contact-info strong {
            color: #333333; /* Màu chữ chính */
        }
        .contact-info strong {
            color: #5d4037; /* Màu nâu đậm hơn cho các tiêu đề nhỏ */
        }
        
        /* === BỔ SUNG: Chỉnh màu cho tiêu đề form === */
        .contact-form-wrapper h2 {
            color: #701f1f; /* Màu nâu đậm chủ đạo */
            font-size: 1.8em;
            margin-top: 0;
            margin-bottom: 20px;
        }
        /* =========================================== */

        .contact-form input, .contact-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; 
        }
        .contact-form button {
            background-color: #701f1f;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            transition: background-color 0.3s;
        }
        .contact-form button:hover {
            background-color: #a83232;
        }
    </style>
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <h1>ĂN KHI ĐÓI</h1>
            <p>Ăn ngon – Sống khỏe</p>
        </div>
        <nav>
            <a href="index.php">TRANG CHỦ</a>
            <a href="store.php">CỬA HÀNG</a>
            <a href="shop.php">SẢN PHẨM</a>
            <a href="contact.php">LIÊN HỆ</a>
            <a href="view_cart.php">🛒 Giỏ hàng</a>

            <?php if(isset($_SESSION['username'])): ?>
                <a href="account/account.php" style="color: #ffb84d; font-weight: bold;">
                    Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>
                </a>
                <a href="logout.php">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php">Đăng nhập</a>
                <a href="register.php">Đăng ký</a>
            <?php endif; ?>

        </nav>
    </div>
</header>

<h1 style="text-align: center; margin-top: 30px; color: #701f1f;">Liên hệ với chúng tôi</h1>

<?= $message ?>

<section class="contact-section" id="contact">
    <div class="contact-form-wrapper">
        <h2>📩 Để lại thông tin để được tư vấn</h2>
        <form class="contact-form" action="send_contact.php" method="POST">
            <input type="text" name="name" placeholder="Họ và tên *" required>
            <input type="email" name="email" placeholder="Email của bạn *" required>
            <input type="tel" name="phone" placeholder="Số điện thoại">
            <textarea name="message" placeholder="Nội dung cần tư vấn *" rows="5" required></textarea>
            <button type="submit">Gửi thông tin</button>
        </form>
    </div>
    
    <div class="contact-info">
        <h3>Thông tin liên hệ</h3>
        <p><strong>Địa chỉ:</strong> 123 Đường Sống Khỏe, Quận Ăn Ngon, TP. HCM</p>
        <p><strong>Hotline:</strong> 1900 6868 (Miễn phí)</p>
        <p><strong>Email:</strong> hotro@ankhidoi.vn</p>
        <p><strong>Giờ làm việc:</strong> 8:00 - 20:00 (Thứ Hai - Thứ Bảy)</p>
        
        <h3 style="margin-top: 20px;">Tìm chúng tôi trên bản đồ</h3>
        <div style="width: 100%; height: 200px; background-color: #e0e0e0; border: 1px solid #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #555;">
            Khu vực hiển thị Bản đồ (Google Maps Embed)
        </div>
    </div>
</section>

<?php include_once "footer.php"; ?>
</body>
</html>