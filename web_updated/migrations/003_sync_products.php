<?php
require_once __DIR__ . '/../database/connect.php';

echo "Syncing Database...\n";

// Create products table if it doesn't exist at all
$create_sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    game_type VARCHAR(100) DEFAULT '',
    type_id INT NULL,
    image_url VARCHAR(255) NOT NULL,
    price INT NOT NULL,
    old_price INT DEFAULT 0,
    badge VARCHAR(50) DEFAULT '',
    details TEXT,
    description TEXT,
    color_class VARCHAR(50) DEFAULT 'bg-secondary',
    icon_class VARCHAR(50) DEFAULT 'fa-box',
    gallery TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
mysqli_query($conn, $create_sql);

$result = mysqli_query($conn, "DESCRIBE products");
$cols = [];
if ($result) {
    while ($r = mysqli_fetch_assoc($result)) {
        $cols[] = $r['Field'];
    }
}

$queries = [];
if (!in_array('old_price', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN old_price INT DEFAULT 0 AFTER price";
if (!in_array('badge', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN badge VARCHAR(50) DEFAULT '' AFTER old_price";
if (!in_array('details', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN details TEXT AFTER badge";
if (!in_array('description', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN description TEXT AFTER details";
if (!in_array('color_class', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN color_class VARCHAR(50) DEFAULT 'bg-secondary' AFTER description";
if (!in_array('icon_class', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN icon_class VARCHAR(50) DEFAULT 'fa-box' AFTER color_class";
if (!in_array('gallery', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN gallery TEXT AFTER icon_class";
if (!in_array('type_id', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN type_id INT NULL AFTER category";
if (!in_array('game_type', $cols)) $queries[] = "ALTER TABLE products ADD COLUMN game_type VARCHAR(100) DEFAULT '' AFTER category";

foreach ($queries as $q) {
    if (mysqli_query($conn, $q)) {
        echo "[OK] Okey: " . $q . "\n";
    } else {
        echo "[ERROR] " . $q . " - " . mysqli_error($conn) . "\n";
    }
}
echo "Done syncing database!\n";
