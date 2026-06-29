<?php
session_start();
require_once '../includes/db.php';
// PDF Motorunu varsa dahil et, yoksa hata vermesin
if (file_exists('../includes/PdfParser.php')) {
    require_once '../includes/PdfParser.php';
}

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$extractedText = "";
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. DOSYA YÜKLEME İŞLEMİ
    if (isset($_FILES['doc_file']) && $_FILES['doc_file']['error'] == 0) {
        $file = $_FILES['doc_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tmpPath = $file['tmp_name'];

        // A) EĞER TXT DOSYASIYSA (KESİN ÇALIŞIR) 🚀
        if ($ext === 'txt') {
            $extractedText = file_get_contents($tmpPath);
            // Türkçe karakter sorununu düzelt (UTF-8)
            $encoding = mb_detect_encoding($extractedText, ['UTF-8', 'ISO-8859-9', 'WINDOWS-1254'], true);
            if ($encoding && $encoding != 'UTF-8') {
                $extractedText = mb_convert_encoding($extractedText, 'UTF-8', $encoding);
            }
            $message = '<div class="alert alert-success">✅ Metin belgesi başarıyla okundu!</div>';
        } 
        // B) EĞER PDF DOSYASIYSA (ŞANSINA BAĞLI) 🎲
        elseif ($ext === 'pdf') {
            if (class_exists('PdfParser')) {
                try {
                    $parser = new PdfParser();
                    $rawText = $parser->parseFile($tmpPath);
                    $extractedText = trim($rawText);
                    
                    if (empty($extractedText) || strlen($extractedText) < 5) {
                        $message = '<div class="alert alert-warning">PDF okundu ama içi boş veya şifreli çıktı. Lütfen metni aşağıya elle yapıştırın veya .txt dosyası deneyin.</div>';
                    } else {
                        $message = '<div class="alert alert-success">✅ PDF işlendi. (Hatalı karakterler varsa lütfen düzeltin).</div>';
                    }
                } catch (Exception $e) {
                    $message = '<div class="alert alert-danger">PDF Hatası: ' . $e->getMessage() . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">PDF Okuyucu motoru bulunamadı.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Lütfen sadece .PDF veya .TXT dosyası yükleyin.</div>';
        }
    }
    
    // 2. MANUEL METİN GİRİŞİ (Yedek Plan)
    if (isset($_POST['manual_text']) && !empty($_POST['manual_text'])) {
        $extractedText = $_POST['manual_text'];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Dosya Dönüştürücü</title>
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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">📄 Dosya'dan Soru Üret (PDF / TXT)</h4>
                </div>
                <div class="card-body">
                    
                    <?php echo $message; ?>

                    <form method="POST" enctype="multipart/form-data" class="mb-4 p-3 bg-light border rounded">
                        <label class="form-label fw-bold">1. Dosya Yükle (Önerilen: .TXT):</label>
                        <div class="input-group">
                            <input type="file" name="doc_file" class="form-control" accept=".pdf, .txt">
                            <button type="submit" class="btn btn-danger">Metni Getir ⚡</button>
                        </div>
                        <div class="form-text">Not Defteri (.txt) dosyaları %100 hatasız çalışır. PDF'ler bazen şifreli olabilir.</div>
                    </form>

                    <hr>

                    <label class="form-label fw-bold">2. Çıkarılan Metin (Düzenlenebilir):</label>
                    <textarea class="form-control mb-3" rows="10" id="resultText" placeholder="Dosya yükleyin veya metni buraya yapıştırın..."><?php echo htmlspecialchars($extractedText); ?></textarea>
                    
                    <div class="d-flex gap-2 justify-content-end">
                        <button onclick="copyText()" class="btn btn-secondary">📋 Kopyala</button>
                        
                        <form action="ai_question.php" method="GET" target="_blank">
                            <button type="button" onclick="sendToAI()" class="btn btn-success fw-bold">
                                🤖 Bu Metinle Soru Üret
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyText() {
    var copyText = document.getElementById("resultText");
    copyText.select();
    document.execCommand("copy");
    alert("Metin kopyalandı!");
}

function sendToAI() {
    // Metni kopyala ve kullanıcıya yol göster
    var copyText = document.getElementById("resultText");
    copyText.select();
    document.execCommand("copy");
    
    if(confirm("Metin kopyalandı! Şimdi AI Soru Sihirbazı açılacak.\n\n'Soru Konusu' kutusuna kopyaladığın metni yapıştırıp 'Soru Üret' demen yeterli.")) {
        window.location.href = 'ai_question.php';
    }
}
</script>

</body>
</html>