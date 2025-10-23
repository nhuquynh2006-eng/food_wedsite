<?php
$host = "localhost";
$user = "root";   // user mặc định XAMPP
$pass = "";
$db   = "food_db"; // tên DB bạn đang dùng

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>
