<?php
session_start();
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../admin_lib/admin_verifier.modules.php';
require_once __DIR__ . '/../../admin_lib/admin_layout.modules.php';
require_once __DIR__ . '/../../admin_lib/admin_transaction.modules.php';

$stats = admin_getOrderStats();
$orders = admin_getAllOrders(50, 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
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
        
        .order-id {
            font-weight: 700;
            color: var(--accent);
        }
        
        .product-name {
            font-weight: 600;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .price {
            color: var(--success);
            font-weight: 700;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
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
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
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
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users me-2"></i> Người dùng
                </a>
                <a class="nav-link active" href="orders.php">
                    <i class="fas fa-shopping-bag me-2"></i> Đơn hàng
                </a>
                <a class="nav-link" href="categories.php">
                    <i class="fas fa-folder me-2"></i> Danh mục
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
                    <i class="fas fa-shopping-bag me-2" style="color: var(--accent);"></i> Quản lý đơn hàng
                </h2>
            </div>

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                        <div class="stat-label">Tổng đơn hàng</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($stats['total_revenue']); ?>đ</div>
                        <div class="stat-label">Tổng doanh thu</div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-container">
                <?php if (count($orders) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Đơn hàng</th>
                                <th>Người dùng</th>
                                <th>Sản phẩm</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th>Ngày mua</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="order-id">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="" style="width: 32px; height: 32px; border-radius: 4px; object-fit: cover;">
                                            <span class="product-name"><?php echo htmlspecialchars($order['product_title']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="price"><?php echo number_format($order['price']); ?>đ</span>
                                    </td>
                                    <td>
                                        <span class="status-badge">
                                            <i class="fas fa-check-circle me-1"></i> Hoàn tất
                                        </span>
                                    </td>
                                    <td style="font-size: 0.9rem; color: var(--text-secondary);">
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td>
                                        <button class="action-btn" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i> Xem
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.5; margin-bottom: 1rem; display: block;"></i>
                        <h5>Chưa có đơn hàng nào</h5>
                        <p>Các đơn hàng sẽ hiển thị ở đây</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewOrder(orderId) {
            alert('Chi tiết đơn hàng #' + orderId);
            // Có thể mở modal hoặc redirect tới trang chi tiết
        }
    </script>
</body>
</html>
