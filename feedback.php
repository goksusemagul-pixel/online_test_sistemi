<?php
session_start();
require_once 'includes/db.php';

// Güvenlik
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$msg = "";

// --- YORUM VE RESİM GÖNDERME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = trim($_POST['message']);
    $imagePath = NULL;

    // Resim Yükleme Var mı?
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 3 * 1024 * 1024) { // 3MB Limit
            $newName = "feedback_" . time() . "_" . rand(100,999) . "." . $ext;
            $target = "uploads/" . $newName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = $target;
            }
        } else {
            $msg = '<div class="alert alert-danger">Sadece JPG/PNG yüklenebilir (Max 3MB).</div>';
        }
    }

    if (!empty($message)) {
        $sql = "INSERT INTO feedback (user_id, message, image_path) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $message, $imagePath]);
        $msg = '<div class="alert alert-success">Mesajınız iletildi! Yönetici cevabı bekleniyor.</div>';
    }
}

// --- GEÇMİŞ MESAJLARI VE CEVAPLARI ÇEK ---
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$feedbacks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Destek ve Yorumlar</title>
    <?php require_once 'includes/style.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎓 Online Sınav</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Geri Dön</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">📩 Yeni Mesaj / Soru</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $msg; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Mesajınız:</label>
                                <textarea name="message" class="form-control" rows="4" required placeholder="Sorunuzu veya görüşünüzü buraya yazın..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ekran Görüntüsü (İsteğe Bağlı):</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-success w-100">Gönder 🚀</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h4 class="mb-3">💬 Geçmiş Mesajlarım</h4>
                
                <?php if(count($feedbacks) == 0): ?>
                    <div class="alert alert-info">Henüz hiç mesaj göndermediniz.</div>
                <?php endif; ?>

                <?php foreach($feedbacks as $f): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold text-primary">Siz:</h6>
                                <small class="text-muted"><?php echo date("d.m.Y H:i", strtotime($f['created_at'])); ?></small>
                            </div>
                            <p class="card-text bg-light p-2 rounded"><?php echo nl2br(htmlspecialchars($f['message'])); ?></p>
                            
                            <?php if(!empty($f['image_path'])): ?>
                                <div class="mb-3">
                                    <a href="<?php echo $f['image_path']; ?>" target="_blank">
                                        <img src="<?php echo $f['image_path']; ?>" class="img-thumbnail" style="max-height: 150px;">
                                    </a>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($f['admin_reply'])): ?>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <h6 class="fw-bold text-success">👨‍🏫 Yönetici Cevabı:</h6>
                                    <small class="text-muted"><?php echo date("d.m.Y H:i", strtotime($f['reply_date'])); ?></small>
                                </div>
                                <div class="alert alert-success mb-0">
                                    <?php echo nl2br(htmlspecialchars($f['admin_reply'])); ?>
                                </div>
                            <?php else: ?>
                                <hr>
                                <span class="badge bg-warning text-dark">⏳ Cevap Bekleniyor...</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</body>
</html>