<?php
class PdfParser {
    public function parseFile($filename) {
        $content = file_get_contents($filename);
        if (!$content) return "Dosya okunamadı.";
        
        // Tüm metni analiz et
        return $this->extractText($content);
    }

    private function extractText($content) {
        $text = '';
        $data_parts = [];

        // 1. ADIM: PDF'in içindeki sıkıştırılmış "Stream" bloklarını bul ve aç
        if (preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded) {
                    $data_parts[] = $decoded;
                } else {
                    $data_parts[] = $stream; // Sıkıştırılmamışsa olduğu gibi al
                }
            }
        } else {
            // Hiç stream yoksa dosyanın kendisini kullan
            $data_parts[] = $content;
        }

        // 2. ADIM: Her parçanın içindeki metinleri avla
        foreach ($data_parts as $data) {
            
            // A) Normal Parantez Yazıları: (Merhaba)
            // Bu regex, parantez içindeki metinleri yakalar.
            if (preg_match_all('/\((.*?)\)/', $data, $m)) {
                foreach ($m[1] as $str) {
                    $text .= $str . " ";
                }
            }

            // B) Hexadecimal (Şifreli) Yazılar: <004D00650072>
            // Bazı PDF'ler harfleri sayıya çevirir. Onları yakalayıp geri çevirelim.
            if (preg_match_all('/\<([0-9a-fA-F]+)\>/', $data, $m)) {
                foreach ($m[1] as $hex) {
                    // Hex kodunu metne çevir
                    $text .= $this->hex2str($hex) . " ";
                }
            }
        }

        return $this->finalCleanup($text);
    }

    // Hex kodunu (Örn: 4D657268616261) okunabilir yazıya (Merhaba) çevirir
    private function hex2str($hex) {
        $str = '';
        // Tek sayıdaysa sonuna 0 ekle (Hata önleyici)
        if (strlen($hex) % 2 != 0) $hex .= "0"; 
        
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $char = substr($hex, $i, 2);
            $dec = hexdec($char);
            
            // Sadece okunabilir karakterleri al (ASCII 32-126 ve Türkçe Karakterler)
            // Kontrol karakterlerini ve boşlukları atla
            if ($dec >= 32) {
                $str .= chr($dec);
            }
        }
        return $str;
    }

    private function finalCleanup($text) {
        // PDF kaçış karakterlerini temizle (\n, \r, \t vb.)
        $text = str_replace(['\\(', '\\)', '\\n', '\\r', '\\t', '\\'], ['', '', ' ', ' ', ' ', ''], $text);

        // Gereksiz karakterleri temizle
        // Sadece Harfler, Sayılar ve Noktalama İşaretleri kalsın.
        $text = preg_replace('/[^a-zA-Z0-9çğıöşüÇĞİÖŞÜ\s\.,:;\-_\?!@%\(\)\+=\/]/u', ' ', $text);

        // Çoklu boşlukları teke indir
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
}
?>