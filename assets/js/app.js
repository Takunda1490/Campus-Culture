/* 
   Campus Culture — Frontend Application Logic
   Single file, no build step, vanilla JavaScript
   Loaded on every page via <script src="assets/js/app.js"></script>
 */

// ===== CONFIG =====
const API_BASE = "api";
const endpoints = {
  products: `${API_BASE}/products.php`,
  auth: `${API_BASE}/auth.php`,
  cart: `${API_BASE}/cart_action.php`,
  checkout: `${API_BASE}/checkout.php`,
  admin: `${API_BASE}/admin_action.php`,
};

// ===== UTILITIES =====
const $ = (sel, ctx = document) => ctx.querySelector(sel);
const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

// Show toast/message in any element
function showMsg(el, msg, isError = false) {
  if (!el) return;
  el.textContent = msg;
  el.className = `small ${isError ? "text-danger" : "text-success"}`;
  setTimeout(() => {
    el.textContent = "";
  }, 4000);
}

// Format price
const fmt = (n) =>
  new Intl.NumberFormat("en-ZW", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
  }).format(n);

// Debounce helper
function debounce(fn, ms = 300) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), ms);
  };
}

// ===== AUTH STATE (shared across pages) =====
let currentUser = null;

async function fetchMe() {
  try {
    const res = await fetch(`${endpoints.auth}?action=me`, {
      credentials: "same-origin",
    });
    const data = await res.json();
    if (data.status === "success" && data.user) {
      currentUser = data.user;
    } else {
      currentUser = null;
    }
  } catch {
    currentUser = null;
  }
  updateAuthNav();
  updateCartBadge();
  return currentUser;
}

function updateAuthNav() {
  const nav = $("#authNav");
  if (!nav) return;

  if (currentUser) {
    const isAdmin = currentUser.role === "admin";
    nav.innerHTML = `
      <li class="nav-item"><span class="nav-link text-white-50">Hi, ${escapeHtml(currentUser.name)}</span></li>
      ${isAdmin ? '<li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>' : ""}
      <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart <span id="cartBadgeNav" class="badge bg-light text-primary ms-1">0</span></a></li>
      <li class="nav-item"><button class="nav-link btn btn-link p-0" id="btnLogout">Logout</button></li>
    `;
    $("#btnLogout")?.addEventListener("click", doLogout);
  } else {
    nav.innerHTML = `
      <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
      <li class="nav-item"><a class="nav-link" href="login.php">Register</a></li>
      <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart"></i> Cart <span id="cartBadgeNav" class="badge bg-light text-primary ms-1">0</span></a></li>
    `;
  }
}

async function doLogout() {
  try {
    await fetch(`${endpoints.auth}?action=logout`, {
      method: "GET",
      credentials: "same-origin",
    });
    currentUser = null;
    updateAuthNav();
    updateCartBadge();
    // Redirect if on protected page
    if (["cart.php", "admin.php"].some((p) => location.pathname.endsWith(p))) {
      location.href = "login.php";
    }
  } catch (e) {
    console.error(e);
  }
}

// ===== CART BADGE =====
async function updateCartBadge() {
  try {
    const res = await fetch(endpoints.cart, { credentials: "same-origin" });
    const data = await res.json();
    const count = data.status === "success" ? data.count || 0 : 0;
    $$("#cartBadge, #cartBadgeNav").forEach((el) => {
      el.textContent = count;
      el.style.display = count > 0 ? "inline-block" : "none";
    });
  } catch {
    /* ignore */
  }
}

