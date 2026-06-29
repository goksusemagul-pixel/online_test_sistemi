<?php
// admin/debug_ai.php

// 1. API ANAHTARINI BURAYA YAZ (Tırnakları silme!)
$apiKey = "AIzaSyBZejWAsQsOxOQRjK9pVUoenedn1Hssz38"; 

// Test edilecek Model ve URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey;

// Gönderilecek basit veri
$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => "Bana sadece 'Merhaba' de."]
            ]
        ]
    ]
];

echo "<h2>🔍 Detaylı Hata Analizi</h2>";
echo "<hr>";

// cURL Başlat
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// SSL Hatalarını Yoksay (Localhost için önemlidir)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Cevabı al
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

// --- SONUÇLARI EKRAANA BAS ---

echo "<b>HTTP Durum Kodu:</b> " . $httpCode . " (200 ise başarılıdır)<br><br>";

if ($curlError) {
    echo "<h3 style='color:red'>❌ BAĞLANTI HATASI (cURL)</h3>";
    echo "Hata Mesajı: " . $curlError . "<br>";
    echo "Sebep: Bilgisayarın Google sunucularına bağlanamıyor.";
} else {
    echo "<b>Google'dan Gelen Ham Cevap:</b><br>";
    echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ccc;'>" . htmlspecialchars($response) . "</pre>";
    
    // JSON Analizi
    $json = json_decode($response, true);
    
    if (isset($json['error'])) {
        echo "<h3 style='color:red'>❌ GOOGLE API HATASI</h3>";
        echo "Kod: " . $json['error']['code'] . "<br>";
        echo "Mesaj: " . $json['error']['message'] . "<br>";
        echo "Durum: " . $json['error']['status'] . "<br>";
    } elseif ($httpCode == 200) {
        echo "<h3 style='color:green'>✅ BAŞARILI! SİSTEM ÇALIŞIYOR.</h3>";
        echo "Cevap: " . $json['candidates'][0]['content']['parts'][0]['text'];
    } else {
        echo "<h3 style='color:orange'>⚠️ BEKLENMEYEN DURUM</h3>";
        echo "Bağlantı var ama cevap işlenemedi.";
    }
}
?>