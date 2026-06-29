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
            btnTheme.textContent = '☀️ Terang';
        }
        btnTheme.onclick = () => {
            body.classList.toggle('light');
            const isLight = body.classList.contains('light');
            btnTheme.textContent = isLight ? '☀️ Terang' : '🌙 Gelap';
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

    async function loadDashboardData() {
        const list = document.getElementById('dashboard-doa-list');
        const countSpan = document.getElementById('doa-count');
        if(!list) return;

        try {
            const res = await fetch('/api/doa');
            currentData = await res.json();
            countSpan.textContent = currentData.length;

            if(currentData.length === 0) {
                list.innerHTML = '<p style="color: var(--muted)">Belum ada data doa. Silakan tambah melalui form.</p>';
                return;
            }

            let html = '';
            currentData.forEach((item, index) => {
                html += `
                <div class="doa-item">
                    <div class="doa-content">
                        <div class="doa-id">#Doa Ke-${index + 1}</div>
                        <div class="doa-arab">${item.arab}</div>
                        <div class="doa-trans">${item.terjemah}</div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" onclick="window.editDoa('${item.id}')" style="padding: 6px 12px;">Edit</button>
                        <button class="btn btn-danger" onclick="window.deleteDoa('${item.id}')" style="padding: 6px 12px;">Hapus</button>
                    </div>
                </div>`;
            });
            list.innerHTML = html;
        } catch(e) {
            list.innerHTML = '<p style="color:red;">Gagal memuat data.</p>';
        }
    }

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
