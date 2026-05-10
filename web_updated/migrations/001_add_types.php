<?php
require_once __DIR__ . '/../database/connect.php';

echo "=== MIGRATION: 001_add_types ===\n\n";

$sql = "CREATE TABLE IF NOT EXISTS types (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 category VARCHAR(100) NOT NULL,
 icon_class VARCHAR(50) DEFAULT 'fa-tag',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
 echo "[OK] Bảng types đã được tạo\n";
} else {
 echo "[SKIP] Bảng types: " . mysqli_error($conn) . "\n";
}

$result = mysqli_query($conn, "DESCRIBE products");
$columns = [];
while ($row = mysqli_fetch_assoc($result)) {
 $columns[] = $row['Field'];
}

if (!in_array('type_id', $columns)) {
 $sql2 = "ALTER TABLE products ADD COLUMN type_id INT NULL AFTER category";
 if (mysqli_query($conn, $sql2)) {
 echo "[OK] Cột type_id đã được thêm vào products\n";
 } else {
  echo "[ERROR] Cột type_id: " . mysqli_error($conn) . "\n";
 }
} else {
 echo "[SKIP] Cột type_id đã tồn tại trong products\n";
}

echo "\n[DONE] Migration hoàn tất!\n";
