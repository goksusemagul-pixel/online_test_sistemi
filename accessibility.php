<style>
    /* Erişilebilirlik Butonu (Sabit) */
    #access-btn {
        position: fixed;
        bottom: 20px;
        right: 80px; /* Chat butonlarıyla çakışmasın diye biraz içeride */
        z-index: 9999;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 24px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        background-color: #0d6efd;
        color: white;
        border: none;
        cursor: pointer;
        transition: transform 0.2s;
    }
    #access-btn:hover { transform: scale(1.1); }

    /* Açılır Panel */
    #access-panel {
        display: none; /* Başlangıçta gizli */
        position: fixed;
        bottom: 80px;
        right: 80px;
        width: 250px;
        background: white;
        border: 2px solid #0d6efd;
        border-radius: 10px;
        padding: 15px;
        z-index: 9999;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Gece modunda panel uyumu */
    body.dark-mode #access-panel {
        background: #333;
        border-color: #fff;
        color: #fff;
    }

    .access-item { margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
    .access-item button { width: 45%; }
</style>

<button id="access-btn" onclick="toggleAccessPanel()" aria-label="Erişilebilirlik Menüsünü Aç">
    ♿
</button>

<div id="access-panel" role="dialog" aria-modal="true" aria-label="Erişilebilirlik Seçenekleri">
    <h6 class="border-bottom pb-2 mb-3 text-center fw-bold">🛠️ Erişilebilirlik</h6>
    
    <div class="access-item">
        <span>Yazı Boyutu:</span>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="changeFontSize(1)" aria-label="Yazıyı Büyüt">A+</button>
            <button class="btn btn-outline-primary" onclick="changeFontSize(-1)" aria-label="Yazıyı Küçült">A-</button>
        </div>
    </div>

    <div class="access-item">
        <span>Görünüm:</span>
        <button class="btn btn-sm btn-outline-dark w-100" onclick="toggleGrayscale()" aria-label="Gri Tonlamayı Aç/Kapat">
            👁️ Gri Ton (Renk Körlüğü)
        </button>
    </div>

    <div class="access-item">
        <button class="btn btn-sm btn-outline-success w-100" onclick="toggleReadingGuide()" aria-label="Okuma Kılavuzunu Aç">
            📏 Okuma Çizgisi
        </button>
    </div>

    <div class="mt-2 text-center">
        <button class="btn btn-sm btn-danger w-100" onclick="resetAccess()" aria-label="Ayarları Sıfırla">
            🔄 Sıfırla
        </button>
    </div>
</div>

<div id="reading-guide" style="display:none; position:fixed; left:0; width:100%; height:5px; background:yellow; z-index:9998; pointer-events:none; opacity:0.5;"></div>

<script>
    // Paneli Aç/Kapat
    function toggleAccessPanel() {
        var panel = document.getElementById('access-panel');
        panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
    }

    // Yazı Boyutu Ayarı
    let currentZoom = 1;
    function changeFontSize(step) {
        currentZoom += step * 0.1;
        if (currentZoom < 0.8) currentZoom = 0.8; // Çok küçülmesin
        if (currentZoom > 1.5) currentZoom = 1.5; // Çok büyümesin
        document.body.style.zoom = currentZoom;
    }

    // Gri Tonlama (Renk Körlüğü Modu)
    let isGray = false;
    function toggleGrayscale() {
        if (!isGray) {
            document.body.style.filter = "grayscale(100%)";
            isGray = true;
        } else {
            document.body.style.filter = "none";
            isGray = false;
        }
    }

    // Okuma Kılavuzu (Disleksi Modu)
    let guideActive = false;
    function toggleReadingGuide() {
        var guide = document.getElementById('reading-guide');
        if (!guideActive) {
            guide.style.display = 'block';
            document.addEventListener('mousemove', moveGuide);
            guideActive = true;
        } else {
            guide.style.display = 'none';
            document.removeEventListener('mousemove', moveGuide);
            guideActive = false;
        }
    }
    function moveGuide(e) {
        var guide = document.getElementById('reading-guide');
        guide.style.top = e.clientY + 'px';
    }

    // Sıfırla
    function resetAccess() {
        document.body.style.zoom = 1;
        currentZoom = 1;
        document.body.style.filter = "none";
        isGray = false;
        document.getElementById('reading-guide').style.display = 'none';
        guideActive = false;
    }
</script>