<?php
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../admin_lib/admin_transaction.modules.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
$username = $_SESSION['username'];

if ($order_id <= 0) {
    header('Location: orders.php');
    exit;
}

$order = user_getOrderDetail($order_id, $username);

if (!$order) {
    header('Location: orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - Nexus Shop</title>
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
        
        .container-main {
            max-width: 900px;
            margin: 2rem auto;
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
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .order-status {
            background: var(--success);
            color: white;
            padding: 0.6rem 1.2rem;
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
        
        .product-info {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .product-img {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .product-details h5 {
            margin: 0 0 0.5rem 0;
            font-weight: 700;
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
            margin-bottom: 1rem;
        }
        
        .copy-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .copy-btn:hover {
            background: #5a47b8;
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

    <div class="container-main px-4">
        <a href="orders.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại lịch sử
        </a>

        <div class="order-card">
            <div class="order-header">
                <div>
                    <h3 style="margin: 0; font-weight: 700;">Đơn hàng #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h3>
                    <small style="color: var(--text-secondary);">
                        <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?>
                    </small>
                </div>
                <div>
                    <span class="order-status">
                        <i class="fas fa-check-circle me-1"></i> Hoàn tất
                    </span>
                </div>
            </div>

            <div class="product-info">
                <img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="<?php echo htmlspecialchars($order['product_title']); ?>" class="product-img">
                <div class="product-details">
                    <h5><?php echo htmlspecialchars($order['product_title']); ?></h5>
                    <p style="color: var(--text-secondary); margin: 0.5rem 0 0 0;">
                        <i class="fas fa-folder me-1"></i> <?php echo htmlspecialchars($order['category']); ?>
                    </p>
                </div>
            </div>

            <div class="info-row">
                <span class="info-label">Giá mua</span>
                <span class="info-value" style="color: var(--success);">
                    <?php echo number_format($order['price']); ?>đ
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Trạng thái thanh toán</span>
                <span class="info-value">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i> Đã thanh toán
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Ngày mua</span>
                <span class="info-value">
                    <?php echo date('d/m/Y H:i:s', strtotime($order['created_at'])); ?>
                </span>
            </div>
        </div>

        <div class="order-card">
            <div class="account-info">
                <h5>
                    <i class="fas fa-lock me-2"></i> Thông tin tài khoản
                </h5>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                    Dưới đây là thông tin đăng nhập của tài khoản bạn đã mua. Vui lòng bảo mật thông tin này.
                </p>
                <div class="account-data" id="accountData"><?php echo htmlspecialchars($order['account_data']); ?></div>
                <button class="copy-btn" onclick="copyAccountInfo()">
                    <i class="fas fa-copy me-2"></i> Sao chép thông tin
                </button>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255, 255, 255, 0.08);">
                <h6 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                    <i class="fas fa-info-circle me-2"></i> Lưu ý
                </h6>
                <ul style="color: var(--text-secondary); font-size: 0.9rem; margin: 0; padding-left: 1.5rem;">
                    <li>Vui lòng đổi mật khẩu ngay sau khi đăng nhập lần đầu tiên</li>
                    <li>Bảo mật thông tin tài khoản của bạn</li>
                    <li>Nếu gặp vấn đề, vui lòng liên hệ hỗ trợ</li>
                </ul>
            </div>
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
