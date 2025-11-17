<?php 
include 'config.php';
session_start();

// Tính tổng số lượng giỏ hàng
$cart_total = 0;

if(isset($_SESSION['user_id'])){
    // User đã login
    $user_id = intval($_SESSION['user_id']);
    $cusQ = $conn->query("SELECT id FROM customers WHERE user_id=$user_id LIMIT 1");
    if($cusQ && $cusQ->num_rows){
        $customer_id = intval($cusQ->fetch_assoc()['id']);
        // Tính tổng số lượng từ tất cả cart items của cart mới nhất
        $cartQ = $conn->query("SELECT SUM(quantity) as total 
                             FROM cart_items 
                             WHERE cart_id=(SELECT id FROM cart WHERE customer_id=$customer_id ORDER BY id DESC LIMIT 1)");
        $cart_total = $cartQ ? intval($cartQ->fetch_assoc()['total']) : 0;
    }
}else{
    // Guest
    if(isset($_SESSION['cart'])){
        foreach($_SESSION['cart'] as $item){
            // Giỏ hàng trong session lưu: ['food_id'=>ID, 'quantity'=>Qty]
            $cart_total += $item['quantity'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ăn Khi Đói</title>
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
            
            <a href="view_cart.php">🛒 Giỏ hàng (<span id="cart-count"><?= $cart_total ?></span>)</a> 

            <form action="search_results.php" method="get" class="search-form-header" style="display:flex; align-items:center;">
                <input type="search" name="q" placeholder="Tìm món ăn..." required 
                        style="padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" 
                        style="background: #701f1f; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; margin-left: 5px;">
                    Tìm
                </button>
            </form>

            <?php if(isset($_SESSION['username'])): ?>
                <a href="account/account.php" style="color: #3e2723; font-weight: bold;">
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

<div id="banner">
    <div class="box-left">
        <h2>
            <span>Thức Ăn</span><br />
            <span>SIÊU NGON</span>
        </h2>
        <p>Giao hàng tận nơi, nhanh chóng</p>
        <p>Gọi là có, cần là đến</p>
        <button>Trải Nghiệm Ngay</button>
    </div>  
</div>

<div id="wp-products">
    <h2>NHỮNG SẢN PHẨM MỚI</h2>
    <ul id="list-products">
        <?php
        $result = $conn->query("SELECT * FROM foods WHERE type='new' LIMIT 6");
        while($row = $result->fetch_assoc()) {
            $food_id = intval($row['id']);
            echo '<div class="item">';
            echo '<img src="ảnh/'.$row['image'].'" alt="">';
            echo '<div class="name">'.$row['name'].'</div>';
            echo '<div class="desc">'.$row['description'].'</div>';
            echo '<div class="price">'.number_format($row['price'],0,",",".").'đ</div>';
            
            // 🚨 THÊM NÚT THÊM VÀO GIỎ HÀNG (Dùng AJAX)
            echo '<button class="add-to-cart" data-id="'.$food_id.'" data-quantity="1">';
            echo '    🛒 Thêm vào giỏ hàng';
            echo '</button>';

            // 🚨 NÚT MUA NGAY (Dùng Form POST để chuyển hướng)
            echo '<form action="add_to_cart.php" method="POST" style="display:inline;">';
            echo '    <input type="hidden" name="food_id" value="'.$food_id.'">';
            echo '    <input type="hidden" name="buy_now" value="1">'; // Chỉ dẫn add_to_cart.php chuyển hướng
            echo '    <button type="submit">💳 Mua Ngay</button>';
            echo '</form>';
            
            echo '</div>';
        }
        ?>
    </ul>

    <div id="view-more">
        <h2>SẢN PHẨM BÁN CHẠY</h2>
        <ul id="list-products">
            <?php
            $result = $conn->query("SELECT * FROM foods WHERE type='bestseller' LIMIT 6");
            while($row = $result->fetch_assoc()) {
                $food_id = intval($row['id']);
                echo '<div class="item">';
                echo '<img src="ảnh/'.$row['image'].'" alt="">';
                echo '<div class="name">'.$row['name'].'</div>';
                echo '<div class="desc">'.$row['description'].'</div>';
                echo '<div class="price">'.number_format($row['price'],0,",",".").'đ</div>';
                
                // 🚨 THÊM NÚT THÊM VÀO GIỎ HÀNG (Dùng AJAX)
                echo '<button class="add-to-cart" data-id="'.$food_id.'" data-quantity="1">';
                echo '    🛒 Thêm vào giỏ hàng';
                echo '</button>';

                // 🚨 NÚT MUA NGAY (Dùng Form POST để chuyển hướng)
                echo '<form action="add_to_cart.php" method="POST" style="display:inline;">';
                echo '    <input type="hidden" name="food_id" value="'.$food_id.'">';
                echo '    <input type="hidden" name="buy_now" value="1">'; // Chỉ dẫn add_to_cart.php chuyển hướng
                echo '    <button type="submit">💳 Mua Ngay</button>';
                echo '</form>';

                echo '</div>';
            }
            ?>
        </ul>
    </div>
</div>
<?php include_once "footer.php"; ?>

<script>
    // Hàm cập nhật số lượng giỏ hàng trên Header
    function updateCartCount(count) {
        const countElement = document.getElementById('cart-count'); // Dùng ID: cart-count
        if (countElement) {
            // Nếu có hàng, hiển thị số lượng, nếu không hiển thị 0
            countElement.textContent = count > 0 ? count : 0; 
        }
    }

    // Hàm hiển thị thông báo
    function showNotification(message, type = 'success') {
        // Tùy chỉnh: Dùng console.log/alert hoặc thư viện Toastr/SweetAlert
        alert(`${type.toUpperCase()}: ${message}`);
    }

    // Chạy khi trang tải xong
    document.addEventListener('DOMContentLoaded', () => {
        // Lắng nghe sự kiện click cho tất cả các nút có class "add-to-cart"
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                const foodId = e.target.getAttribute('data-id');
                const quantity = parseInt(e.target.getAttribute('data-quantity') || 1);
                
                // Chuẩn bị dữ liệu gửi đi (JSON)
                const data = { food_id: foodId, quantity: quantity };

                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cập nhật số lượng giỏ hàng trên Header bằng dữ liệu từ server trả về
                        updateCartCount(data.cart_total_items);
                        // Thông báo thành công
                        showNotification(`Đã thêm ${data.food_name} vào giỏ hàng!`);
                    } else {
                        showNotification(data.message || 'Lỗi khi thêm vào giỏ hàng.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Lỗi kết nối:', error);
                    showNotification('Lỗi kết nối máy chủ.', 'error');
                });
            });
        });
    });
</script>
<script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/6909b2e623927319492bd62e/1j96u5lrb';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
</body>
</html>
