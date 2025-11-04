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
    <nav>
      <a href="index.php">TRANG CHỦ</a>
      <a href="store.php">CỬA HÀNG</a>
      <a href="shop.php">SẢN PHẨM</a>
      <a href="about_store.php">VỀ CHÚNG TÔI</a>
      <a href="contact.php">LIÊN HỆ</a>
      <a href="view_cart.php">🛒 Giỏ hàng</a>

      <form action="search_results.php" method="get" class="search-form-header" style="display:flex; align-items:center;">
            <input type="search" name="q" placeholder="Tìm món ăn..." required 
                   style="padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" 
                    style="background: #701f1f; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; margin-left: 5px;">
                Tìm
            </button>
        </form>

     <?php if(isset($_SESSION['username'])): ?>
        <a href="account/account.php" style="color: #3e2723; font-weight: bold;">
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
<?php include_once "footer.php"; ?>
</body>
<script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6909b2e623927319492bd62e/1j96u5lrb';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
</html>
