<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['exam_id'])) {
    header("Location: index.php");
    exit;
}

$exam_id = (int)$_POST['exam_id'];
$user_id = $_SESSION['user_id'];
$user_answers = isset($_POST['answers']) ? $_POST['answers'] : [];

$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

$score = 0;
$correct_count = 0;
$wrong_count = 0;

foreach ($questions as $q) {
    $q_id = $q['id'];
    if (isset($user_answers[$q_id]) && $user_answers[$q_id] === $q['correct_answer']) {
        $score += $q['points'];
        $correct_count++;
    } else {
        $wrong_count++;
    }
}

// SONUCU KAYDET
$stmt = $pdo->prepare("INSERT INTO results (user_id, exam_id, score, correct_count, wrong_count) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $exam_id, $score, $correct_count, $wrong_count]);

// --- YAPAY ZEKA (EXPERT SYSTEM) ANALİZ MODÜLÜ ---
// Kriter: Yapay Zeka Desteği
$ai_message = "";
$ai_color = "";

if ($score >= 85) {
    $ai_message = "🤖 <strong>Yapay Zeka Analizi:</strong> Mükemmel bir performans! Konuya tamamen hakimsin. Bir sonraki, daha zor seviyedeki sınavları denemeni öneriyorum.";
    $ai_color = "success"; // Yeşil
} elseif ($score >= 50) {
    $ai_message = "🤖 <strong>Yapay Zeka Analizi:</strong> Orta seviyedesin. Temel bilgilerin var ancak detaylarda eksiklerin görünüyor. Yanlış yaptığın soruların konularına tekrar göz atmalısın.";
    $ai_color = "warning"; // Sarı
} else {
    $ai_message = "🤖 <strong>Yapay Zeka Analizi:</strong> Konu eksiklerin tespit edildi. Bu konuyu baştan çalışman gerekiyor. Bol bol tekrar yapmadan yeni sınava girme.";
    $ai_color = "danger"; // Kırmızı
}
// ------------------------------------------------
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sınav Sonucu ve AI Analizi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow text-center">
                    <div class="card-header bg-primary text-white">
                        <h3>Sınav Sonucu 🏁</h3>
                    </div>
                    <div class="card-body p-4">
                        
                        <h1 class="display-1 fw-bold text-primary mb-3"><?php echo $score; ?></h1>
                        <p class="text-muted">PUANINIZ</p>

                        <div class="row mt-4 mb-4">
                            <div class="col-6 border-end">
                                <h4 class="text-success">Doğru</h4>
                                <span class="fs-4">✅ <?php echo $correct_count; ?></span>
                            </div>
                            <div class="col-6">
                                <h4 class="text-danger">Yanlış</h4>
                                <span class="fs-4">❌ <?php echo $wrong_count; ?></span>
                            </div>
                        </div>

                        <div class="alert alert-<?php echo $ai_color; ?> text-start shadow-sm p-4">
                            <?php echo $ai_message; ?>
                        </div>

                        <div class="mt-4">
                            <a href="index.php" class="btn btn-outline-dark w-100">Ana Sayfaya Dön</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'theme.php'; ?>

</body>
</html>