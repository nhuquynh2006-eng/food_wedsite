<?php
session_start();

// Kết nối database
include 'config.php'; // Sử dụng file config.php nếu có

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: account.php");
    exit;
}

// Lấy user_id (ưu tiên session, hoặc tra theo username)
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} else {
    // Nên lấy user_id từ username bằng prepared statement
    $stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt_user->bind_param("s", $_SESSION['username']);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($u = $result_user->fetch_assoc()) {
        $user_id = $u['id'];
    } else {
        die("❌ Không tìm thấy người dùng.");
    }
    $stmt_user->close();
}

// Lấy customer_id của người dùng
$stmt_cus = $conn->prepare("SELECT id FROM customers WHERE user_id = ? LIMIT 1");
$stmt_cus->bind_param("i", $user_id);
$stmt_cus->execute();
$cusQ = $stmt_cus->get_result();
if (!$cusQ || $cusQ->num_rows == 0) {
    die("❌ Không tìm thấy thông tin khách hàng.");
}
$customer_id = intval($cusQ->fetch_assoc()['id']);
$stmt_cus->close();

// Lấy giỏ hàng gần nhất
$cartQ = $conn->query("SELECT id FROM cart WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1");
if (!$cartQ || $cartQ->num_rows == 0) {
    die("❌ Giỏ hàng trống.");
}
$cart_id = intval($cartQ->fetch_assoc()['id']);

// Lấy sản phẩm trong giỏ hàng
$items = [];
$total = 0;
// Sử dụng Prepared Statement cho việc SELECT cũng an toàn hơn
$stmt_items = $conn->prepare("SELECT ci.food_id, ci.quantity, f.price, f.name 
                             FROM cart_items ci 
                             JOIN foods f ON ci.food_id = f.id 
                             WHERE ci.cart_id = ?");
$stmt_items->bind_param("i", $cart_id);
$stmt_items->execute();
$res = $stmt_items->get_result();

if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $r['subtotal'] = $r['price'] * $r['quantity'];
        $total += $r['subtotal'];
        $items[] = $r;
    }
} else {
    die("❌ Không có sản phẩm nào trong giỏ hàng.");
}
$stmt_items->close();

// Khi nhấn nút "Xác nhận thanh toán"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // === 1. LẤY PHƯƠNG THỨC THANH TOÁN ===
    $payment_method = $_POST['payment_method'] ?? 'COD';
    $status = 'pending';
    
    // Bắt đầu Transaction để đảm bảo toàn vẹn dữ liệu
    $conn->begin_transaction();

    try {
        // === 2. TẠO ĐƠN HÀNG VÀ LƯU PHƯƠNG THỨC THANH TOÁN (SỬ DỤNG PREPARED STATEMENT) ===
        $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, total, status, payment_method) 
                                     VALUES (?, ?, ?, ?)");
        if (!$stmt_order) throw new Exception("Prepare order failed: " . $conn->error);
        
        $stmt_order->bind_param("idss", $customer_id, $total, $status, $payment_method);
        $stmt_order->execute();
        $order_id = $conn->insert_id;
        $stmt_order->close();

        // === 3. LƯU CHI TIẾT TỪNG MÓN HÀNG (SỬ DỤNG PREPARED STATEMENT) ===
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, food_id, quantity, price)
                                    VALUES (?, ?, ?, ?)");
        if (!$stmt_item) throw new Exception("Prepare item failed: " . $conn->error);

        foreach ($items as $it) {
            $fid = intval($it['food_id']);
            $qty = intval($it['quantity']);
            $price = $it['price'];
            
            $stmt_item->bind_param("iiid", $order_id, $fid, $qty, $price);
            $stmt_item->execute();
        }
        $stmt_item->close();
        
        // === 4. XÓA GIỎ HÀNG SAU KHI THANH TOÁN ===
        $stmt_delete_cart = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        if (!$stmt_delete_cart) throw new Exception("Prepare delete cart failed: " . $conn->error);
        $stmt_delete_cart->bind_param("i", $cart_id);
        $stmt_delete_cart->execute();
        $stmt_delete_cart->close();
        
        // Hoàn tất Transaction
        $conn->commit();
        
        echo "<script>alert('✅ Đặt hàng thành công! Mã đơn hàng của bạn là #" . $order_id . ".');window.location='index.php';</script>";
        exit;

    } catch (Exception $e) {
        // Nếu có lỗi, ROLLBACK và báo lỗi
        $conn->rollback();
        echo "<script>alert('❌ Lỗi khi đặt hàng: " . $e->getMessage() . "');window.location='view_cart.php';</script>";
        exit;
    }
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

  <form method="POST">
    
    <div class="payment-selection" style="margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 8px;">
        <label for="payment_method" style="display: block; font-weight: bold; margin-bottom: 10px; color: #5d4037;">
            Chọn phương thức thanh toán:
        </label>
        <select name="payment_method" id="payment_method" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #aaa; font-size: 16px;">
            <option value="COD">1. Thanh toán khi nhận hàng (COD)</option>
            <option value="Bank Transfer">2. Chuyển khoản ngân hàng</option>
            <option value="Momo">3. Thanh toán qua Momo</option>
            <option value="ZaloPay">4. Thanh toán qua ZaloPay</option>
        </select>
    </div>
    
    <div class="total">Tổng cộng: <?= number_format($total, 0, ",", ".") ?>đ</div>

    <button type="submit" class="btn btn-checkout" style="margin-top: 20px;">✅ Xác nhận thanh toán</button>
    <a href="view_cart.php" class="btn btn-continue">⬅ Quay lại giỏ hàng</a>
  </form>
</div>
</body>
</html>