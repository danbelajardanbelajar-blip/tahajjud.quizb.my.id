<div class="header-dash">
    <div>
        <h1>Dashboard Doa & Dzikir</h1>
        <p style="color: var(--muted); margin: 5px 0 0 0; font-size: 14px;">Kelola teks ayat/doa secara real-time</p>
    </div>
</div>

<div class="card-form" id="form-container">
    <h3 style="margin-top:0;" id="form-title">✨ Tambah Doa Baru</h3>
    <form id="doa-form">
        <input type="hidden" name="id" id="doa-id" value="">
        
        <div class="form-group">
            <label>Teks Arab</label>
            <textarea name="arab" id="doa-arab" rows="3" class="arab-input" required placeholder="Gunakan harakat..."></textarea>
        </div>
        
        <div class="form-group">
            <label>Arti / Terjemahan</label>
            <textarea name="terjemah" id="doa-terjemah" rows="3" required placeholder="Masukkan arti dalam bahasa indonesia..."></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">💾 Simpan Data</button>
        <button type="button" class="btn btn-secondary" id="btn-cancel-edit" style="display:none;">Batal</button>
        <span id="form-message" style="margin-left: 10px; font-size: 14px;"></span>
    </form>
</div>

<h2>Daftar Doa Saat Ini (<span id="doa-count">0</span>)</h2>
<div id="dashboard-doa-list">
    <p style="color: var(--muted)">Memuat data...</p>
</div>
