<?php
// includes/style.php (YENİ DOSYA)

// Varsayılan tema (Açık renk - Cosmo)
$default_theme = "https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cosmo/bootstrap.min.css";

// Koyu tema (Koyu renk - Cyborg)
$dark_theme = "https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cyborg/bootstrap.min.css";

// Tema Değiştirme İsteği Geldi mi?
if (isset($_GET['theme_select'])) {
    $selected_theme = $_GET['theme_select'];
    setcookie("site_theme", $selected_theme, time() + (86400 * 30), "/"); // 30 Günlük
    
    $clean_url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $clean_url");
    exit;
}

// Hangi tema aktif?
$current_theme_url = isset($_COOKIE['site_theme']) && $_COOKIE['site_theme'] == 'dark' ? $dark_theme : $default_theme;
$current_theme_name = isset($_COOKIE['site_theme']) && $_COOKIE['site_theme'] == 'dark' ? 'dark' : 'light';
?>

<link href="<?php echo $current_theme_url; ?>" rel="stylesheet">

<style>
    body { transition: background-color 0.5s, color 0.5s; }
    .card { transition: background-color 0.5s; }
</style>