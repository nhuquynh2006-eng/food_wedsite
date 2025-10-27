<?php
session_start();
include 'config.php';
// Lưu ý: Dòng trên giả định file config.php chứa đoạn code kết nối database ($conn)

// 1. Lấy danh sách tất cả sản phẩm trực tiếp từ database
$products = [];
$sql = "SELECT f.id, f.name, f.price, f.image, f.description, c.name AS category 
        FROM foods f
        JOIN categories c ON f.category_id = c.id
        ORDER BY f.created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Lưu sản phẩm dưới dạng mảng kết hợp
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cửa Hàng - Ăn Khi Đói</title>
    <link rel="stylesheet" href="main.css">
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
      <a href="about_store.php">VỀ CHÚNG TÔI</a>
      <a href="contact.php">LIÊN HỆ</a>
      <a href="view_cart.php">🛒 Giỏ hàng</a>

      <?php if(isset($_SESSION['username'])): ?>
  <a href="account/account.php">👤 <?= htmlspecialchars($_SESSION['username']) ?></a>
  <a href="logout.php">Đăng xuất</a>
<?php else: ?>
  <a href="login.php">Đăng nhập</a>
  <a href="register.php">Đăng ký</a>
<?php endif; ?>

    </nav>
  </div>
</header>

<div id="wp-products" class="store-page">
    <h2>TẤT CẢ SẢN PHẨM</h2>

    <?php if (empty($products)): ?>
        <p style="text-align: center; color: #5d4037;">Không có sản phẩm nào để hiển thị.</p>
    <?php else: ?>
        <div id="list-products">
            <?php foreach ($products as $product): ?>
            <div class="item">
                <img src="ảnh/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="name"><?= htmlspecialchars($product['name']) ?></div>
                <div class="desc"><?= htmlspecialchars($product['category']) ?></div>
                <div class="price"><?= number_format($product['price'], 0, ",", ".") ?>đ</div>
                
                <form action="add_to_cart.php" method="POST">
                    <input type="hidden" name="food_id" value="<?= intval($product['id']) ?>">
                    <button type="submit">🛒 Mua Ngay</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once "footer.php"; ?>

</body>
</html>