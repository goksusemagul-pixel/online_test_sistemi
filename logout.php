<?php
session_start(); // Oturumu başlat
session_unset(); // Tüm değişkenleri temizle
session_destroy(); // Oturumu tamamen yok et (Kriter 14)

// Giriş sayfasına geri gönder
header("Location: login.php");
exit;
?>