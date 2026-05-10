<?php
require_once __DIR__ . '/../database/connect.php';

echo "=== NEXUS DATABASE SEED ===\n\n";

echo "[0/4] Kiểm tra schema...\n";

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
mysqli_query($conn, $sql);
echo "[OK] Bảng categories sẵn sàng\n";

$sql = "CREATE TABLE IF NOT EXISTS types (
 id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 category VARCHAR(100) NOT NULL,
 category_id INT NULL,
 icon_class VARCHAR(50) DEFAULT 'fa-tag',
 sort_order INT DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
mysqli_query($conn, $sql);
echo "[OK] Bảng types sẵn sàng\n";

$result2 = mysqli_query($conn, "DESCRIBE types");
$typeCols = [];
while ($r = mysqli_fetch_assoc($result2)) { $typeCols[] = $r['Field']; }
if (!in_array('category_id', $typeCols)) {
 mysqli_query($conn, "ALTER TABLE types ADD COLUMN category_id INT NULL AFTER category");
 echo "[OK] Cột category_id đã thêm vào types\n";
} else {
 echo "[SKIP] Cột category_id đã tồn tại\n";
}
if (!in_array('sort_order', $typeCols)) {
 mysqli_query($conn, "ALTER TABLE types ADD COLUMN sort_order INT DEFAULT 0 AFTER icon_class");
 echo "[OK] Cột sort_order đã thêm vào types\n";
} else {
 echo "[SKIP] Cột sort_order đã tồn tại\n";
}

$result = mysqli_query($conn, "DESCRIBE products");
$cols = [];
while ($r = mysqli_fetch_assoc($result)) { $cols[] = $r['Field']; }
if (!in_array('type_id', $cols)) {
 mysqli_query($conn, "ALTER TABLE products ADD COLUMN type_id INT NULL AFTER category");
 echo "[OK] Cột type_id đã thêm vào products\n";
} else {
 echo "[SKIP] Cột type_id đã tồn tại\n";
}

if (!in_array('game_type', $cols)) {
 mysqli_query($conn, "ALTER TABLE products ADD COLUMN game_type VARCHAR(100) DEFAULT '' AFTER category");
 echo "[OK] Cột game_type đã thêm vào products\n";
} else {
 echo "[SKIP] Cột game_type đã tồn tại\n";
}

echo "\n[1/4] Xóa dữ liệu cũ...\n";
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "TRUNCATE TABLE products");
echo "[OK] Đã xóa products\n";
mysqli_query($conn, "TRUNCATE TABLE types");
echo "[OK] Đã xóa types\n";
mysqli_query($conn, "TRUNCATE TABLE categories");
echo "[OK] Đã xóa categories\n";
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

echo "\n[2/4] Seed categories...\n";

$categories = [
 ['Game', 'fa-gamepad', 'Game & Gaming Accounts', 1],
 ['Netflix', 'fa-n', 'Netflix Premium Accounts', 2],
 ['YouTube', 'fa-youtube', 'YouTube Premium & Music', 3],
 ['Spotify', 'fa-spotify', 'Spotify Premium', 4],
 ['Disney+', 'fa-play', 'Disney+ Premium', 5],
 ['GPT / AI', 'fa-robot', 'AI & Productivity Tools', 6],
 ['AI Tools', 'fa-wand-magic-sparkles', 'Design & Creative AI', 7],
 ['Cloud', 'fa-cloud', 'Cloud Storage', 8],
 ['Social', 'fa-share-nodes', 'Social Media', 9],
 ['Khác', 'fa-ellipsis', 'Other Products', 10],
];

$catIdMap = [];
$stmt = mysqli_prepare($conn, "INSERT INTO categories (name, icon_class, description, sort_order) VALUES (?, ?, ?, ?)");
foreach ($categories as $c) {
 mysqli_stmt_bind_param($stmt, "sssi", $c[0], $c[1], $c[2], $c[3]);
 mysqli_stmt_execute($stmt);
 $catIdMap[$c[0]] = mysqli_insert_id($conn);
}
echo "[OK] Đã seed " . count($categories) . " categories\n";

echo "\n[3/4] Seed types...\n";

