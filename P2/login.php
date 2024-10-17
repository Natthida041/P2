<?php
session_start();
ob_start();  // เริ่มต้นการใช้ output buffering

require_once 'db.php';  // เรียกไฟล์เชื่อมต่อฐานข้อมูล

// สมมติว่าเป็นการเชื่อมต่อฐานข้อมูลที่นี่ หากเชื่อมต่อสำเร็จ
$connected = $conn ? true : false; // ตรวจสอบการเชื่อมต่อฐานข้อมูล

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // ตรวจสอบว่าผู้ใช้มีอยู่และรหัสผ่านถูกต้องหรือไม่
    if ($user && password_verify($password, $user['password'])) {
        // เก็บสถานะการล็อกอินในเซสชัน
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username']; // เก็บชื่อผู้ใช้ใน session

        if ($connected) {
            $_SESSION['message'] = "<div class='alert success'>เชื่อมต่อฐานข้อมูลสำเร็จ</div>";
        }

        // เปลี่ยนเส้นทางไปยังหน้า HOME/home.php
        header('Location: /P2/HOME/manage/page.php');
        exit();
    } else {
        $_SESSION['error_message'] = "<div class='alert error'>Username หรือ Password ไม่ถูกต้อง!</div>";
    }
}

ob_end_flush();  // จบ output buffering
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #eef2f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            position: relative;
        }

        .alert {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: left;
            font-size: 16px;
        }

        .alert.success {
            background-color: #e0f7fa;
            color: #00796b;
            border: 1px solid #00796b;
        }

        .alert.error {
            background-color: #fce4e4;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        h2 {
            font-size: 30px;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        button {
            padding: 12px;
            border: none;
            border-radius: 6px;
            background-color: #3498db;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }

            input[type="text"],
            input[type="password"] {
                padding: 10px;
            }

            button {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- แสดงข้อความเชื่อมต่อฐานข้อมูลสำเร็จหรือข้อผิดพลาด -->
        <?php if (isset($_SESSION['message'])): ?>
            <?= $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <?= $_SESSION['error_message']; ?>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <h2>เข้าสู่ระบบ</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required>
            <input type="password" name="password" placeholder="รหัสผ่าน" required>
            <button type="submit" name="login">เข้าสู่ระบบ</button>
        </form>
    </div>

</body>
</html>
