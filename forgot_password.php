<?php
session_start();
require_once 'includes/db.php';

$step = 1; // 1: E-posta sor, 2: Kod ve Yeni Şifre sor
$message = "";
$message_type = "";
$demo_code = ""; // Simülasyon için kodu ekrana basacağız

// FORM GÖNDERİLDİ Mİ?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // AŞAMA 1: KOD GÖNDERME İSTEĞİ
    if (isset($_POST['email_check'])) {
        $email = trim($_POST['email']);
        
        // E-posta var mı bak
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Rastgele 5 haneli kod üret
            $code = rand(10000, 99999);
            
            // Kodu veritabanına yaz
            $update = $pdo->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
            $update->execute([$code, $email]);
            
            $step = 2;
            $email_val = $email; // Diğer forma taşımak için
            
            // --- SİMÜLASYON ---
            // Normalde burada mail($email, "Kodunuz", $code) çalışırdı.
            // Localhost'ta mail gitmeyeceği için kodu ekrana yazıyoruz.
            $message = "✅ Doğrulama kodu e-postanıza (temsili) gönderildi.<br><strong>DEMO KODUNUZ: $code</strong>";
            $message_type = "success";
            $demo_code = $code; 
            
        } else {
            $message = "❌ Bu e-posta adresi sistemde kayıtlı değil.";
            $message_type = "danger";
        }
    }

    // AŞAMA 2: ŞİFRE DEĞİŞTİRME İŞLEMİ
    if (isset($_POST['reset_password'])) {
        $email = $_POST['email'];
        $code_input = $_POST['code'];
        $new_pass = $_POST['new_password'];
        
        // E-posta ve Kod eşleşiyor mu?
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_code = ?");
        $stmt->execute([$email, $code_input]);
        
        if ($stmt->rowCount() > 0) {
            // Şifreyi güncelle ve kodu sil (tek kullanımlık olsun)
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            
            $update = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE email = ?");
            $update->execute([$hashed_password, $email]);
            
            $message = "🎉 Şifreniz başarıyla değiştirildi! <a href='login.php'>Giriş Yap</a>";
            $message_type = "success";
            $step = 3; // İşlem bitti
        } else {
            $message = "❌ Girdiğiniz kod hatalı!";
            $message_type = "danger";
            $step = 2; // Tekrar dene
            $email_val = $email;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Şifremi Unuttum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark fw-bold">🔑 Şifre Sıfırlama</div>
                    <div class="card-body">
                        
                        <?php if(!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                            <form method="POST">
                                <p class="text-muted">Kayıtlı e-posta adresinizi girin.</p>
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="ornek@email.com" required>
                                </div>
                                <button type="submit" name="email_check" class="btn btn-primary w-100">Kod Gönder</button>
                            </form>
                            <div class="mt-3 text-center">
                                <a href="login">Giriş Ekranına Dön</a>
                            </div>
                        <?php endif; ?>

                        <?php if ($step == 2): ?>
                            <form method="POST">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_val); ?>">
                                
                                <div class="mb-3">
                                    <label>Doğrulama Kodu</label>
                                    <input type="text" name="code" class="form-control" placeholder="12345" required>
                                    <small class="text-muted">Yukarıdaki yeşil kutuda yazan kodu girin.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label>Yeni Şifre</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Yeni şifreniz" required>
                                </div>

                                <button type="submit" name="reset_password" class="btn btn-success w-100">Şifreyi Güncelle</button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>