<?php
require_once __DIR__ . '/../database/connect.php';

/**
 * Module quản lý giao dịch và đơn hàng
 */

function user_getOrderHistory($username) {
    global $conn;
    
    $username = mysqli_real_escape_string($conn, $username);
    
    $sql = "SELECT o.id, o.product_id, o.account_id, o.price, o.status, o.created_at,
                   p.title as product_title, p.image_url, p.category,
                   u.id as user_id
            FROM orders o
            JOIN products p ON o.product_id = p.id
            JOIN users u ON o.user_id = u.id
            WHERE u.username = '$username'
            ORDER BY o.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $orders = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    
    return $orders;
}

function user_getTransactionHistory($username) {
    global $conn;
    
    $username = mysqli_real_escape_string($conn, $username);
    
    $sql = "SELECT t.id, t.amount, t.balance_before, t.balance_after, t.type, t.description, t.created_at
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            WHERE u.username = '$username'
            ORDER BY t.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    $transactions = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $transactions[] = $row;
        }
    }
    
    return $transactions;
}

function user_getOrderDetail($order_id, $username) {
    global $conn;
    
    $order_id = intval($order_id);
    $username = mysqli_real_escape_string($conn, $username);
    
    $sql = "SELECT o.id, o.product_id, o.account_id, o.price, o.status, o.created_at,
                   p.title as product_title, p.image_url, p.category, p.description,
                   a.account_data,
                   u.id as user_id
            FROM orders o
            JOIN products p ON o.product_id = p.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN account_stock a ON o.account_id = a.id
            WHERE o.id = $order_id AND u.username = '$username'
            LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

function admin_getAllOrders($limit = 50, $offset = 0) {
    global $conn;
    
    $limit = intval($limit);
    $offset = intval($offset);
    
    $sql = "SELECT o.id, o.user_id, o.product_id, o.account_id, o.price, o.status, o.created_at,
                   u.username, p.title as product_title, p.image_url
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN products p ON o.product_id = p.id
            ORDER BY o.created_at DESC
            LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    $orders = [];
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }
    }
    
    return $orders;
}

function admin_createOrder($user_id, $product_id, $price) {
    global $conn;
    
    $user_id = intval($user_id);
    $product_id = intval($product_id);
    $price = intval($price);
    
    // Lấy tài khoản khả dụng từ kho
    require_once __DIR__ . '/admin_account_stock.modules.php';
    $account = admin_getAvailableAccount($product_id);
    
    if (!$account) {
        return ['success' => false, 'message' => 'Tài khoản không khả dụng'];
    }
    
    $account_id = $account['id'];
    
    $sql = "INSERT INTO orders (user_id, product_id, account_id, price, status) 
            VALUES ($user_id, $product_id, $account_id, $price, 'completed')";
    
    if (mysqli_query($conn, $sql)) {
        $order_id = mysqli_insert_id($conn);
        
        // Đánh dấu tài khoản là đã bán
        admin_markAccountAsSold($account_id);
        
        // Ghi nhận giao dịch
        $balance_before = admin_getUserBalance($user_id);
        $balance_after = $balance_before - $price;
        
        admin_recordTransaction($user_id, -$price, $balance_before, $balance_after, 'purchase', "Mua sản phẩm #$product_id");
        
        // Cập nhật số dư người dùng
        admin_updateUserBalance($user_id, $balance_after);
        
        return ['success' => true, 'message' => 'Tạo đơn hàng thành công', 'order_id' => $order_id];
    }
    
    return ['success' => false, 'message' => 'Lỗi khi tạo đơn hàng'];
}

function admin_recordTransaction($user_id, $amount, $balance_before, $balance_after, $type, $description) {
    global $conn;
    
    $user_id = intval($user_id);
    $amount = intval($amount);
    $balance_before = intval($balance_before);
    $balance_after = intval($balance_after);
    $type = mysqli_real_escape_string($conn, $type);
    $description = mysqli_real_escape_string($conn, $description);
    
    $sql = "INSERT INTO transactions (user_id, amount, balance_before, balance_after, type, description) 
            VALUES ($user_id, $amount, $balance_before, $balance_after, '$type', '$description')";
    
    return mysqli_query($conn, $sql);
}

function admin_getUserBalance($user_id) {
    global $conn;
    
    $user_id = intval($user_id);
    
    $sql = "SELECT balance FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return intval(mysqli_fetch_assoc($result)['balance']);
    }
    
    return 0;
}

function admin_updateUserBalance($user_id, $new_balance) {
    global $conn;
    
    $user_id = intval($user_id);
    $new_balance = intval($new_balance);
    
    $sql = "UPDATE users SET balance = $new_balance WHERE id = $user_id";
    
    return mysqli_query($conn, $sql);
}

function admin_topUpBalance($user_id, $amount, $description = 'Nạp tiền thủ công') {
    global $conn;
    
    $user_id = intval($user_id);
    $amount = intval($amount);
    
    $balance_before = admin_getUserBalance($user_id);
    $balance_after = $balance_before + $amount;
    
    admin_updateUserBalance($user_id, $balance_after);
    admin_recordTransaction($user_id, $amount, $balance_before, $balance_after, 'topup', $description);
    
    return true;
}

function admin_countOrders() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM orders";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return intval(mysqli_fetch_assoc($result)['total']);
    }
    
    return 0;
}

function admin_getOrderStats() {
    global $conn;
    
    $stats = [];
    
    $r = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
    $stats['total_orders'] = intval(mysqli_fetch_assoc($r)['total'] ?? 0);
    
    $r = mysqli_query($conn, "SELECT SUM(price) as total FROM orders");
    $stats['total_revenue'] = intval(mysqli_fetch_assoc($r)['total'] ?? 0);
    
    return $stats;
}

?>
