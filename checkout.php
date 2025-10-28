<?php
// === CƠ CHẾ BẮT LỖI MẠNH ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. KẾT NỐI DATABASE
include 'config.php'; // Đảm bảo file này tồn tại và chứa biến $conn

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: login.php"); // Chuyển hướng về login.php
    exit;
}

// Lấy user_id (Sử dụng Prepared Statement để bảo mật)
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id === 0) {
    $stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    if (!$stmt_user) die("Prepare user failed: " . $conn->error);
    $stmt_user->bind_param("s", $_SESSION['username']);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($u = $result_user->fetch_assoc()) {
        $user_id = $u['id'];
        $_SESSION['user_id'] = $user_id;
    } else {
        die("❌ Không tìm thấy người dùng (users).");
    }
    $stmt_user->close();
}

// Lấy customer_id của người dùng (Giả sử bảng customers có full_name, phone, address)
$stmt_cus = $conn->prepare("SELECT id, full_name, phone, address FROM customers WHERE user_id = ? LIMIT 1");
if (!$stmt_cus) die("Prepare customer failed: " . $conn->error);
$stmt_cus->bind_param("i", $user_id);
$stmt_cus->execute();
$cusQ = $stmt_cus->get_result();
if (!$cusQ || $cusQ->num_rows == 0) {
    die("❌ Không tìm thấy thông tin khách hàng (customers).");
}
$customer_data = $cusQ->fetch_assoc();
$customer_id = intval($customer_data['id']);
$stmt_cus->close();

// Lưu thông tin khách hàng hiện tại để pre-fill form
$customer_name = htmlspecialchars($customer_data['full_name'] ?? '');
$customer_phone = htmlspecialchars($customer_data['phone'] ?? '');
$customer_address = htmlspecialchars($customer_data['address'] ?? '');

// Lấy giỏ hàng gần nhất và tính tổng tiền (Sử dụng Prepared Statement)
$cartQ = $conn->query("SELECT id FROM cart WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1");
if (!$cartQ || $cartQ->num_rows == 0) {
    die("❌ Giỏ hàng trống.");
}
$cart_id = intval($cartQ->fetch_assoc()['id']);

