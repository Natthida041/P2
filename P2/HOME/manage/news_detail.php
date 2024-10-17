<?php
require_once '../db.php';  // เชื่อมต่อฐานข้อมูล
error_reporting(E_ALL);
ini_set('display_errors', 1);

// กำหนดค่าคงที่สำหรับเส้นทางหลักของไฟล์อัปโหลด
define('UPLOAD_BASE_PATH', '/P2/HOME/uploads/');

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id'])) {
    $article_id = intval($_GET['id']);  // ดึง id จาก URL และแปลงเป็นจำนวนเต็ม

    // ดึงข้อมูลบทความจากตาราง articles
    $sql_article = "SELECT a.title, a.description, a.created_at, 
                           IFNULL(ad.first_name, 'ไม่ทราบชื่อผู้เขียน') AS author_name, 
                           c.name AS category_name 
                    FROM articles a 
                    LEFT JOIN admin ad ON a.author_id = ad.id
                    LEFT JOIN categories c ON a.category_id = c.id
                    WHERE a.id = ?";
    $stmt_article = $conn->prepare($sql_article);
    if ($stmt_article === false) {
        die("เกิดข้อผิดพลาดในการเตรียม SQL สำหรับบทความ: " . $conn->error);
    }
    $stmt_article->bind_param("i", $article_id);
    $stmt_article->execute();
    $article_result = $stmt_article->get_result();
    $article = $article_result->fetch_assoc();

    if (!$article) {
        die("ไม่พบบทความที่ต้องการ.");
    }

    // ดึงข้อมูลไฟล์สื่อที่เกี่ยวข้องจากตาราง article_media และ media
    $sql_media = "SELECT m.file_name, m.file_type 
                  FROM media m 
                  INNER JOIN article_media am ON m.id = am.media_id 
                  WHERE am.article_id = ?";
    $stmt_media = $conn->prepare($sql_media);
    if ($stmt_media === false) {
        die("เกิดข้อผิดพลาดในการเตรียม SQL สำหรับสื่อ: " . $conn->error);
    }
    $stmt_media->bind_param("i", $article_id);
    $stmt_media->execute();
    $media_result = $stmt_media->get_result();
    $media_files = $media_result->fetch_all(MYSQLI_ASSOC);

    // ปิด statement หลังใช้งานเสร็จ
    $stmt_article->close();
    $stmt_media->close();

    // จัดลำดับไฟล์สื่อตามลำดับที่ต้องการ: PDF, Video, Image
    usort($media_files, function($a, $b) {
        $order = ['pdf' => 1, 'video' => 2, 'image' => 3];
        return $order[$a['file_type']] - $order[$b['file_type']];
    });

} else {
    die("ไม่พบรหัสบทความ.");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #ecf0f1;
            --text-color: #34495e;
            --header-color: #2c3e50;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .header {
            background-color: rgba(0, 140, 186, 0.9);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .header nav {
            display: flex;
            gap: 1rem;
        }

        .header nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .header nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .article-meta {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .media {
            margin-top: 2rem;
        }

        .media h2 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .media-item {
            margin-bottom: 1.5rem;
        }

        .media-item img, 
        .media-item video {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .media-item img:hover,
        .media-item video:hover {
            transform: scale(1.02);
        }

        .pdf-link {
            display: inline-block;
            background-color: var(--secondary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .pdf-link:hover {
            background-color: #27ae60;
        }

        .error {
            color: #e74c3c;
            font-weight: bold;
        }

        .popup-img {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
        }

        .popup-img img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(255, 255, 255, 0.1);
        }

        .close {
            position: absolute;
            top: 15px;
            right: 25px;
            color: white;
            font-size: 35px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #bbb;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header nav {
                margin-top: 1rem;
            }

            .container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="header-content">
            <h1>โรงเรียนอนุบาลกุลจินต์</h1>
            <nav>
                <a href="/P2/HOME/manage/page.php">หน้าหลัก</a>
                <a href="/P2/HOME/edit.php?id=<?= $article_id ?>">แก้ไข</a>
                <a href="/P2/HOME/delete.php?id=<?= $article_id ?>" onclick="return confirm('คุณต้องการลบบทความนี้หรือไม่?')">ลบ</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1><?= htmlspecialchars($article['title']) ?></h1>
        <div class="article-meta">
            <span>โดย: <?= htmlspecialchars($article['author_name']) ?></span> | 
            <span>หมวดหมู่: <?= htmlspecialchars($article['category_name']) ?></span> | 
            <span>วันที่: <?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
        </div>
        <div class="article-content">
            <?= nl2br(htmlspecialchars($article['description'])) ?>
        </div>

        <div class="media">
            <h2>สื่อที่เกี่ยวข้อง</h2>
            <?php if (!empty($media_files)): ?>
                <?php foreach ($media_files as $media): ?>
                    <?php
                    $file_name = htmlspecialchars($media['file_name']);
                    $file_type = $media['file_type'];
                    $file_path = '';
                    $error_message = '';

                    if ($file_type === 'pdf') {
                        $file_path = UPLOAD_BASE_PATH . 'pdf/' . $file_name;
                    } elseif ($file_type === 'image') {
                        $file_path = UPLOAD_BASE_PATH . 'images/' . $file_name;
                    } elseif ($file_type === 'video') {
                        $file_path = UPLOAD_BASE_PATH . 'videos/' . $file_name;
                    }

                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path)) {
                        $error_message = "ไม่พบไฟล์: {$file_name}";
                    }
                    ?>
                    <div class="media-item">
                        <?php if (!empty($error_message)): ?>
                            <p class="error"><?= $error_message ?></p>
                        <?php else: ?>
                            <?php if ($file_type === 'pdf'): ?>
                                <a href="<?= $file_path ?>" target="_blank" class="pdf-link">ดาวน์โหลด <?= $file_name ?></a>
                            <?php elseif ($file_type === 'image'): ?>
                                <img src="<?= $file_path ?>" alt="<?= $file_name ?>" onclick="showPopupImage(this.src)">
                            <?php elseif ($file_type === 'video'): ?>
                                <video controls>
                                    <source src="<?= $file_path ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <p>ไฟล์นี้ไม่รองรับการแสดงผลในหน้านี้: <?= $file_name ?> (<?= $file_type ?>)</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>ไม่มีสื่อที่เกี่ยวข้องกับบทความนี้</p>
            <?php endif; ?>
        </div>

    <div class="popup-img" id="popupImg">
        <span class="close" onclick="closePopupImage()">&times;</span>
        <img id="popupImgSrc" src="">
    </div>

    <script>
        function showPopupImage(src) {
            const popup = document.getElementById('popupImg');
            const popupImgSrc = document.getElementById('popupImgSrc');
            popupImgSrc.src = src;
            popup.style.display = 'flex';
        }

        function closePopupImage() {
            const popup = document.getElementById('popupImg');
            popup.style.display = 'none';
        }
    </script>
</body>
</html>