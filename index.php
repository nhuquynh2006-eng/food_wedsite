<?php 
include 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ăn Khi Đói</title>
  <link rel="stylesheet" href="main.css">
</head>
<body>

<!-- Header -->
<header>
  <div class="container">
    <div class="logo">
      <h1>ĂN KHI ĐÓI</h1>
      <p>Ăn ngon – Sống khỏe</p>
    </div>
    <nav class="menu">
      <div class="item"><a href="index.php">TRANG CHỦ</a></div>
      <div class="item"><a href="store.php">CỬA HÀNG</a></div>
      <div class="item"><a href="#wp-products">SẢN PHẨM</a></div>
      <div class="item"><a href="about_store.php">VỀ CHÚNG TÔI</a></div>
      <div class="item"><a href="#contact">LIÊN HỆ</a></div>
      <div class="item"><a href="view_cart.php">🛒 Giỏ hàng</a></div>

      <?php if(isset($_SESSION['username'])): ?>
  <div class="item"><a href="account/account.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a></div>
  <div class="item"><a href="logout.php">Đăng xuất</a></div>
<?php else: ?>
  <div class="item"><a href="login.php">Đăng nhập</a></div>
  <div class="item"><a href="register.php">Đăng ký</a></div>
<?php endif; ?>

    </nav>
  </div>
</header>

<!-- Banner -->
<div id="banner">
  <div class="box-left">
    <h2>
      <span>Thức Ăn</span><br />
      <span>SIÊU NGON</span>
    </h2>
    <p>Giao hàng tận nơi, nhanh chóng</p>
    <p>Gọi là có, cần là đến</p>
    <button>Trải Nghiệm Ngay</button>
  </div>
  <div class="box-right">
    <img src="./ảnh/kem caramel.jpg" alt="Kem Caramel" />
    <img src="./ảnh/súp.jpg" alt="Súp ngon">
    <img src="./ảnh/pasta.jpg" alt="Pasta">
  </div>
</div>

<!-- Sản phẩm -->
<div id="wp-products">
  <h2>NHỮNG SẢN PHẨM MỚI</h2>
  <ul id="list-products">
    <?php
    $result = $conn->query("SELECT * FROM foods WHERE type='new' LIMIT 6");
    while($row = $result->fetch_assoc()) {
        echo '<div class="item">';
        echo '<img src="ảnh/'.$row['image'].'" alt="">';
        echo '<div class="name">'.$row['name'].'</div>';
        echo '<div class="desc">'.$row['description'].'</div>';
        echo '<div class="price">'.number_format($row['price'],0,",",".").'đ</div>';
        echo '<form action="add_to_cart.php" method="POST">';
        echo '    <input type="hidden" name="food_id" value="'.$row['id'].'">';
        echo '    <button type="submit">🛒 Mua Ngay</button>';
        echo '</form>';
        echo '</div>';
    }
    ?>
  </ul>

  <div id="view-more">
    <h2>SẢN PHẨM BÁN CHẠY</h2>
    <ul id="list-products">
      <?php
      $result = $conn->query("SELECT * FROM foods WHERE type='bestseller' LIMIT 6");
      while($row = $result->fetch_assoc()) {
          echo '<div class="item">';
          echo '<img src="ảnh/'.$row['image'].'" alt="">';
          echo '<div class="name">'.$row['name'].'</div>';
          echo '<div class="desc">'.$row['description'].'</div>';
          echo '<div class="price">'.number_format($row['price'],0,",",".").'đ</div>';
          echo '<form action="add_to_cart.php" method="POST">';
          echo '    <input type="hidden" name="food_id" value="'.$row['id'].'">';
          echo '    <button type="submit">🛒 Mua Ngay</button>';
          echo '</form>';
          echo '</div>';
      }
      ?>
    </ul>
  </div>
</div>

<!-- Giới thiệu -->
<section class="about-section">
  <div class="about-container">
    <div class="about-text">
      <h2>✨ Về Ăn Khi Đói</h2>
      <p><strong>Ăn Khi Đói</strong> mang đến trải nghiệm ẩm thực tuyệt vời ngay tại nhà bạn.
      Chúng tôi phục vụ đa dạng món ăn từ truyền thống đến hiện đại, với nguyên liệu tươi ngon và chất lượng nhất.</p>
      <p>Sứ mệnh của chúng tôi là mang hương vị ngon, dịch vụ nhanh chóng và sự hài lòng tuyệt đối cho khách hàng.</p>
    </div>
    <div class="about-image">
      <img src="./ảnh/sushi.jpg" alt="Giới thiệu Ăn Khi Đói">
    </div>
  </div>
</section>

<!-- Liên hệ -->
<section class="contact-section" id="contact">
  <h2>📩 Để lại thông tin để được tư vấn</h2>
  <form class="contact-form" action="send_contact.php" method="POST">
    <input type="text" name="name" placeholder="Họ và tên" required>
    <input type="email" name="email" placeholder="Email của bạn" required>
    <input type="tel" name="phone" placeholder="Số điện thoại">
    <textarea name="message" placeholder="Nội dung cần tư vấn..." rows="5" required></textarea>
    <button type="submit">Gửi thông tin</button>
  </form>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="footer-container">
    <div class="footer-col">
      <h3>Ăn Khi Đói</h3>
      <p>Ăn Khi Đói - nơi mang đến món ăn tươi ngon, giao hàng tận nơi, nhanh chóng và tiện lợi.</p>
    </div>
    <div class="footer-col">
      <h3>Liên hệ</h3>
      <p>📍 123 Đường ABC, Quận XYZ, TP.HCM</p>
      <p>📧 ankhi@example.com</p>
      <p>📞 0123 456 789</p>
    </div>
    <div class="footer-col">
      <h3>Theo dõi</h3>
      <p>Facebook | Instagram | Youtube</p>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2025 Ăn Khi Đói. All rights reserved.</p>
  </div>
</footer>

</body>
</html>
