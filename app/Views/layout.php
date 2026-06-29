<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Doa & Dzikir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Ensure CSS uses base path /public/assets/css/style.css because of routing -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <script>
        // Global variables
        const CSRF_TOKEN = '<?php echo $_SESSION["csrf_token"] ?? ""; ?>';
        const IS_LOGGED_IN = <?php echo isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? 'true' : 'false'; ?>;
    </script>
</head>
<body class="show-meaning">
    <div class="controls">
        <a href="/" class="btn-ctrl spa-link" id="navHome" style="display: none;">🏠 Beranda</a>
        <a href="/dashboard" class="btn-ctrl spa-link" id="navDashboard">⚙️ Dashboard</a>
        <button class="btn-ctrl" id="translateToggle"><span>🙈</span> Sembunyi Arti</button>
        <button class="btn-ctrl" id="themeToggle">🌙 Gelap</button>
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <button class="btn-ctrl" id="btnLogout">🚪 Logout</button>
        <?php endif; ?>
    </div>

    <div class="wrapper" id="app-container">
        <?php echo $content; ?>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
