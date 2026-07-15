<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';


$id = (int) ($_GET['id'] ?? 0);
?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Detail Session</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">

</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="card shadow-sm">

            <div class="card-body">

                <h3>📋 Detail Session</h3>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h2 id="session-label" class="mb-1">
                            Loading...
                        </h2>

                        <div>

                            <span id="session-status" class="badge bg-secondary">
                                Loading...
                            </span>

                            <span class="text-muted ms-2" id="session-created">
                                Loading...
                            </span>

                        </div>

                    </div>

                    <div>

                        <a href="index.php" class="btn btn-outline-secondary">
                            ← Kembali
                        </a>

                    </div>

                </div>

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

                <div class="card shadow-sm mt-4">

                    <div class="card-header">

                        <strong>💸 Settlement</strong>

                    </div>

                    <div class="card-body p-0">

                        <table class="table table-striped table-hover mb-0">

                            <thead>

                                <tr>

                                    <th>Dari</th>
                                    <th>Kepada</th>
                                    <th class="text-end">Nominal</th>

                                </tr>

                            </thead>

                            <tbody id="settlement-list">

                                <tr>

                                    <td colspan="3" class="text-center">

                                        Loading...

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="card shadow-sm mt-4">

                    <div class="card-header">

                        <strong>📝 Riwayat Transaksi</strong>

                    </div>

                    <div class="card-body p-0">

                        <table class="table table-striped table-hover mb-0">

                            <thead>

                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Dibayar Oleh</th>
                                    <th class="text-end">Nominal</th>
                                </tr>

                            </thead>

                            <tbody id="history-list">

                                <tr>

                                    <td colspan="4" class="text-center">

                                        Loading...

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>
        window.APP = {
            sessionId: <?= $id ?>,
            apiBase: '../api/dashboard'
        };
    </script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/api.js"></script>
    <script src="assets/js/detail.js"></script>
</body>

</html>