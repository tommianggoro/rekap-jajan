async function initDashboard() {

    try {

        // Cek API
        const pingResponse = await fetch('../api/dashboard/ping.php');
        const pingResult = await pingResponse.json();

        document.getElementById('status').innerHTML = pingResult.message;

        // Ambil session aktif
        const sessionResponse = await fetch('../api/dashboard/session.php');
        const sessionResult = await sessionResponse.json();

        const tbody = document.getElementById('session-list');

        tbody.innerHTML = '';

        if (sessionResult.data.length === 0) {

            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center">
                        Tidak ada session aktif.
                    </td>
                </tr>
            `;

        } else {

            sessionResult.data.forEach(session => {

                tbody.innerHTML += `
                    <tr>

                        <td>${session.id}</td>

                        <td>${session.label}</td>

                        <td>
                            <span class="badge bg-success">
                                ${session.status}
                            </span>
                        </td>

                        <td>${session.created_at}</td>

                        <td>

                            <button
                                class="btn btn-sm btn-primary btn-detail"
                                data-id="${session.id}"
                                data-label="${session.label}">

                                Detail

                            </button>

                        </td>

                    </tr>
                `;

            });

        }

    } catch (err) {

        console.error(err);

        document.getElementById('status').innerHTML = 'API Error';

    }

    document.querySelectorAll('.btn-detail').forEach(button => {

        button.addEventListener('click', function () {

            window.location.href =
                'detail.php?id=' +
                encodeURIComponent(this.dataset.id);
        });

    });
}

initDashboard();