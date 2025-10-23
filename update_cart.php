<?php
session_start();
$conn = new mysqli("localhost", "root", "", "food_db");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// Kiểm tra login (username hoặc user_id)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: account.php");
    exit;
}

$item_id = intval($_POST['item_id'] ?? 0);
$action = $_POST['action'] ?? ''; // "increase" / "decrease" / "set"
$quantity = intval($_POST['quantity'] ?? 1);

if ($item_id <= 0) {
    header("Location: view_cart.php");
    exit;
}

// Lấy current item để đảm bảo item hợp lệ
$itQ = $conn->query("SELECT id, quantity FROM cart_items WHERE id = $item_id LIMIT 1");
if (!$itQ || $itQ->num_rows == 0) {
    header("Location: view_cart.php");
    exit;
}
$row = $itQ->fetch_assoc();
$cur = intval($row['quantity']);

if ($action === 'increase') {
    $cur++;
} elseif ($action === 'decrease') {
    $cur--;
} else { // set trực tiếp
    $cur = max(1, $quantity);
}

if ($cur <= 0) {
    $conn->query("DELETE FROM cart_items WHERE id = $item_id");
} else {
    $conn->query("UPDATE cart_items SET quantity = $cur WHERE id = $item_id");
}

header("Location: view_cart.php");
exit;
?>
