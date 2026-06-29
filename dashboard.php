<?php
$file_json = 'data_doa.json';

// Ambil data yang sudah ada
$data_doa = [];
if (file_exists($file_json)) {
    $json_string = file_get_contents($file_json);
    $data_doa = json_decode($json_string, true) ?? [];
}

// PROSES SIMPAN (TAMBAH / EDIT)
if (isset($_POST['save_doa'])) {
    $id = $_POST['doa_id'];
    $arab = $_POST['arab'];
    $terjemah = $_POST['terjemah'];

    if ($id !== '') {
        // Mode Edit
        $data_doa[$id] = ['arab' => $arab, 'terjemah' => $terjemah];
    } else {
        // Mode Tambah Baru
        $data_doa[] = ['arab' => $arab, 'terjemah' => $terjemah];
    }

    file_put_contents($file_json, json_encode($data_doa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: dashboard.php");
    exit;
}

// PROSES HAPUS
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (isset($data_doa[$id])) {
        unset($data_doa[$id]);
        // Reset susunan index array agar rapi kembali dari 0
        $data_doa = array_values($data_doa);
        file_put_contents($file_json, json_encode($data_doa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    header("Location: dashboard.php");
    exit;
}

// Ambil data spesifik untuk form edit jika tombol edit diklik
$edit_id = '';
$edit_arab = '';
$edit_terjemah = '';
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    if (isset($data_doa[$edit_id])) {
        $edit_arab = $data_doa[$edit_id]['arab'];
        $edit_terjemah = $data_doa[$edit_id]['terjemah'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin - Doa & Dzikir</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root{
    --bg:#020617;
    --card:#0f172a;
    --text:#f8fafc;
    --muted:#94a3b8;
    --accent:#38bdf8;
    --danger:#ef4444;
    --success:#22c55e;
}
body {
    margin: 0; padding: 30px 15px;
    background: var(--bg); color: var(--text);
    font-family: system-ui, -apple-system, sans-serif;
}
.container { max-width: 900px; margin: auto; }
.header-dash { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.header-dash h1 { margin: 0; font-size: 24px; }
.btn {
    padding: 8px 16px; border-radius: 8px; border: none; font-size: 13px; 
    cursor: pointer; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center;
}
.btn-primary { background: var(--accent); color: #000; }
.btn-danger { background: var(--danger); color: #fff; }
.btn-success { background: var(--success); color: #fff; }
.btn-secondary { background: #334155; color: #fff; margin-left: 8px; }

/* FORM STYLE */
.card-form { background: var(--card); padding: 20px; border-radius: 15px; margin-bottom: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 6px; font-size: 14px; color: var(--muted); }
textarea { 
    width: 100%; border: 1px solid #334155; background: #1e293b; color: #fff; 
    border-radius: 8px; padding: 12px; font-size: 15px; box-sizing: border-box; resize: vertical;
}
textarea.arab-input { direction: rtl; text-align: right; font-family: system-ui, serif; font-size: 20px; }

/* LIST STYLE */
.doa-item { 
    background: var(--card); padding: 20px; border-radius: 12px; margin-bottom: 15px; 
    display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;
}
.doa-content { flex: 1; }
.doa-arab { direction: rtl; text-align: right; font-size: 20px; margin-bottom: 10px; line-height: 1.6; }
.doa-id { color: var(--accent); font-weight: bold; font-size: 14px; margin-bottom: 5px; }
.doa-trans { color: var(--muted); font-size: 14px; line-height: 1.5; }
.actions { display: flex; gap: 8px; }
</style>
</head>
<body>

<div class="container">
    <div class="header-dash">
        <div>
            <h1>Dashboard Doa & Dzikir</h1>
            <p style="color: var(--muted); margin: 5px 0 0 0; font-size: 14px;">Kelola teks ayat/doa secara real-time</p>
        </div>
        <a href="index.php" target="_blank" class="btn btn-secondary">👁️ Lihat Web Utama</a>
    </div>

    <!-- FORM INPUT / EDIT -->
    <div class="card-form">
        <h3 style="margin-top:0;"><?php echo $edit_id !== '' ? '📝 Edit Doa' : '✨ Tambah Doa Baru'; ?></h3>
        <form action="dashboard.php" method="POST">
            <input type="hidden" name="doa_id" value="<?php echo $edit_id; ?>">
            
            <div class="form-group">
                <label>Teks Arab</label>
                <textarea name="arab" rows="3" class="arab-input" required placeholder="Gunakan harakat..."><?php echo htmlspecialchars($edit_arab); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Arti / Terjemahan</label>
                <textarea name="terjemah" rows="3" required placeholder="Masukkan arti dalam bahasa indonesia..."><?php echo htmlspecialchars($edit_terjemah); ?></textarea>
            </div>
            
            <button type="submit" name="save_doa" class="btn btn-success">💾 Simpan Data</button>
            <?php if ($edit_id !== ''): ?>
                <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- LIST DATA DOA -->
    <h2>Daftar Doa Saat Ini (<?php echo count($data_doa); ?>)</h2>
    <?php if (empty($data_doa)): ?>
        <p style="color: var(--muted)">Belum ada data doa. Silakan tambah melalui form di atas.</p>
    <?php else: ?>
        <?php foreach ($data_doa as $index => $item): ?>
            <div class="doa-item">
                <div class="doa-content">
                    <div class="doa-id">#Doa Ke-<?php echo $index + 1; ?></div>
                    <div class="doa-arab"><?php echo $item['arab']; ?></div>
                    <div class="doa-trans"><?php echo $item['terjemah']; ?></div>
                </div>
                <div class="actions">
                    <a href="dashboard.php?edit=<?php echo $index; ?>" class="btn btn-primary" style="padding: 6px 12px;">Edit</a>
                    <a href="dashboard.php?delete=<?php echo $index; ?>" class="btn btn-danger" style="padding: 6px 12px;" onclick="return confirm('Yakin ingin menghapus doa ini?')">Hapus</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>