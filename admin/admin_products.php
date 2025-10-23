<<?php
include '../config.php';
include __DIR__ . '/_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $category_input = trim($_POST['category_id'] ?? '');
    $type = $conn->real_escape_string($_POST['type'] ?? 'normal');
    $available = isset($_POST['available']) ? 1 : 0;
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $image = '';

    // ✅ Xử lý upload ảnh
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetDir = dirname(__DIR__) . '/ảnh/foods/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $imageName);
        $image = 'foods/' . $imageName; // Lưu tên file ảnh
    } else {
        $image = $conn->real_escape_string($_POST['image'] ?? '');
    }

    // ✅ Kiểm tra danh mục
    if ($category_input === '') {
        $category_value = 'NULL';
    } else {
        $cid = intval($category_input);
        $check = $conn->query("SELECT id FROM categories WHERE id=$cid LIMIT 1");
        if (!$check || $check->num_rows == 0) {
            echo "<script>alert('ID danh mục không tồn tại. Vui lòng kiểm tra.');history.back();</script>";
            exit;
        }
        $category_value = $cid;
    }

    // ✅ Câu lệnh SQL đúng với bảng foods
    $sql = "INSERT INTO foods (category_id,name,description,price,image,available,type)
            VALUES ($category_value,'$name','$description',$price,'$image',$available,'$type')";
    $conn->query($sql);
    echo "<script>alert('✅ Thêm món thành công!');window.location='admin_products.php';</script>";
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $r = $conn->query("SELECT * FROM foods WHERE id=$id")->fetch_assoc();
    if ($r && $r['image']) {
        @unlink(dirname(__DIR__) . '/ảnh/' . $r['image']);
    }
    $conn->query("DELETE FROM foods WHERE id=$id");
    header("Location: admin_products.php");
    exit;
}

$products = $conn->query("SELECT * FROM foods ORDER BY created_at DESC");
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Quản lý sản phẩm</title>
<link rel="stylesheet" href="admin_style.css">
</head>
<body>
<?php include __DIR__ . '/admin_header_small.php'; ?>
<div class="page-title">🍜 Quản lý sản phẩm</div>
<div class="akd-card">
  <div class="akd-card-title">➕ Thêm sản phẩm</div>
  <div class="akd-panel">
    <form method="post" enctype="multipart/form-data" class="form-grid">
      <input name="name" placeholder="Tên món" required>
      <input name="price" type="number" placeholder="Giá (VNĐ)" required>
      <select name="category_id">
        <option value="">-- Chọn danh mục (tùy chọn) --</option>
        <?php while($c = $categories->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
      <select name="type">
        <option value="normal">normal</option>
        <option value="new">new</option>
        <option value="bestseller">bestseller</option>
      </select>
      <label><input type="checkbox" name="available" checked> Còn bán</label>
      <input type="file" name="image" accept="image/*">
      <textarea name="description" placeholder="Mô tả"></textarea>
      <button name="add_product" class="akd-btn akd-btn-primary">➕ Thêm món</button>
    </form>
  </div>

  <div class="akd-panel" style="margin-top:18px">
    <div class="small center">Danh sách sản phẩm</div>
    <div class="table-wrap">
      <table class="styled-table">
        <thead>
          <tr>
            <th>Ảnh</th><th>Tên</th><th>Giá</th><th>Loại</th><th>Trạng thái</th><th>Ngày</th><th>Hành động</th>
          </tr>
        </thead>
        <tbody>
        <?php while($p = $products->fetch_assoc()): ?>
          <tr>
            <td>
              <?php if($p['image']): ?>
                <img src="../ảnh/<?= htmlspecialchars($p['image']) ?>" style="width:70px;height:70px;object-fit:cover;border-radius:8px">
              <?php else: ?>
                <i>Không ảnh</i>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= number_format($p['price'],0,',','.') ?>đ</td>
            <td><?= htmlspecialchars($p['type']) ?></td>
            <td><?= $p['available'] ? '✅' : '❌' ?></td>
            <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
            <td>
              <a class="akd-btn akd-btn-edit" href="admin_products.php?edit=<?= $p['id'] ?>">Sửa</a>
              <a class="akd-btn akd-btn-delete" href="?delete=<?= $p['id'] ?>" onclick="return confirm('Xóa món này?')">Xóa</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