// ===== HTML ESCAPE =====
function escapeHtml(str) {
  return String(str).replace(
    /[&<>"']/g,
    (s) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[
        s
      ],
  );
}

// ===== PAGE DETECTION & INIT =====
const page = (() => {
  const path = location.pathname.split("/").pop();
  if (path === "index.php" || path === "" || path === "/") return "index";
  if (path === "product.php") return "product";
  if (path === "cart.php") return "cart";
  if (path === "login.php") return "login";
  if (path === "admin.php") return "admin";
  return "unknown";
})();

// Main init
document.addEventListener("DOMContentLoaded", async () => {
  await fetchMe(); // Always check auth first

  switch (page) {
    case "index":
      initIndex();
      break;
    case "product":
      initProduct();
      break;
    case "cart":
      initCart();
      break;
    case "login":
      initLogin();
      break;
    case "admin":
      initAdmin();
      break;
  }
});

// ============================================================
// INDEX PAGE — Product Catalog with Search & Filter
// ============================================================
function initIndex() {
  const grid = $("#productGrid");
  const loading = $("#loadingState");
  const searchInput = $("#searchInput");
  const categoryPills = $("#categoryPills");

  let currentCategory = "All";
  let searchTerm = "";

  // Render category pills
  async function loadCategories() {
    try {
      const res = await fetch(`${endpoints.products}`, {
        credentials: "same-origin",
      });
      const data = await res.json();
      if (data.status !== "success") return;

      const cats = ["All", ...new Set(data.data.map((p) => p.category))];
      categoryPills.innerHTML = cats
        .map(
          (c) => `
        <button class="category-pill ${c === "All" ? "active" : ""}" data-cat="${escapeHtml(c)}">${escapeHtml(c)}</button>
      `,
        )
        .join("");

      $$(".category-pill", categoryPills).forEach((btn) => {
        btn.addEventListener("click", () => {
          $$(".category-pill", categoryPills).forEach((b) =>
            b.classList.remove("active"),
          );
          btn.classList.add("active");
          currentCategory = btn.dataset.cat;
          loadProducts();
        });
      });
    } catch (e) {
      console.error(e);
    }
  }

  // Load products with current filters
  async function loadProducts() {
    loading.style.display = "block";
    grid.innerHTML = "";
    try {
      const params = new URLSearchParams();
      if (currentCategory !== "All") params.set("category", currentCategory);
      if (searchTerm) params.set("search", searchTerm);

      const res = await fetch(`${endpoints.products}?${params}`, {
        credentials: "same-origin",
      });
      const data = await res.json();
      loading.style.display = "none";

      if (data.status !== "success" || !data.data.length) {
        grid.innerHTML =
          '<div class="col-12 text-center text-muted py-5">No products found.</div>';
        return;
      }

      grid.innerHTML = data.data
        .map(
          (p) => `
        <div class="col-6 col-md-4 col-lg-3">
          <article class="card product-card h-100">
            <img src="${escapeHtml(p.image_url)}" alt="${escapeHtml(p.name)}" class="card-img-top" loading="lazy">
            <div class="card-body d-flex flex-column">
              <span class="badge bg-secondary mb-2">${escapeHtml(p.category)}</span>
              <h3 class="card-title h6">${escapeHtml(p.name)}</h3>
              <p class="card-text text-muted small flex-grow-1">${escapeHtml(p.description || "").slice(0, 80)}...</p>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="text-primary fw-bold">${fmt(p.price)}</span>
                <a href="product.php?id=${p.id}" class="btn btn-sm btn-outline-primary">View</a>
              </div>
            </div>
          </article>
        </div>
      `,
        )
        .join("");
    } catch (e) {
      loading.style.display = "none";
      grid.innerHTML =
        '<div class="col-12 text-center text-danger py-5">Failed to load products.</div>';
      console.error(e);
    }
  }

  // Search debounced
  searchInput?.addEventListener(
    "input",
    debounce((e) => {
      searchTerm = e.target.value.trim();
      loadProducts();
    }),
  );

  // Initial load
  loadCategories();
  loadProducts();
}

// 
// PRODUCT PAGE — Single Product Detail + Add to Cart
// 
function initProduct() {
  const container = $("#productDetail");
  const urlParams = new URLSearchParams(location.search);
  const id = urlParams.get("id");

  if (!id) {
    container.innerHTML =
      '<div class="col-12 text-center text-danger py-5">Invalid product.</div>';
    return;
  }

  async function load() {
    try {
      const res = await fetch(
        `${endpoints.products}?search=${encodeURIComponent(id)}`,
        { credentials: "same-origin" },
      );
      // Note: products.php doesn't support ?id=, so we fetch all and filter client-side (acceptable for small catalog)
      // Better: add ?id= support in products.php, but keeping API unchanged per blueprint
      const data = await res.json();
      const product = data.data?.find((p) => String(p.id) === id);

      if (!product) throw new Error("Not found");

      container.innerHTML = `
        <div class="col-md-6">
          <img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name)}" class="img-fluid rounded shadow-sm" style="max-height:400px;object-fit:cover;">
        </div>
        <div class="col-md-6">
          <span class="badge bg-secondary mb-2">${escapeHtml(product.category)}</span>
          <h2>${escapeHtml(product.name)}</h2>
          <p class="text-muted">${escapeHtml(product.description || "No description.")}</p>
          <div class="mb-3"><span class="h3 text-primary">${fmt(product.price)}</span></div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" id="qtyInput" class="form-control w-auto" value="1" min="1" max="${product.stock}" style="max-width:100px;">
          </div>
          <button class="btn btn-primary btn-lg px-4" id="btnAddCart" data-id="${product.id}" ${product.stock <= 0 ? "disabled" : ""}>
            ${product.stock > 0 ? "Add to Cart" : "Out of Stock"}
          </button>
          <span class="ms-3" id="addCartMsg"></span>
        </div>
      `;

      $("#btnAddCart")?.addEventListener("click", addToCart);
    } catch (e) {
      container.innerHTML =
        '<div class="col-12 text-center text-danger py-5">Failed to load product.</div>';
      console.error(e);
    }
  }

  async function addToCart(e) {
    const btn = e.currentTarget;
    const id = btn.dataset.id;
    const qty = parseInt($("#qtyInput")?.value || "1", 10);
    const msgEl = $("#addCartMsg");

    btn.disabled = true;
    btn.textContent = "Adding…";
    try {
      const res = await fetch(endpoints.cart, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({ action: "add", product_id: id, quantity: qty }),
      });
      const data = await res.json();
      if (data.status === "success") {
        showMsg(msgEl, "Added to cart!");
        updateCartBadge();
      } else {
        showMsg(msgEl, data.message, true);
      }
    } catch (err) {
      showMsg(msgEl, "Error adding to cart", true);
    } finally {
      btn.disabled = false;
      btn.textContent = "Add to Cart";
    }
  }

  load();
}

