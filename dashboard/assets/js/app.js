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

function formatRupiah(value) {

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);

}

function formatTanggal(datetime) {

    const date = new Date(datetime);

    return date.toLocaleString('id-ID', {

        day: '2-digit',

        month: 'long',

        year: 'numeric',

        hour: '2-digit',

        minute: '2-digit'

    });

}

initDashboard();