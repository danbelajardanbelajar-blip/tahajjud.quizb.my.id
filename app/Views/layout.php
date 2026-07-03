<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Doa & Dzikir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@400;600;700&family=Inter:wght@400;500;600&family=Lora:ital,wght@0,400;0,600;1,400&family=Noto+Naskh+Arabic:wght@400;600;700&family=Poppins:wght@400;500;600&family=Roboto:wght@400;500;700&family=Scheherazade+New:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <!-- Ensure CSS uses base path /public/assets/css/style.css because of routing -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        // Global variables
        const CSRF_TOKEN = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
        const IS_LOGGED_IN = <?php echo isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? 'true' : 'false'; ?>;
    </script>
</head>
<body class="">
    <div class="controls">
        <a href="/" class="btn-ctrl spa-link" id="navHome" style="display: none;">🏠 Beranda</a>
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <a href="/dashboard" class="btn-ctrl spa-link" id="navDashboard">⚙️ Dashboard</a>
            <a href="/tracker" class="btn-ctrl spa-link" id="navTracker">📊 Tracker</a>
        <?php endif; ?>
        <button class="btn-ctrl" id="settingsToggle">⚙️ Pengaturan</button>
        <button class="btn-ctrl" id="translateToggle"><span>👁️</span> Tampilkan Arti</button>
        <button class="btn-ctrl" id="themeToggle">☀️ Terang</button>
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <button class="btn-ctrl" id="btnLogout">🚪 Logout</button>
        <?php endif; ?>
    </div>

    <!-- SETTINGS MODAL -->
    <div class="modal-overlay" id="settingsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin:0;">⚙️ Pengaturan Tampilan</h3>
                <button class="btn-close" id="settingsClose">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Ukuran Teks</label>
                    <div style="display: flex; gap: 10px; align-items: center; justify-content: space-between; background: var(--bg); padding: 10px; border-radius: 8px; border: 1px solid var(--muted);">
                        <button class="btn btn-secondary" id="btn-font-minus" style="font-size: 18px; padding: 4px 16px;">A-</button>
                        <span id="font-scale-display" style="font-weight: bold;">100%</span>
                        <button class="btn btn-secondary" id="btn-font-plus" style="font-size: 18px; padding: 4px 16px;">A+</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Font Arab</label>
                    <select id="select-font-arab" class="form-control">
                        <option value="'Amiri', serif">Amiri (Klasik)</option>
                        <option value="'Noto Naskh Arabic', serif">Noto Naskh Arabic</option>
                        <option value="'Scheherazade New', serif">Scheherazade New</option>
                        <option value="'Tajawal', sans-serif">Tajawal (Modern)</option>
                        <option value="'Cairo', sans-serif">Cairo (Kufi)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Font Terjemahan</label>
                    <select id="select-font-latin" class="form-control">
                        <option value="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif">System Default</option>
                        <option value="'Inter', sans-serif">Inter</option>
                        <option value="'Roboto', sans-serif">Roboto</option>
                        <option value="'Poppins', sans-serif">Poppins</option>
                        <option value="'Lora', serif">Lora (Serif)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper" id="app-container">
        <?php echo $content; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
