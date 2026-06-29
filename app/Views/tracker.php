<div class="header-dash">
    <div>
        <h1>📊 Tracker Kunjungan</h1>
        <p style="color: var(--muted); margin: 5px 0 0 0; font-size: 14px;">Mendeteksi semua kunjungan (terakhir 1000)</p>
    </div>
</div>

<div class="card-form" style="overflow-x: auto;">
    <?php if (empty($visits)): ?>
        <p style="color: var(--muted); text-align: center;">Belum ada data kunjungan.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text);">
                    <th style="padding: 10px;">No</th>
                    <th style="padding: 10px;">Waktu (Server)</th>
                    <th style="padding: 10px;">IP Address</th>
                    <th style="padding: 10px;">URI Tujuan</th>
                    <th style="padding: 10px;">User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($visits as $v): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 10px; color: var(--muted);"><?php echo $no++; ?></td>
                    <td style="padding: 10px; white-space: nowrap;"><?php echo htmlspecialchars($v['timestamp'] ?? '-'); ?></td>
                    <td style="padding: 10px;"><code><?php echo htmlspecialchars($v['ip'] ?? '-'); ?></code></td>
                    <td style="padding: 10px; color: var(--primary);"><?php echo htmlspecialchars($v['uri'] ?? '-'); ?></td>
                    <td style="padding: 10px; font-size: 12px; color: var(--muted);"><?php echo htmlspecialchars($v['user_agent'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
