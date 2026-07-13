async function initDashboard() {

    try {

        // Cek API
        const pingResponse = await fetch('../api/dashboard/ping.php');
        const pingResult = await pingResponse.json();

        document.getElementById('status').innerHTML = pingResult.message;

        // Ambil session aktif
        const sessionResponse = await fetch('../api/dashboard/session.php');
        const sessionResult = await sessionResponse.json();

        document.getElementById('session-list').textContent =
            JSON.stringify(sessionResult.data, null, 4);

    } catch (err) {

        console.error(err);

        document.getElementById('status').innerHTML = 'API Error';

    }

}

initDashboard();