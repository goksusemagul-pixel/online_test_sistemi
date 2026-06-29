<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_id = $_POST['exam_id'];
    $answers = isset($_POST['answers']) ? $_POST['answers'] : []; 
    $user_id = $_SESSION['user_id'];

    $correct = 0;
    $wrong = 0;
    $empty = 0;

    // Doğru cevapları çek (Sütun adı correct_answer veya correct_option olabilir, ikisini de dene)
    try {
        $sql = "SELECT id, correct_answer FROM questions WHERE exam_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$exam_id]);
        $questions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        // Eğer sütun adı farklıysa
        $sql = "SELECT id, correct_option FROM questions WHERE exam_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$exam_id]);
        $questions = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    $total_questions = count($questions);

    // HESAPLAMA MOTORU (Sorun buradaydı, düzelttik)
    foreach ($questions as $q_id => $correct_opt) {
        // Veritabanındaki cevabı temizle (Boşlukları sil, BÜYÜK HARFE çevir)
        $clean_correct = strtoupper(trim($correct_opt)); 

        if (isset($answers[$q_id])) {
            // Öğrencinin cevabını temizle
            $clean_student = strtoupper(trim($answers[$q_id]));
            
            if ($clean_student === $clean_correct) {
                $correct++;
            } else {
                $wrong++;
            }
        } else {
            $empty++;
        }
    }

    // Puanlama
    $score = 0;
    if ($total_questions > 0) {
        $score = ($correct / $total_questions) * 100;
    }

    // Kaydet
    $sql = "INSERT INTO results (user_id, exam_id, score, correct_count, wrong_count) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $exam_id, round($score), $correct, $wrong]);

    header("Location: my_results.php?success=1");
    exit;
}
?>