// ============================================================
// CART PAGE — View/Update Cart + Checkout
// ============================================================
function initCart() {
  const cartSection = $("#cartSection");
  const emptyCart = $("#emptyCart");
  const checkoutSection = $("#checkoutSection");
  const cartBody = $("#cartBody");
  const orderSummary = $("#orderSummary");
  const checkoutForm = $("#checkoutForm");
  const checkoutMsg = $("#checkoutMsg");
  const placeOrderBtn = $("#placeOrderBtn");

  // Load cart
  async function loadCart() {
    try {
      const res = await fetch(endpoints.cart, { credentials: "same-origin" });
      const data = await res.json();

      if (data.status !== "success" || !data.data.length) {
        cartSection.style.display = "none";
        checkoutSection.style.display = "none";
        emptyCart.classList.remove("d-none");
        return;
      }

      cartSection.style.display = "block";
      checkoutSection.style.display = "grid";
      emptyCart.classList.add("d-none");

      // Render rows
      cartBody.innerHTML = data.data
        .map(
          (item) => `
        <tr data-id="${item.id}">
          <td>
            <div class="d-flex align-items-center gap-3">
              <img src="${escapeHtml(item.image_url)}" alt="" class="rounded">
              <div>
                <div class="fw-medium">${escapeHtml(item.name)}</div>
                <small class="text-muted">${fmt(item.price)} each</small>
              </div>
            </div>
          </td>
          <td class="text-center">${fmt(item.price)}</td>
          <td class="text-center">
            <input type="number" class="form-control qty-input mx-auto" value="${item.quantity}" min="1" max="99" data-id="${item.id}">
          </td>
          <td class="text-end fw-medium">${fmt(item.subtotal)}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-danger" data-remove="${item.id}"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      `,
        )
        .join("");

      // Render summary
      orderSummary.innerHTML = `
        <div class="summary-row"><span>Subtotal</span><span>${fmt(data.total)}</span></div>
        <div class="summary-row"><span>Shipping</span><span>Free</span></div>
        <div class="summary-row total"><span>Total</span><span>${fmt(data.total)}</span></div>
      `;

      // Bind qty changes
      $$(".qty-input", cartBody).forEach((input) => {
        input.addEventListener(
          "change",
          debounce((e) =>
            updateQty(e.target.dataset.id, parseInt(e.target.value, 10)),
          ),
        );
      });
      // Bind remove
      $$("[data-remove]", cartBody).forEach((btn) => {
        btn.addEventListener("click", (e) =>
          removeItem(e.currentTarget.dataset.remove),
        );
      });
    } catch (e) {
      console.error(e);
    }
  }

  async function updateQty(id, qty) {
    if (qty < 1) return removeItem(id);
    try {
      await fetch(endpoints.cart, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({
          action: "update",
          product_id: id,
          quantity: qty,
        }),
      });
      loadCart();
    } catch (e) {
      console.error(e);
    }
  }

  async function removeItem(id) {
    try {
      await fetch(endpoints.cart, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({ action: "remove", product_id: id }),
      });
      loadCart();
    } catch (e) {
      console.error(e);
    }
  }

  // Checkout submit
  checkoutForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const address = checkoutForm.shipping_address.value.trim();
    if (!address) return showMsg(checkoutMsg, "Enter shipping address", true);

    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = "Placing Order…";
    try {
      const res = await fetch(endpoints.checkout, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify({ shipping_address: address }),
      });
      const data = await res.json();
      if (data.status === "success") {
        showMsg(
          checkoutMsg,
          `Order #${data.order_id} placed! Total: ${fmt(data.total)}`,
        );
        setTimeout(() => (location.href = "index.php"), 2000);
      } else {
        showMsg(checkoutMsg, data.message, true);
      }
    } catch (err) {
      showMsg(checkoutMsg, "Checkout failed", true);
    } finally {
      placeOrderBtn.disabled = false;
      placeOrderBtn.textContent = "Place Order";
    }
  });

  loadCart();
}

