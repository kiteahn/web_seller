<?php
require_once __DIR__ . '/../database/connect.php';

echo "=== MIGRATION: 002_add_categories ===\n\n";

$sql = "CREATE TABLE IF NOT EXISTS categories (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 icon_class VARCHAR(50) DEFAULT 'fa-folder',
 description VARCHAR(255) DEFAULT '',
 sort_order INT DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
 echo "[OK] Bảng categories đã được tạo\n";
} else {
 $err = mysqli_error($conn);
 if (strpos($err, 'already exists') !== false) {
 echo "[SKIP] Bảng categories đã tồn tại\n";
 } else {
 echo "[ERROR] Bảng categories: $err\n";
 }
}

$result = mysqli_query($conn, "DESCRIBE types");
$typeCols = [];
while ($r = mysqli_fetch_assoc($result)) { $typeCols[] = $r['Field']; }

if (!in_array('category_id', $typeCols)) {
 if (mysqli_query($conn, "ALTER TABLE types ADD COLUMN category_id INT NULL AFTER category")) {
 echo "[OK] Cột category_id đã thêm vào types\n";
 } else {
 echo "[ERROR] category_id: " . mysqli_error($conn) . "\n";
 }
} else {
 echo "[SKIP] Cột category_id đã tồn tại trong types\n";
}

if (!in_array('sort_order', $typeCols)) {
 if (mysqli_query($conn, "ALTER TABLE types ADD COLUMN sort_order INT DEFAULT 0 AFTER icon_class")) {
 echo "[OK] Cột sort_order đã thêm vào types\n";
 } else {
 echo "[ERROR] sort_order: " . mysqli_error($conn) . "\n";
 }
} else {
 echo "[SKIP] Cột sort_order đã tồn tại trong types\n";
}

$result = mysqli_query($conn, "SELECT DISTINCT category FROM types");
$defaultCategories = [
 ['Game', 'fa-gamepad'],
 ['Netflix', 'fa-n'],
 ['YouTube', 'fa-youtube'],
 ['Spotify', 'fa-spotify'],
 ['Disney+', 'fa-play'],
 ['Amazon', 'fa-amazon'],
 ['Apple', 'fa-apple'],
 ['GPT', 'fa-robot'],
 ['AI Tools', 'fa-wand-magic-sparkles'],
 ['Cloud', 'fa-cloud'],
 ['Social', 'fa-share-nodes'],
 ['Khác', 'fa-ellipsis'],
];

$sort = 1;
foreach ($defaultCategories as $cat) {
 $name = mysqli_real_escape_string($conn, $cat[0]);
 $icon = mysqli_real_escape_string($conn, $cat[1]);
 mysqli_query($conn, "INSERT IGNORE INTO categories (name, icon_class, sort_order) VALUES ('$name', '$icon', $sort)");
 $sort++;
}

while ($row = mysqli_fetch_assoc($result)) {
 $catName = mysqli_real_escape_string($conn, $row['category']);
 $exists = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$catName' LIMIT 1");
 if (mysqli_num_rows($exists) === 0) {
 mysqli_query($conn, "INSERT IGNORE INTO categories (name, icon_class, sort_order) VALUES ('$catName', 'fa-folder', $sort)");
 $sort++;
 }
}
echo "[OK] Đã sync categories từ types\n";

$result = mysqli_query($conn, "SELECT id, category FROM types WHERE category_id IS NULL");
$linked = 0;
while ($row = mysqli_fetch_assoc($result)) {
 $typeId = intval($row['id']);
 $catName = mysqli_real_escape_string($conn, $row['category']);
 $catResult = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$catName' LIMIT 1");
 if ($catResult && mysqli_num_rows($catResult) > 0) {
 $catRow = mysqli_fetch_assoc($catResult);
 mysqli_query($conn, "UPDATE types SET category_id = {$catRow['id']} WHERE id = $typeId");
 $linked++;
 }
}
echo "[OK] Đã link $linked types -> categories\n";

echo "\n[DONE] Migration hoàn tất!\n";
