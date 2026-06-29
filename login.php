<?php
session_start();
require_once 'includes/db.php';
if(file_exists('includes/lang.php')) { require_once 'includes/lang.php'; }

// Beni Hatırla Çerezi Var mı? Varsa e-postayı değişkene ata
$saved_email = "";
if (isset($_COOKIE['remember_email'])) {
    $saved_email = $_COOKIE['remember_email'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']); // Kutucuk işaretli mi?

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Giriş Başarılı: Session Oluştur
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['lang'] = 'tr'; 

            // --- COOKIE İŞLEMİ (BENİ HATIRLA) ---
            if ($remember) {
                // E-postayı 30 gün boyunca hatırla
                setcookie("remember_email", $email, time() + (86400 * 30), "/");
            } else {
                // İşaretli değilse çerezi sil (süresini geçmişe ayarla)
                setcookie("remember_email", "", time() - 3600, "/");
            }
            // ------------------------------------

            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "E-posta veya şifre hatalı!";
        }
    } else {
        $error = "Lütfen alanları doldurun.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>🎓 Giriş Yap</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>E-posta Adresi</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($saved_email); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Şifre</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="remember" id="rememberCheck" 
                                <?php echo ($saved_email != "") ? "checked" : ""; ?>>
                                <label class="form-check-label text-muted" for="rememberCheck">E-postamı Hatırla (Cookie)</label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg">Giriş Yap</button>
                            
                            <div class="mt-3 text-center">
                                <a href="forgot_password.php" class="text-decoration-none text-danger fw-bold">
                                    🔑 Şifremi Unuttum?
                                </a>
                            </div>
                        </form>

                        <div class="mt-3 text-center border-top pt-3">
                            <small>Hesabın yok mu?</small> 
                            <a href="register.php" class="fw-bold">Kayıt Ol</a>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/accessibility.php'; ?>
</body>
</html>