<?php
// admin/model_list.php

$apiKey = "AIzaSyBZejWAsQsOxOQRjK9pVUoenedn1Hssz38"; // <--- DİKKAT!

// Google'a "Benim kullanabileceğim modelleri listele" diyoruz
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

echo "<h2>📋 Senin Hesabına Tanımlı Modeller</h2>";

if (isset($data['models'])) {
    echo "<ul>";
    foreach ($data['models'] as $model) {
        // Bize sadece 'generateContent' yapabilenler lazım (Soru üretecek olanlar)
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            $name = str_replace("models/", "", $model['name']);
            echo "<li><strong style='color:green'>" . $name . "</strong> (Bunu kullanabilirsin)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "HATA: Liste alınamadı. <br>";
    print_r($data);
}
?>