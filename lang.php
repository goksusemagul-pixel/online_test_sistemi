<?php
// includes/lang.php

// Çıktı tamponlamayı başlat (Header hatasını önler)
ob_start();

// Oturum açık değilse başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- 1. HANGİ DİL SEÇİLMELİ? (Mantık Sırası) ---

// A. Kullanıcı butona bastı mı? (Örn: ?lang=en)
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    
    // Güvenlik: Sadece tr veya en olabilir
    if ($lang != 'tr' && $lang != 'en') {
        $lang = 'tr';
    }

    // Seçimi hem Session'a hem Cookie'ye kaydet
    $_SESSION['lang'] = $lang;
    setcookie("site_lang", $lang, time() + (86400 * 30), "/"); // 30 Gün

    // Değişikliğin görülmesi için sayfayı temizle ve YENİLE
    $clean_url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $clean_url");
    exit;
}

// B. Butona basılmadıysa, hafızada (Session) var mı?
elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

// C. Session yoksa, tarayıcı çerezinde (Cookie) var mı?
elseif (isset($_COOKIE['site_lang'])) {
    $lang = $_COOKIE['site_lang'];
    $_SESSION['lang'] = $lang; // Cookie'yi Session'a aktar
}

// D. Hiçbiri yoksa varsayılan TR olsun
else {
    $lang = 'tr';
    $_SESSION['lang'] = 'tr';
}

// --- 2. SÖZLÜK DİZİLERİ ---

$tr = [
    'seo_title' => 'Online Sınav Sistemi',
    'seo_desc' => 'Öğrenciler için online test çözme platformu.',
    'school' => 'Okul',
    'class' => 'Sınıf',
    'my_results' => 'Sonuçlarım',
    'active_exams' => 'Aktif Sınavlar',
    'duration' => 'Dakika',
    'start_btn' => 'Sınava Başla',
    'no_exam' => 'Şu an aktif sınav bulunmuyor.',
    'logout' => 'Çıkış Yap'
];

$en = [
    'seo_title' => 'Online Exam System',
    'seo_desc' => 'Online testing platform for students.',
    'school' => 'School',
    'class' => 'Class',
    'my_results' => 'My Results',
    'active_exams' => 'Active Exams',
    'duration' => 'Minutes',
    'start_btn' => 'Start Quiz',
    'no_exam' => 'No active exams at the moment.',
    'logout' => 'Logout'
];

// Aktif dili değişkene ata
$t = ($lang == 'en') ? $en : $tr;

// İşlem bitti, tamponu boşalt
ob_end_flush();
?>