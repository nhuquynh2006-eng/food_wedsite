<?php
include '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$error = $success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $error = "Mật khẩu mới và xác nhận không khớp.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row || !password_verify($current, $row['password'])) {
            $error = "Mật khẩu hiện tại không đúng.";
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hash, $user_id);
            if ($upd->execute()) {
                $success = "Đổi mật khẩu thành công.";
            } else {
                $error = "Lỗi khi cập nhật mật khẩu.";
            }
        }
    }
}
?>


<main style="max-width:520px;margin:40px auto;">
  <h2>Đổi mật khẩu</h2>
  <?php if($error) echo "<p style='color:red'>$error</p>"; ?>
  <?php if($success) echo "<p style='color:green'>$success</p>"; ?>
  <form method="POST">
    <input type="password" name="current_password" placeholder="Mật khẩu hiện tại" required>
    <input type="password" name="new_password" placeholder="Mật khẩu mới" required>
    <input type="password" name="confirm_password" placeholder="Xác nhận mật khẩu" required>
    <button type="submit">Lưu</button>
  </form>
  <p><a href="account.php">Quay lại</a></p>
</main>


