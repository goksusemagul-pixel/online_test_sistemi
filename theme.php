<style>
    /* Sabit Duran Yuvarlak Buton */
    #theme-toggle-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #343a40;
        color: white;
        border: none;
        font-size: 24px;
        cursor: pointer;
        z-index: 9999;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        transition: transform 0.2s;
    }
    #theme-toggle-btn:hover {
        transform: scale(1.1);
    }

    /* --- KARANLIK MOD STİLLERİ --- */
    body.dark-mode {
        background-color: #121212 !important; /* Koyu Gri Arkaplan */
        color: #e0e0e0 !important; /* Açık Gri Yazı */
    }

    /* Kartlar (Kutular) */
    .dark-mode .card {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        color: #fff !important;
    }
    
    /* Tablolar */
    .dark-mode .table {
        color: #e0e0e0 !important;
        background-color: #1e1e1e !important;
    }
    .dark-mode .table-striped > tbody > tr:nth-of-type(odd) > * {
        background-color: #2c2c2c !important;
        color: #fff !important;
    }
    
    /* Navbar (Üst Menü) */
    .dark-mode .navbar {
        background-color: #000 !important; /* Simsiyah menü */
    }

    /* Listeler ve Formlar */
    .dark-mode .list-group-item {
        background-color: #1e1e1e;
        color: #fff;
        border-color: #333;
    }
    .dark-mode input, .dark-mode select, .dark-mode textarea {
        background-color: #2c2c2c !important;
        border-color: #444 !important;
        color: #fff !important;
    }
</style>

<button id="theme-toggle-btn" onclick="toggleTheme()">🌙</button>

<script>
    // Sayfa açılınca hafızaya bak: Kullanıcı daha önce Dark Mode seçmiş mi?
    const currentTheme = localStorage.getItem('theme');
    const btn = document.getElementById('theme-toggle-btn');
    
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
        btn.innerHTML = "☀️"; // İkonu Güneş yap
    }

    // Butona Tıklanınca Çalışan Fonksiyon
    function toggleTheme() {
        document.body.classList.toggle('dark-mode');
        
        let theme = 'light';
        // Eğer şu an dark mode aktifse
        if (document.body.classList.contains('dark-mode')) {
            theme = 'dark';
            btn.innerHTML = "☀️"; // Güneş yap
        } else {
            btn.innerHTML = "🌙"; // Ay yap
        }
        
        // Seçimi tarayıcı hafızasına (LocalStorage) kaydet
        localStorage.setItem('theme', theme);
    }
</script>