$types = [
 ['Valorant', 'Game', 'fa-gamepad', 1, 1],
 ['CS2 / CSGO', 'Game', 'fa-gamepad', 1, 2],
 ['Minecraft', 'Game', 'fa-cubes', 1, 3],
 ['Genshin Impact', 'Game', 'fa-dragon', 1, 4],
 ['Liên Quân Mobile', 'Game', 'fa-gamepad', 1, 5],
 ['Free Fire', 'Game', 'fa-fire', 1, 6],
 ['PUBG Mobile', 'Game', 'fa-crosshairs', 1, 7],
 ['Roblox', 'Game', 'fa-robot', 1, 8],
 ['FIFA / EA FC', 'Game', 'fa-futbol', 1, 9],
 ['Netflix Premium', 'Netflix', 'fa-n', 2, 1],
 ['Netflix Standard', 'Netflix', 'fa-n', 2, 2],
 ['YouTube Premium', 'YouTube', 'fa-youtube', 3, 1],
 ['YouTube Music', 'YouTube', 'fa-youtube', 3, 2],
 ['Spotify Premium', 'Spotify', 'fa-spotify', 4, 1],
 ['Disney+ Premium', 'Disney+', 'fa-play', 5, 1],
 ['Prime Video', 'Amazon', 'fa-amazon', 5, 2],
 ['Apple TV+', 'Apple', 'fa-apple', 5, 3],
 ['ChatGPT Plus', 'GPT', 'fa-robot', 6, 1],
 ['Claude Pro', 'GPT', 'fa-robot', 6, 2],
 ['Midjourney', 'AI Tools', 'fa-wand-magic-sparkles', 7, 1],
 ['Canva Pro', 'AI Tools', 'fa-palette', 7, 2],
 ['Notion', 'AI Tools', 'fa-note-sticky', 7, 3],
 ['Google One', 'Cloud', 'fa-cloud', 8, 1],
 ['iCloud+', 'Cloud', 'fa-cloud', 8, 2],
 ['Dropbox', 'Cloud', 'fa-cloud-arrow-up', 8, 3],
 ['Facebook', 'Social', 'fa-facebook', 9, 1],
 ['TikTok', 'Social', 'fa-tiktok', 9, 2],
 ['Twitter / X', 'Social', 'fa-x-twitter', 9, 3],
 ['Instagram', 'Social', 'fa-instagram', 9, 4],
 ['Khác', 'Khác', 'fa-ellipsis', 10, 1],
];

$typeIdMap = [];
$stmt = mysqli_prepare($conn, "INSERT INTO types (name, category, category_id, icon_class, sort_order) VALUES (?, ?, ?, ?, ?)");
foreach ($types as $t) {
 mysqli_stmt_bind_param($stmt, "ssisi", $t[0], $t[1], $t[3], $t[2], $t[4]);
 mysqli_stmt_execute($stmt);
 $typeIdMap[$t[0]] = mysqli_insert_id($conn);
}
echo "[OK] Đã seed " . count($types) . " types\n";

echo "\n[4/4] Seed products...\n";

