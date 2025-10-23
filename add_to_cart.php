<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_db");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Nếu chưa login -> về trang đăng nhập
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: account.php");
    exit;
}

// Lấy user_id từ session hoặc lookup theo username
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
} else {
    $username = $conn->real_escape_string($_SESSION['username']);
    $u = $conn->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
    if ($u && $u->num_rows) {
        $user_id = intval($u->fetch_assoc()['id']);
    } else {
        die("Không tìm thấy user tương ứng với username.");
    }
}

// Lấy/ tạo customers.user -> customer_id
$cusQ = $conn->query("SELECT id FROM customers WHERE user_id = $user_id LIMIT 1");
if ($cusQ && $cusQ->num_rows) {
    $customer_id = intval($cusQ->fetch_assoc()['id']);
} else {
    // tạo mới customers (không bắt buộc phone/address)
    $conn->query("INSERT INTO customers (user_id) VALUES ($user_id)");
    $customer_id = $conn->insert_id;
}

// Lấy hoặc tạo cart gần nhất cho customer
$cartQ = $conn->query("SELECT id FROM cart WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1");
if ($cartQ && $cartQ->num_rows) {
    $cart_id = intval($cartQ->fetch_assoc()['id']);
} else {
    $conn->query("INSERT INTO cart (customer_id) VALUES ($customer_id)");
    $cart_id = $conn->insert_id;
}

// Lấy dữ liệu từ form
$food_id = intval($_POST['food_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);
if ($food_id <= 0) {
    header("Location: index.php");
    exit;
}

// Kiểm tra tồn tại food
$fQ = $conn->query("SELECT id FROM foods WHERE id = $food_id LIMIT 1");
if (!$fQ || $fQ->num_rows == 0) {
    die("Sản phẩm không tồn tại.");
}

// Thêm hoặc cập nhật cart_items
$check = $conn->query("SELECT id, quantity FROM cart_items WHERE cart_id=$cart_id AND food_id=$food_id LIMIT 1");
if ($check && $check->num_rows) {
    $r = $check->fetch_assoc();
    $newQty = intval($r['quantity']) + max(1, $quantity);
    $item_id = intval($r['id']);
    $conn->query("UPDATE cart_items SET quantity=$newQty WHERE id=$item_id");
} else {
    $q = max(1, $quantity);
    $conn->query("INSERT INTO cart_items (cart_id, food_id, quantity) VALUES ($cart_id, $food_id, $q)");
}

header("Location: view_cart.php");
exit;
?>
