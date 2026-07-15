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

function handleSearch() {

    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        console.log('Searching for:', searchInput.value);
        initDashboard(searchInput.value);

    }, 400);

}