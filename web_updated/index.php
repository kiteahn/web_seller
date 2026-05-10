<?php
session_start();
require_once __DIR__ . '/lib/productsModules.php';
require_once __DIR__ . '/lib/userModules.php'; // Đưa userModules vào để lấy thông tin Balance từ getUserInfo($username)

$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$all_products = getProducts();

// Count by category
$cat_counts = ['all' => count($all_products)];
// Count by type
$type_counts = ['all' => count($all_products)];
foreach ($all_products as $p) {
    $cat = $p['category'];
    if (!isset($cat_counts[$cat])) $cat_counts[$cat] = 0;
    $cat_counts[$cat]++;

    $type = $p['type_name'] ?? '';
    if (!empty($type)) {
        if (!isset($type_counts[$type])) $type_counts[$type] = 0;
        $type_counts[$type]++;
    }
}

// Filter by category + type
$products = [];
foreach ($all_products as $item) {
    $matchCat = ($category_filter == 'all' || $item['category'] == $category_filter);
    $matchType = ($type_filter == 'all' || ($item['type_name'] ?? '') == $type_filter);
    if ($matchCat && $matchType) {
        $products[] = $item;
    }
}

// Search filter
if ($search_query !== '') {
    $q = mb_strtolower($search_query, 'UTF-8');
    $products = array_filter($products, function ($p) use ($q) {
        return mb_strpos(mb_strtolower($p['title'] ?? '', 'UTF-8'), $q) !== false
            || mb_strpos(mb_strtolower($p['category'] ?? '', 'UTF-8'), $q) !== false
            || mb_strpos(mb_strtolower($p['type_name'] ?? '', 'UTF-8'), $q) !== false;
    });
    $products = array_values($products);
}

// Sort
usort($products, function ($a, $b) use ($sort_by) {
    switch ($sort_by) {
        case 'price_asc':
            return $a['price'] <=> $b['price'];
        case 'price_desc':
            return $b['price'] <=> $a['price'];
        case 'name':
            return strcmp($a['title'], $b['title']);
        default:
            return $b['id'] <=> $a['id'];
    }
});