// ============================================================
// LOGIN PAGE — Login & Register Forms
// ============================================================
function initLogin() {
  const loginForm = $("#loginForm");
  const registerForm = $("#registerForm");
  const loginMsg = $("#loginMsg");
  const registerMsg = $("#registerMsg");

  // If already logged in, redirect
  if (currentUser) location.href = "index.php";

  loginForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd = new FormData(loginForm);
    const body = {
      action: "login",
      email: fd.get("email"),
      password: fd.get("password"),
    };
    const btn = loginForm.querySelector("button");
    btn.disabled = true;
    btn.textContent = "Signing in…";

    try {
      const res = await fetch(endpoints.auth, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (data.status === "success") {
        showMsg(loginMsg, "Welcome back!");
        currentUser = data.user;
        updateAuthNav();
        setTimeout(() => (location.href = "index.php"), 800);
      } else {
        showMsg(loginMsg, data.message, true);
      }
    } catch (err) {
      showMsg(loginMsg, "Login failed", true);
    } finally {
      btn.disabled = false;
      btn.textContent = "Sign In";
    }
  });

  registerForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd = new FormData(registerForm);
    if (fd.get("password").length < 6)
      return showMsg(registerMsg, "Password min 6 chars", true);
    const body = {
      action: "register",
      full_name: fd.get("full_name"),
      email: fd.get("email"),
      password: fd.get("password"),
    };
    const btn = registerForm.querySelector("button");
    btn.disabled = true;
    btn.textContent = "Creating…";

    try {
      const res = await fetch(endpoints.auth, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (data.status === "success") {
        showMsg(registerMsg, "Account created! Redirecting…");
        currentUser = data.user;
        updateAuthNav();
        setTimeout(() => (location.href = "index.php"), 800);
      } else {
        showMsg(registerMsg, data.message, true);
      }
    } catch (err) {
      showMsg(registerMsg, "Registration failed", true);
    } finally {
      btn.disabled = false;
      btn.textContent = "Create Account";
    }
  });
}

