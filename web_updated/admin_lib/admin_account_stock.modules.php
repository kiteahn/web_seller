<?php
require_once __DIR__ . '/../database/connect.php';

/**
 * Module quản lý kho tài khoản game
 * Hỗ trợ CRUD tài khoản trong kho, theo dõi trạng thái (available/sold)
 */

function admin_getAccountStockByProduct($product_id) {
    global $conn;
    $product_id = intval($product_id);
    $accounts = [];
    
    $sql = "SELECT id, product_id, account_data, status, created_at, sold_at 
            FROM account_stock 
            WHERE product_id = $product_id 
            ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $accounts[] = $row;
        }
    }
    return $accounts;
}

function admin_getAccountStockStats() {
    global $conn;
    $stats = [];
    
    $r = mysqli_query($conn, "SELECT COUNT(*) as total FROM account_stock");
    $stats['total_accounts'] = intval(mysqli_fetch_assoc($r)['total'] ?? 0);
    
    $r = mysqli_query($conn, "SELECT COUNT(*) as total FROM account_stock WHERE status = 'available'");
    $stats['available_accounts'] = intval(mysqli_fetch_assoc($r)['total'] ?? 0);
    
    $r = mysqli_query($conn, "SELECT COUNT(*) as total FROM account_stock WHERE status = 'sold'");
    $stats['sold_accounts'] = intval(mysqli_fetch_assoc($r)['total'] ?? 0);
    
    return $stats;
}

function admin_addAccountStock($product_id, $account_data) {
    global $conn;
    
    $product_id = intval($product_id);
    $account_data = mysqli_real_escape_string($conn, $account_data);
    
    $sql = "INSERT INTO account_stock (product_id, account_data, status) 
            VALUES ($product_id, '$account_data', 'available')";
    
    return mysqli_query($conn, $sql);
}

function admin_addAccountStockBulk($product_id, $accounts_array) {
    global $conn;
    
    $product_id = intval($product_id);
    $count = 0;
    
    $stmt = mysqli_prepare($conn, "INSERT INTO account_stock (product_id, account_data, status) VALUES (?, ?, 'available')");
    
    foreach ($accounts_array as $account_data) {
        $account_data = trim($account_data);
        if (!empty($account_data)) {
            mysqli_stmt_bind_param($stmt, "is", $product_id, $account_data);
            if (mysqli_stmt_execute($stmt)) {
                $count++;
            }
        }
    }
    
    return $count;
}

function admin_getAvailableAccount($product_id) {
    global $conn;
    
    $product_id = intval($product_id);
    
    $sql = "SELECT id, product_id, account_data 
            FROM account_stock 
            WHERE product_id = $product_id AND status = 'available' 
            LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

function admin_markAccountAsSold($account_id) {
    global $conn;
    
    $account_id = intval($account_id);
    
    $sql = "UPDATE account_stock 
            SET status = 'sold', sold_at = NOW() 
            WHERE id = $account_id";
    
    return mysqli_query($conn, $sql);
}

function admin_deleteAccountStock($account_id) {
    global $conn;
    
    $account_id = intval($account_id);
    
    $sql = "DELETE FROM account_stock WHERE id = $account_id LIMIT 1";
    
    return mysqli_query($conn, $sql);
}

function admin_getAccountById($account_id) {
    global $conn;
    
    $account_id = intval($account_id);
    
    $sql = "SELECT * FROM account_stock WHERE id = $account_id";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

function admin_handleAccountStockRequest() {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'get_by_product':
            $product_id = intval($_REQUEST['product_id'] ?? 0);
            $accounts = admin_getAccountStockByProduct($product_id);
            admin_respondJson(true, 'Lấy dữ liệu thành công', $accounts);
            break;
            
        case 'stats':
            $stats = admin_getAccountStockStats();
            admin_respondJson(true, 'Thống kê thành công', $stats);
            break;
            
        case 'add':
            $product_id = intval($_POST['product_id'] ?? 0);
            $account_data = $_POST['account_data'] ?? '';
            
            if ($product_id <= 0 || empty($account_data)) {
                admin_respondJson(false, 'Dữ liệu không hợp lệ');
                break;
            }
            
            if (admin_addAccountStock($product_id, $account_data)) {
                admin_respondJson(true, 'Thêm tài khoản thành công');
            } else {
                admin_respondJson(false, 'Lỗi khi thêm tài khoản');
            }
            break;
            
        case 'bulk_add':
            $product_id = intval($_POST['product_id'] ?? 0);
            $accounts_text = $_POST['accounts_text'] ?? '';
            
            if ($product_id <= 0 || empty($accounts_text)) {
                admin_respondJson(false, 'Dữ liệu không hợp lệ');
                break;
            }
            
            $accounts_array = array_filter(array_map('trim', explode("\n", $accounts_text)));
            $count = admin_addAccountStockBulk($product_id, $accounts_array);
            
            admin_respondJson(true, "Đã thêm $count tài khoản thành công", ['count' => $count]);
            break;
            
        case 'delete':
            $account_id = intval($_POST['account_id'] ?? 0);
            
            if ($account_id <= 0) {
                admin_respondJson(false, 'ID không hợp lệ');
                break;
            }
            
            if (admin_deleteAccountStock($account_id)) {
                admin_respondJson(true, 'Xóa tài khoản thành công');
            } else {
                admin_respondJson(false, 'Lỗi khi xóa tài khoản');
            }
            break;
            
        default:
            admin_respondJson(false, 'Action không hợp lệ');
    }
}

function admin_respondJson($success, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Xử lý request nếu được gọi trực tiếp
if (basename($_SERVER['PHP_SELF']) === 'admin_account_stock.modules.php') {
    require_once __DIR__ . '/admin_verifier.modules.php';
    admin_handleAccountStockRequest();
}
?>
