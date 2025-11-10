<?php
session_start();
include 'config.php';

// --- XỬ LÝ LỌC DANH MỤC ---
$selected_category_id = intval($_GET['category_id'] ?? 0);
$current_category_name = 'Tất Cả Sản Phẩm';

// 1. Lấy danh sách tất cả các Danh mục (để tạo thanh chọn/navigation)
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
        if ($cat['id'] == $selected_category_id) {
            $current_category_name = $cat['name'];
        }
    }
}

// 2. Lấy danh sách sản phẩm dựa trên lựa chọn
$sql = "SELECT f.id, f.name, f.price, f.image, f.description, c.name AS category 
        FROM foods f
        JOIN categories c ON f.category_id = c.id";

// Thêm điều kiện lọc nếu người dùng đã chọn một danh mục
if ($selected_category_id > 0) {
    // Sử dụng Prepared Statement cho truy vấn chính (an toàn hơn)
    $sql .= " WHERE f.category_id = ?";
    $stmt = $conn->prepare($sql . " ORDER BY f.created_at DESC");
    $stmt->bind_param("i", $selected_category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Không lọc: Lấy tất cả
    $result = $conn->query($sql . " ORDER BY f.created_at DESC");
}

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
// --- KẾT THÚC PHẦN XỬ LÝ LỌC ---

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sản Phẩm - Ăn Khi Đói</title>
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

<div id="wp-products" class="store-page">
    <h2>🍔 <?= $current_category_name ?></h2>
    
    <div class="category-selector" style="text-align: center; margin-bottom: 30px;">
        <label for="category_select" style="font-size: 18px; font-weight: bold; margin-right: 15px;">Lọc theo Danh mục:</label>
        <select id="category_select" onchange="window.location.href=this.value" 
            style="padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-size: 16px;">
            
            <option value="shop.php" <?= $selected_category_id == 0 ? 'selected' : '' ?>>Tất Cả Sản Phẩm</option>
            
            <?php foreach ($categories as $cat): ?>
                <option value="shop.php?category_id=<?= $cat['id'] ?>" 
                    <?= $selected_category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if (empty($products)): ?>
    <p style="text-align: center; color: #5d4037;">Không có sản phẩm nào trong danh mục này.</p>
<?php else: ?>
    <div id="list-products" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
        <?php foreach ($products as $product): 
            // Tạo URL động tới trang chi tiết sản phẩm
            $detail_url = "food_detail.php?id=" . intval($product['id']);
        ?>
        <div class="item">
            
            <a href="<?= $detail_url ?>" style="text-decoration: none; color: inherit;">
                <img src="ảnh/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="name"><?= htmlspecialchars($product['name']) ?></div>
                <div class="desc"><?= htmlspecialchars($product['description']) ?></div>
                <div class="price"><?= number_format($product['price'], 0, ",", ".") ?>đ</div>
            </a>
            <form action="add_to_cart.php" method="POST">
                <input type="hidden" name="food_id" value="<?= intval($product['id']) ?>">
                <button type="submit">🛒 Mua Ngay</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php include_once "footer.php"; ?>

</body>
</html>