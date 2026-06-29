<?php
// admin/index.php
session_start();
require_once '../includes/db.php';

// GÜVENLİK KONTROLÜ
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// 1. GENEL İSTATİSTİKLERİ ÇEK
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$studentCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM exams");
$examCount = $stmt->fetchColumn();

// 2. SINAV TÜRLERİNE GÖRE DETAYLI SAYILAR (TYT/AYT/YDT)
// Eğer veritabanında category sütunu boş olanlar varsa hata vermemesi için try-catch veya basit sorgu kullanıyoruz
try {
    $tytCount = $pdo->query("SELECT COUNT(*) FROM exams WHERE category = 'TYT'")->fetchColumn();
    $aytCount = $pdo->query("SELECT COUNT(*) FROM exams WHERE category = 'AYT'")->fetchColumn();
    $ydtCount = $pdo->query("SELECT COUNT(*) FROM exams WHERE category = 'YDT'")->fetchColumn();
} catch (Exception $e) {
    // Eğer category sütunu henüz yoksa 0 varsayalım
    $tytCount = 0; $aytCount = 0; $ydtCount = 0;
}

// Okunmamış yorum var mı?
$unreadCount = $pdo->query("SELECT COUNT(*) FROM feedback WHERE admin_reply IS NULL")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Paneli</title>
    <?php require_once '../includes/style.php'; ?>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index">Yönetici Paneli</a>
            <div class="d-flex">
                <a href="../index" class="btn btn-outline-light btn-sm me-2" target="_blank">Siteyi Gör</a>
                <a href="../logout" class="btn btn-danger btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">Hoş Geldin, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3 h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users"></i> Öğrenciler</h5>
                        <p class="card-text display-6 fw-bold"><?php echo $studentCount; ?></p>
                        <small>Toplam kayıtlı öğrenci</small>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card text-white bg-success mb-3 h-100 shadow-sm" style="background: linear-gradient(135deg, #198754, #20c997);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title"><i class="fas fa-file-alt"></i> Toplam Sınav</h5>
                                <p class="card-text display-6 fw-bold mb-0"><?php echo $examCount; ?></p>
                            </div>
                            
                            <div class="text-end">
                                <span class="badge bg-light text-dark p-2 mb-1 border">📘 TYT: <?php echo $tytCount; ?></span>
                                <span class="badge bg-light text-dark p-2 mb-1 border">📙 AYT: <?php echo $aytCount; ?></span>
                                <span class="badge bg-light text-dark p-2 mb-1 border">📕 YDT: <?php echo $ydtCount; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <a href="files" class="btn btn-warning w-100 p-3 fw-bold shadow-sm text-dark">
                📂 Dosya ve Doküman Yönetimi (PDF/Resim Yükle)
            </a>
        </div>

        <div class="row">
            
            <div class="col-md-3 mb-4">
                <div class="card text-white shadow h-100" style="background: linear-gradient(45deg, #6610f2, #d63384);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold">🤖 AI Sihirbazı</h5>
                                <p class="card-text small">Otomatik Soru Üret</p>
                            </div>
                            <div style="font-size: 2.5rem;">✨</div>
                        </div>
                        <a href="ai_question" class="btn btn-light text-dark fw-bold w-100 mt-3 stretched-link">
                            Üretmeye Başla 🚀
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white shadow h-100" style="background: linear-gradient(45deg, #fd7e14, #ffc107);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold">💬 Destek</h5>
                                <p class="card-text small">
                                    <?php echo $unreadCount > 0 ? $unreadCount . " Yeni Mesaj!" : "Mesajları Oku"; ?>
                                </p>
                            </div>
                            <div style="font-size: 2.5rem;">📩</div>
                        </div>
                        <a href="comments" class="btn btn-light text-dark fw-bold w-100 mt-3 stretched-link">
                            Mesajlara Git
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card text-white shadow h-100" style="background: linear-gradient(45deg, #dc3545, #f06595);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title fw-bold">📄 PDF Araçları</h5>
                                <p class="card-text small">Metne Dönüştür</p>
                            </div>
                            <div style="font-size: 2.5rem;">📝</div>
                        </div>
                        <a href="pdf_convert" class="btn btn-light text-dark fw-bold w-100 mt-3 stretched-link">
                            Dönüştür ⚡
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4"> 
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center">
                        <h4 class="text-success">📝 Sınav Yönetimi</h4>
                        <p class="text-muted small">TYT, AYT, YDT sınavları ekle.</p>
                        <a href="exams" class="btn btn-success w-100 fw-bold">
                            Sınav Ekle / Düzenle
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body text-center">
                        <h4 class="text-primary">❓ Soru Bankası</h4>
                        <p class="text-muted small">Soruları havuza ekle.</p>
                        <a href="questions" class="btn btn-primary w-100 fw-bold">
                            Soruları Yönet
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <h4>🏷️ Kategoriler</h4>
                        <p class="text-muted small">Dersleri düzenle.</p>
                        <a href="categories" class="btn btn-info text-white w-100">Yönet</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card h-100 border-primary shadow-sm">
                    <div class="card-body text-center">
                        <h4>📊 Sonuçlar</h4>
                        <p class="text-muted small">Raporları incele.</p>
                        <a href="results" class="btn btn-primary w-100">Gör</a>
                    </div>
                </div>
            </div>

        </div> 
    </div>

</body>
</html>