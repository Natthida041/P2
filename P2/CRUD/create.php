<?php
require_once 'db.php';  // เชื่อมต่อฐานข้อมูล
include 'crud.php';     // เรียกใช้ฟังก์ชันจาก crud.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];

    // เรียกฟังก์ชันสร้างแอดมินจาก crud.php
    createAdmin($username, $first_name, $last_name, $password);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="create.css">
    <title>เพิ่มผู้ใช้งานแอดมิน</title>
    
</head>
<body>
    <div class="container">
        <h1>สร้างผู้ใช้แอดมินใหม่</h1>
        <form action="create.php" method="POST">
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" name="username" required>

            <label for="first_name">ชื่อ:</label>
            <input type="text" name="first_name" required>

            <label for="last_name">นามสกุล:</label>
            <input type="text" name="last_name" required>

            <label for="password">รหัสผ่าน:</label>
            <input type="password" name="password" required>

            <input type="submit" value="เพิ่มผู้ใช้งานแอดมิน">
        </form>
        <a href="home.php" class="back-link">กลับไปหน้าจัดการผู้ใช้</a>
    </div>
</body>
</html>