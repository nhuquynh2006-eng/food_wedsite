<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_db");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Lấy dữ liệu từ form
$food_id = intval($_POST['food_id'] ?? 0);
// Quantity mặc định là 1 vì form shop.php không có input quantity
$quantity = intval($_POST['quantity'] ?? 1); 

if ($food_id <= 0 || $quantity <= 0) {
    // Chuyển hướng về trang chủ nếu dữ liệu không hợp lệ
    header("Location: index.php");
    exit;
}

// Kiểm tra tồn tại food
$fQ = $conn->query("SELECT id FROM foods WHERE id = $food_id LIMIT 1");
if (!$fQ || $fQ->num_rows == 0) {
    // Chuyển hướng về trang chủ nếu sản phẩm không tồn tại
    header("Location: index.php?error=" . urlencode("Sản phẩm không tồn tại."));
    exit;
}

// =================================================================
// === LOGIC XỬ LÝ GIỎ HÀNG ===
// =================================================================

// --- PHẦN 1: XỬ LÝ KHI NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP (Dùng DB Cart) ---
if (isset($_SESSION['user_id']) || isset($_SESSION['username'])) {
    
    // 1. Lấy user_id
    $user_id = intval($_SESSION['user_id'] ?? 0);
    if ($user_id === 0) {
        $username = $conn->real_escape_string($_SESSION['username']);
        $u = $conn->query("SELECT id FROM users WHERE username = '$username' LIMIT 1");
        if ($u && $u->num_rows) {
            $user_id = intval($u->fetch_assoc()['id']);
            $_SESSION['user_id'] = $user_id; // Cập nhật lại session
        } else {
            // Nếu không tìm thấy user, ta xử lý như Guest (chuyển sang phần 2)
            goto guest_cart; 
        }
    }

    // 2. Lấy/tạo customer_id
    $cusQ = $conn->query("SELECT id FROM customers WHERE user_id = $user_id LIMIT 1");
    if ($cusQ && $cusQ->num_rows) {
        $customer_id = intval($cusQ->fetch_assoc()['id']);
    } else {
        $conn->query("INSERT INTO customers (user_id) VALUES ($user_id)");
        $customer_id = $conn->insert_id;
    }

    // 3. Lấy hoặc tạo cart_id gần nhất cho customer
    $cartQ = $conn->query("SELECT id FROM cart WHERE customer_id = $customer_id ORDER BY id DESC LIMIT 1");
    if ($cartQ && $cartQ->num_rows) {
        $cart_id = intval($cartQ->fetch_assoc()['id']);
    } else {
        $conn->query("INSERT INTO cart (customer_id) VALUES ($customer_id)");
        $cart_id = $conn->insert_id;
    }

    // 4. Thêm hoặc cập nhật cart_items (Trong DB)
    $check = $conn->query("SELECT id, quantity FROM cart_items WHERE cart_id=$cart_id AND food_id=$food_id LIMIT 1");
    if ($check && $check->num_rows) {
        $r = $check->fetch_assoc();
        // Lấy số lượng mới (lớn hơn hoặc bằng 1)
        $newQty = intval($r['quantity']) + $quantity; 
        $item_id = intval($r['id']);
        $conn->query("UPDATE cart_items SET quantity=$newQty WHERE id=$item_id");
    } else {
        $conn->query("INSERT INTO cart_items (cart_id, food_id, quantity) VALUES ($cart_id, $food_id, $quantity)");
    }
    
    // Chuyển hướng thành công và thoát
    header("Location: view_cart.php?success=add_db");
    exit;
}

// --- PHẦN 2: XỬ LÝ KHI LÀ KHÁCH VÃNG LAI (Dùng Session Cart) ---
guest_cart:

// 1. Khởi tạo giỏ hàng trong Session nếu chưa tồn tại
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$product_exists = false;

// 2. Kiểm tra nếu sản phẩm đã có trong Session Cart
foreach ($_SESSION['cart'] as $key => $item) {
    if ($item['food_id'] == $food_id) {
        $_SESSION['cart'][$key]['quantity'] += $quantity;
        $product_exists = true;
        break;
    }
}

// 3. Nếu chưa có, thêm mới vào Session Cart
if (!$product_exists) {
    $_SESSION['cart'][] = [
        'food_id' => $food_id,
        'quantity' => $quantity
    ];
}

// Chuyển hướng thành công và thoát
header("Location: view_cart.php?success=add_session");
exit;

?>
