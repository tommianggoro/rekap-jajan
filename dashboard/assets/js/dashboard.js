let searchTimeout = null;
const logoutButton = document.getElementById('logout-button');
const searchInput = document.getElementById('search-session');

async function loadDashboardSummary() {

    const summary = await apiGet(
        `${window.APP.apiBase}/summary.php`
    );

    document.getElementById('total-session').textContent =
        summary.total_session;

    document.getElementById('active-session').textContent =
        summary.active_session;

    document.getElementById('closed-session').textContent =
        summary.closed_session;

    document.getElementById('total-expense').textContent =
        formatRupiah(summary.total_expense);
}


if (logoutButton) {
    logoutButton.addEventListener('click', logout);
}
if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
}

async function initDashboard(keyword = '') {
    const tbody = document.getElementById('session-list');
    tbody.innerHTML = `<tr><td colspan="4" class="text-center">Loading...</td></tr>`;

    try {
        // Memanggil API history.php (yang nanti isinya kita ubah untuk return group by label)
        const groups = await apiGet(`${window.APP.apiBase}/history.php?keyword=${encodeURIComponent(keyword)}`);

        tbody.innerHTML = '';

        if (groups.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center">Tidak ada grup jajan ditemukan.</td></tr>`;
            return;
        }

        groups.forEach(group => {
            const activeBadge = group.active_count > 0 
                ? `<span class="badge bg-primary">${group.active_count} Sesi</span>` 
                : `<span class="badge bg-secondary">0</span>`;
                
            const closedBadge = group.closed_count > 0 
                ? `<span class="badge bg-success">${group.closed_count} Sesi</span>` 
                : `<span class="badge bg-secondary">0</span>`;

            tbody.innerHTML += `
                <tr>
                    <td><strong>${group.label}</strong></td>
                    <td class="text-center">${activeBadge}</td>
                    <td class="text-center">${closedBadge}</td>
                    <td class="text-center">
                        <a href="detail.php?label=${encodeURIComponent(group.label)}" class="btn btn-sm btn-outline-primary">
                            👁️ Lihat Detail
                        </a>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Gagal memuat data: ${error.message}</td></tr>`;
    }
}

async function logout() {

    if (!confirm('Yakin ingin logout?')) {
        return;
    }

    try {

        await apiPost(
            '../api/auth/logout.php',
            new FormData()
        );

        window.location.href = 'login.php';

    } catch (error) {

        alert(error.message);

    }

}

loadDashboardSummary();
initDashboard();

function handleSearch() {

    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        console.log('Searching for:', searchInput.value);
        initDashboard(searchInput.value);

    }, 400);

}