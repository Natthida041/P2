<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โรงเรียนอนุบาลกุลจินต์</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
            color: #333;
        }

        .header {
            background-color: rgba(0, 140, 186, 0.9);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 32px;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .nav {
            background-color: rgba(0, 96, 128, 0.9);
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .nav a {
            color: white;
            padding: 10px 20px;
            margin-left: 10px;
            text-decoration: none;
            font-size: 18px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav a:hover {
            background-color: rgba(0, 80, 117, 0.8);
        }

        .content {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }

        .category {
            padding: 50px;
            color: white;
            font-size: 24px;
            font-weight: 600;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .category:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .category-academic {
            background-color: #28a745;
        }

        .category-train {
            background-color: #ffc107;
        }

        .category-activity {
            background-color: #ff5722;
        }

        @media (max-width: 768px) {
            .header {
                font-size: 24px;
            }

            .nav a {
                font-size: 16px;
                padding: 8px 15px;
            }

            .category {
                font-size: 20px;
                padding: 40px;
            }
        }

        .footer {
            background-color: rgba(0, 96, 128, 0.9);
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        โรงเรียนอนุบาลกุลจินต์
    </div>

    <!-- Navigation Bar -->
    <div class="nav">
        <a href="/P2/HOME/home.php">เพิ่มข่าวสารใหม่</a>
        <a href="/P2/CRUD/home.php">จัดการผู้ใช้</a>
        <a href="/P2/logout.php">ออกจากระบบ</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <a href="/P2/HOME/manage/academic.php" class="category category-academic">งานวิชาการ</a>
        <a href="/P2/HOME/manage/train.php" class="category category-train">อบรม/สัมมนา</a>
        <a href="/P2/HOME/manage/activity.php" class="category category-activity">กิจกรรม</a>
    </div>


    <div class="footer">
        &copy; 1 ถนนรณภูมิ ตำบลหาดใหญ่ อำเภอหาดใหญ่ จังหวัดสงขลา 90110. Tel. : 074-257884, Fax. : 074-258107, E-mail : kunlajin@gmail.com.
    </div>

</body>
</html>
