<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_db");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Nếu chưa login -> chuyển về trang đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: account.php");
    exit;
}

// Lấy user_id từ session hoặc lookup username
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} else {
    $username = $conn->real_escape_string($_SESSION['username']);
    $u = $conn->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if ($u && $u->num_rows) {
        $user_id = intval($u->fetch_assoc()['id']);
    } else {
        die("Không tìm thấy user.");
    }
}

// Lấy customer_id
$cusQ = $conn->query("SELECT id FROM customers WHERE user_id = $user_id LIMIT 1");
if ($cusQ && $cusQ->num_rows) {
    $customer_id = intval($cusQ->fetch_assoc()['id']);
} else {
    // nếu chưa có customers -> giỏ trống
    $customer_id = 0;
}

// Lấy cart_id mới nhất (nếu có)
$cart_id = 0;
if ($customer_id) {
    $cartQ = $conn->query("SELECT id FROM cart WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1");
    if ($cartQ && $cartQ->num_rows) $cart_id = intval($cartQ->fetch_assoc()['id']);
}

// Xử lý xóa (nếu có param remove)
if (isset($_GET['remove'])) {
    $rem = intval($_GET['remove']);
    if ($rem > 0) {
        $conn->query("DELETE FROM cart_items WHERE id = $rem AND cart_id = $cart_id");
    }
    header("Location: view_cart.php");
    exit;
}

// Lấy danh sách items
$items = [];
$total = 0;
if ($cart_id) {
    $sql = "SELECT ci.id AS item_id, f.id AS food_id, f.name, f.price, f.image, ci.quantity
            FROM cart_items ci
            JOIN foods f ON ci.food_id = f.id
            WHERE ci.cart_id = $cart_id";
    $res = $conn->query($sql);
    if ($res && $res->num_rows) {
        while ($r = $res->fetch_assoc()) {
            $r['subtotal'] = floatval($r['price']) * intval($r['quantity']);
            $total += $r['subtotal'];
            $items[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Giỏ hàng</title>
<link rel="stylesheet" href="main.css">
</head>
<body>
<header>
  <div class="container">
    <div class="logo">
      <h1>ĂN KHI ĐÓI</h1>
      <p>Ăn ngon – Sống khỏe</p>
    </div>
    <div class="menu">
      <div class="item"><a href="index.php">TRANG CHỦ</a></div>
      <div class="item"><a href="shop.php#cua-hang">CỬA HÀNG</a></div>
      <div class="item"><a href="Product.php">VỀ CHÚNG TÔI</a></div>
      <div class="item"><a href="contact.php">LIÊN HỆ</a></div>
      <div class="item"><a href="view_cart.php">🛒 Giỏ hàng</a></div>
    </div>
    <div class="search-container">
      <input class="search-box" placeholder="Tìm kiếm...">
      <button class="search-button">Tìm</button>
    </div>
  </div>
</header>

<div class="cart-container">
  <h2>🛒 Giỏ hàng của bạn</h2>

  <?php if (empty($items)): ?>
    <p style="text-align:center;color:#f0e68c;">Giỏ hàng đang trống. <a href="index.php" class="btn btn-continue">Tiếp tục mua hàng</a></p>
  <?php else: ?>
    <table class="cart-table">
      <tr>
        <th>Ảnh</th>
        <th>Tên món</th>
        <th>Giá</th>
        <th>Số lượng</th>
        <th>Tổng</th>
        <th>Hành động</th>
      </tr>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><img src="ảnh/<?= htmlspecialchars($it['image']) ?>" alt="<?= htmlspecialchars($it['name']) ?>"></td>
        <td><?= htmlspecialchars($it['name']) ?></td>
        <td><?= number_format($it['price'], 0, ',', '.') ?>đ</td>
        <td>
          <form method="POST" action="update_cart.php" class="quantity-form">
            <input type="hidden" name="item_id" value="<?= intval($it['item_id']) ?>">
            <button type="submit" name="action" value="decrease" class="qty-btn">➖</button>
            <input class="qty-input" type="number" name="quantity" value="<?= intval($it['quantity']) ?>" min="1">
            <button type="submit" name="action" value="increase" class="qty-btn">➕</button>
          </form>
        </td>
        <td><?= number_format($it['subtotal'], 0, ',', '.') ?>đ</td>
        <td><a class="btn btn-remove" href="view_cart.php?remove=<?= intval($it['item_id']) ?>" onclick="return confirm('Xóa sản phẩm khỏi giỏ?')">❌ Xóa</a></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <div class="total">Tổng cộng: <?= number_format($total, 0, ',', '.') ?>đ</div>
    <div style="text-align:right;">
      <a class="btn btn-checkout" href="checkout.php">✅ Thanh toán</a>
      <a class="btn btn-continue" href="index.php">⬅ Tiếp tục mua hàng</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
