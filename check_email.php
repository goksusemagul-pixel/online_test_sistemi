<?php
// check_email.php - AJAX isteği buraya gelir
require_once 'includes/db.php';

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Veritabanında bu e-posta var mı?
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "var"; // E-posta zaten kayıtlı
    } else {
        echo "yok"; // E-posta müsait
    }
}
?>