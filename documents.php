<?php
session_start();
require_once 'includes/db.php';
if(file_exists('includes/lang.php')) require_once 'includes/lang.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

// Dosyaları Çek
$files = $pdo->query("SELECT * FROM files ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ders Materyalleri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="./">🎓 Online Sınav</a>
            <div>
                <a href="./" class="btn btn-outline-light btn-sm me-2">Ana Sayfa</a>
                <a href="logout" class="btn btn-danger btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        📂 Ders Materyalleri ve Dokümanlar
                    </div>
                    <div class="card-body">
                        
                        <?php if(count($files) == 0): ?>
                            <div class="alert alert-info text-center">Henüz ders materyali eklenmemiş.</div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach($files as $f): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center p-3 border rounded bg-white h-100 shadow-sm">
                                        <div class="fs-1 me-3 text-secondary">
                                            <?php 
                                            $icon = "📄";
                                            if($f['file_type'] == 'pdf') $icon = "📕";
                                            elseif(in_array($f['file_type'], ['jpg','png'])) $icon = "🖼️";
                                            elseif(in_array($f['file_type'], ['zip','rar'])) $icon = "📦";
                                            echo $icon;
                                            ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($f['title']); ?></h6>
                                            <small class="text-muted">
                                                Tarih: <?php echo date("d.m.Y", strtotime($f['uploaded_at'])); ?> | 
                                                Boyut: <?php echo $f['file_size']; ?>
                                            </small>
                                        </div>
                                        <div>
                                            <a href="<?php echo $f['file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" download>
                                                ⬇️ İndir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>