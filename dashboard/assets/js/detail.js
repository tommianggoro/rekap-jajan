const sessionId = window.APP.sessionId;

async function loadSession() {

    const session = await apiGet(
        `${window.APP.apiBase}/session_detail.php?id=${sessionId}`
    );

    document.getElementById('session-label').innerHTML =
        session.label;

    document.getElementById('session-status').innerHTML =
        session.status;

    document.getElementById('session-created').innerHTML =
        formatTanggal(session.created_at);

}

async function loadRecap() {

    const recap = await apiGet(
        `${window.APP.apiBase}/recap.php?id=${sessionId}`
    );

    document.getElementById('total-expense').textContent =
        formatRupiah(recap.total_expense);

    document.getElementById('member-count').textContent =
        recap.member_count;

    document.getElementById('per-person').textContent =
        formatRupiah(recap.per_person);

    const tbody = document.getElementById('member-list');

    tbody.innerHTML = '';

    recap.members.forEach(member => {

        const badge = member.status === 'creditor'
            ? '<span class="badge bg-success">Menagih</span>'
            : '<span class="badge bg-danger">Membayar</span>';

        const balance = Number(member.balance);

        const balanceText =
            (balance >= 0 ? '+' : '-') +
            formatRupiah(Math.abs(balance));

        tbody.innerHTML += `

            <tr>

                <td>${member.first_name}</td>

                <td class="text-end">

                    ${formatRupiah(member.total_spent)}

                </td>

                <td class="text-end">

                    ${balanceText}

                </td>

                <td class="text-center">

                    ${badge}

                </td>

            </tr>

        `;

    });

    const settlementBody = document.getElementById('settlement-list');

    settlementBody.innerHTML = '';

    if (recap.settlements.length === 0) {

        settlementBody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center">
                    Tidak ada settlement.
                </td>
            </tr>
        `;

    } else {

        recap.settlements.forEach(item => {

            settlementBody.innerHTML += `

                <tr>

                    <td>${item.from}</td>

                    <td>${item.to}</td>

                    <td class="text-end">

                        ${formatRupiah(item.amount)}

                    </td>

                </tr>

            `;

        });

    }

}

async function loadHistory() {

    const history = await apiGet(
        `${window.APP.apiBase}/history.php?id=${sessionId}`
    );

    const tbody = document.getElementById('history-list');

    tbody.innerHTML = '';

    if (history.length === 0) {

        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center">
                    Belum ada transaksi.
                </td>
            </tr>
        `;

        return;
    }

    history.forEach(item => {

        tbody.innerHTML += `

            <tr>

                <td>${formatTanggal(item.created_at)}</td>

                <td>${item.description}</td>

                <td>${item.paid_by}</td>

                <td class="text-end">
                    ${formatRupiah(item.amount)}
                </td>

            </tr>

        `;

    });

}

loadSession();
loadRecap();
loadHistory();