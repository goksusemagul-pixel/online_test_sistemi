<?php
session_start();
require_once 'includes/db.php';

// Güvenlik
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// FİLTRELEME: Linkten gelen kategoriye göre (TYT, AYT vs.)
$category_filter = isset($_GET['cat']) ? $_GET['cat'] : 'all';

if ($category_filter == 'all') {
    $stmt = $pdo->prepare("SELECT * FROM exams ORDER BY id DESC");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE category = ? ORDER BY id DESC");
    $stmt->execute([$category_filter]);
}
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sınavlar - Filtreli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hover-card { transition: all 0.3s; border-left: 5px solid transparent; }
        .hover-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        /* Kategorilere göre kenar renkleri */
        .border-tyt { border-left-color: #0dcaf0 !important; } /* Mavi */
        .border-ayt { border-left-color: #ffc107 !important; } /* Sarı */
        .border-ydt { border-left-color: #dc3545 !important; } /* Kırmızı */
        .border-genel { border-left-color: #6c757d !important; } /* Gri */
    </style>
</head>
<body class="bg-light">

<?php if(file_exists('includes/navbar.php')) include 'includes/navbar.php'; ?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📚 Sınav Listesi</h2>
        <a href="index.php" class="btn btn-outline-secondary">🔙 Ana Sayfaya Dön</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-2 d-flex justify-content-center gap-2 flex-wrap bg-white rounded">
            <a href="quiz.php?cat=all" class="btn <?php echo ($category_filter=='all') ? 'btn-dark' : 'btn-outline-dark'; ?> rounded-pill px-4">Tümü</a>
            <a href="quiz.php?cat=TYT" class="btn <?php echo ($category_filter=='TYT') ? 'btn-info' : 'btn-outline-info'; ?> rounded-pill px-4">TYT</a>
            <a href="quiz.php?cat=AYT" class="btn <?php echo ($category_filter=='AYT') ? 'btn-warning' : 'btn-outline-warning'; ?> rounded-pill px-4">AYT</a>
            <a href="quiz.php?cat=YDT" class="btn <?php echo ($category_filter=='YDT') ? 'btn-danger' : 'btn-outline-danger'; ?> rounded-pill px-4">YDT</a>
        </div>
    </div>

    <div class="row">
        <?php if(count($exams) > 0): ?>
            <?php foreach($exams as $exam): ?>
                <?php 
                    // Kategori Rengi ve Sınıfı Belirle
                    $cat = !empty($exam['category']) ? $exam['category'] : 'Genel';
                    $borderClass = 'border-genel';
                    $badgeColor = 'secondary';
                    
                    if($cat == 'TYT') { $borderClass = 'border-tyt'; $badgeColor = 'info'; }
                    if($cat == 'AYT') { $borderClass = 'border-ayt'; $badgeColor = 'warning'; }
                    if($cat == 'YDT') { $borderClass = 'border-ydt'; $badgeColor = 'danger'; }
                ?>
                
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm hover-card <?php echo $borderClass; ?>">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-<?php echo $badgeColor; ?> mb-1"><?php echo $cat; ?></span>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($exam['title']); ?></h5>
                                <small class="text-muted">⏱️ Süre: <?php echo $exam['duration']; ?> dk</small>
                            </div>
                            <a href="solve_exam.php?id=<?php echo $exam['id']; ?>" class="btn btn-primary">
                                Başla ▶
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning text-center w-100">
                Bu kategoride sınav bulunamadı.
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>