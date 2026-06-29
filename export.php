<?php
session_start();
require_once 'includes/db.php';

// Güvenlik: Giriş yapmayan indiremez
if (!isset($_SESSION['user_id'])) {
    exit("Yetkisiz erişim.");
}

// Hangi format isteniyor? (json veya xml)
$format = isset($_GET['type']) ? $_GET['type'] : 'json';
$user_id = $_SESSION['user_id'];

// 1. Verileri Çek
$sql = "SELECT exams.title, results.score, results.correct_count, results.wrong_count, results.created_at 
        FROM results 
        JOIN exams ON results.exam_id = exams.id 
        WHERE results.user_id = ? 
        ORDER BY results.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Formatlama ve İndirme İşlemi

if ($format == 'json') {
    // --- JSON FORMATI ---
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="sonuclarim.json"');
    
    // JSON_PRETTY_PRINT: Okunabilir yapar
    // JSON_UNESCAPED_UNICODE: Türkçe karakterleri bozmaz (ğ, ş, ı vs.)
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} elseif ($format == 'xml') {
    // --- XML FORMATI ---
    header('Content-Type: text/xml; charset=utf-8');
    header('Content-Disposition: attachment; filename="sonuclarim.xml"');
    
    // XML Başlığı
    echo "<?xml version='1.0' encoding='UTF-8'?>\n";
    echo "<sonuclar>\n";
    
    foreach ($data as $row) {
        echo "\t<sinav>\n";
        echo "\t\t<baslik>" . htmlspecialchars($row['title']) . "</baslik>\n";
        echo "\t\t<puan>" . $row['score'] . "</puan>\n";
        echo "\t\t<dogru>" . $row['correct_count'] . "</dogru>\n";
        echo "\t\t<yanlis>" . $row['wrong_count'] . "</yanlis>\n";
        echo "\t\t<tarih>" . $row['created_at'] . "</tarih>\n";
        echo "\t</sinav>\n";
    }
    
    echo "</sonuclar>";
}
?>