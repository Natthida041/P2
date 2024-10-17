<?php
require_once 'db.php';
include 'crud.php';

// ตรวจสอบว่าได้รับ ID ของผู้ใช้หรือไม่
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูลเพื่อนำมาแสดงในฟอร์ม
    $sql = "SELECT * FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];

    // อัปเดตข้อมูลผู้ใช้
    updateAdmin($id, $username, $first_name, $last_name, $password); 
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <title>อัปเดตข้อมูลผู้ใช้แอดมิน</title>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 2em;
            font-weight: 700;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 8px;
            font-size: 1em;
            color: #34495e;
            font-weight: 600;
        }

        input[type="text"],
        input[type="password"] {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        input[type="submit"] {
            padding: 12px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(52, 152, 219, 0.3);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #2980b9;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>อัปเดตข้อมูลผู้ใช้แอดมิน</h1>
        <form action="update.php?id=<?= $admin['id']; ?>" method="POST">
            <input type="hidden" name="id" value="<?= $admin['id']; ?>">

            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($admin['username']); ?>" required>

            <label for="first_name">ชื่อ:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($admin['first_name']); ?>" required>

            <label for="last_name">นามสกุล:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($admin['last_name']); ?>" required>

            <label for="password">รหัสผ่าน:</label>
            <input type="password" name="password" placeholder="เว้นว่างไว้หากไม่ต้องการเปลี่ยน">

            <input type="submit" value="อัปเดตข้อมูล">
        </form>
        <a href="home.php" class="back-link">กลับไปหน้าจัดการผู้ใช้</a>
    </div>
</body>
</html>