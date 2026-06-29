<?php
$host = 'localhost';
$dbname = 'online_test_db'; // SQL kodunda oluşturduğumuz veritabanı adı
$username = 'root'; // XAMPP varsayılan kullanıcı
$password = ''; // XAMPP varsayılan şifre (boş)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>