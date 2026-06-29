document.addEventListener('DOMContentLoaded', () => {
    /* =====================
       THEME & TRANSLATE TOGGLE
    ===================== */
    const body = document.body;
    const btnTheme = document.getElementById('themeToggle');
    const btnTrans = document.getElementById('translateToggle');
    const appContainer = document.getElementById('app-container');
    const navHome = document.getElementById('navHome');
    const navDashboard = document.getElementById('navDashboard');
    const btnLogout = document.getElementById('btnLogout');

    if(btnTheme) {
        const savedTheme = localStorage.getItem('theme');
        if(savedTheme === 'light'){
            body.classList.add('light');
            btnTheme.textContent = '🌙 Gelap';
        }
        btnTheme.onclick = () => {
            body.classList.toggle('light');
            const isLight = body.classList.contains('light');
            btnTheme.textContent = isLight ? '🌙 Gelap' : '☀️ Terang';
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
        };
    }

    if(btnTrans) {
        btnTrans.onclick = () => {
            body.classList.toggle('show-meaning');
            const isShow = body.classList.contains('show-meaning');
            btnTrans.innerHTML = isShow ? '<span>🙈</span> Sembunyi Arti' : '<span>👁️</span> Tampilkan Arti';
            
            if(!isShow) {
                document.querySelectorAll('.doa').forEach(i => i.classList.remove('active'));
            }
        };
    }
    /* =====================
       SETTINGS MODAL & FONTS
    ===================== */
    const btnSettings = document.getElementById('settingsToggle');
    const settingsModal = document.getElementById('settingsModal');
    const settingsClose = document.getElementById('settingsClose');
    const btnFontMinus = document.getElementById('btn-font-minus');
    const btnFontPlus = document.getElementById('btn-font-plus');
    const fontScaleDisplay = document.getElementById('font-scale-display');
    const selectFontArab = document.getElementById('select-font-arab');
    const selectFontLatin = document.getElementById('select-font-latin');
    const root = document.documentElement;

    let fontScale = parseFloat(localStorage.getItem('fontScale')) || 1;
    let fontArab = localStorage.getItem('fontArab') || "'Amiri', serif";
    let fontLatin = localStorage.getItem('fontLatin') || "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";

    function updateFontSettings() {
        root.style.setProperty('--font-scale', fontScale);
        root.style.setProperty('--font-arab', fontArab);
        root.style.setProperty('--font-latin', fontLatin);
        fontScaleDisplay.textContent = Math.round(fontScale * 100) + '%';
        selectFontArab.value = fontArab;
        selectFontLatin.value = fontLatin;
    }
    updateFontSettings();

    if(btnSettings && settingsModal) {
        btnSettings.onclick = () => settingsModal.classList.add('active');
        settingsClose.onclick = () => settingsModal.classList.remove('active');
        settingsModal.onclick = (e) => {
            if(e.target === settingsModal) settingsModal.classList.remove('active');
        }

        btnFontMinus.onclick = () => {
            if(fontScale > 0.6) {
                fontScale -= 0.1;
                localStorage.setItem('fontScale', fontScale);
                updateFontSettings();
            }
        };

        btnFontPlus.onclick = () => {
            if(fontScale < 2.0) {
                fontScale += 0.1;
                localStorage.setItem('fontScale', fontScale);
                updateFontSettings();
            }
        };

        selectFontArab.onchange = (e) => {
            fontArab = e.target.value;
            localStorage.setItem('fontArab', fontArab);
            updateFontSettings();
        };

        selectFontLatin.onchange = (e) => {
            fontLatin = e.target.value;
            localStorage.setItem('fontLatin', fontLatin);
            updateFontSettings();
        };
    }
    /* =====================
       SPA ROUTING (History API)
    ===================== */
    document.querySelectorAll('.spa-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const url = e.currentTarget.getAttribute('href');
            history.pushState(null, '', url);
            loadPage(url);
        });
    });

    window.addEventListener('popstate', () => {
        loadPage(location.pathname);
    });

    function updateNav(url) {
        if(url === '/' || url === '/tahajjud.quizb.my.id/') {
            if(navHome) navHome.style.display = 'none';
            if(navDashboard) navDashboard.style.display = 'flex';
        } else if (url.includes('/dashboard')) {
            if(navHome) navHome.style.display = 'flex';
            if(navDashboard) navDashboard.style.display = 'none';
        } else if (url.includes('/login')) {
            if(navHome) navHome.style.display = 'flex';
            if(navDashboard) navDashboard.style.display = 'none';
        }
    }

    async function loadPage(url) {
        updateNav(url);
        appContainer.innerHTML = '<div style="text-align:center; padding: 50px;">Memuat...</div>';
        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'fetch' }
            });
            if(res.redirected) {
                // Handle redirection (e.g., from dashboard to login)
                history.pushState(null, '', res.url);
                updateNav(res.url);
                const redirRes = await fetch(res.url, { headers: { 'X-Requested-With': 'fetch' } });
                appContainer.innerHTML = await redirRes.text();
            } else {
                appContainer.innerHTML = await res.text();
            }
            initPageLogic();
        } catch(e) {
            appContainer.innerHTML = '<div style="text-align:center; color:red;">Gagal memuat halaman.</div>';
        }
    }

    /* =====================
       PAGE LOGIC INITIALIZATION
    ===================== */
    function initPageLogic() {
        const path = location.pathname;
        if(path === '/' || path === '/tahajjud.quizb.my.id/') {
            loadDoaData();
            if(btnTrans) btnTrans.style.display = 'flex';
        } else if(path.includes('/dashboard')) {
            loadDashboardData();
            initDashboardForm();
            if(btnTrans) btnTrans.style.display = 'none';
        } else if(path.includes('/login')) {
            initLoginForm();
            if(btnTrans) btnTrans.style.display = 'none';
        }
    }

    /* =====================
       HOME PAGE
    ===================== */
    async function loadDoaData() {
        const list = document.getElementById('doa-list');
        if(!list) return;
        
        try {
            const res = await fetch('/api/doa');
            const data = await res.json();
            
            if(data.length === 0) {
                list.innerHTML = '<div style="text-align:center; color:var(--muted); margin-top:50px;"><p>Belum ada data doa.</p></div>';
                return;
            }

            let html = '';
            data.forEach(item => {
                html += `
                <div class="doa" onclick="this.classList.toggle('active')">
                    <div class="arab">${item.arab}</div>
                    ${item.terjemah ? `<div class="terjemah">${item.terjemah}</div>` : ''}
                </div>`;
            });
            list.innerHTML = html;
        } catch(e) {
            list.innerHTML = '<p style="color:red; text-align:center;">Gagal memuat doa.</p>';
        }
    }

    /* =====================
       DASHBOARD
    ===================== */
    let currentData = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    async function loadDashboardData() {
        const countSpan = document.getElementById('doa-count');
        if(!document.getElementById('dashboard-doa-list')) return;

        try {
            const res = await fetch('/api/doa');
            currentData = await res.json();
            countSpan.textContent = currentData.length;
            currentPage = 1;
            renderDashboardPage();
        } catch(e) {
            document.getElementById('dashboard-doa-list').innerHTML = '<p style="color:red;">Gagal memuat data.</p>';
        }
    }

    function renderDashboardPage() {
        const list = document.getElementById('dashboard-doa-list');
        const paginationControls = document.getElementById('pagination-controls');
        const btnPrev = document.getElementById('btn-prev-page');
        const btnNext = document.getElementById('btn-next-page');
        const pageInfo = document.getElementById('page-info');

        if(currentData.length === 0) {
            list.innerHTML = '<p style="color: var(--muted)">Belum ada data doa. Silakan tambah melalui form.</p>';
            paginationControls.style.display = 'none';
            return;
        }

        const totalPages = Math.ceil(currentData.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const pageData = currentData.slice(startIndex, startIndex + itemsPerPage);

        let html = '';
        pageData.forEach((item, index) => {
            const globalIndex = startIndex + index;
            html += `
            <div class="doa-item">
                <div class="doa-content">
                    <div class="doa-id">#Doa Ke-${globalIndex + 1}</div>
                    <div class="doa-arab">${item.arab}</div>
                    <div class="doa-trans">${item.terjemah}</div>
                </div>
                <div class="actions" style="flex-direction: column; align-items: flex-end;">
                    <div style="display:flex; gap:8px; margin-bottom:8px;">
                        <button class="btn-icon" onclick="window.moveDoa(${globalIndex}, -1)" ${globalIndex === 0 ? 'disabled' : ''} title="Geser ke atas">⬆️</button>
                        <button class="btn-icon" onclick="window.moveDoa(${globalIndex}, 1)" ${globalIndex === currentData.length - 1 ? 'disabled' : ''} title="Geser ke bawah">⬇️</button>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn btn-primary" onclick="window.editDoa('${item.id}')" style="padding: 6px 12px;">Edit</button>
                        <button class="btn btn-danger" onclick="window.deleteDoa('${item.id}')" style="padding: 6px 12px;">Hapus</button>
                    </div>
                </div>
            </div>`;
        });
        list.innerHTML = html;

        if (totalPages > 1) {
            paginationControls.style.display = 'flex';
            pageInfo.textContent = `Halaman ${currentPage} / ${totalPages}`;
            btnPrev.disabled = currentPage === 1;
            btnNext.disabled = currentPage === totalPages;
            
            btnPrev.onclick = () => { if(currentPage > 1) { currentPage--; renderDashboardPage(); window.scrollTo(0, 0); } };
            btnNext.onclick = () => { if(currentPage < totalPages) { currentPage++; renderDashboardPage(); window.scrollTo(0, 0); } };
        } else {
            paginationControls.style.display = 'none';
        }
    }

    window.moveDoa = async (index, direction) => {
        if (index + direction < 0 || index + direction >= currentData.length) return;
        
        // Swap locally
        const temp = currentData[index];
        currentData[index] = currentData[index + direction];
        currentData[index + direction] = temp;
        
        // Render optimistically
        renderDashboardPage();

        // Send to server
        const newOrderIds = currentData.map(item => item.id);
        try {
            await fetch('/api/doa/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ order: newOrderIds })
            });
        } catch(e) {
            alert('Gagal menyimpan urutan baru ke server.');
        }
    };


    function initDashboardForm() {
        const form = document.getElementById('doa-form');
        const btnCancel = document.getElementById('btn-cancel-edit');
        if(!form) return;

        btnCancel.onclick = () => {
            form.reset();
            document.getElementById('doa-id').value = '';
            document.getElementById('form-title').textContent = '✨ Tambah Doa Baru';
            btnCancel.style.display = 'none';
        };

        form.onsubmit = async (e) => {
            e.preventDefault();
            const msg = document.getElementById('form-message');
            msg.textContent = 'Menyimpan...';
            msg.style.color = 'var(--muted)';

            const payload = {
                id: document.getElementById('doa-id').value,
                arab: document.getElementById('doa-arab').value,
                terjemah: document.getElementById('doa-terjemah').value
            };

            try {
                const res = await fetch('/api/doa', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if(res.ok && data.success) {
                    msg.textContent = 'Berhasil disimpan!';
                    msg.style.color = 'var(--success)';
                    btnCancel.click(); // reset form
                    loadDashboardData();
                    setTimeout(() => msg.textContent = '', 3000);
                } else {
                    msg.textContent = data.error || 'Gagal menyimpan.';
                    msg.style.color = 'var(--danger)';
                }
            } catch(e) {
                msg.textContent = 'Terjadi kesalahan.';
                msg.style.color = 'var(--danger)';
            }
        };
    }

    window.editDoa = (id) => {
        const item = currentData.find(d => d.id === id);
        if(!item) return;
        document.getElementById('doa-id').value = item.id;
        document.getElementById('doa-arab').value = item.arab;
        document.getElementById('doa-terjemah').value = item.terjemah;
        document.getElementById('form-title').textContent = '📝 Edit Doa';
        document.getElementById('btn-cancel-edit').style.display = 'inline-block';
        window.scrollTo(0, 0);
    };

    window.deleteDoa = async (id) => {
        if(!confirm('Yakin ingin menghapus doa ini?')) return;
        
        try {
            const res = await fetch(`/api/doa/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            });
            const data = await res.json();
            if(res.ok && data.success) {
                loadDashboardData();
            } else {
                alert(data.error || 'Gagal menghapus');
            }
        } catch(e) {
            alert('Terjadi kesalahan.');
        }
    };

    /* =====================
       LOGIN / LOGOUT
    ===================== */
    function initLoginForm() {
        const form = document.getElementById('login-form');
        if(!form) return;

        form.onsubmit = async (e) => {
            e.preventDefault();
            const msg = document.getElementById('login-message');
            const formData = new FormData(form);

            try {
                const res = await fetch('/login', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'fetch' },
                    body: formData
                });
                const data = await res.json();
                if(res.ok && data.success) {
                    // Update global state and navigate
                    history.pushState(null, '', '/dashboard');
                    loadPage('/dashboard');
                    // Reload the window to show logout button properly if needed, but we can just let it be.
                    window.location.reload(); 
                } else {
                    msg.textContent = data.message || 'Login gagal';
                }
            } catch(e) {
                msg.textContent = 'Terjadi kesalahan jaringan.';
            }
        };
    }

    if(btnLogout) {
        btnLogout.onclick = async () => {
            await fetch('/logout', { method: 'POST', headers: { 'X-Requested-With': 'fetch' } });
            window.location.href = '/';
        };
    }

    // Initialize on first load
    initPageLogic();
    updateNav(location.pathname);
});
