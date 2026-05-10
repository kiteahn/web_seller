<?php
session_start();
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../admin_lib/admin_verifier.modules.php';
require_once __DIR__ . '/../../admin_lib/admin_layout.modules.php';
require_once __DIR__ . '/../../admin_lib/admin_account_stock.modules.php';

$stats = admin_getAccountStockStats();
$products_result = mysqli_query($conn, "SELECT id, title FROM products ORDER BY title ASC");
$products = [];
while ($row = mysqli_fetch_assoc($products_result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý kho tài khoản - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-base: #0f0f1e;
            --card-base: #1a1a2e;
            --text-primary: #e0e0e0;
            --text-secondary: #a0a0a0;
            --accent: #6e56cf;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        
        body {
            background: var(--bg-base);
            color: var(--text-primary);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .sidebar {
            background: var(--card-base);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            min-height: 100vh;
            padding: 1.5rem 0;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .stat-card {
            background: var(--card-base);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
        }
        
        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .form-card {
            background: var(--card-base);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            color: var(--text-primary);
            box-shadow: 0 0 0 0.2rem rgba(110, 86, 207, 0.25);
        }
        
        .btn-primary {
            background: var(--accent);
            border: none;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: #5a47b8;
        }
        
        .table-container {
            background: var(--card-base);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table {
            margin: 0;
            color: var(--text-primary);
        }
        
        .table thead {
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .table thead th {
            color: var(--text-secondary);
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            border-color: rgba(255, 255, 255, 0.04);
            padding: 1rem;
            vertical-align: middle;
        }
        
        .status-available {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-sold {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .account-data-preview {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            padding: 0.5rem;
            font-family: monospace;
            font-size: 0.8rem;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .action-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-primary);
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: white;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="px-3 mb-3">
                <h5 style="font-weight: 700; margin: 0;">
                    <i class="fas fa-cog me-2" style="color: var(--accent);"></i> Admin
                </h5>
            </div>
            <nav class="nav flex-column px-3">
                <a class="nav-link" href="../dashboard.php">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <a class="nav-link" href="products.php">
                    <i class="fas fa-box me-2"></i> Sản phẩm
                </a>
                <a class="nav-link active" href="account-stock.php">
                    <i class="fas fa-database me-2"></i> Kho tài khoản
                </a>
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users me-2"></i> Người dùng
                </a>
                <a class="nav-link" href="orders.php">
                    <i class="fas fa-shopping-bag me-2"></i> Đơn hàng
                </a>
                <hr class="my-2" style="border-color: rgba(255, 255, 255, 0.08);">
                <a class="nav-link" href="../../../auth/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="flex: 1;">
            <div class="mb-4">
                <h2 style="font-weight: 700; margin: 0;">
                    <i class="fas fa-database me-2" style="color: var(--accent);"></i> Quản lý kho tài khoản
                </h2>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_accounts']; ?></div>
                        <div class="stat-label">Tổng tài khoản</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--success);"><?php echo $stats['available_accounts']; ?></div>
                        <div class="stat-label">Khả dụng</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <div class="stat-value" style="color: var(--danger);"><?php echo $stats['sold_accounts']; ?></div>
                        <div class="stat-label">Đã bán</div>
                    </div>
                </div>
            </div>

            <!-- Add Account Form -->
            <div class="form-card">
                <h5 style="margin-bottom: 1.5rem; font-weight: 700;">
                    <i class="fas fa-plus-circle me-2"></i> Thêm tài khoản
                </h5>
                <form id="addAccountForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Chọn sản phẩm</label>
                            <select class="form-select" id="productSelect" required>
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>">
                                        <?php echo htmlspecialchars($product['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thông tin tài khoản</label>
                            <input type="text" class="form-control" id="accountData" placeholder="username|password|extra_info" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Thêm tài khoản
                    </button>
                </form>
            </div>

            <!-- Bulk Add Form -->
            <div class="form-card">
                <h5 style="margin-bottom: 1.5rem; font-weight: 700;">
                    <i class="fas fa-upload me-2"></i> Thêm hàng loạt
                </h5>
                <form id="bulkAddForm">
                    <div class="mb-3">
                        <label class="form-label">Chọn sản phẩm</label>
                        <select class="form-select" id="bulkProductSelect" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>">
                                    <?php echo htmlspecialchars($product['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Danh sách tài khoản (mỗi dòng một tài khoản)</label>
                        <textarea class="form-control" id="bulkAccountsText" rows="6" placeholder="username1|password1|extra&#10;username2|password2|extra&#10;..." required></textarea>
                        <small class="text-muted">Định dạng: username|password|thông_tin_thêm (mỗi dòng một tài khoản)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i> Thêm hàng loạt
                    </button>
                </form>
            </div>

            <!-- Accounts List -->
            <div class="form-card">
                <h5 style="margin-bottom: 1.5rem; font-weight: 700;">
                    <i class="fas fa-list me-2"></i> Danh sách tài khoản
                </h5>
                <div class="mb-3">
                    <label class="form-label">Lọc theo sản phẩm</label>
                    <select class="form-select" id="filterProductSelect" onchange="loadAccounts()">
                        <option value="">-- Tất cả sản phẩm --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="table-container">
                    <table class="table" id="accountsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sản phẩm</th>
                                <th>Thông tin tài khoản</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-4" style="color: var(--text-secondary);">
                                    Chọn sản phẩm để xem danh sách tài khoản
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('addAccountForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const productId = document.getElementById('productSelect').value;
            const accountData = document.getElementById('accountData').value;

            const response = await fetch('/admin_lib/admin_account_stock.modules.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=add&product_id=${productId}&account_data=${encodeURIComponent(accountData)}`
            });

            const result = await response.json();
            alert(result.message);
            if (result.success) {
                document.getElementById('addAccountForm').reset();
                loadAccounts();
            }
        });

        document.getElementById('bulkAddForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const productId = document.getElementById('bulkProductSelect').value;
            const accountsText = document.getElementById('bulkAccountsText').value;

            const response = await fetch('/admin_lib/admin_account_stock.modules.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=bulk_add&product_id=${productId}&accounts_text=${encodeURIComponent(accountsText)}`
            });

            const result = await response.json();
            alert(result.message);
            if (result.success) {
                document.getElementById('bulkAddForm').reset();
                loadAccounts();
            }
        });

        async function loadAccounts() {
            const productId = document.getElementById('filterProductSelect').value;
            if (!productId) {
                document.getElementById('accountsTable').innerHTML = `
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sản phẩm</th>
                            <th>Thông tin tài khoản</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: var(--text-secondary);">
                                Chọn sản phẩm để xem danh sách tài khoản
                            </td>
                        </tr>
                    </tbody>
                `;
                return;
            }

            const response = await fetch(`/admin_lib/admin_account_stock.modules.php?action=get_by_product&product_id=${productId}`);
            const result = await response.json();

            if (result.success && result.data.length > 0) {
                let html = `<thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Thông tin tài khoản</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead><tbody>`;

                result.data.forEach(account => {
                    const statusClass = account.status === 'available' ? 'status-available' : 'status-sold';
                    const statusText = account.status === 'available' ? 'Khả dụng' : 'Đã bán';
                    html += `
                        <tr>
                            <td>${account.id}</td>
                            <td>${account.product_id}</td>
                            <td><div class="account-data-preview">${account.account_data}</div></td>
                            <td><span class="${statusClass}">${statusText}</span></td>
                            <td>${new Date(account.created_at).toLocaleDateString('vi-VN')}</td>
                            <td>
                                <button class="action-btn" onclick="deleteAccount(${account.id})">
                                    <i class="fas fa-trash me-1"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody>';
                document.getElementById('accountsTable').innerHTML = html;
            } else {
                document.getElementById('accountsTable').innerHTML = `
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sản phẩm</th>
                            <th>Thông tin tài khoản</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center py-4" style="color: var(--text-secondary);">
                                Không có tài khoản nào cho sản phẩm này
                            </td>
                        </tr>
                    </tbody>
                `;
            }
        }

        async function deleteAccount(accountId) {
            if (!confirm('Bạn chắc chắn muốn xóa tài khoản này?')) return;

            const response = await fetch('/admin_lib/admin_account_stock.modules.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&account_id=${accountId}`
            });

            const result = await response.json();
            alert(result.message);
            if (result.success) {
                loadAccounts();
            }
        }
    </script>
</body>
</html>
