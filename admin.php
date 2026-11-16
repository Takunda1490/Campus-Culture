<?php
// Admin panel — protected by role check in app.js
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Campus Culture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Campus Culture</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">← Shop</a></li>
                </ul>
                <ul class="navbar-nav" id="authNav"></ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Admin Panel</h2>
                <span class="badge bg-secondary" id="adminRoleBadge">Admin</span>
            </div>

            <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#addProductTab" role="tab">Add Product</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#ordersTab" role="tab">Orders</button></li>
            </ul>

            <div class="tab-content" id="adminTabContent">
                <!-- ADD PRODUCT -->
                <div class="tab-pane fade show active" id="addProductTab" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form id="addProductForm" enctype="multipart/form-data" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category" required>
                                            <option value="">Select…</option>
                                            <option>Audio</option>
                                            <option>Accessories</option>
                                            <option>Peripherals</option>
                                            <option>Computing</option>
                                            <option>Mobile</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Price</label>
                                        <input type="number" step="0.01" min="0" class="form-control" name="price" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Stock</label>
                                        <input type="number" min="0" class="form-control" name="stock" value="10">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Image</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">Add Product</button>
                                        <span class="ms-3" id="addProductMsg"></span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ORDERS TABLE -->
                <div class="tab-pane fade" id="ordersTab" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header">All Orders</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="ordersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th class="text-end">Total</th>
                                            <th>Status</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ordersBody">
                                        <!-- Injected by app.js -->
                                    </tbody>
                                </table>
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