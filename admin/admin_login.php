<?php
session_start();
include '../config.php';

if (isset($_SESSION['admin_username'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows) {
        $admin = $res->fetch_assoc();
        if ($admin['status'] !== 'active') {
            $error = "Tài khoản bị khóa hoặc không hoạt động.";
        } elseif ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_fullname'] = $admin['fullname'];
            $_SESSION['admin_role'] = $admin['role'];
            $conn->query("UPDATE admins SET last_login = NOW() WHERE id = {$admin['id']}");
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Sai mật khẩu.";
        }
    } else {
        $error = "Tài khoản không tồn tại.";
    }
    $stmt->close();
}
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<link rel="stylesheet" href="admin_style.css">
</head>
<body style="background:var(--be)">
<div style="max-width:420px;margin:80px auto">
  <div style="background:#fff;border-radius:12px;padding:26px;box-shadow:0 8px 20px rgba(0,0,0,0.08)">
    <h2 style="color:var(--dark-brown);margin:0 0 8px 0">🔐 Đăng nhập Admin</h2>
    <?php if ($error): ?><div style="color:#b71c1c;margin-bottom:8px"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <input name="username" placeholder="Tên đăng nhập" required style="margin-bottom:8px;padding:10px;border-radius:8px;border:1px solid #ddd">
      <input name="password" type="password" placeholder="Mật khẩu" required style="margin-bottom:8px;padding:10px;border-radius:8px;border:1px solid #ddd">
      <button type="submit" style="background:var(--accent);color:#fff;padding:10px 18px;border-radius:8px;border:none">Đăng nhập</button>
    </form>
  </div>
</div>
</body>
</html>