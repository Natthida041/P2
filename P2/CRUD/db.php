<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "news_page2";

$conn = new mysqli('localhost', 'root', '', 'news_page2'); // แก้ไขข้อมูลการเชื่อมต่อให้ตรงกับเซิร์ฟเวอร์จริง

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>