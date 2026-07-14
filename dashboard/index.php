<?php
require_once __DIR__ . '/../bootstrap.php';
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

            <h2 class="mb-3">
                🍜 Dashboard Rekap Jajan
            </h2>

            <hr>

            <p>

                Status API :

                <span id="status" class="badge text-bg-secondary">

                    Checking...

                </span>

            </p>

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

<script src="assets/js/app.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>