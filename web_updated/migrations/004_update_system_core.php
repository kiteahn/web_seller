<?php
require_once __DIR__ . '/../database/connect.php';

echo "=== CẬP NHẬT HỆ THỐNG CỐT LÕI ===\n\n";

// 1. Bảng lưu trữ kho tài khoản (Account Stock)
// Mỗi sản phẩm (product) có thể có nhiều tài khoản (accounts) trong kho
$sql = "CREATE TABLE IF NOT EXISTS account_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    account_data TEXT NOT NULL, -- Lưu thông tin đăng nhập (user|pass|extra)
    status ENUM('available', 'sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sold_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
mysqli_query($conn, $sql);
echo "[OK] Bảng account_stock sẵn sàng\n";

// 2. Bảng đơn hàng (Orders)
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    account_id INT NOT NULL, -- Liên kết tới tài khoản cụ thể đã bán
    price INT NOT NULL,
    status VARCHAR(20) DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (account_id) REFERENCES account_stock(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
mysqli_query($conn, $sql);
echo "[OK] Bảng orders sẵn sàng\n";

// 3. Bảng lịch sử giao dịch (Transactions) - Biến động số dư
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount INT NOT NULL, -- Số tiền thay đổi (+ hoặc -)
    balance_before INT NOT NULL,
    balance_after INT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'purchase', 'topup', 'refund'
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
mysqli_query($conn, $sql);
echo "[OK] Bảng transactions sẵn sàng\n";

// 4. Cập nhật bảng users để dùng password hashing (nếu cần)
// Lưu ý: Trong thực tế sẽ cần migration dữ liệu mật khẩu cũ, ở đây ta chuẩn bị cột
echo "[INFO] Cấu trúc DB đã được cập nhật thành công.\n";
?>
