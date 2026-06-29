<?php
session_start();
require_once '../includes/db.php';

// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- AYARLAR ---
$apiKey = "AIzaSyBZejWAsQsOxOQRjK9pVUoenedn1Hssz38"; // <--- ŞİFRENİ BURAYA YAPIŞTIR
// Senin hesabında çalışan en hızlı model:
$apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

$message = "";

// Form Gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $topic = htmlspecialchars($_POST['topic']);
    $exam_id = $_POST['exam_id'];

    // Yapay Zekaya Gidecek Emir
    $promptText = "Bana '$topic' konusu hakkında zorluk seviyesi orta olan 1 adet çoktan seçmeli soru hazırla. 
    Çıktıyı SADECE şu JSON formatında ver, başka hiçbir yazı yazma:
    {
        \"question_text\": \"Soru metni buraya\",
        \"option_a\": \"A şıkkı\",
        \"option_b\": \"B şıkkı\",
        \"option_c\": \"C şıkkı\",
        \"option_d\": \"D şıkkı\",
        \"correct_answer\": \"A\" (Sadece A, B, C veya D harfi)
    }
    Lütfen Türkçe cevap ver.";

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $promptText]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    
    if(curl_errno($ch)){
        $message = '<div class="alert alert-danger">Bağlantı Hatası: ' . curl_error($ch) . '</div>';
    } else {
        $result = json_decode($response, true);
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Temizlik
            $rawText = str_replace(['```json', '```'], '', $rawText);
            $aiData = json_decode($rawText, true);

            if ($aiData) {
                // --- DÜZELTİLEN KISIM BURASI (correct_answer) ---
                try {
                    $sql = "INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_answer) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $exam_id,
                        $aiData['question_text'],
                        $aiData['option_a'],
                        $aiData['option_b'],
                        $aiData['option_c'],
                        $aiData['option_d'],
                        $aiData['correct_answer'] // JSON'dan gelen anahtar
                    ]);
                    $message = '<div class="alert alert-success">🤖 Yapay Zeka soruyu başarıyla oluşturdu ve ekledi!</div>';

                } catch (PDOException $e) {
                    // Eğer hala sütun hatası verirse bunu görelim
                    $message = '<div class="alert alert-danger">Veritabanı Hatası: ' . $e->getMessage() . '</div>';
                }
                
            } else {
                $message = '<div class="alert alert-warning">AI cevabı okunamadı. Tekrar deneyin.</div>';
            }
        } else {
            // Detaylı hata gösterimi
            $errMsg = isset($result['error']['message']) ? $result['error']['message'] : "Bilinmeyen API Hatası";
            $message = '<div class="alert alert-danger">Google Hatası: ' . $errMsg . '</div>';
        }
    }
    curl_close($ch);
}

// Sınavları Çek
$exams = $pdo->query("SELECT * FROM exams")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>AI Soru Üretici</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h1 class="animate__animated animate__pulse animate__infinite">✨ 🤖</h1>
                    <h3>Yapay Zeka Soru Sihirbazı</h3>
                    <p>Model: Gemini 2.5 Flash ⚡</p>
                </div>
                <div class="card-body p-5">
                    
                    <?php echo $message; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hangi Sınava Eklensin?</label>
                            <select name="exam_id" class="form-select" required>
                                <?php foreach($exams as $exam): ?>
                                    <option value="<?php echo $exam['id']; ?>"><?php echo htmlspecialchars($exam['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Soru Konusu Nedir?</label>
                            <input type="text" name="topic" class="form-control form-control-lg" placeholder="Örn: Osmanlı Devleti Yükselme Dönemi..." required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">
                                ⚡ Soruyu Üret ve Kaydet
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="questions.php" class="btn btn-outline-secondary">Listeye Dön</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>