<?php
// admin/categories.php
session_start();
require_once '../includes/db.php';

// Güvenlik: Admin değilse at
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Kategori Ekleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cat_name'])) {
    $name = htmlspecialchars(trim($_POST['cat_name']));
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: categories.php"); // Sayfayı yenile
        exit;
    }
}

// Kategori Silme
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: categories.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategoriler</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <h2>Kategori Yönetimi</h2>
        <a href="index.php" class="btn btn-secondary">Geri Dön</a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card p-3">
                <h5>Yeni Ders Ekle</h5>
                <form method="POST">
                    <input type="text" name="cat_name" class="form-control mb-2" placeholder="Örn: Matematik" required>
                    <button type="submit" class="btn btn-success w-100">Ekle</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <table class="table table-white bg-white shadow-sm">
                <thead><tr><th>ID</th><th>Ders Adı</th><th>İşlem</th></tr></thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id']; ?></td>
                        <td><?php echo $cat['name']; ?></td>
                        <td><a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silinsin mi?')">Sil</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>