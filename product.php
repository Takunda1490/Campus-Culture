<?php
// product.php?id=1
$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product — Campus Culture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Campus Culture</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">← Back to Shop</a></li>
                </ul>
                <ul class="navbar-nav" id="authNav"></ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="row g-4" id="productDetail">
                <!-- Injected by app.js -->
                <div class="col-12 text-center text-muted py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading product…</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white-50 py-4 mt-auto">
        <div class="container text-center small">&copy; 2025 Campus Culture</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="assets/js/app.js"></script>
</body>

</html>