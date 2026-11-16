<?php
// Cart page — requires login (enforced by app.js redirect)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart — Campus Culture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Campus Culture</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Continue Shopping</a></li>
                </ul>
                <ul class="navbar-nav" id="authNav"></ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <h2 class="mb-4">Shopping Cart</h2>

            <!-- Cart table -->
            <div class="card shadow-sm" id="cartSection">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="cartTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <!-- Rows injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div class="text-center py-5 d-none" id="emptyCart">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <h4 class="mt-3">Your cart is empty</h4>
                <a href="index.php" class="btn btn-primary mt-2">Browse Products</a>
            </div>

            <!-- Checkout summary -->
            <div class="row mt-4" id="checkoutSection" style="display:none;">
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">Checkout</div>
                        <div class="card-body">
                            <form id="checkoutForm">
                                <div class="mb-3">
                                    <label class="form-label">Shipping Address</label>
                                    <textarea class="form-control" name="shipping_address" rows="3" required placeholder="Full address, city, postal code"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100 btn-lg" id="placeOrderBtn">Place Order</button>
                            </form>
                            <div class="text-center mt-3" id="checkoutMsg"></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header">Order Summary</div>
                        <div class="card-body" id="orderSummary">
                            <!-- Injected by app.js -->
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