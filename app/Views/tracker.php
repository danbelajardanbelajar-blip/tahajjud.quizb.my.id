<div class="header-dash">
    <div>
        <h1>📊 Tracker Kunjungan</h1>
        <p style="color: var(--muted); margin: 5px 0 0 0; font-size: 14px;">Mendeteksi semua kunjungan (terakhir 1000)</p>
    </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card-form" style="padding: 20px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: rgba(255,255,255,0.8); font-size: 13px; margin: 0 0 5px 0;">Total Kunjungan</p>
                <h3 style="color: white; margin: 0; font-size: 32px; font-weight: 700;"><?php echo number_format($totalVisits ?? 0); ?></h3>
            </div>
            <div style="font-size: 40px; opacity: 0.3;">👥</div>
        </div>
    </div>
    
    <div class="card-form" style="padding: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: rgba(255,255,255,0.8); font-size: 13px; margin: 0 0 5px 0;">IP Unik</p>
                <h3 style="color: white; margin: 0; font-size: 32px; font-weight: 700;"><?php echo number_format($totalUniqueIPs ?? 0); ?></h3>
            </div>
            <div style="font-size: 40px; opacity: 0.3;">🌐</div>
        </div>
    </div>
    
    <div class="card-form" style="padding: 20px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <p style="color: rgba(255,255,255,0.8); font-size: 13px; margin: 0 0 5px 0;">Kunjungan Hari Ini</p>
                <h3 style="color: white; margin: 0; font-size: 32px; font-weight: 700;"><?php echo number_format($todayCount ?? 0); ?></h3>
            </div>
            <div style="font-size: 40px; opacity: 0.3;">📅</div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card-form" style="padding: 20px;">
        <h3 style="margin: 0 0 15px 0; color: var(--text); font-size: 16px;">📈 Kunjungan 7 Hari Terakhir</h3>
        <canvas id="visitsChart" style="max-height: 250px;"></canvas>
    </div>
    
    <div class="card-form" style="padding: 20px;">
        <h3 style="margin: 0 0 15px 0; color: var(--text); font-size: 16px;">🔝 Top 10 URI Terbanyak</h3>
        <canvas id="uriChart" style="max-height: 250px;"></canvas>
    </div>
</div>

<!-- Tracker Table -->
<div class="card-form" style="overflow-x: auto;">
    <h3 style="margin: 0 0 15px 0; color: var(--text); font-size: 16px;">📋 Detail Kunjungan</h3>
    <?php if (empty($visits)): ?>
        <p style="color: var(--muted); text-align: center;">Belum ada data kunjungan.</p>
    <?php else: ?>
        <table id="tracker-table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text);">
                    <th style="padding: 12px;">No</th>
                    <th style="padding: 12px;">Waktu (Server)</th>
                    <th style="padding: 12px;">IP Address</th>
                    <th style="padding: 12px;">URI Tujuan</th>
                    <th style="padding: 12px;">User Agent</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($visits as $v): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; color: var(--muted);"><?php echo $no++; ?></td>
                    <td style="padding: 12px; white-space: nowrap;"><?php echo htmlspecialchars($v['timestamp'] ?? '-'); ?></td>
                    <td style="padding: 12px;"><code style="background: var(--bg-secondary); padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?php echo htmlspecialchars($v['ip'] ?? '-'); ?></code></td>
                    <td style="padding: 12px; color: var(--primary); font-weight: 500;"><?php echo htmlspecialchars($v['uri'] ?? '-'); ?></td>
                    <td style="padding: 12px; font-size: 12px; color: var(--muted); max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($v['user_agent'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Visits by date chart
const visitsCtx = document.getElementById('visitsChart').getContext('2d');
const visitsData = <?php echo json_encode(array_values($visitsByDate ?? [])); ?>;
const visitsLabels = <?php echo json_encode(array_keys($visitsByDate ?? [])); ?>;

new Chart(visitsCtx, {
    type: 'line',
    data: {
        labels: visitsLabels.map(d => {
            const date = new Date(d);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        }),
        datasets: [{
            label: 'Kunjungan',
            data: visitsData,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Top URIs chart
const uriCtx = document.getElementById('uriChart').getContext('2d');
const uriData = <?php echo json_encode(array_values($topURIs ?? [])); ?>;
const uriLabels = <?php echo json_encode(array_keys($topURIs ?? [])); ?>;

new Chart(uriCtx, {
    type: 'bar',
    data: {
        labels: uriLabels.map(u => u.length > 20 ? u.substring(0, 20) + '...' : u),
        datasets: [{
            label: 'Kunjungan',
            data: uriData,
            backgroundColor: [
                '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1'
            ],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
