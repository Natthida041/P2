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
    $sql_article = "SELECT a.title, a.description 
                    FROM articles a 
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
    $sql_media = "SELECT m.id, m.file_name, m.file_type 
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

    // จัดกลุ่มไฟล์สื่อเป็น PDF, Video, Image
    $pdf_files = [];
    $video_files = [];
    $image_files = [];
    foreach ($media_files as $media) {
        if ($media['file_type'] === 'pdf') {
            $pdf_files[] = $media;
        } elseif ($media['file_type'] === 'video') {
            $video_files[] = $media;
        } elseif ($media['file_type'] === 'image') {
            $image_files[] = $media;
        }
    }

    // เมื่อมีการส่งฟอร์มแก้ไข
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // การลบไฟล์
        foreach ($media_files as $media) {
            $media_id = $media['id'];
            $file_type = $media['file_type'];

            if (isset($_POST['delete_media_' . $media_id])) {
                // ลบไฟล์จากโฟลเดอร์
                if ($file_type === 'pdf') {
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_BASE_PATH . 'pdf/' . $media['file_name'];
                } elseif ($file_type === 'image') {
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_BASE_PATH . 'images/' . $media['file_name'];
                } elseif ($file_type === 'video') {
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_BASE_PATH . 'videos/' . $media['file_name'];
                }

                if (file_exists($file_path)) {
                    unlink($file_path);  // ลบไฟล์ออกจากโฟลเดอร์
                }

                // ลบข้อมูลไฟล์จากฐานข้อมูล
                $sql_delete_media = "DELETE FROM media WHERE id = ?";
                $stmt_delete_media = $conn->prepare($sql_delete_media);
                $stmt_delete_media->bind_param("i", $media_id);
                $stmt_delete_media->execute();

                // หลังจากลบไฟล์สำเร็จ กลับไปหน้า edit.php
                $_SESSION['success'] = "ลบไฟล์สำเร็จ";
                header("Location: edit.php?id=" . $article_id);
                exit();
            }
        }

        // การบันทึกการเปลี่ยนแปลงบทความ
        if (isset($_POST['save_changes'])) {
            // รับค่าที่ส่งมาจากฟอร์ม
            $title = $_POST['title'];
            $description = $_POST['description'];

            // อัปเดตบทความในฐานข้อมูล
            $sql_update = "UPDATE articles SET title = ?, description = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $title, $description, $article_id);

            if ($stmt_update->execute()) {
                // จัดการการเพิ่มไฟล์ PDF, Image, Video ใหม่
                if (isset($_FILES['new_pdf']) && $_FILES['new_pdf']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['new_pdf']['tmp_name'];
                    $file_name = $_FILES['new_pdf']['name'];
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_BASE_PATH . 'pdf/' . $file_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // เพิ่มข้อมูลไฟล์ PDF ลงในฐานข้อมูล
                        $sql_insert_media = "INSERT INTO media (file_name, file_type, file_path) VALUES (?, 'pdf', ?)";
                        $stmt_insert_media = $conn->prepare($sql_insert_media);
                        $stmt_insert_media->bind_param("ss", $file_name, $file_path);
                        $stmt_insert_media->execute();

                        // เชื่อมโยงกับ article
                        $media_id = $stmt_insert_media->insert_id;
                        $sql_link_media = "INSERT INTO article_media (article_id, media_id) VALUES (?, ?)";
                        $stmt_link_media = $conn->prepare($sql_link_media);
                        $stmt_link_media->bind_param("ii", $article_id, $media_id);
                        $stmt_link_media->execute();
                    }
                }

                if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['new_image']['tmp_name'];
                    $file_name = $_FILES['new_image']['name'];
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_BASE_PATH . 'images/' . $file_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // เพิ่มข้อมูลไฟล์รูปภาพลงในฐานข้อมูล
                        $sql_insert_media = "INSERT INTO media (file_name, file_type, file_path) VALUES (?, 'image', ?)";
                        $stmt_insert_media = $conn->prepare($sql_insert_media);
                        $stmt_insert_media->bind_param("ss", $file_name, $file_path);
                        $stmt_insert_media->execute();

                        // เชื่อมโยงกับ article
                        $media_id = $stmt_insert_media->insert_id;
                        $sql_link_media = "INSERT INTO article_media (article_id, media_id) VALUES (?, ?)";
                        $stmt_link_media = $conn->prepare($sql_link_media);
                        $stmt_link_media->bind_param("ii", $article_id, $media_id);
                        $stmt_link_media->execute();
                    }
                }

                if (isset($_FILES['new_video']) && $_FILES['new_video']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['new_video']['tmp_name'];
                    $file_name = $_FILES['new_video']['name'];
                    $file_path = $_SERVER['DOCUMENT_ROOT'] . UPLOAD_BASE_PATH . 'videos/' . $file_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        // เพิ่มข้อมูลไฟล์วิดีโอลงในฐานข้อมูล
                        $sql_insert_media = "INSERT INTO media (file_name, file_type, file_path) VALUES (?, 'video', ?)";
                        $stmt_insert_media = $conn->prepare($sql_insert_media);
                        $stmt_insert_media->bind_param("ss", $file_name, $file_path);
                        $stmt_insert_media->execute();

                        // เชื่อมโยงกับ article
                        $media_id = $stmt_insert_media->insert_id;
                        $sql_link_media = "INSERT INTO article_media (article_id, media_id) VALUES (?, ?)";
                        $stmt_link_media = $conn->prepare($sql_link_media);
                        $stmt_link_media->bind_param("ii", $article_id, $media_id);
                        $stmt_link_media->execute();
                    }
                }

                // การบันทึกสำเร็จ
                $_SESSION['success'] = "บันทึกการเปลี่ยนแปลงสำเร็จ";
                // กลับไปหน้า news_detail.php หลังจากบันทึกสำเร็จ
                header("Location: /P2/HOME/manage/news_detail.php?id=" . $article_id);
                exit();
            } else {
                // เกิดข้อผิดพลาดในการบันทึกบทความ
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตบทความ.";
            }
        }
    }

    // ปิด statement หลังใช้งานเสร็จ
    $stmt_article->close();
    $stmt_media->close();

} else {
    die("ไม่พบรหัสบทความ.");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขบทความ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            background-color: #00838f;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .nav {
            text-align: center;
            margin: 20px 0;
        }

        .nav a {
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav a:hover {
            background-color: #004d40;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 20px 0 5px 0;
        }

        input[type="text"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            padding: 12px 25px;
            font-size: 16px;
            background-color: #0097a7;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #00796b;
        }

        .media-item {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        video,
        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .media-item button {
            background-color: #e74c3c;
        }

        .media-item button:hover {
            background-color: #c0392b;
        }

    </style>
</head>
<body>
    <header class="header">
        <h1>โรงเรียนอนุบาลกุลจินต์ - แก้ไขบทความ</h1>
    </header>
    
    <nav class="nav">
        <a href="/P2/HOME/manage/news_detail.php?id=<?= $article_id ?>">กลับ</a>
    </nav>

    <div class="container">
        <h1>แก้ไขบทความ: <?= htmlspecialchars($article['title']) ?></h1>

        <form method="POST" enctype="multipart/form-data">
            <label for="title">ชื่อบทความ:</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($article['title']) ?>" required>

            <label for="description">คำอธิบาย:</label>
            <textarea name="description" id="description" rows="5" required><?= htmlspecialchars($article['description']) ?></textarea>

            <h2>ไฟล์สื่อที่เกี่ยวข้อง</h2>

            <h3>PDF ที่เกี่ยวข้อง</h3>
            <label for="new_pdf">เพิ่ม PDF ใหม่:</label>
            <input type="file" name="new_pdf">
            <?php foreach ($pdf_files as $media): ?>
                <div class="media-item">
                    <p><strong>ไฟล์ PDF ปัจจุบัน:</strong> <?= htmlspecialchars($media['file_name']) ?></p>
                    <button type="submit" name="delete_media_<?= $media['id'] ?>">ลบไฟล์นี้</button>
                </div>
            <?php endforeach; ?>

            <h3>วิดีโอที่เกี่ยวข้อง</h3>
            <label for="new_video">เพิ่มวิดีโอใหม่:</label>
            <input type="file" name="new_video">
            <?php foreach ($video_files as $media): ?>
                <div class="media-item">
                    <video controls>
                        <source src="<?= UPLOAD_BASE_PATH . 'videos/' . $media['file_name'] ?>" type="video/mp4">
                        Browser ของคุณไม่รองรับการเล่นวิดีโอ
                    </video>
                    <button type="submit" name="delete_media_<?= $media['id'] ?>">ลบวิดีโอนี้</button>
                </div>
            <?php endforeach; ?>

            <h3>รูปภาพที่เกี่ยวข้อง</h3>
            <label for="new_image">เพิ่มรูปภาพใหม่:</label>
            <input type="file" name="new_image">
            <?php foreach ($image_files as $media): ?>
                <div class="media-item">
                    <img src="<?= UPLOAD_BASE_PATH . 'images/' . $media['file_name'] ?>" alt="รูปภาพที่เกี่ยวข้อง">
                    <button type="submit" name="delete_media_<?= $media['id'] ?>">ลบรูปภาพนี้</button>
                </div>
            <?php endforeach; ?>

            <button type="submit" name="save_changes">บันทึกการเปลี่ยนแปลง</button>
        </form>
    </div>
</body>
</html>
