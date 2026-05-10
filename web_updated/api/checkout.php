<?php
session_start();
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../lib/userModules.php';
require_once __DIR__ . '/../admin_lib/admin_transaction.modules.php';
require_once __DIR__ . '/../admin_lib/admin_account_stock.modules.php';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để mua hàng']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'buy':
        handleBuyProduct();
        break;
        
    case 'check_stock':
        handleCheckStock();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

function handleBuyProduct() {
    global $conn;
    
    $product_id = intval($_POST['product_id'] ?? 0);
    $username = $_SESSION['username'];
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        return;
    }
    
    // Lấy thông tin sản phẩm
    $sql = "SELECT id, title, price FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        return;
    }
    
    $product = mysqli_fetch_assoc($result);
    $price = intval($product['price']);
    
    // Lấy thông tin người dùng
    $sql = "SELECT id, balance FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại']);
        return;
    }
    
    $user = mysqli_fetch_assoc($result);
    $user_id = intval($user['id']);
    $balance = intval($user['balance']);
    
    // Kiểm tra số dư
    if ($balance < $price) {
        echo json_encode([
            'success' => false, 
            'message' => 'Số dư không đủ',
            'balance' => $balance,
            'price' => $price,
            'needed' => $price - $balance
        ]);
        return;
    }
    
    // Lấy tài khoản khả dụng từ kho
    $available_account = admin_getAvailableAccount($product_id);
    
    if (!$available_account) {
        echo json_encode(['success' => false, 'message' => 'Tài khoản không khả dụng. Vui lòng thử lại sau']);
        return;
    }
    
    $account_id = intval($available_account['id']);
    
    // Tạo đơn hàng
    $sql = "INSERT INTO orders (user_id, product_id, account_id, price, status) 
            VALUES (?, ?, ?, ?, 'completed')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $user_id, $product_id, $account_id, $price);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Lỗi khi tạo đơn hàng']);
        return;
    }
    
    $order_id = mysqli_insert_id($conn);
    
    // Đánh dấu tài khoản là đã bán
    admin_markAccountAsSold($account_id);
    
    // Cập nhật số dư người dùng
    $new_balance = $balance - $price;
    $sql = "UPDATE users SET balance = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $new_balance, $user_id);
    mysqli_stmt_execute($stmt);
    
    // Ghi nhận giao dịch
    admin_recordTransaction(
        $user_id, 
        -$price, 
        $balance, 
        $new_balance, 
        'purchase', 
        "Mua sản phẩm: " . $product['title']
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Mua hàng thành công',
        'order_id' => $order_id,
        'new_balance' => $new_balance,
        'product_title' => $product['title'],
        'price' => $price
    ]);
}

function handleCheckStock() {
    $product_id = intval($_GET['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ']);
        return;
    }
    
    $available_count = admin_getAvailableAccountCount($product_id);
    
    echo json_encode([
        'success' => true,
        'product_id' => $product_id,
        'available' => $available_count > 0,
        'count' => $available_count
    ]);
}

function admin_getAvailableAccountCount($product_id) {
    global $conn;
    
    $product_id = intval($product_id);
    
    $sql = "SELECT COUNT(*) as count FROM account_stock WHERE product_id = $product_id AND status = 'available'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return intval(mysqli_fetch_assoc($result)['count']);
    }
    
    return 0;
}

?>
