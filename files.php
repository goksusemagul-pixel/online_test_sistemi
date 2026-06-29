<?php
session_start();
require_once '../includes/db.php';

// Güvenlik: Admin değilse at
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";
$message_type = "";

// --- DOSYA YÜKLEME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['course_file'])) {
    $title = trim($_POST['title']);
    
    // Dosya Bilgileri
    $file = $_FILES['course_file'];
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // 1. Dosya Türü Kontrolü (PDF, Word, Excel, Resim, Zip)
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'png', 'zip', 'rar'];
    
    // 2. Boyut Kontrolü (Max 10 MB)
    $maxSize = 10 * 1024 * 1024; 

    if (!empty($title) && $fileError === 0) {
        if (in_array($ext, $allowed)) {
            if ($fileSize < $maxSize) {
                // 3. Güvenli İsimlendirme (uniqid ile çakışmayı ve Türkçe karakter sorununu önle)
                $newFileName = "doc_" . uniqid() . "." . $ext;
                $uploadDest = '../uploads/' . $newFileName;
                
                // Dosyayı Taşı
                if (move_uploaded_file($fileTmp, $uploadDest)) {
                    // Veritabanına Yaz
                    $dbPath = 'uploads/' . $newFileName;
                    $sizeStr = round($fileSize / 1024, 2) . " KB"; // KB cinsinden yaz
                    
                    $stmt = $pdo->prepare("INSERT INTO files (title, file_path, file_type, file_size) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $dbPath, $ext, $sizeStr]);
                    
                    $message = "Dosya başarıyla yüklendi! ✅";
                    $message_type = "success";
                } else {
                    $message = "Dosya yüklenirken sunucu hatası oluştu.";
                    $message_type = "danger";
                }
            } else {
                $message = "Dosya çok büyük! (Max: 10MB)";
                $message_type = "warning";
            }
        } else {
            $message = "İzin verilmeyen dosya türü! (Sadece PDF, Office, Resim, Zip)";
            $message_type = "danger";
        }
    } else {
        $message = "Lütfen bir başlık yazın ve dosya seçin.";
        $message_type = "danger";
    }
}

// --- DOSYA SİLME İŞLEMİ ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Önce dosya yolunu bul
    $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ?");
    $stmt->execute([$id]);
    $fileData = $stmt->fetch();
    
    if ($fileData) {
        // Sunucudan fiziksel olarak sil
        $fullPath = "../" . $fileData['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Veritabanından sil
        $del = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $del->execute([$id]);
        
        header("Location: files.php");
        exit;
    }
}

// Dosyaları Listele
$files = $pdo->query("SELECT * FROM files ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Dosya Yönetimi</title>
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
        <h3 class="mb-4">📂 Dosya ve Doküman Yönetimi</h3>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">Yeni Dosya Yükle</div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Doküman Başlığı</label>
                                <input type="text" name="title" class="form-control" placeholder="Örn: Hafta 1 Ders Notları" required>
                            </div>
                            <div class="mb-3">
                                <label>Dosya Seç</label>
                                <input type="file" name="course_file" class="form-control" required>
                                <small class="text-muted">PDF, Word, Excel, Resim, Zip (Max 10MB)</small>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Yükle</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">Yüklü Dosyalar</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Başlık</th>
                                    <th>Tür</th>
                                    <th>Boyut</th>
                                    <th>Tarih</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($files as $f): ?>
                                <tr>
                                    <td>
                                        <a href="../<?php echo $f['file_path']; ?>" target="_blank" class="text-decoration-none fw-bold">
                                            <?php echo htmlspecialchars($f['title']); ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo strtoupper($f['file_type']); ?></span></td>
                                    <td><small><?php echo $f['file_size']; ?></small></td>
                                    <td><small><?php echo date("d.m.Y", strtotime($f['uploaded_at'])); ?></small></td>
                                    <td>
                                        <a href="?delete=<?php echo $f['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu dosyayı silmek istiyor musunuz?')">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if(count($files) == 0): ?>
                                    <tr><td colspan="5" class="text-center text-muted p-3">Henüz dosya yüklenmedi.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>