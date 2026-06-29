<?php
session_start();
require_once '../includes/db.php';

// Admin Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- CEVAP GÖNDERME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_id'])) {
    $reply_id = $_POST['reply_id'];
    $reply_text = trim($_POST['reply_text']);
    
    $stmt = $pdo->prepare("UPDATE feedback SET admin_reply = ?, reply_date = NOW() WHERE id = ?");
    $stmt->execute([$reply_text, $reply_id]);
    
    header("Location: comments.php?success=1");
    exit;
}

// Mesajları Çek (Kullanıcı adlarıyla birlikte)
$sql = "SELECT feedback.*, users.username, users.avatar_path 
        FROM feedback 
        JOIN users ON feedback.user_id = users.id 
        ORDER BY feedback.created_at DESC";
$comments = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Öğrenci Yorumları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Admin Paneli</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">Geri Dön</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">💬 Öğrenci Mesajları ve Destek</h2>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">Cevabınız gönderildi!</div>
    <?php endif; ?>

    <div class="row">
        <?php foreach($comments as $c): ?>
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm <?php echo empty($c['admin_reply']) ? 'border-warning' : 'border-success'; ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">👤 <?php echo htmlspecialchars($c['username']); ?></span>
                        <small class="text-muted ms-2">(<?php echo date("d.m.Y H:i", strtotime($c['created_at'])); ?>)</small>
                    </div>
                    <?php if(empty($c['admin_reply'])): ?>
                        <span class="badge bg-warning text-dark">Cevap Bekliyor</span>
                    <?php else: ?>
                        <span class="badge bg-success">Cevaplandı</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <p class="lead fs-6"><?php echo nl2br(htmlspecialchars($c['message'])); ?></p>
                            
                            <?php if(!empty($c['image_path'])): ?>
                                <div class="mt-2">
                                    <a href="../<?php echo $c['image_path']; ?>" target="_blank">
                                        <img src="../<?php echo $c['image_path']; ?>" class="img-thumbnail" style="max-height: 200px;">
                                    </a>
                                    <small class="d-block text-muted">Ekli Görsel (Tıkla Büyüt)</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 border-start">
                            <form method="POST">
                                <input type="hidden" name="reply_id" value="<?php echo $c['id']; ?>">
                                <label class="form-label fw-bold text-success">Cevabınız:</label>
                                <textarea name="reply_text" class="form-control mb-2" rows="3" required><?php echo $c['admin_reply']; ?></textarea>
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <?php echo empty($c['admin_reply']) ? 'Yanıtla' : 'Cevabı Güncelle'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>