<?php
require_once __DIR__ . '/../bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Detail Session</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="card shadow-sm">

            <div class="card-body">

                <h3>📋 Detail Session</h3>

                <hr>

                <div class="row mt-4">

                    <div class="col-md-4 mb-3">

                        <div class="card border-success shadow-sm">

                            <div class="card-body">

                                <h6 class="text-muted">💰 Total Pengeluaran</h6>

                                <h3 id="total-expense">

                                    Rp 0

                                </h3>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <div class="card border-primary shadow-sm">

                            <div class="card-body">

                                <h6 class="text-muted">👥 Jumlah Member</h6>

                                <h3 id="member-count">

                                    0

                                </h3>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <div class="card border-warning shadow-sm">

                            <div class="card-body">

                                <h6 class="text-muted">💵 Per Orang</h6>

                                <h3 id="per-person">

                                    Rp 0

                                </h3>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card shadow-sm mt-4">

                        <div class="card-header">

                            <strong>👥 Ringkasan Member</strong>

                        </div>

                        <div class="card-body p-0">

                            <table class="table table-striped table-hover mb-0">

                                <thead>

                                    <tr>
                                        <th>Nama</th>
                                        <th class="text-end">Pengeluaran</th>
                                        <th class="text-end">Selisih</th>
                                        <th class="text-center">Status</th>
                                    </tr>

                                </thead>

                                <tbody id="member-list">

                                    <tr>

                                        <td colspan="2" class="text-center">

                                            Loading...

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                <p>

                <strong>ID Session :</strong>

                <span id="session-id">

                    <?= $id ?>

                </span>

            </p>

            <p>

                <strong>Label :</strong>

                <span id="session-label">

                    Loading...

                </span>

            </p>

            <p>

                <strong>Status :</strong>

                <span id="session-status">

                    Loading...

                </span>

            </p>

            <p>

                <strong>Dibuat :</strong>

                <span id="session-created">

                    Loading...

                </span>

            </p>

                <a href="index.php" class="btn btn-secondary">
                    ← Kembali
                </a>

            </div>

        </div>

    </div>

    <script>

        const sessionId = <?= $id ?>;

        async function loadSession() {

            const response = await fetch(
                '../api/dashboard/session_detail.php?id=' + sessionId
            );

            const result = await response.json();

            if (!result.success) {

                alert(result.message);

                return;

            }

            const session = result.data;

            document.getElementById('session-label').innerHTML =
                session.label;

            document.getElementById('session-status').innerHTML =
                session.status;

            document.getElementById('session-created').innerHTML =
                session.created_at;

        }

        async function loadRecap() {

            const response = await fetch(
                '../api/dashboard/recap.php?id=' + sessionId
            );

            const result = await response.json();

            if (!result.success) {
                return;
            }

            const recap = result.data;

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

        }

        function formatRupiah(number) {

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);

        }

        loadSession();
        loadRecap();

    </script>
</body>

</html>