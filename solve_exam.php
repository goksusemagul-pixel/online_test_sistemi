<?php
session_start();
require_once 'includes/db.php';

// Güvenlik: Giriş yapılmış mı?
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// URL'den Sınav ID'sini al
if (!isset($_GET['exam_id'])) {
    die("HATA: Sınav ID'si bulunamadı.");
}

$exam_id = (int)$_GET['exam_id'];

// 1. Sınav Bilgilerini Çek
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    die("HATA: Böyle bir sınav bulunamadı!");
}

// 2. Soruları Çek
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

if (count($questions) == 0) {
    die("<div class='alert alert-warning'>Bu sınava henüz soru eklenmemiş.</div>");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($exam['title']); ?></title>
    <?php require_once 'includes/style.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Süre Sayacını Ekranın Üstüne Sabitle */
        .timer-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #dc3545; /* Kırmızı */
            color: white;
            text-align: center;
            padding: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        /* Sayacın altında kalan içeriği aşağı itmek için */
        body { padding-top: 70px; }
    </style>
</head>
<body class="bg-light">

    <div class="timer-bar">
        ⏳ Kalan Süre: <span id="displayTime">--:--</span>
    </div>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-primary text-white text-center">
                        <h2><?php echo htmlspecialchars($exam['title']); ?></h2>
                        <small>Toplam Süre: <?php echo $exam['duration']; ?> Dakika</small>
                    </div>
                </div>

                <form action="submit_exam.php" method="POST" id="examForm">
                    <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">

                    <?php foreach($questions as $index => $q): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <span class="badge bg-secondary me-2">Soru <?php echo $index + 1; ?></span>
                                </h5>
                                <p class="card-text fw-bold lead"><?php echo htmlspecialchars($q['question_text']); ?></p>
                                <hr>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="A" id="q<?php echo $q['id']; ?>_a" required>
                                    <label class="form-check-label" for="q<?php echo $q['id']; ?>_a">A) <?php echo htmlspecialchars($q['option_a']); ?></label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="B" id="q<?php echo $q['id']; ?>_b">
                                    <label class="form-check-label" for="q<?php echo $q['id']; ?>_b">B) <?php echo htmlspecialchars($q['option_b']); ?></label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="C" id="q<?php echo $q['id']; ?>_c">
                                    <label class="form-check-label" for="q<?php echo $q['id']; ?>_c">C) <?php echo htmlspecialchars($q['option_c']); ?></label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="answers[<?php echo $q['id']; ?>]" value="D" id="q<?php echo $q['id']; ?>_d">
                                    <label class="form-check-label" for="q<?php echo $q['id']; ?>_d">D) <?php echo htmlspecialchars($q['option_d']); ?></label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-bold" onclick="return confirm('Sınavı bitirmek istiyor musun?')">
                            🏁 Sınavı Bitir ve Gönder
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        // PHP'den gelen dakika bilgisini saniyeye çeviriyoruz
        var totalMinutes = <?php echo $exam['duration']; ?>;
        var timeInSeconds = totalMinutes * 60; 

        var display = document.getElementById('displayTime');
        var form = document.getElementById('examForm');

        var timerInterval = setInterval(function() {
            // Dakika ve Saniye Hesapla
            var minutes = Math.floor(timeInSeconds / 60);
            var seconds = timeInSeconds % 60;

            // Tek haneli sayıların başına 0 koy (Örn: 9 yerine 09)
            seconds = seconds < 10 ? '0' + seconds : seconds;
            minutes = minutes < 10 ? '0' + minutes : minutes;

            // Ekrana Yaz
            display.textContent = minutes + ":" + seconds;

            // Süre Azalt
            timeInSeconds--;

            // Süre Bitti mi?
            if (timeInSeconds < 0) {
                clearInterval(timerInterval);
                display.textContent = "00:00";
                
                // Kullanıcıya haber ver ve formu gönder
                alert("⏳ SÜRE DOLDU! Sınavınız otomatik olarak gönderiliyor...");
                
                // Formdaki 'required' (zorunlu alan) kontrollerini kaldır ki boş soru olsa bile göndersin
                var inputs = document.querySelectorAll('input[required]');
                for (var i = 0; i < inputs.length; i++) {
                    inputs[i].removeAttribute('required');
                }

                form.submit(); // Formu gönder
            }

        }, 1000); // Her 1000 milisaniyede (1 saniyede) bir çalıştır
    </script>

</body>
</html>