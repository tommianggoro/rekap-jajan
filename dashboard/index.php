<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Dashboard Rekap Jajan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2 class="mb-0">
                    🍜 Dashboard Rekap Jajan
                </h2>

                <div class="d-flex align-items-center gap-3">

                    <span class="text-secondary">

                        👤 <?= htmlspecialchars($_SESSION['user']['full_name']) ?>

                    </span>

                    <button
                        id="logout-button"
                        class="btn btn-outline-danger">

                        Logout

                    </button>

                </div>

            </div>

            <hr>

            <p>

                Status API :

                <span id="status" class="badge text-bg-secondary">

                    Checking...

                </span>

            </p>

            <hr>

            <div class="row mb-4">

                <div class="col-md-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Total Session
                            </h6>

                            <h2 id="total-session">
                                ...
                            </h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Session Aktif
                            </h6>

                            <h2 class="text-primary"
                                id="active-session">

                                ...

                            </h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Session Selesai
                            </h6>

                            <h2 class="text-success"
                                id="closed-session">

                                ...

                            </h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card shadow-sm">

                        <div class="card-body text-center">

                            <h6 class="text-muted mb-2">
                                Total Pengeluaran
                            </h6>

                            <h4 class="text-danger"
                                id="total-expense">

                                ...

                            </h4>

                        </div>

                    </div>

                </div>

            </div>

            <hr>

            <h5 class="mt-4">Session Aktif</h5>

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">

                    <tr>

                        <th>ID</th>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th width="120">Aksi</th>

                    </tr>

                </thead>

                <tbody id="session-list">

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
window.APP = {
    apiBase: '../api/dashboard'
};
</script>

<script src="assets/js/app.js"></script>
<script src="assets/js/api.js"></script>
<script src="assets/js/dashboard.js"></script>
</body>

</html>