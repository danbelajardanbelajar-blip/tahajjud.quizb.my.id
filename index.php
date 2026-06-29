<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Doa & Dzikir</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap" rel="stylesheet">

<style>
/* =====================
   THEME VARIABLES
===================== */
:root{
    --bg:#020617;
    --bg-grad:#000;
    --card:#0f172a;
    --text:#f8fafc;
    --muted:#94a3b8;
    --accent:#38bdf8;
}

body.light{
    --bg:#f8fafc;
    --bg-grad:#e5e7eb;
    --card:#ffffff;
    --text:#020617;
    --muted:#475569;
}

/* =====================
   BASE
===================== */
*{box-sizing:border-box}

html{scroll-behavior:smooth}

body{
    margin:0;
    background:radial-gradient(circle at top,var(--bg),var(--bg-grad));
    color:var(--text);
    font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    overflow-x:hidden;
    transition:background .4s,color .4s;
}

/* =====================
   WRAPPER
===================== */
.wrapper{
    max-width:900px;
    margin:auto;
    padding:70px 18px 90px;
}

/* =====================
   HEADER
===================== */
.header{
    text-align:center;
    margin-bottom:60px;
    animation:fadeDown 1s ease;
}
.header h1{
    margin:0;
    font-size:28px;
    font-weight:600;
}
.header p{
    margin-top:8px;
    font-size:14px;
    color:var(--muted);
}

/* =====================
   CONTROLS (TOP RIGHT)
===================== */
.controls{
    position:fixed;
    top:16px;
    right:16px;
    display:flex;
    gap:8px;
    z-index:999;
}

.btn-ctrl{
    background:var(--card);
    color:var(--text);
    border:none;
    border-radius:999px;
    padding:10px 16px;
    font-size:13px;
    font-weight: 500;
    cursor:pointer;
    box-shadow:0 10px 30px rgba(0,0,0,.25);
    transition:.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-ctrl:hover{
    transform:translateY(-2px);
    background: var(--accent);
    color: #fff;
}

/* =====================
   DOA CARD
===================== */
.doa{
    position:relative;
    background:linear-gradient(180deg,var(--bg),var(--card));
    border-radius:20px;
    padding:28px 26px;
    margin-bottom:32px;
    box-shadow:0 20px 50px rgba(0,0,0,.35);
    transform:translateY(40px) scale(.96);
    opacity:0;
    transition:all .5s cubic-bezier(.22,1,.36,1);
    cursor:pointer;
    overflow:hidden;
    border: 1px solid transparent;
}

.doa.show{
    transform:translateY(0) scale(1);
    opacity:1;
}

.doa:hover {
    border-color: var(--accent);
}

/* =====================
   TEXT ELEMENTS
===================== */
.arab{
    font-family:'Amiri','Noto Naskh Arabic',serif;
    font-size:26px;
    line-height:2.15;
    direction:rtl;
    text-align:right;
    transition: color 0.3s;
}

.terjemah{
    font-size: 15px;
    line-height: 1.6;
    color: var(--muted);
    border-top: 1px solid rgba(148, 163, 184, 0.2);
    margin-top: 15px;
    padding-top: 15px;
    display: none; /* Sembunyi secara default */
    animation: slideIn 0.4s ease-out;
}

/* Logika Show/Hide Terjemah via Class */
.show-meaning .terjemah,
.doa.active .terjemah {
    display: block;
}

/* =====================
   ANIMATIONS
===================== */
@keyframes fadeDown{
    from{opacity:0;transform:translateY(-20px)}
    to{opacity:1;transform:none}
}

@keyframes slideIn{
    from{opacity:0; transform: translateY(10px);}
    to{opacity:1; transform: translateY(0);}
}

/* Mobile */
@media(max-width:600px){
    .arab{font-size:22px}
    .header h1{font-size:22px}
    .btn-ctrl{padding: 8px 12px; font-size: 12px;}
}
</style>
</head>
<body class="show-meaning">

<div class="controls">
    <button class="btn-ctrl" id="translateToggle"><span>🙈</span> Sembunyi Arti</button>
    <button class="btn-ctrl" id="themeToggle">🌙 Gelap</button>
</div>

<div class="wrapper">

<div class="header">
    <h1>Doa & Dzikir</h1>
    <p>Scroll perlahan • Klik kartu untuk intip arti</p>
</div>

<?php
$file_json = 'data_doa.json';
$data_doa = [];

// Membaca database JSON secara dinamis
if (file_exists($file_json)) {
    $json_string = file_get_contents($file_json);
    $data_doa = json_decode($json_string, true) ?? [];
}

if (!empty($data_doa)) {
    foreach($data_doa as $item){
        echo '<div class="doa" onclick="toggleThisTranslate(this)">';
        echo '  <div class="arab">'.$item['arab'].'</div>';
        if(!empty($item['terjemah'])){
            echo '  <div class="terjemah">'.$item['terjemah'].'</div>';
        }
        echo '</div>';
    }
} else {
    echo '<div style="text-align:center; color:var(--muted); margin-top:50px;">';
    echo '  <p>Belum ada data doa. Silakan isi terlebih dahulu melalui <a href="dashboard.php" style="color:var(--accent)">Dashboard</a>.</p>';
    echo '</div>';
}
?>

</div>

<script>
/* =====================
   SCROLL ANIMATION
===================== */
const items = document.querySelectorAll('.doa');
const observer = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
        if(e.isIntersecting){
            e.target.classList.add('show');
        }
    });
},{threshold:.15});
items.forEach(i=>observer.observe(i));

/* =====================
   THEME TOGGLE
===================== */
const btnTheme = document.getElementById('themeToggle');
const body = document.body;

const savedTheme = localStorage.getItem('theme');
if(savedTheme === 'light'){
    body.classList.add('light');
    btnTheme.textContent = '☀️ Terang';
}

btnTheme.onclick = () => {
    body.classList.toggle('light');
    const isLight = body.classList.contains('light');
    btnTheme.textContent = isLight ? '☀️ Terang' : '🌙 Gelap';
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
};

/* =====================
   GLOBAL TRANSLATE TOGGLE
===================== */
const btnTrans = document.getElementById('translateToggle');

btnTrans.onclick = () => {
    body.classList.toggle('show-meaning');
    const isShow = body.classList.contains('show-meaning');
    btnTrans.innerHTML = isShow ? '<span>🙈</span> Sembunyi Arti' : '<span>👁️</span> Tampilkan Arti';
    
    // Reset status kartu individual jika dinonaktifkan secara global
    if(!isShow) {
        items.forEach(i => i.classList.remove('active'));
    }
};

/* =====================
   INDIVIDUAL CLICK TOGGLE
===================== */
function toggleThisTranslate(element) {
    element.classList.toggle('active');
}
</script>

</body>
</html>