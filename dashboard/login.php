<?php
require_once __DIR__ . '/../bootstrap.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/app.css">

</head>

<body class="bg-light">

<div class="container">

    <div class="row justify-content-center vh-100 align-items-center">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-body">

                    <h3 class="text-center mb-4">
                        Dashboard Rekap Jajan
                    </h3>

                    <form id="login-form">

                        <div class="mb-3">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                id="username"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                id="password"
                                class="form-control"
                                required>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            Login

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
window.APP = {
    apiBase: '../api/auth'
};
</script>

<script src="assets/js/api.js"></script>
<script src="assets/js/login.js"></script>

</body>

</html>