<?php
include 'config.php'; // Đảm bảo đường dẫn file config.php đúng
session_start(); 

$search_query = trim($_GET['q'] ?? '');
$products = [];

// Xử lý truy vấn nếu có từ khóa
if (!empty($search_query)) {
    // Sử dụng Prepared Statement để bảo mật
    $like_term = '%' . $search_query . '%';
    
    // Truy vấn để lấy foods và JOIN categories để lấy tên danh mục (nếu cần)
    $sql = "
        SELECT 
            f.id, 
            f.name, 
            f.price, 
            f.image, 
            f.description, 
            c.name AS category 
        FROM foods f 
        JOIN categories c ON f.category_id = c.id
        WHERE f.name LIKE ? OR f.description LIKE ? 
        ORDER BY f.name ASC
    ";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Tham số 'ss' cho 2 giá trị string
        $stmt->bind_param("ss", $like_term, $like_term); 
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả tìm kiếm | Ăn Khi Đói</title>
    <link rel="stylesheet" href="main.css">
    <style>
        /* Đảm bảo style của sản phẩm khớp với shop.php (nếu cần, bạn nên đặt style này trong main.css) */
        #wp-products { max-width: 1200px; margin: 30px auto; padding: 20px; }
        #list-products { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .item { 
            width: 280px; 
            border: 1px solid #ccc; 
            padding: 10px; 
            border-radius: 8px; 
            text-align: center;
        }
        .item img { 
            width: 100%; 
            height: auto; 
            border-radius: 4px; 
            margin-bottom: 10px;
        }
        .item .name { font-weight: bold; margin: 5px 0; }
        .item .price { color: red; font-weight: bold; margin: 5px 0; }
        .item .desc { font-size: 0.9em; color: #666; margin-bottom: 10px; }
        .item button { 
            background-color: #701f1f; 
            color: white; 
            padding: 10px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
        }
    </style>
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
          
          <form action="search_results.php" method="get" class="search-form-header" style="display:inline-flex; align-items:center; margin-left: 10px;">
              <input type="search" name="q" placeholder="Tìm món ăn..." required value="<?= htmlspecialchars($search_query) ?>"
                     style="padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px; width: 150px;">
              <button type="submit" 
                      style="background: #701f1f; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; margin-left: 5px;">
                  Tìm
              </button>
          </form>
          
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
        <h2>🔍 Kết Quả Tìm Kiếm: "**<?= htmlspecialchars($search_query) ?>**"</h2>
        <p style="text-align: center; color: #5d4037; font-size: 1.1em; margin-bottom: 20px;">
            <?php if (!empty($search_query) && !empty($products)): ?>
                Tìm thấy **<?= count($products) ?>** sản phẩm phù hợp.
            <?php endif; ?>
        </p>
        
        <?php if (empty($products)): ?>
            <p style="text-align: center; color: #5d4037;">
                <?php if (!empty($search_query)): ?>
                    Không tìm thấy sản phẩm nào phù hợp với từ khóa "<?= htmlspecialchars($search_query) ?>".
                <?php else: ?>
                    Vui lòng nhập từ khóa để tìm kiếm.
                <?php endif; ?>
            </p>
        <?php else: ?>
            <div id="list-products">
                <?php foreach ($products as $product): ?>
                <div class="item">
                    <img src="ảnh/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="desc"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?>...</div>
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

    <?php  include_once "footer.php";  ?>

</body>
</html>