$total_count = count($products);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACC STORE 247 | Premium Market</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-base: #09090b;
            --bg-secondary: #121214;
            --card-base: #18181b;
            --card-hover: #27272a;
            --accent: #fafafa;
            --accent-hover: #e4e4e7;
            --accent-glow: rgba(250, 250, 250, 0.05);
            /* muted glow */
            --text-primary: #fafafa;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-accent: rgba(255, 255, 255, 0.15);
            --green: #10b981;
            --red: #ef4444;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Geist Sans', 'Plus Jakarta Sans', -apple-system, sans-serif;
            background-color: var(--bg-base);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
        }

        /* Navbar */
        .navbar {
            background: rgba(9, 9, 11, 0.85) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-subtle);
            padding: 14px 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--text-primary) !important;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }

        /* Hero */
        .hero-section {
            position: relative;
            padding: 80px 0;
            border-radius: 12px;
            margin-top: 30px;
            overflow: hidden;
            background: url('https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=2071&auto=format&grayscale') center/cover;
            border: 1px solid var(--border-subtle);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(9, 9, 11, 0.4);
            /* dark overlay */
            pointer-events: none;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #09090b 0%, rgba(9, 9, 11, 0.7) 100%);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--card-base);
            border: 1px solid var(--border-subtle);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .hero-title {
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1.15;
            margin: 16px 0;
            letter-spacing: -0.02em;
        }

        .hero-title span {
            color: var(--text-primary);
        }

        .hero-sub {
            color: var(--text-secondary);
            font-size: 1.05rem;
            max-width: 480px;
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }

        .stat-card {
            background: var(--card-base);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 20px 24px;
            transition: 0.3s;
        }

        .stat-card:hover {
            border-color: var(--border-accent);
            transform: translateY(-4px);
            box-shadow: none;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 800;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Controls Bar */
        .controls-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin: 40px 0 24px;
            justify-content: space-between;
        }

        .controls-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            background: var(--card-base);
            border: 1px solid var(--border-subtle);
            border-radius: 50px;
            padding: 9px 16px 9px 42px;
            color: var(--text-primary);
            font-size: 0.9rem;
            width: 260px;
            transition: 0.3s;
            outline: none;
        }

        .search-box input:focus {
            border-color: var(--accent);
            box-shadow: none;
        }

        .search-box input::placeholder {
            color: var(--text-muted);
        }

        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .sort-select {
            background: var(--card-base);
            border: 1px solid var(--border-subtle);
            border-radius: 50px;
            padding: 9px 16px;
            color: var(--text-primary);
            font-size: 0.9rem;
            outline: none;
            transition: 0.3s;
            cursor: pointer;
        }

        .sort-select:focus {
            border-color: var(--accent);
        }

        /* Filter Pills */
        .filter-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-subtle);
            border-radius: 50px;
            padding: 8px 20px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.88rem;
            transition: 0.3s;
        }

        .filter-pill:hover {
            background: rgba(255, 255, 255, 0.04);
            color: white;
            border-color: rgba(255, 255, 255, 0.15);
        }

        .filter-pill.active {
            background: rgba(110, 86, 207, 0.12);
            color: #A78BFA;
            border-color: var(--accent);
        }

        .filter-count {
            font-size: 0.72rem;
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 7px;
            border-radius: 50px;
        }

        /* Product Grid */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-count-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Product Card */
        .product-card {
            background: var(--card-base);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            overflow: hidden;
            transition: 0.3s;
            height: 100%;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-accent);
        }

        .card-img-box {
            position: relative;
            padding: 12px 12px 0;
            overflow: hidden;
            z-index: 1;
        }

        .card-img-box img {
            border-radius: 8px;
            height: 185px;
            width: 100%;
            object-fit: cover;
        }

        .badge-premium {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 2;
            background: var(--card-hover);
            border: 1px solid var(--border-subtle);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .card-body {
            padding: 16px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            position: relative;
            z-index: 1;
        }

        .item-category {
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            color: var(--text-muted);
        }

        .item-title {
            font-weight: 600;
            font-size: 0.95rem;
            line-height: 1.4;
            margin: 0 0 12px;
        }

        .item-title a {
            color: var(--text-primary);
            transition: color 0.2s;
        }

        .item-title a:hover {
            color: #A78BFA;
        }

        .details-list {
            list-style: none;
            padding: 0;
            margin: 0 0 auto;
            font-size: 0.82rem;
        }

        .details-list li {
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
        }

        .details-list li span {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid var(--border-subtle);
        }

        .price-info {}

        .old-price {
            font-size: 0.82rem;
            color: var(--text-muted);
            text-decoration: line-through;
        }

        .price-box {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }

        .btn-buy {
            background: var(--text-primary);
            color: var(--bg-base) !important;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            padding: 10px 18px;
            font-size: 0.85rem;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-buy:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .btn-cart {
            background: transparent;
            color: var(--text-primary);
            border: 1px solid var(--border-subtle);
            border-radius: 6px;
            padding: 10px 14px;
            transition: 0.2s;
        }

        .btn-cart:hover {
            background: var(--card-hover);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-secondary);
        }

        .empty-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Quick View Overlay */
        .product-card .quick-view {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(9, 9, 11, 0.95) 0%, transparent 100%);
            padding: 40px 20px 16px;
            z-index: 3;
            opacity: 0;
            transform: translateY(10px);
            transition: 0.3s;
            display: flex;
            gap: 8px;
        }

        .product-card:hover .quick-view {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
                margin-top: -20px;
            }

            .controls-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box input {
                width: 100%;
            }

            .filter-group {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fa-solid fa-ghost me-2"></i>NEXUS STORE</a>

            <div class="d-flex align-items-center ms-auto">
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <a href="admin" class="text-danger text-decoration-none me-3 fw-bold"><i class="fa-solid fa-shield-halved"></i> Quản trị</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['username'])):
                    $balance = getBalance($_SESSION['username']);
                ?>
                    <div class="dropdown">
                        <button class="btn px-4 dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false"
                            style="background: var(--card-base); color: var(--text-primary); border: 1px solid var(--border-subtle); border-radius: 6px; font-weight: 500;">
                            <i class="fa-regular fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-sm" aria-labelledby="userMenu" style="background: var(--card-base); border: 1px solid var(--border-subtle);">
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-wallet text-warning me-2"></i> Số dư: <?php echo number_format($balance, 0, ',', '.'); ?>đ</a></li>
                            <li>
                                <hr class="dropdown-divider" style="border-color: var(--border-subtle);">
                            </li>
                            <li><a class="dropdown-item text-danger" href="auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="auth/login.php" class="btn px-4" style="background: var(--text-primary); color: var(--bg-base); border: none; border-radius: 6px; font-weight: 600;">
                        <i class="fa-regular fa-user me-1"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <!-- Hero Banner -->
        <div class="hero-section">
            <div class="hero-overlay"></div>
            <div class="hero-content px-5">
                <div class="row align-items-center">
                    <div class="col-lg-7 py-4">
                        <div class="hero-badge">
                            <i class="fa-solid fa-sparkles"></i> Chợ tài khoản chuyên nghiệp nhất VN
                        </div>
                        <h1 class="hero-title">
                            Sống trọn đam mê<br>
                            <span>Giao dịch an toàn.</span>
                        </h1>
                        <p class="hero-sub">Hàng trăm tài khoản chất lượng cao, giao dịch nhanh chóng qua ví tích hợp, bảo mật tuyệt đối 100%.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(110,86,207,0.15); color: #A78BFA;"><i class="fa-solid fa-layer-group"></i></div>
                <div class="stat-value"><?php echo $cat_counts['all']; ?></div>
                <div class="stat-label">Tổng sản phẩm</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239,68,68,0.15); color: #F87171;"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="stat-value"><?php echo isset($cat_counts['Valorant']) ? $cat_counts['Valorant'] : 0; ?></div>
                <div class="stat-label">Valorant</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: #34D399;"><i class="fa-brands fa-steam"></i></div>
                <div class="stat-value"><?php echo isset($cat_counts['Steam']) ? $cat_counts['Steam'] : 0; ?></div>
                <div class="stat-label">Steam</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(56,189,248,0.15); color: #38BDF8;"><i class="fa-solid fa-users"></i></div>
                <div class="stat-value"><?php echo isset($cat_counts['Mạng xã hội']) ? $cat_counts['Mạng xã hội'] : 0; ?></div>
                <div class="stat-label">Social MXH</div>
            </div>
        </div>

        <!-- Controls Bar -->
        <div class="controls-bar">
            <div class="controls-left">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <select class="sort-select" id="sortSelect" style="background: var(--card-base); color: var(--text-primary);">
                    <option value="newest" <?php echo ($sort_by == 'newest') ? 'selected' : ''; ?>>Mới nhất</option>
                    <option value="price_asc" <?php echo ($sort_by == 'price_asc') ? 'selected' : ''; ?>>Giá: Thấp → Cao</option>
                    <option value="price_desc" <?php echo ($sort_by == 'price_desc') ? 'selected' : ''; ?>>Giá: Cao → Thấp</option>
                    <option value="name" <?php echo ($sort_by == 'name') ? 'selected' : ''; ?>>Tên A → Z</option>
                </select>
            </div>
            <div class="filter-group">
                <a href="?category=all" class="filter-pill <?php echo ($category_filter == 'all') ? 'active' : ''; ?>">
                    Tất cả <span class="filter-count"><?php echo $cat_counts['all']; ?></span>
                </a>
                <?php foreach ($cat_counts as $cat => $cnt): if ($cat === 'all') continue; ?>
                    <a href="?category=<?php echo urlencode($cat); ?>" class="filter-pill <?php echo ($category_filter == $cat) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat); ?> <span class="filter-count"><?php echo $cnt; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php // Type filter — chỉ hiện khi có loại 
            ?>
            <?php if (count($type_counts) > 1): ?>
                <div class="filter-group mt-2">
                    <span style="font-size:0.75rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-right:4px;">Loại:</span>
                    <a href="?category=<?php echo urlencode($category_filter); ?>&type=all" class="filter-pill <?php echo ($type_filter == 'all') ? 'active' : ''; ?>" style="padding:6px 14px;font-size:0.8rem;">
                        Tất cả
                    </a>
                    <?php foreach ($type_counts as $type => $cnt): if ($type === 'all') continue; ?>
                        <a href="?category=<?php echo urlencode($category_filter); ?>&type=<?php echo urlencode($type); ?>" class="filter-pill <?php echo ($type_filter == $type) ? 'active' : ''; ?>" style="padding:6px 14px;font-size:0.8rem;">
                            <i class="fa-solid fa-tag me-1" style="font-size:0.7rem;"></i><?php echo htmlspecialchars($type); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Grid -->
        <div class="section-header">
            <div class="section-title">
                <i class="fa-solid fa-bolt text-warning"></i> Cửa Hàng
                <span class="product-count-label">— <?php echo $total_count; ?> sản phẩm</span>
            </div>
        </div>

        <div class="row g-4" id="productGrid">
            <?php
            if (count($products) > 0) {
                foreach ($products as $row) {
                    $detailsHTML = "";
                    if ($row['details'] != "") {
                        $detailsArr = json_decode($row['details'], true);
                        if (is_array($detailsArr)) {
                            foreach ($detailsArr as $key => $val) {
                                $detailsHTML .= "<li>{$key} <span>{$val}</span></li>";
                            }
                        }
                    }
                    $priceStr = number_format($row['price'], 0, ',', '.') . 'đ';
                    $oldPriceStr = ($row['old_price'] > 0) ? "<div class='old-price'>" . number_format($row['old_price'], 0, ',', '.') . "đ</div>" : "";
                    $badgeHTML = ($row['badge'] != "") ? "<span class='badge-premium'>{$row['badge']}</span>" : "";
            ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="product-card">
                            <div class="card-img-box">
                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" loading="lazy">
                                <?php echo $badgeHTML; ?>
                                <div class="quick-view">
                                    <a href="products/index.php?id=<?php echo $row['id']; ?>" class="btn-buy" style="flex:1; justify-content:center;">
                                        <i class="fa-solid fa-eye"></i> Xem chi tiết
                                    </a>
                                    <button class="btn-cart"><i class="fa-solid fa-cart-arrow-down"></i></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="item-category <?php echo htmlspecialchars($row['color_class']); ?>">
                                    <i class="fa-brands <?php echo htmlspecialchars($row['icon_class']); ?> me-1"></i> <?php echo htmlspecialchars($row['category']); ?>
                                    <?php if (!empty($row['type_name'])): ?>
                                        <span class="badge ms-1" style="background:rgba(255,255,255,0.05);color:var(--text-primary);border: 1px solid var(--border-subtle);font-size:0.65rem;font-weight:500;padding:2px 8px;border-radius:6px;">
                                            <i class="fa-solid <?php echo htmlspecialchars($row['type_icon'] ?? 'fa-tag'); ?> me-1"></i><?php echo htmlspecialchars($row['type_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="item-title">
                                    <a href="products/index.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                                </h5>

                                <div class="p-3" style="background: var(--card-base); border-top: 1px solid var(--border-subtle); border-bottom: 1px solid var(--border-subtle);">
                                    <ul class="details-list"><?php echo $detailsHTML; ?></ul>
                                </div>

                                <div class="card-footer">
                                    <div class="price-info">
                                        <?php echo $oldPriceStr; ?>
                                        <div class="price-box"><?php echo $priceStr; ?></div>
                                    </div>
                                    <a href="products/index.php?id=<?php echo $row['id']; ?>" class="btn-buy">
                                        <i class="fa-solid fa-bolt"></i> Mua ngay
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="empty-title">Không tìm thấy sản phẩm nào</div>
                        <div class="empty-desc">Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm khác nhé!</div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search with debounce
        let searchTimer;
        const searchInput = document.getElementById('searchInput');
        const sortSelect = document.getElementById('sortSelect');

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                updateURL();
            }, 400);
        });

        sortSelect.addEventListener('change', updateURL);

        function updateURL() {
            const params = new URLSearchParams(window.location.search);
            const q = searchInput.value.trim();
            const sort = sortSelect.value;
            const cat = params.get('category') || 'all';

            params.set('category', cat);
            if (q) params.set('q', q);
            else params.delete('q');
            if (sort !== 'newest') params.set('sort', sort);
            else params.delete('sort');

            window.location.search = params.toString();
        }
    </script>
</body>

</html>