$products = [
 ['Tài khoản Valorant Rank Bạc 2 - 50 ACC VP', 'Game', 'Valorant', 15000, 25000, 'Hot', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=600&q=80', 'Tài khoản Valorant rank Bạc 2, đã tích lũy 50 acc VP, bảo mật 2 lớp chưa bật.', '{"Rank":"Bạc 2","VP":"50 ACC VP","Tướng":"15/26","Skin":"5 Blade","2FA":"Chưa bật"}', 'bg-danger', 'fa-gamepad'],
 ['Tài khoản Valorant Rank Vàng 1 - Full Tướng', 'Game', 'Valorant', 45000, 60000, 'VIP', 'https://images.unsplash.com/photo-1552820728-8b83bb6b773f?w=600&q=80', 'Tài khoản Valorant rank Vàng 1, sở hữu full tướng, nhiều skin giá trị.', '{"Rank":"Vàng 1","VP":"200 ACC VP","Tướng":"26/26","Skin":"20+ Blade","2FA":"Đã bật"}', 'bg-danger', 'fa-gamepad'],
 ['Tài khoản CS2 Prime - Rank Silver 3', 'Game', 'CS2 / CSGO', 12000, 0, '', 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=600&q=80', 'Tài khoản CS2 Prime Status, rank Silver 3, account mới tạo.', '{"Rank":"Silver 3","Prime":"Có","Hours":"120h","Skins":"2 Blade","VAC":"Sạch"}', 'bg-dark', 'fa-gamepad'],
 ['Tài khoản CS2 Rank Nova 3 - 500h', 'Game', 'CS2 / CSGO', 35000, 50000, 'Deal', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=600&q=80', 'Tài khoản CS2 Nova 3 với 500 giờ chơi, Prime đã active.', '{"Rank":"Nova 3","Prime":"Có","Hours":"500h","Skins":"8 Blade","VAC":"Sạch"}', 'bg-dark', 'fa-gamepad'],
 ['Tài khoản Minecraft Realms Premium 1 Tháng', 'Game', 'Minecraft', 25000, 35000, '', 'https://images.unsplash.com/photo-1587573089734-599d584352eb?w=600&q=80', 'Tài khoản Minecraft Java Edition + Realms Premium 30 ngày, đăng nhập ngay.', '{"Edition":"Java","Realms":"30 ngày","Skin":"Premium","Profile":"Sạch"}', 'bg-success','fa-cubes'],
 ['Tài khoản Genshin Impact AR55 - Có Raiden', 'Game', 'Genshin Impact', 180000,250000,'VIP', 'https://images.unsplash.com/photo-1534423861386-85a16f5d13fd?w=600&q=80', 'Tài khoản Genshin AR55, sở hữu Raiden Shogun C2, 5 sao khác, primogem dồn.', '{"AR":"55","Raiden":"C2","5-Star":"8 Blade","Primogem":"15,000+","Adventure":"Còn"}', 'bg-info', 'fa-dragon'],
 ['Tài khoản Liên Quân Rank Kim Cương 3', 'Game', 'Liên Quân Mobile', 35000, 0, 'Hot', 'https://images.unsplash.com/photo-1511512578047-dfb367046420?w=600&q=80', 'Tài khoản Liên Quân rank Kim Cương 3, tướng đầy đủ, skin SL cao.', '{"Rank":"Kim Cương 3","Tướng":"90+","Skin SL":"10+","Tier":"Pro"}', 'bg-warning','fa-gamepad'],
 ['Tài khoản Free Fire Max OB44 - Rank Huyền Thoại','Game','Free Fire', 80000, 120000,'VIP', 'https://images.unsplash.com/photo-1551103782-8ab07afd45c1?w=600&q=80', 'Tài khoản Free Fire Max rank Huyền Thoại, nhiều skin súng hiếm.', '{"Rank":"Huyền Thoại","Version":"Max OB44","Skins Súng":"15+","Pet":"4"}', 'bg-orange', 'fa-fire'],
 ['Tài khoản Netflix Premium 1 Tháng - 4K HDR', 'Netflix', 'Netflix Premium', 35000, 55000, 'Hot', 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=600&q=80', 'Netflix Premium chất lượng 4K HDR, xem được trên 4 thiết bị cùng lúc.', '{"Chất lượng":"4K HDR","Thiết bị":"4 cùng lúc","Profile":"5/5","Shared":"Có thể"}', 'bg-danger', 'fa-n'],
 ['Tài khoản Netflix Premium 3 Tháng - Tiết kiệm','Netflix', 'Netflix Premium', 90000, 150000,'Deal', 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=600&q=80', 'Gói Netflix Premium 3 tháng, tiết kiệm hơn 40%. Bảo hành full thời gian.', '{"Chất lượng":"4K HDR","Thiết bị":"4 cùng lúc","Profile":"5/5","Thời hạn":"3 tháng"}', 'bg-danger', 'fa-n'],
 ['Tài khoản Netflix Standard 1 Tháng - Full HD', 'Netflix', 'Netflix Standard',25000, 0, '', 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?w=600&q=80', 'Netflix Standard chất lượng Full HD, xem trên 2 thiết bị cùng lúc.', '{"Chất lượng":"Full HD 1080p","Thiết bị":"2 cùng lúc","Profile":"2/2"}', 'bg-danger', 'fa-n'],
 ['Tài khoản YouTube Premium 1 Tháng', 'YouTube', 'YouTube Premium', 18000, 28000, 'Hot', 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=600&q=80', 'YouTube Premium — xem không quảng cáo, tải offline, phát nền.', '{"Loại":"Cá nhân","Quảng cáo":"Không","Tải offline":"Có","Phát nền":"Có","YouTube Music":"Kèm"}', 'bg-danger', 'fa-youtube'],
 ['Tài khoản YouTube Music Premium 6 Tháng', 'YouTube', 'YouTube Music', 55000, 90000, 'Deal', 'https://images.unsplash.com/photo-1614680376573-df3480f0c6ff?w=600&q=80', 'YouTube Music Premium 6 tháng — nghe nhạc không quảng cáo, tải về máy, phát nền.', '{"Loại":"Cá nhân","Thời hạn":"6 tháng","Quảng cáo":"Không","Offline":"Có"}', 'bg-danger', 'fa-youtube'],
 ['Tài khoản Spotify Premium 1 Tháng', 'Spotify', 'Spotify Premium', 15000, 22000, '', 'https://images.unsplash.com/photo-1614680376408-81e91ffe3db7?w=600&q=80', 'Spotify Premium — nghe nhạc chất lượng cao 320kbps, không quảng cáo, skip không giới hạn.', '{"Chất lượng":"320kbps","Quảng cáo":"Không","Skip":"Không giới hạn","Offline":"Có"}', 'bg-dark', 'fa-spotify'],
 ['Tài khoản Spotify Premium Family 6 Tháng', 'Spotify', 'Spotify Premium', 120000,180000,'VIP', 'https://images.unsplash.com/photo-1614680376408-81e91ffe3db7?w=600&q=80', 'Spotify Family — dùng được 6 tháng, tối đa 6 thành viên, quản lý qua dashboard.', '{"Loại":"Family","Thành viên":"6/6","Thời hạn":"6 tháng","Quality":"HiFi Ready"}', 'bg-dark', 'fa-spotify'],
 ['Tài khoản Disney+ Premium 1 Tháng', 'Disney+', 'Disney+ Premium', 25000, 40000, 'Hot', 'https://images.unsplash.com/photo-1618828665011-0a5c4da5c08d?w=600&q=80', 'Disney+ Premium — xem Disney, Marvel, Star Wars, Pixar, National Geographic không quảng cáo.', '{"Chất lượng":"4K HDR","Quảng cáo":"Không","Thiết bị":"4 cùng lúc","Content":"Full"}', 'bg-info', 'fa-play'],
 ['Tài khoản ChatGPT Plus 1 Tháng', 'GPT', 'ChatGPT Plus', 60000, 90000, 'VIP', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=600&q=80', 'ChatGPT Plus — GPT-4o, DALL-E, browsing, Advanced Data Analysis, GPTs tùy chỉnh.', '{"Model":"GPT-4o","DALL-E":"Có","Browsing":"Có","Advanced Analysis":"Có","GPTs":"Có"}', 'bg-success', 'fa-robot'],
 ['Tài khoản Claude Pro 1 Tháng', 'GPT', 'Claude Pro', 80000, 120000,'', 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?w=600&q=80', 'Claude Pro — truy cập Claude 3 Opus, Sonnet, Haiku, claude.ai riêng, usage limit cao.', '{"Models":"3 Opus/Sonnet/Haiku","Context":"200K tokens","Priority":"Cao","Usage":"Unlimited"}', 'bg-warning', 'fa-robot'],
 ['Tài khoản Midjourney 1 Tháng - Standard', 'AI Tools', 'Midjourney', 120000, 150000,'', 'https://images.unsplash.com/photo-1683160735206-8a8c5e2e8e9e?w=600&q=80', 'Midjourney Standard — 15 giờ fast GPU mỗi tháng, unlimited slow generations.', '{"Plan":"Standard","Fast GPU":"15h/tháng","Slow":"Unlimited","Stealth":"Có"}', 'bg-secondary','fa-wand-magic-sparkles'],
 ['Tài khoản Canva Pro 1 Năm', 'AI Tools', 'Canva Pro', 250000, 400000,'Deal', 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?w=600&q=80', 'Canva Pro bản quyền 1 năm — 610,000+ templates, Brand Kit, Background Remover, AI Magic Write.', '{"Templates":"610,000+","Brand Kit":"Có","Magic Write":"Có","Downloads":"Unlimited"}', 'bg-secondary','fa-palette'],
 ['Tài khoản Google One 200GB - 6 Tháng', 'Cloud', 'Google One', 45000, 70000, 'Deal', 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=600&q=80', 'Google One 200GB — lưu trữ Drive, Gmail, Photos không giới hạn, share với 5 người.', '{"Dung lượng":"200GB","Share":"5 người","Drive":"Có","Photos":"Không giới hạn"}', 'bg-light', 'fa-cloud'],
 ['Tài khoản iCloud+ 200GB - 1 Tháng', 'Cloud', 'iCloud+', 22000, 0, '', 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=600&q=80', 'iCloud+ 200GB — lưu trữ iPhone, iPad, MacBook, chia sẻ Family 6 người.', '{"Dung lượng":"200GB","Family":"6 người","Private Relay":"Có","Hide Email":"Có"}', 'bg-primary', 'fa-cloud'],
];

$stmt2 = mysqli_prepare($conn, "INSERT INTO products (title, category, game_type, type_id, price, old_price, badge, image_url, description, details, color_class, icon_class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$count = 0;
foreach ($products as $p) {
 $typeId = isset($typeIdMap[$p[2]]) ? $typeIdMap[$p[2]] : null;
 mysqli_stmt_bind_param($stmt2, "sssiiissssss",
 $p[0], $p[1], $p[2], $typeId, $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10]);
 if (mysqli_stmt_execute($stmt2)) $count++;
}
echo "[OK] Đã seed $count products\n";

echo "\n=== SEED HOÀN TẤT ===\n";
echo "Categories: " . count($categories) . "\n";
echo "Types: " . count($types) . "\n";
echo "Products: $count\n\n";
echo "[Xong] Database sạch, dữ liệu mới đã sẵn sàng!\n";
