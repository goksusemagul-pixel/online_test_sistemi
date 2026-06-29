<?php
// admin/test_api.php

$apiKey = "AIzaSyD3Tz9noc0LpZfP4XSjHNAYcytG4leZ6xQ"; // <--- DİKKAT!

// Google'a "Benim hangi modelleri kullanmaya iznim var?" diye soralım
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "<h1>🔍 API Teşhis Raporu</h1>";

if (isset($data['error'])) {
    echo "<h3 style='color:red'>❌ HATA VAR!</h3>";
    echo "<b>Hata Mesajı:</b> " . $data['error']['message'] . "<br>";
    echo "<b>Sebep:</b> Projenizde API kapalı olabilir veya anahtar hatalı.";
    echo "<br><br>👉 <a href='https://console.cloud.google.com/apis/library/generativelanguage.googleapis.com' target='_blank'>Buraya Tıkla ve ENABLE Butonuna Bas</a>";
} else {
    echo "<h3 style='color:green'>✅ BAĞLANTI BAŞARILI!</h3>";
    echo "Sizin kullanabileceğiniz modeller şunlar:<br><ul>";
    foreach ($data['models'] as $model) {
        // Sadece 'generateContent' destekleyenleri gösterelim
        if(in_array("generateContent", $model['supportedGenerationMethods'])) {
            echo "<li>" . str_replace("models/", "", $model['name']) . "</li>";
        }
    }
    echo "</ul>";
    echo "<b>ÇÖZÜM:</b> Yukarıdaki listeden birini `ai_question.php` dosyasındaki model adıyla değiştirin.";
}
?>