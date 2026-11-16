<?php
// Login / Register page
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account — Campus Culture</title>
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
            <div class="row justify-content-center">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <ul class="nav nav-tabs card-header-tabs" id="authTabs" role="tablist">
                                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#loginTab" role="tab">Login</button></li>
                                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#registerTab" role="tab">Register</button></li>
                            </ul>
                        </div>
                        <div class="card-body tab-content" id="authTabContent">
                            <!-- LOGIN FORM -->
                            <div class="tab-pane fade show active" id="loginTab" role="tabpanel">
                                <form id="loginForm" novalidate>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required autocomplete="email">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" required autocomplete="current-password">
                                    </div>
                                    <div class="d-grid"><button type="submit" class="btn btn-primary">Sign In</button></div>
                                    <div class="text-center mt-3" id="loginMsg"></div>
                                </form>
                            </div>
                            <!-- REGISTER FORM -->
                            <div class="tab-pane fade" id="registerTab" role="tabpanel">
                                <form id="registerForm" novalidate>
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" required autocomplete="name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required autocomplete="email">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" required minlength="6" autocomplete="new-password">
                                    </div>
                                    <div class="d-grid"><button type="submit" class="btn btn-success">Create Account</button></div>
                                    <div class="text-center mt-3" id="registerMsg"></div>
                                </form>
                            </div>
                        </div>
                    </div>
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