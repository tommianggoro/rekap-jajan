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

loadDashboardSummary();