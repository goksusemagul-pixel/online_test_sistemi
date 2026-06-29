<?php
session_start();
require_once '../includes/db.php';

// Admin Kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Soru Silme İşlemi
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: questions.php?deleted=1");
    exit;
}

// Soruları ve Sınav Adını Çek
// (Hangi sorunun hangi sınava ait olduğunu görmek için JOIN kullandık)
$sql = "SELECT questions.*, exams.title as exam_title 
        FROM questions 
        LEFT JOIN exams ON questions.exam_id = exams.id 
        ORDER BY questions.id DESC";
$questions = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Soru Bankası Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
    <style>
        /* Arama kutusu için özel stil */
        .search-box {
            position: relative;
        }
        .search-box input {
            padding-left: 35px;
            border-radius: 20px;
        }
        .search-box i {
            position: absolute;
            left: 12px;
            top: 10px;
            color: #888;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Admin Paneli</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">Geri Dön</a>
    </div>
</nav>

<div class="container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>❓ Soru Bankası</h2>
        <a href="ai_question.php" class="btn btn-success">
            ✨ Yeni Soru Ekle (AI veya Manuel)
        </a>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
        <div class="alert alert-success animate__animated animate__fadeOut animate__delay-2s">Soru başarıyla silindi.</div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 text-primary">Toplam Soru Sayısı: <?php echo count($questions); ?></h5>
                </div>
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="liveSearchInput" class="form-control" placeholder="Tabloda ara (Soru, Sınav Adı)...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="dataTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th style="width: 40%;">Soru Metni</th>
                            <th>Ait Olduğu Sınav</th>
                            <th>Doğru Cevap</th>
                            <th class="text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($questions as $q): ?>
                        <tr>
                            <td>#<?php echo $q['id']; ?></td>
                            <td title="<?php echo htmlspecialchars($q['question_text']); ?>">
                                <?php echo mb_substr(htmlspecialchars($q['question_text']), 0, 60) . '...'; ?>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?php echo htmlspecialchars($q['exam_title'] ?? 'Genel/Silinmiş Sınav'); ?>
                                </span>
                            </td>
                            <td class="fw-bold text-center text-success">
                                <?php echo $q['correct_answer']; ?>
                            </td>
                            <td class="text-end">
                                <button onclick="confirmDelete(<?php echo $q['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt"></i> Sil
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
    // --- 1. ÖZELLİK: CANLI TABLO ARAMA (LIVE SEARCH) ---
    document.getElementById('liveSearchInput').addEventListener('keyup', function() {
        // 1. Yazılan metni al ve küçük harfe çevir
        let filter = this.value.toLowerCase();
        // 2. Tablonun gövdesindeki (tbody) tüm satırları (tr) seç
        let rows = document.querySelectorAll('#dataTable tbody tr');

        // 3. Her satır için döngü başlat
        rows.forEach(row => {
            // Satırın içindeki tüm metni al ve küçük harfe çevir
            let text = row.textContent.toLowerCase();
            
            // Eğer satırdaki metin, aranan kelimeyi içeriyorsa göster, içermiyorsa gizle
            if (text.includes(filter)) {
                row.style.display = ''; // Varsayılan görünüm (Göster)
            } else {
                row.style.display = 'none'; // Gizle
            }
        });
    });

    // --- 2. ÖZELLİK: ŞIK SİLME ONAYI (SWEETALERT2) ---
    function confirmDelete(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu soruyu silmek istediğinize emin misiniz? Bu işlem geri alınamaz!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, Sil!',
            cancelButtonText: 'Vazgeç'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kullanıcı Evet derse PHP'nin silme linkine yönlendir
                window.location.href = "questions.php?delete_id=" + id;
            }
        })
    }
</script>

</body>
</html>