// ============================================================
// ADMIN PAGE — Add Product + View Orders
// ============================================================
function initAdmin() {
  // Guard: only allow admins
  if (!currentUser || currentUser.role !== "admin") {
    document.body.innerHTML =
      '<div class="container text-center py-5"><h2>Access Denied</h2><p class="text-muted">Admin only.</p><a href="index.php" class="btn btn-primary">Go Home</a></div>';
    return;
  }

  const addForm = $("#addProductForm");
  const addMsg = $("#addProductMsg");
  const ordersBody = $("#ordersBody");

  // Add product
  addForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const fd = new FormData(addForm);
    const btn = addForm.querySelector("button");
    btn.disabled = true;
    btn.textContent = "Adding…";

    try {
      const res = await fetch(endpoints.admin, {
        method: "POST",
        credentials: "same-origin",
        body: fd, // FormData for file upload
      });
      const data = await res.json();
      if (data.status === "success") {
        showMsg(addMsg, "Product added!");
        addForm.reset();
        loadOrders(); // Refresh orders list
      } else {
        showMsg(addMsg, data.message, true);
      }
    } catch (err) {
      showMsg(addMsg, "Failed to add product", true);
    } finally {
      btn.disabled = false;
      btn.textContent = "Add Product";
    }
  });

  // Load orders
  async function loadOrders() {
    try {
      const res = await fetch(endpoints.admin, { credentials: "same-origin" });
      const data = await res.json();
      if (data.status !== "success") return;

      ordersBody.innerHTML = data.data
        .map(
          (o) => `
        <tr>
          <td>#${o.id}</td>
          <td>${escapeHtml(o.full_name)}<br><small class="text-muted">${escapeHtml(o.email)}</small></td>
          <td>${new Date(o.created_at).toLocaleString()}</td>
          <td class="text-end fw-medium">${fmt(o.total_amount)}</td>
          <td><span class="badge bg-${o.status === "Delivered" ? "success" : o.status === "Cancelled" ? "danger" : "warning"}">${escapeHtml(o.status)}</span></td>
          <td>
            <button class="btn btn-sm btn-outline-primary" data-order="${o.id}"><i class="bi bi-eye"></i></button>
          </td>
        </tr>
      `,
        )
        .join("");

      // Bind detail buttons
      $$("[data-order]", ordersBody).forEach((btn) => {
        btn.addEventListener("click", () =>
          showOrderDetail(
            btn.dataset.order,
            data.data.find((o) => String(o.id) === btn.dataset.order),
          ),
        );
      });
    } catch (e) {
      console.error(e);
    }
  }

  function showOrderDetail(id, order) {
    const itemsHtml = order.items
      .map(
        (i) => `
      <div class="order-item"><span>${escapeHtml(i.name)} × ${i.quantity}</span><span>${fmt(i.price_at_purchase * i.quantity)}</span></div>
    `,
      )
      .join("");
    alert(
      `Order #${id}\nCustomer: ${order.full_name}\nDate: ${new Date(order.created_at).toLocaleString()}\nAddress: ${order.shipping_address}\n\nItems:\n${order.items.map((i) => `  ${i.name} × ${i.quantity} = ${fmt(i.price_at_purchase * i.quantity)}`).join("\n")}\n\nTotal: ${fmt(order.total_amount)}`,
    );
  }

  loadOrders();
}

// ===== INITIAL CART BADGE (after fetchMe) =====
// Already called in fetchMe() → updateCartBadge()
