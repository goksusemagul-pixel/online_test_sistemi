<?php
session_start();
require_once 'includes/db.php';

// Güvenlik: Giriş yapılmış mı?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- İNDİRME İŞLEMLERİ (JSON & XML) ---
if (isset($_GET['export'])) {
    // Sadece bu kullanıcıya ait verileri temiz bir şekilde çekelim
    $stmt = $pdo->prepare("SELECT exams.title as sinav_adi, results.score as puan, 
                           results.correct_count as dogru, results.wrong_count as yanlis, 
                           results.created_at as tarih
                           FROM results 
                           JOIN exams ON results.exam_id = exams.id 
                           WHERE results.user_id = ? 
                           ORDER BY results.created_at DESC");
    $stmt->execute([$user_id]);
    $export_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 1. JSON İNDİRME
    if ($_GET['export'] == 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="sonuclarim.json"');
        echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. XML İNDİRME
    if ($_GET['export'] == 'xml') {
        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="sonuclarim.xml"');
        
        $xml = new SimpleXMLElement('<sonuclar/>');
        foreach ($export_data as $row) {
            $item = $xml->addChild('sinav');
            $item->addChild('ad', $row['sinav_adi']);
            $item->addChild('puan', $row['puan']);
            $item->addChild('dogru', $row['dogru']);
            $item->addChild('yanlis', $row['yanlis']);
            $item->addChild('tarih', $row['tarih']);
        }
        echo $xml->asXML();
        exit;
    }
}

// --- SAYFA GÖRÜNÜMÜ İÇİN VERİ ÇEKME ---
$stmt = $pdo->prepare("SELECT results.*, exams.title 
                       FROM results 
                       JOIN exams ON results.exam_id = exams.id 
                       WHERE results.user_id = ? 
                       ORDER BY results.created_at DESC");
$stmt->execute([$user_id]);
$results = $stmt->fetchAll();

// Grafik Verileri (Eskiden yeniye sırala ki grafik soldan sağa aksın)
$chartData = array_reverse($results);
$labels = [];
$scores = [];

foreach ($chartData as $row) {
    $labels[] = $row['title'] . ' (' . date("d.m", strtotime($row['created_at'])) . ')';
    $scores[] = $row['score'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sonuçlarım</title>
    <?php require_once 'includes/style.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎓 Online Sınav</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Geri Dön</a>
        </div>
    </nav>

    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📊 Sonuçlarım</h2>
            <div>
                <span class="text-muted me-2">Verileri İndir:</span>
                <a href="?export=json" class="btn btn-dark btn-sm">📄 JSON</a>
                <a href="?export=xml" class="btn btn-warning btn-sm">Ow XML</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">tebrikler! Sınavınız başarıyla tamamlandı ve kaydedildi.</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Başarı Grafiği</div>
                    <div class="card-body">
                        <canvas id="scoreChart" height="150"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">Geçmiş Liste</div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sınav</th>
                                    <th>Puan</th>
                                    <th>Tarih</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $res): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($res['title']); ?></td>
                                    <td>
                                        <?php if($res['score'] >= 50): ?>
                                            <span class="badge bg-success"><?php echo $res['score']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?php echo $res['score']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date("d.m.Y", strtotime($res['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('scoreChart').getContext('2d');
        const scoreChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Sınav Puanı',
                    data: <?php echo json_encode($scores); ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#0d6efd',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100, // Eksen her zaman 100'e kadar çıkar
                        ticks: { stepSize: 20 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>