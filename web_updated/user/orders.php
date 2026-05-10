<?php
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../admin_lib/admin_transaction.modules.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../auth/login.php');
    exit;
}

$username = $_SESSION['username'];
$orders = user_getOrderHistory($username);
$transactions = user_getTransactionHistory($username);

// Lấy thông tin người dùng
$sql = "SELECT balance FROM users WHERE username = ?";
$stmt = mysqli_prepare($GLOBALS['conn'], $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$balance = intval($user['balance']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - Nexus Shop</title>
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
        
        .navbar {
            background: var(--card-base);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem 0;
        }
        
        .container-main {
            max-width: 1200px;
            margin: 2rem auto;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .balance-card {
            background: linear-gradient(135deg, var(--accent), #8b5cf6);
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            min-width: 250px;
        }
        
        .balance-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        
        .balance-value {
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 2rem;
        }
        
        .nav-link {
            color: var(--text-secondary);
            border: none;
            border-bottom: 2px solid transparent;
            padding: 1rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: var(--text-primary);
        }
        
        .nav-link.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
            background: transparent;
        }
        
        .order-item {
            background: var(--card-base);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .order-item-img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .order-item-info {
            flex: 1;
            min-width: 250px;
        }
        
        .order-item-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .order-item-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .order-item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success);
            margin-right: 1rem;
        }
        
        .order-item-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .order-item-btn:hover {
            background: #5a47b8;
            color: white;
        }
        
        .transaction-item {
            background: var(--card-base);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .transaction-type {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .transaction-type.purchase {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .transaction-type.topup {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .transaction-amount {
            font-weight: 700;
            font-size: 1.1rem;
            min-width: 120px;
            text-align: right;
        }
        
        .transaction-amount.negative {
            color: #ef4444;
        }
        
        .transaction-amount.positive {
            color: var(--success);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .back-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
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
            <div class="ms-auto">
                <a href="../index.php" style="color: var(--text-primary); text-decoration: none; margin-right: 1rem;">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
                <a href="../auth/logout.php" style="color: var(--text-secondary); text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
    </nav>

    <div class="container-main px-4">
        <a href="../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Quay lại cửa hàng
        </a>

        <div class="header-section">
            <h2 style="margin: 0; font-weight: 700;">
                <i class="fas fa-history me-2" style="color: var(--accent);"></i>
                Lịch sử tài khoản
            </h2>
            <div class="balance-card">
                <div class="balance-label">Số dư hiện tại</div>
                <div class="balance-value"><?php echo number_format($balance); ?>đ</div>
            </div>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#orders" role="tab">
                    <i class="fas fa-shopping-bag me-2"></i> Đơn hàng (<?php echo count($orders); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#transactions" role="tab">
                    <i class="fas fa-exchange-alt me-2"></i> Giao dịch (<?php echo count($transactions); ?>)
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab Đơn hàng -->
            <div id="orders" class="tab-pane fade show active">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="<?php echo htmlspecialchars($order['product_title']); ?>" class="order-item-img">
                            <div class="order-item-info">
                                <div class="order-item-title">
                                    <?php echo htmlspecialchars($order['product_title']); ?>
                                </div>
                                <div class="order-item-meta">
                                    <i class="fas fa-hashtag me-1"></i> Đơn #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                    <span style="margin: 0 0.5rem;">•</span>
                                    <i class="fas fa-calendar me-1"></i> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </div>
                            </div>
                            <div class="order-item-price">
                                <?php echo number_format($order['price']); ?>đ
                            </div>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="order-item-btn">
                                <i class="fas fa-eye me-1"></i> Xem chi tiết
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h5>Chưa có đơn hàng nào</h5>
                        <p>Bạn chưa mua sản phẩm nào. <a href="../index.php" style="color: var(--accent);">Khám phá cửa hàng ngay</a></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab Giao dịch -->
            <div id="transactions" class="tab-pane fade">
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $trans): ?>
                        <div class="transaction-item">
                            <div>
                                <div class="transaction-type <?php echo $trans['type']; ?>">
                                    <?php if ($trans['type'] === 'purchase'): ?>
                                        <i class="fas fa-shopping-bag"></i> Mua hàng
                                    <?php elseif ($trans['type'] === 'topup'): ?>
                                        <i class="fas fa-plus-circle"></i> Nạp tiền
                                    <?php else: ?>
                                        <i class="fas fa-undo"></i> Hoàn tiền
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                    <?php echo htmlspecialchars($trans['description']); ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.3rem;">
                                    <?php echo date('d/m/Y H:i:s', strtotime($trans['created_at'])); ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div class="transaction-amount <?php echo ($trans['amount'] > 0) ? 'positive' : 'negative'; ?>">
                                    <?php echo ($trans['amount'] > 0 ? '+' : '') . number_format($trans['amount']); ?>đ
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                    Số dư: <?php echo number_format($trans['balance_after']); ?>đ
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5>Chưa có giao dịch nào</h5>
                        <p>Lịch sử giao dịch của bạn sẽ hiển thị ở đây</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
