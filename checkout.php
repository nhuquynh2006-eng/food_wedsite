<?php
session_start();

// Kết nối database
$conn = new mysqli("localhost", "root", "", "food_db");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: account.php");
    exit;
}

// Lấy user_id (ưu tiên session, hoặc tra theo username)
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} else {
    $username = $conn->real_escape_string($_SESSION['username']);
    $u = $conn->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if ($u && $u->num_rows > 0) {
        $user_id = intval($u->fetch_assoc()['id']);
    } else {
        die("❌ Không tìm thấy người dùng có username = $username");
    }
}

// Lấy customer_id của người dùng
$cusQ = $conn->query("SELECT id FROM customers WHERE user_id = $user_id LIMIT 1");
if (!$cusQ || $cusQ->num_rows == 0) {
    die("❌ Không tìm thấy thông tin khách hàng (customers).");
}
$customer_id = intval($cusQ->fetch_assoc()['id']);

// Lấy giỏ hàng gần nhất
$cartQ = $conn->query("SELECT id FROM cart WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1");
if (!$cartQ || $cartQ->num_rows == 0) {
    die("❌ Giỏ hàng trống.");
}
$cart_id = intval($cartQ->fetch_assoc()['id']);

// Lấy sản phẩm trong giỏ hàng
$items = [];
$total = 0;
$sql = "SELECT ci.food_id, ci.quantity, f.price, f.name 
        FROM cart_items ci 
        JOIN foods f ON ci.food_id = f.id 
        WHERE ci.cart_id = $cart_id";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $r['subtotal'] = $r['price'] * $r['quantity'];
        $total += $r['subtotal'];
        $items[] = $r;
    }
} else {
    die("❌ Không có sản phẩm nào trong giỏ hàng.");
}

// Khi nhấn nút "Xác nhận thanh toán"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tạo đơn hàng
    $conn->query("INSERT INTO orders (customer_id, total, status) VALUES ($customer_id, $total, 'pending')");
    $order_id = $conn->insert_id;

    // Lưu chi tiết từng món hàng
    foreach ($items as $it) {
        $fid = intval($it['food_id']);
        $qty = intval($it['quantity']);
        $price = $it['price'];
        $conn->query("INSERT INTO order_items (order_id, food_id, quantity, price)
                      VALUES ($order_id, $fid, $qty, $price)");
    }

    // Xóa giỏ hàng sau khi thanh toán
    $conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");

    echo "<script>alert('✅ Đặt hàng thành công! Cảm ơn bạn.');window.location='index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Thanh toán</title>
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
      <div class="item"><a href="contact.php">LIÊN HỆ</a></div>
      <div class="item"><a href="view_cart.php">🛒 Giỏ hàng</a></div>
    </div>
  </div>
</header>

<div class="cart-container">
  <h2>🧾 Xác nhận đơn hàng</h2>

  <table class="cart-table">
    <tr>
      <th>Tên món</th>
      <th>Giá</th>
      <th>Số lượng</th>
      <th>Tổng</th>
    </tr>
    <?php foreach ($items as $it): ?>
      <tr>
        <td><?= htmlspecialchars($it['name']) ?></td>
        <td><?= number_format($it['price'], 0, ",", ".") ?>đ</td>
        <td><?= $it['quantity'] ?></td>
        <td><?= number_format($it['subtotal'], 0, ",", ".") ?>đ</td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="total">Tổng cộng: <?= number_format($total, 0, ",", ".") ?>đ</div>

  <form method="POST">
    <button type="submit" class="btn btn-checkout">✅ Xác nhận thanh toán</button>
    <a href="view_cart.php" class="btn btn-continue">⬅ Quay lại giỏ hàng</a>
  </form>
</div>
</body>
</html>
