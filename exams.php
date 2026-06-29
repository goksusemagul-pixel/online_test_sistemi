<?php
session_start();
require_once '../includes/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- 1. KATEGORİLERİ ÇEK (Veritabanından) ---
// Hata almamak için önce kategorileri listeliyoruz
try {
    $catsStmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $catsStmt->fetchAll();
} catch (Exception $e) {
    die("Hata: Categories tablosu bulunamadı. Lütfen önce kategorileri SQL ile ekleyin.");
}

// --- 2. SINAV EKLEME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_exam'])) {
    try {
        $title = $_POST['title'];
        $category_id = $_POST['category_id']; // Artık ID alıyoruz
        $duration = $_POST['duration'];
        $is_active = 1; 

        // Veritabanına Ekle (category_id olarak)
        $stmt = $pdo->prepare("INSERT INTO exams (title, category_id, duration, is_active) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $category_id, $duration, $is_active])) {
            header("Location: exams.php?success=created");
            exit;
        }
    } catch (PDOException $e) {
        $error = "Veritabanı Hatası: " . $e->getMessage();
    }
}

// --- 3. SINAV SİLME İŞLEMİ ---
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: exams.php?success=deleted");
    exit;
}

// Sınavları Listele (Kategori İsimleriyle Birlikte JOIN yaparak)
$sql = "SELECT exams.*, categories.name as cat_name 
        FROM exams 
        LEFT JOIN categories ON exams.category_id = categories.id 
        ORDER BY exams.id DESC";
$exams = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sınav Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Yönetici Paneli</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">Geri Dön</a>
    </div>
</nav>

<div class="container">
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger">
            <strong>Hata Oluştu:</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_GET['success']) && $_GET['success'] == 'created'): ?>
        <div class="alert alert-success">✅ Sınav başarıyla oluşturuldu!</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">➕ Yeni Sınav Oluştur</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sınav Adı:</label>
                            <input type="text" name="title" class="form-control" required placeholder="Örn: TYT Matematik Deneme-1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Sınav Türü (Kategori):</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Seçiniz...</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Süre (Dakika):</label>
                            <input type="number" name="duration" class="form-control" required value="40">
                        </div>
                        
                        <button type="submit" name="add_exam" class="btn btn-primary w-100 fw-bold">
                            Sınavı Oluştur 🚀
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0">📋 Mevcut Sınavlar</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Başlık</th>
                                <th>Kategori</th>
                                <th>Süre</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($exams as $exam): ?>
                            <tr>
                                <td>#<?php echo $exam['id']; ?></td>
                                <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                <td>
                                    <?php 
                                    $catName = isset($exam['cat_name']) ? $exam['cat_name'] : 'Genel';
                                    $badge = 'secondary';
                                    if($catName == 'TYT') $badge = 'primary';
                                    if($catName == 'AYT') $badge = 'danger';
                                    if($catName == 'YDT') $badge = 'warning text-dark';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $catName; ?></span>
                                </td>
                                <td><?php echo $exam['duration']; ?> dk</td>
                                <td>
                                    <a href="exams.php?delete_id=<?php echo $exam['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Bu sınavı silmek istediğine emin misin?')">
                                        Sil 🗑️
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>