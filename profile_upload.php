<?php
session_start();
require_once 'includes/db.php';

// Güvenlik
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- 1. FİLİGRAN FONKSİYONU (Server Side) ---
function addWatermark($sourceFilePath, $watermarkText) {
    $ext = strtolower(pathinfo($sourceFilePath, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg': case 'jpeg': $im = @imagecreatefromjpeg($sourceFilePath); break;
        case 'png': $im = @imagecreatefrompng($sourceFilePath); imagealphablending($im, false); imagesavealpha($im, true); break;
        default: return false;
    }
    if (!$im) return false;

    $white = imagecolorallocate($im, 255, 255, 255);
    $bg_box = imagecolorallocatealpha($im, 0, 0, 0, 60);
    $font = 4;
    $padding = 10;
    
    $text_width = imagefontwidth($font) * strlen($watermarkText);
    $text_height = imagefontheight($font);
    $img_width = imagesx($im);
    $img_height = imagesy($im);
    
    $x = $img_width - $text_width - $padding;
    $y = $img_height - $text_height - $padding;

    imagefilledrectangle($im, $x - 5, $y - 5, $img_width - $padding + 5, $img_height - $padding + 5, $bg_box);
    imagestring($im, $font, $x, $y, $watermarkText, $white);

    switch ($ext) {
        case 'jpg': case 'jpeg': imagejpeg($im, $sourceFilePath, 90); break;
        case 'png': imagepng($im, $sourceFilePath, 8); break;
    }
    imagedestroy($im);
    return true;
}

// --- 2. YÜKLEME İŞLEMİ (AJAX'tan gelen veri) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar_file'])) {
    
    $file = $_FILES['avatar_file'];
    $uploadDir = 'uploads/';
    
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // Dosya uzantısını güvenli yap (Blob genelde png veya jpg gelir)
    $ext = 'png'; 
    $newFileName = "avatar_" . $user_id . "_" . time() . "." . $ext;
    $targetFile = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // PHP Filigranı Basıyor
        addWatermark($targetFile, "ONLINE SINAV - " . date("Y"));

        // Veritabanını Güncelle
        $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
        $stmt->execute([$targetFile, $user_id]);

        // Başarılı (JSON dönüyoruz çünkü işlemi JS yapacak)
        echo json_encode(['status' => 'success', 'url' => $targetFile]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dosya kaydedilemedi.']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Gelişmiş Profil Düzenleyici</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .img-container img { max-width: 100%; }
        .preview-box { overflow: hidden; width: 150px; height: 150px; border-radius: 50%; border: 2px solid #ddd; margin: 0 auto; }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">🎓 Online Sınav</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Geri Dön</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🎨 Profil Fotoğrafı Stüdyosu</h4>
                    </div>
                    <div class="card-body text-center">
                        
                        <div class="mb-4">
                            <label class="btn btn-outline-primary btn-lg">
                                📁 Resim Seç
                                <input type="file" id="imageInput" accept="image/*" hidden>
                            </label>
                        </div>

                        <div id="editorArea" style="display:none;">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6>Kırpma Alanı:</h6>
                                    <div class="img-container" style="max-height: 400px;">
                                        <img id="image" src="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h6>Önizleme:</h6>
                                    <div class="preview-box mb-3"></div>
                                    
                                    <h6>Efektler:</h6>
                                    <div class="d-grid gap-2">
                                        <button onclick="applyFilter('none')" class="btn btn-sm btn-light border">Orijinal</button>
                                        <button onclick="applyFilter('grayscale(100%)')" class="btn btn-sm btn-secondary">⚫⚪ Siyah Beyaz</button>
                                        <button onclick="applyFilter('sepia(100%)')" class="btn btn-sm btn-warning text-white">🟠 Sepya (Eskitme)</button>
                                        <button onclick="applyFilter('contrast(150%)')" class="btn btn-sm btn-dark">🌗 Yüksek Kontrast</button>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <button id="saveBtn" class="btn btn-success btn-lg w-100">
                                ✅ Kırp, Filigran Ekle ve Kaydet
                            </button>
                        </div>

                        <div id="loading" class="mt-3 text-info" style="display:none;">
                            ⏳ İşleniyor ve Yükleniyor... Lütfen bekleyin.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
        let cropper;
        const image = document.getElementById('image');
        const input = document.getElementById('imageInput');
        const editorArea = document.getElementById('editorArea');
        let currentFilter = 'none'; // Seçilen efekt

        // 1. Resim Seçildiğinde Editörü Aç
        input.addEventListener('change', function (e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const file = files[0];
                const reader = new FileReader();
                
                reader.onload = function (e) {
                    image.src = e.target.result;
                    editorArea.style.display = 'block';
                    
                    // Eğer eski cropper varsa yok et, yenisini başlat
                    if (cropper) { cropper.destroy(); }
                    
                    cropper = new Cropper(image, {
                        aspectRatio: 1, // Kare kırpma zorunluluğu (Profil resmi için)
                        viewMode: 1,
                        preview: '.preview-box'
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        // 2. Efekt Uygulama Fonksiyonu
        function applyFilter(filter) {
            currentFilter = filter;
            // Hem ana resme hem önizlemeye efekti CSS ile uygula
            document.querySelector('.cropper-container').style.filter = filter;
            document.querySelector('.preview-box').style.filter = filter;
        }

        // 3. KAYDET BUTONU
        document.getElementById('saveBtn').addEventListener('click', function () {
            if (!cropper) return;

            document.getElementById('loading').style.display = 'block';

            // Kırpılan alanı al
            const canvas = cropper.getCroppedCanvas({
                width: 400, // Sunucuya gidecek boyut (400x400 ideal)
                height: 400
            });

            // --- EFEKTİ CANVAS'A İŞLE (ÖNEMLİ!) ---
            // CSS filtresi sadece görüntüde vardır, resmin kendisine işlemek için yeni canvas açıyoruz.
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = canvas.width;
            finalCanvas.height = canvas.height;
            const ctx = finalCanvas.getContext('2d');

            // Efekti uygula
            ctx.filter = currentFilter;
            ctx.drawImage(canvas, 0, 0);

            // Resmi Blob'a çevir ve sunucuya gönder
            finalCanvas.toBlob(function (blob) {
                const formData = new FormData();
                formData.append('avatar_file', blob, 'profile.png');

                // AJAX ile PHP'ye gönder
                fetch('profile_upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Harika!',
                            text: 'Profil fotoğrafın güncellendi ve filigran eklendi.',
                            icon: 'success',
                            confirmButtonText: 'Tamam'
                        }).then(() => {
                            window.location.reload(); // Sayfayı yenile
                        });
                    } else {
                        Swal.fire('Hata', 'Yükleme başarısız oldu.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('loading').style.display = 'none';
                    Swal.fire('Hata', 'Sunucu hatası oluştu.', 'error');
                });
            });
        });
    </script>

</body>
</html>