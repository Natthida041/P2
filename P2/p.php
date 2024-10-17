<?php
// การเข้ารหัสรหัสผ่านด้วย password_hash()
$password = '123456789';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// การเชื่อมต่อฐานข้อมูล
$servername = "localhost";    // หรือ IP ของเซิร์ฟเวอร์
$username = "root";           // ชื่อผู้ใช้ฐานข้อมูล (เปลี่ยนตามการตั้งค่าของคุณ)
$password = "";               // รหัสผ่านฐานข้อมูล (ถ้าไม่มีให้ใส่เป็นค่าว่าง)
$dbname = "news_page";        // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL สำหรับเพิ่มผู้ใช้ admin1
$sql = "INSERT INTO admin (username, first_name, last_name, password, created_at)
        VALUES ('admin1', 'admin', 'Ladmin', '$hashedPassword', NOW())";

// รันคำสั่ง SQL
if ($conn->query($sql) === TRUE) {
    echo "เพิ่มผู้ใช้สำเร็จ";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// ปิดการเชื่อมต่อ
$conn->close();
?>