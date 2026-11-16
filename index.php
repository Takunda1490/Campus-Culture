<?php
// No session_start() here; APIs handle their own session
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Culture — Tech Gadgets for Students</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Campus Culture</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart <span id="cartBadge" class="badge bg-light text-primary ms-1">0</span></a></li>
                </ul>
                <ul class="navbar-nav" id="authNav">
                    <!-- Filled by app.js after auth check -->
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <header class="bg-light py-5 border-bottom">
        <div class="container text-center">
            <h1 class="display-4 fw-bold text-primary">Tech Essentials for Student Life</h1>
            <p class="lead text-muted">Curated gadgets to power your studies, play, and everything in between.</p>
        </div>
    </header>

    <!-- ===== SEARCH & FILTER ===== -->
    <section class="py-4 bg-white border-bottom">
        <div class="container">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-6">
                    <input type="search" id="searchInput" class="form-control" placeholder="Search products…" aria-label="Search">
                </div>
                <div class="col-12 col-md-6">
                    <div class="d-flex flex-wrap gap-2" id="categoryPills">
                        <!-- Category pills injected by app.js -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== PRODUCT GRID ===== -->
    <main class="py-5">
        <div class="container">
            <div class="row g-4" id="productGrid">
                <!-- Product cards injected by app.js -->
                <div class="col-12 text-center text-muted py-5" id="loadingState">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading…</span></div>
                    <p class="mt-2">Loading products…</p>
                </div>
            </div>
        </div>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="bg-dark text-white-50 py-4 mt-auto">
        <div class="container text-center small">
            &copy; 2025 Campus Culture — University of Zimbabwe Student Project
        </div>
    </footer>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- App logic -->
    <script src="assets/js/app.js"></script>
</body>

</html>