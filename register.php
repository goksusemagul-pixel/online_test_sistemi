<?php
session_start();
require_once 'includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $school = trim($_POST['school']);
    $class_level = trim($_POST['class_level']);

    // Backend Kontrolü (Güvenlik için yine de kalsın)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $message = "Bu e-posta adresi zaten kullanılıyor!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, role, school, class_level) VALUES (?, ?, ?, 'student', ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$username, $email, $hashed_password, $school, $class_level])) {
            header("Location: login.php?status=success");
            exit;
        } else {
            $message = "Kayıt sırasında bir hata oluştu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> </head>
<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center">
                        <h4>🎓 Öğrenci Kayıt Formu</h4>
                    </div>
                    <div class="card-body p-4">
                        
                        <?php if(!empty($message)): ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="POST" id="registerForm">
                            
                            <div class="mb-3">
                                <label>Ad Soyad</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>E-posta Adresi</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                                <small id="emailFeedback" class="fw-bold"></small>
                            </div>

                            <div class="mb-3">
                                <label>Okul Adı</label>
                                <input type="text" name="school" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Sınıf Seviyesi</label>
                                <select name="class_level" class="form-select">
                                    <option value="9. Sınıf">9. Sınıf</option>
                                    <option value="10. Sınıf">10. Sınıf</option>
                                    <option value="11. Sınıf">11. Sınıf</option>
                                    <option value="12. Sınıf">12. Sınıf</option>
                                    <option value="Mezun">Mezun</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Şifre</label>
                                    <input type="password" name="password" id="pass1" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Şifre Tekrar</label>
                                    <input type="password" id="pass2" class="form-control" required>
                                </div>
                            </div>
                            <small id="passFeedback" class="d-block mb-3 fw-bold"></small>

                            <button type="submit" id="btnSubmit" class="btn btn-success w-100 btn-lg">Kayıt Ol</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small>Zaten hesabın var mı?</small> <a href="login.php" class="fw-bold">Giriş Yap</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            
            // 1. AJAX ÖRNEĞİ: E-posta kontrolü
            // Kullanıcı e-posta kutusundan çıktığı an (blur) çalışır
            $('#email').on('blur', function() {
                var emailVal = $(this).val();
                
                if (emailVal !== '') {
                    $.ajax({
                        url: 'check_email.php',      // Arka plana sor
                        type: 'POST',
                        data: {email: emailVal},
                        success: function(response) {
                            if (response == 'var') {
                                $('#emailFeedback').html('❌ Bu e-posta zaten kayıtlı!').css('color', 'red');
                                $('#btnSubmit').prop('disabled', true); // Butonu kilitle
                            } else {
                                $('#emailFeedback').html('✅ E-posta uygun.').css('color', 'green');
                                $('#btnSubmit').prop('disabled', false); // Butonu aç
                            }
                        }
                    });
                }
            });

            // 2. FORM DOĞRULAMA: Şifre Eşleşme Kontrolü
            // Kullanıcı tuşa bastıkça (keyup) çalışır
            $('#pass1, #pass2').on('keyup', function() {
                var pass1 = $('#pass1').val();
                var pass2 = $('#pass2').val();

                if (pass1 !== '' && pass2 !== '') {
                    if (pass1 === pass2) {
                        $('#passFeedback').html('✅ Şifreler eşleşiyor').css('color', 'green');
                        $('#btnSubmit').prop('disabled', false);
                    } else {
                        $('#passFeedback').html('❌ Şifreler eşleşmiyor!').css('color', 'red');
                        $('#btnSubmit').prop('disabled', true); // Hatalıysa göndermesin
                    }
                } else {
                    $('#passFeedback').text('');
                }
            });

        });
    </script>
    <?php include 'includes/accessibility.php'; ?>

</body>
</html>