// Mengambil parameter 'label' dari URL browser
const urlParams = new URLSearchParams(window.location.search);
const sessionLabel = urlParams.get('label') || '';

if (!sessionLabel) {
    alert('Parameter label tidak ditemukan!');
    window.location.href = 'index.php';
}

async function loadSession() {
    // Memanggil API detail dengan parameter label
    const session = await apiGet(
        `${window.APP.apiBase}/session_detail.php?label=${encodeURIComponent(sessionLabel)}`
    );

    let sessions = session.data;

    const statusEl = document.getElementById('session-status');
    // Pastikan menggunakan kecocokan huruf (case-sensitive) yang sesuai dengan data dari API
    if (sessions.status === 'Active' || sessions.status === 'open') {
        statusEl.className = 'badge bg-success';
    } else {
        statusEl.className = 'badge bg-secondary'; // Warna abu-abu jika sedang tidak aktif/closed
    }

    statusEl.innerHTML = sessions.status;
    
    // Karena ini akumulasi beberapa sesi, info tanggal dibuat bisa dikosongkan 
    // atau diisi dengan rentang waktu jika backend menyediakannya.
    const createdEl = document.getElementById('session-created');
    if (createdEl) {
        createdEl.innerHTML = sessions.created_at ? 'Dibuat: ' + formatTanggal(sessions.created_at) : '-';
        createdEl.innerHTML += sessions.last_created_at ? ' - Terakhir Diperbarui: ' + formatTanggal(sessions.last_created_at) : '';
    }
}

async function loadRecap() {
    // Memanggil API rekap berdasarkan label
    const recap = await apiGet(
        `${window.APP.apiBase}/recap.php?label=${encodeURIComponent(sessionLabel)}`
    );

    document.getElementById('total-expense').textContent = formatRupiah(recap.total_expense);
    document.getElementById('member-count').textContent = recap.member_count;
    document.getElementById('per-person').textContent = formatRupiah(recap.per_person);

    const tbody = document.getElementById('member-list');
    tbody.innerHTML = '';

    recap.members.forEach(member => {
        const badge = member.status === 'creditor'
            ? '<span class="badge bg-success">Menagih</span>'
            : '<span class="badge bg-danger">Membayar</span>';

        const balance = Number(member.balance);
        const balanceText = (balance >= 0 ? '+' : '-') + formatRupiah(Math.abs(balance));

        tbody.innerHTML += `
            <tr>
                <td>${member.first_name}</td>
                <td class="text-end">${formatRupiah(member.total_spent)}</td>
                <td class="text-end">${balanceText}</td>
                <td class="text-center">${badge}</td>
            </tr>
        `;
    });

    const settlementBody = document.getElementById('settlement-list');
    settlementBody.innerHTML = '';

    if (!recap.settlements || recap.settlements.length === 0) {
        settlementBody.innerHTML = `
            <tr><td colspan="3" class="text-center">Tidak ada settlement.</td></tr>
        `;
    } else {
        recap.settlements.forEach(item => {
            settlementBody.innerHTML += `
                <tr>
                    <td>${item.from}</td>
                    <td>${item.to}</td>
                    <td class="text-end">${formatRupiah(item.amount)}</td>
                </tr>
            `;
        });
    }
}

async function loadHistory() {
    // Memanggil API history transaksi berdasarkan label
    const history = await apiGet(
        `${window.APP.apiBase}/history.php?label=${encodeURIComponent(sessionLabel)}`
    );

    const tbody = document.getElementById('history-list');
    tbody.innerHTML = '';

    if (history.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="4" class="text-center">Belum ada transaksi.</td></tr>
        `;
        return;
    }

    history.forEach(item => {
        tbody.innerHTML += `
            <tr>
                <td>${formatTanggal(item.created_at)}</td>
                <td>${item.description}</td>
                <td>${item.paid_by}</td>
                <td class="text-end">${formatRupiah(item.amount)}</td>
            </tr>
        `;
    });
}

// Eksekusi fungsi pembacaan data
loadSession();
loadRecap();
loadHistory();