<?php
// admin/results.php
session_start();
require_once '../includes/db.php';

// Güvenlik: Admin değilse at
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// SONUÇLARI VERİTABANINDAN ÇEK (Tabloları Birleştirme - JOIN)
// Users tablosundan öğrenci adını, Exams tablosundan sınav adını, Results tablosundan puanı alıyoruz.
$query = "SELECT results.*, users.username, exams.title as exam_title 
          FROM results 
          JOIN users ON results.user_id = users.id 
          JOIN exams ON results.exam_id = exams.id 
          ORDER BY results.id DESC";

$stmt = $pdo->query($query);
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sınav Sonuçları</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>📊 Öğrenci Sınav Raporları</h3>
            <button class="btn btn-primary" onclick="window.print()">🖨️ Raporu Yazdır</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Öğrenci Adı</th>
                            <th>Sınav Adı</th>
                            <th>Puan</th>
                            <th>Doğru / Yanlış</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['exam_title']); ?></td>
                            
                            <td>
                                <?php if($row['score'] >= 50): ?>
                                    <span class="badge bg-success fs-6"><?php echo $row['score']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger fs-6"><?php echo $row['score']; ?></span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="text-success fw-bold"><?php echo $row['correct_count']; ?> D</span> / 
                                <span class="text-danger fw-bold"><?php echo $row['wrong_count']; ?> Y</span>
                            </td>
                            <td><?php echo date("d.m.Y H:i", strtotime($row['completed_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if(count($results) == 0): ?>
                            <tr><td colspan="6" class="text-center">Henüz kimse sınav olmamış.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>