$items = [];
$total = 0;
$stmt_items = $conn->prepare("SELECT ci.food_id, ci.quantity, f.price, f.name 
                             FROM cart_items ci 
                             JOIN foods f ON ci.food_id = f.id 
                             WHERE ci.cart_id = ?");
if (!$stmt_items) die("Prepare cart items failed: " . $conn->error);
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

// =========================================================
// === XỬ LÝ KHI NGƯỜI DÙNG NHẤN NÚT "Xác nhận thanh toán" ===
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy dữ liệu từ form
    // LƯU Ý: Phải sử dụng giá trị METHOD TỪ BẢNG PAYMENTS của bạn (cash, credit_card, momo, zalo_pay)
    $payment_method_code = trim($_POST['payment_method'] ?? 'cash'); 
    
    $post_name = trim($_POST['name'] ?? '');
    $post_phone = trim($_POST['phone'] ?? '');
    $post_address = trim($_POST['address'] ?? '');
    $order_status = 'pending';
    $payment_status = 'pending'; // Trạng thái thanh toán ban đầu

    // Kiểm tra các trường bắt buộc
    if (empty($post_name) || empty($post_phone) || empty($post_address)) {
         echo "<script>alert('Vui lòng điền đầy đủ Họ tên, Số điện thoại và Địa chỉ.');window.location='checkout.php';</script>";
         exit;
    }
    
    // Bắt đầu Transaction 
    $conn->begin_transaction();

    try {
        // 4. CẬP NHẬT THÔNG TIN GIAO HÀNG VÀO BẢNG customers
        $stmt_update_customer = $conn->prepare("UPDATE customers SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        if (!$stmt_update_customer) throw new Exception("Prepare update customer failed: " . $conn->error);
        
        $stmt_update_customer->bind_param("sssi", $post_name, $post_phone, $post_address, $customer_id);
        if (!$stmt_update_customer->execute()) throw new Exception("Execute update customer failed: " . $stmt_update_customer->error);
        $stmt_update_customer->close();

        // 5. TẠO ĐƠN HÀNG MỚI (ORDERS) - Chỉ chèn các cột hiện có: customer_id, total, status
        // Không chèn payment_method vì nó nằm trong bảng payments
        $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, total, status) 
                                     VALUES (?, ?, ?)");
        if (!$stmt_order) throw new Exception("Prepare order failed: " . $conn->error);
        
        $stmt_order->bind_param("ids", $customer_id, $total, $order_status);
        if (!$stmt_order->execute()) throw new Exception("Execute order failed: " . $stmt_order->error);
        $order_id = $conn->insert_id;
        $stmt_order->close();
        
        // 5B. TẠO THÔNG TIN THANH TOÁN VÀO BẢNG PAYMENTS
        // Dựa trên cấu trúc bảng Payments của bạn: order_id, amount, method, status
        $stmt_payment = $conn->prepare("INSERT INTO payments (order_id, amount, method, status)
                                       VALUES (?, ?, ?, ?)");
        if (!$stmt_payment) throw new Exception("Prepare payment failed: " . $conn->error);
        
        $stmt_payment->bind_param("idss", $order_id, $total, $payment_method_code, $payment_status);
        if (!$stmt_payment->execute()) throw new Exception("Execute payment failed: " . $stmt_payment->error);
        $stmt_payment->close();

        // 6. LƯU CHI TIẾT TỪNG MÓN HÀNG (order_items)
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, food_id, quantity, price)
                                    VALUES (?, ?, ?, ?)");
        if (!$stmt_item) throw new Exception("Prepare item failed: " . $conn->error);

        foreach ($items as $it) {
            $fid = intval($it['food_id']);
            $qty = intval($it['quantity']);
            $price = $it['price'];
            
            $stmt_item->bind_param("iiid", $order_id, $fid, $qty, $price);
            if (!$stmt_item->execute()) throw new Exception("Execute item failed: " . $stmt_item->error);
        }
        $stmt_item->close();
        
        // 7. XÓA GIỎ HÀNG SAU KHI THANH TOÁN
        $stmt_delete_cart = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        if (!$stmt_delete_cart) throw new Exception("Prepare delete cart failed: " . $conn->error);
        $stmt_delete_cart->bind_param("i", $cart_id);
        if (!$stmt_delete_cart->execute()) throw new Exception("Execute delete cart failed: " . $stmt_delete_cart->error);
        $stmt_delete_cart->close();
        
        // Hoàn tất Transaction
        $conn->commit();
        
        // THÔNG BÁO THÀNH CÔNG VÀ CHUYỂN HƯỚNG
        echo "<script>alert('✅ Đặt hàng thành công! Đơn hàng #" . $order_id . " của bạn đang được xử lý.');window.location='index.php';</script>";
        exit;

    } catch (Exception $e) {
        // Nếu có lỗi, Rollback và báo lỗi
        $conn->rollback();
        $errorMessage = "❌ Lỗi khi đặt hàng: " . $e->getMessage() . " - SQLSTATE: " . ($conn->sqlstate ?? 'N/A');
        echo "<script>alert('" . addslashes($errorMessage) . "');window.location='view_cart.php';</script>";
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
        <a href="account/account.php" style="color: #ffb84d; font-weight: bold;">
          👤 <?= htmlspecialchars($_SESSION['username']) ?>
        </a>
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
    
    <div class="delivery-info" style="margin: 25px 0; padding: 15px; border: 1px solid #ffb84d; border-radius: 8px; background: #fff8e1;">
        <h3 style="margin-top: 0; color: #3e2723;">🚚 Thông tin nhận hàng (Vui lòng kiểm tra & cập nhật)</h3>
        
        <label for="name" style="display: block; font-weight: bold; margin-bottom: 5px;">Họ và Tên:</label>
        <input type="text" id="name" name="name" value="<?= $customer_name ?>" required 
               style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #aaa;" placeholder="Nhập họ và tên đầy đủ">

        <label for="phone" style="display: block; font-weight: bold; margin-bottom: 5px;">Số điện thoại:</label>
        <input type="tel" id="phone" name="phone" value="<?= $customer_phone ?>" required 
               style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #aaa;" placeholder="Nhập số điện thoại">

        <label for="address" style="display: block; font-weight: bold; margin-bottom: 5px;">Địa chỉ giao hàng:</label>
        <input type="text" id="address" name="address" value="<?= $customer_address ?>" required 
               style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #aaa;" placeholder="Nhập địa chỉ cụ thể">
    </div>
    
    <div class="payment-selection" style="margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 8px;">
        <label for="payment_method" style="display: block; font-weight: bold; margin-bottom: 10px; color: #5d4037;">
            Chọn phương thức thanh toán:
        </label>
        <select name="payment_method" id="payment_method" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #aaa; font-size: 16px;">
            <option value="cash">1. Thanh toán tiền mặt (COD)</option>
            <option value="credit_card">2. Thẻ tín dụng/ghi nợ</option>
            <option value="momo">3. Thanh toán qua Momo</option>
            <option value="zalo_pay">4. Thanh toán qua ZaloPay</option>
        </select>
    </div>
    
    <div class="total">Tổng cộng: <?= number_format($total, 0, ",", ".") ?>đ</div>

    <button type="submit" class="btn btn-checkout" style="margin-top: 20px;">✅ Xác nhận thanh toán</button>
    <a href="view_cart.php" class="btn btn-continue">⬅ Quay lại giỏ hàng</a>
  </form>
</div>
</body>
</html>