<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name']; 
    $email = $_POST['email']; 
    $message = $_POST['message'];
    $conn->query("INSERT INTO contacts (name,email,message) VALUES ('$name','$email','$message')");
    $success = "Gửi liên hệ thành công!";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Liên hệ</title>
  <link rel="stylesheet" href="main.css">
</head>
<body>
<h1>Liên hệ với chúng tôi</h1>
<form method="POST">
  <input type="text" name="name" placeholder="Họ tên" required><br>
  <input type="email" name="email" placeholder="Email" required><br>
  <textarea name="message" placeholder="Nội dung" required></textarea><br>
  <button type="submit">Gửi</button>
</form>
<?php if (isset($success)) echo "<p>$success</p>"; ?>
</body>
</html>
