<?php
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../lib/userModules.php';
require_once __DIR__ . '/../admin_lib/admin_transaction.modules.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit;
}

$order_id = intval($_GET['order_id'] ?? 0);
$username = $_SESSION['username'];

if ($order_id <= 0) {
    header('Location: ../index.php');
    exit;
}

// Lấy thông tin đơn hàng
$order = user_getOrderDetail($order_id, $username);

if (!$order) {
    header('Location: ../index.php');
    exit;
}

// Giải mã thông tin tài khoản
$account_info = $order['account_data'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin đơn hàng - Nexus Shop</title>
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
        
        .navbar {
            background: var(--card-base);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem 0;
        }
        
        .container {
            max-width: 900px;
            margin: 3rem auto;
        }
        
        .order-card {
            background: var(--card-base);
            border-radius: 12px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 2rem;
        }
        
        .order-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .order-status {
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .account-info {
            background: rgba(110, 86, 207, 0.1);
            border: 1px solid rgba(110, 86, 207, 0.2);
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .account-info h5 {
            color: var(--accent);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .account-data {
            background: var(--bg-base);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            white-space: pre-wrap;
            font-size: 0.9rem;
        }
        
        .copy-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 1rem;
            transition: background 0.3s;
        }
        
        .copy-btn:hover {
            background: #5a47b8;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .back-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .back-link:hover {
            color: #5a47b8;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h5">
                <i class="fas fa-gamepad me-2" style="color: var(--accent);"></i>
                <strong>Nexus Shop</strong>
            </span>
        </div>
    </nav>

    <div class="container">
        <a href="../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại cửa hàng
        </a>

        <div class="success-message">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Mua hàng thành công!</strong> Thông tin tài khoản của bạn được hiển thị dưới đây.
        </div>

        <div class="order-card">
            <div class="order-header">
                <div>
                    <h3 style="margin: 0; font-weight: 700;">Đơn hàng #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <small style="color: var(--text-secondary);">
                        <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?>
                    </small>
                </div>
                <div class="ms-auto">
                    <span class="order-status">
                        <i class="fas fa-check me-1"></i> Hoàn tất
                    </span>
                </div>
            </div>

            <div class="info-row">
                <span class="info-label">Sản phẩm</span>
                <span class="info-value"><?php echo htmlspecialchars($order['product_title']); ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Danh mục</span>
                <span class="info-value"><?php echo htmlspecialchars($order['category']); ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Giá</span>
                <span class="info-value" style="color: var(--success);">
                    <?php echo number_format($order['price']); ?>đ
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Trạng thái</span>
                <span class="info-value">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i> Đã thanh toán
                </span>
            </div>
        </div>

        <div class="order-card">
            <div class="account-info">
                <h5>
                    <i class="fas fa-lock me-2"></i> Thông tin tài khoản
                </h5>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                    Dưới đây là thông tin đăng nhập của tài khoản bạn vừa mua. Vui lòng lưu lại thông tin này ở nơi an toàn.
                </p>
                <div class="account-data" id="accountData"><?php echo htmlspecialchars($account_info); ?></div>
                <button class="copy-btn" onclick="copyAccountInfo()">
                    <i class="fas fa-copy me-2"></i> Sao chép thông tin
                </button>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                <h6 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle me-2"></i> Lưu ý quan trọng
                </h6>
                <ul style="color: var(--text-secondary); font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                    <li>Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu tiên</li>
                    <li>Bảo mật thông tin tài khoản của bạn</li>
                    <li>Nếu gặp vấn đề, vui lòng liên hệ hỗ trợ</li>
                </ul>
            </div>
        </div>

        <div class="order-card">
            <h5 style="margin-bottom: 1.5rem;">
                <i class="fas fa-history me-2"></i> Lịch sử mua hàng
            </h5>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">
                Bạn có thể xem tất cả các đơn hàng của mình trong <a href="../user/orders.php" style="color: var(--accent);">trang lịch sử đơn hàng</a>.
            </p>
        </div>
    </div>

    <script>
        function copyAccountInfo() {
            const accountData = document.getElementById('accountData').textContent;
            navigator.clipboard.writeText(accountData).then(() => {
                alert('Đã sao chép thông tin tài khoản!');
            }).catch(() => {
                alert('Lỗi khi sao chép. Vui lòng sao chép thủ công.');
            });
        }
    </script>
</body>
</html>
