<?php 
include 'config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Giới thiệu - Ăn Khi Đói</title>
  <link rel="stylesheet" href="main.css">
  <style>
    .about-store {
      max-width: 1000px;
      margin: 40px auto;
      padding: 20px;
      line-height: 1.6;
    }
    .about-store h2 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 20px;
      color: #701f1f;
    }
    .about-section {
      display: flex;
      align-items: center;
      margin-bottom: 40px;
      gap: 20px;
    }
    .about-section img {
      width: 50%;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .about-section .text {
      width: 50%;
    }
    .about-section .text h3 {
      color: #3b6944;
      margin-bottom: 10px;
    }
    .about-section .text p {
      font-size: 15px;
      color: #333;
    }
    .highlight {
      background: #f0e68c;
      padding: 10px;
      border-left: 5px solid #701f1f;
      margin-top: 10px;
    }
     /* Slideshow */
    .slideshow-container {
      position: relative;
      max-width: 100%;
      margin: 20px auto;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    }
    .slides {
      display: none;
      width: 300px;
      animation: fade 2s;
    }
    @keyframes fade {
      from {opacity: .4} 
      to {opacity: 1}
    }
    .dots {
      text-align: center;
      margin-top: 10px;
    }
    .dot {
      height: 12px;
      width: 12px;
      margin: 0 4px;
      background-color: #bbb;
      border-radius: 50%;
      display: inline-block;
      transition: background-color 0.6s ease;
      cursor: pointer;
    }
    .active-dot {
      background-color: #701f1f;
    }
  </style>
</head>
<body>
<!-- Header -->
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

  <div class="about-store">
  <h2>✨ Giới thiệu về Ăn Khi Đói</h2>

  <!-- Slideshow hình ảnh cửa hàng -->
  <div class="slideshow-container">
    <img class="slides" src="ảnh/quầy.jpg" alt="Cửa hàng Ăn Khi Đói">
    <img class="slides" src="ảnh/cảnh.jpg" alt="Không gian cửa hàng">
    <img class="slides" src="ảnh/hình.jpg" alt="Đội ngũ nhân viên">
  </div>
  <div class="dots">
    <span class="dot"></span> 
    <span class="dot"></span> 
    <span class="dot"></span> 
  </div>

<div class="about-store">
  <h2>✨ Giới thiệu về Ăn Khi Đói</h2>

  <div class="about-section">
    <div class="text">
      <h3>Hành trình bắt đầu</h3>
      <p><strong>Ăn Khi Đói</strong> được ra đời với mong muốn mang đến những món ăn ngon, nhanh chóng và tiện lợi cho mọi người. 
      Từ những nguyên liệu tươi ngon, đội ngũ đầu bếp đã tạo ra hương vị độc đáo, vừa giữ được truyền thống, vừa kết hợp hiện đại.</p>
    </div>
    <img src="ảnh/món.jpg" alt="Cửa hàng Ăn Khi Đói">
  </div>

  <div class="about-section">
    <img src="ảnh/bếp.jpg" alt="Không gian cửa hàng">
    <div class="text">
      <h3>Không gian & Dịch vụ</h3>
      <p>Chúng tôi không chỉ mang đến bữa ăn ngon, mà còn là trải nghiệm thoải mái. 
      Không gian thân thiện, dịch vụ chu đáo và tận tâm chính là điều khiến khách hàng luôn muốn quay lại.</p>
      <div class="highlight">
        💡 Sứ mệnh: <em>"Ăn Khi Đói – Ăn ngon, sống khỏe, hạnh phúc mỗi ngày!"</em>
      </div>
    </div>
  </div>

  <div class="about-section">
    <div class="text">
      <h3>Tầm nhìn tương lai</h3>
      <p>Trong tương lai, <strong>Ăn Khi Đói</strong> sẽ không chỉ là cửa hàng bán đồ ăn, 
      mà còn là một thương hiệu ẩm thực hàng đầu, gắn liền với sự an tâm, chất lượng và niềm vui trong từng bữa ăn.</p>
    </div>
    <img src="ảnh/staff.jpg" alt="Đội ngũ nhân viên">
  </div>
</div>
<script>
let slideIndex = 0;
showSlides();

function showSlides() {
  let i;
  let slides = document.getElementsByClassName("slides");
  let dots = document.getElementsByClassName("dot");

  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }

  slideIndex++;
  if (slideIndex > slides.length) {slideIndex = 1}    

  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active-dot", "");
  }

  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active-dot";

  setTimeout(showSlides, 4000); // đổi ảnh sau 4s
}
</script>
<?php include_once "footer.php"; ?>
</body>
</html>
