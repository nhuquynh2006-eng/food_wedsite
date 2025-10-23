<?php
include '../config.php';
session_start();

// 🧩 Kiểm tra đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

$username = $_SESSION['username'];

// 🧩 Lấy thông tin user hiện tại
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 🧩 Nếu người dùng submit form cập nhật
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];

    // Cập nhật vào database
    $update = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE username = ?");
    $update->bind_param("sss", $new_username, $new_email, $username);
    $update->execute();
    $update->close();

    // Cập nhật lại session
    $_SESSION['username'] = $new_username;

    // Quay về trang account với thông báo thành công
    header("Location: account.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chỉnh sửa thông tin cá nhân</title>
  <link rel="stylesheet" href="../main.css">
  <style>
    body {
      background-color: #fffaf4;
      font-family: 'Segoe UI', sans-serif;
    }
    .profile-edit {
      max-width: 500px;
      margin: 60px auto;
      padding: 30px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .profile-edit h2 {
      text-align: center;
      color: #701f1f;
      margin-bottom: 25px;
    }
    .profile-edit label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #444;
    }
    .profile-edit input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 10px;
      transition: 0.2s;
    }
    .profile-edit input:focus {
      border-color: #701f1f;
      outline: none;
      box-shadow: 0 0 5px rgba(112,31,31,0.3);
    }
    .profile-edit button {
      width: 100%;
      background: #701f1f;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }
    .profile-edit button:hover {
      background: #913333;
    }
    .back-link {
      text-align: center;
      margin-top: 20px;
    }
    .back-link a {
      color: #701f1f;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="profile-edit">
    <h2>✏️ Chỉnh sửa thông tin cá nhân</h2>
    <form method="POST">
      <label for="username">Tên đăng nhập</label>
      <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" value="<?= htmlspecialchars($user['password']) ?>" required>

      <button type="submit"> Lưu thay đổi</button>
    </form>

    <div class="back-link">
      <a href="account.php">← Quay lại tài khoản</a>
    </div>
  </div>
</body>
</html>
