<?php
session_start();
require_once __DIR__ . '/../lib/productsModules.php';
require_once __DIR__ . '/../lib/userModules.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$all_products = getProducts();
$product = null;
foreach ($all_products as $p) {
    if (intval($p['id']) === $product_id) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: /index.php');
    exit;
}

$detailsArr = [];
if ($product['details'] != "") {
    $decoded = json_decode($product['details'], true);
    if (is_array($decoded)) $detailsArr = $decoded;
}

$related = [];
foreach ($all_products as $p) {
    if (intval($p['id']) !== $product_id && $p['category'] === $product['category']) {
        $related[] = $p;
    }
}
$related = array_slice($related, 0, 4);

$priceStr = number_format($product['price'], 0, ',', '.') . 'đ';
$oldPriceStr = ($product['old_price'] > 0) ? number_format($product['old_price'], 0, ',', '.') . 'đ' : '';
$badgeText = ($product['badge'] != "") ? htmlspecialchars($product['badge']) : "";
$discount = ($product['old_price'] > 0 && $product['price'] > 0)
    ? round((1 - $product['price'] / $product['old_price']) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> | NEXUS STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg: #09090b;
            --bg2: #121214;
            --card: #18181b;
            --card2: #27272a;
            --accent: #fafafa;
            --accent-light: #a1a1aa;
            --accent-dim: rgba(250, 250, 250, 0.1);
            --text1: #fafafa;
            --text2: #a1a1aa;
            --text3: #71717a;
            --border: rgba(255, 255, 255, 0.08);
            --border2: rgba(255, 255, 255, 0.12);
            --green: #10b981;
            --green-dim: rgba(16, 185, 129, 0.1);
            --red: #ef4444;
            --amber: #f59e0b;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Geist Sans', 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text1);
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ::selection {
            background: var(--accent);
            color: #fff;
        }

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--card2);
            border-radius: 3px;
        }

        /* Nav */
        .navbar {
            background: rgba(9, 9, 11, 0.85) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 14px 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--text1) !important;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }

        .nav-btn {
            background: var(--card2);
            color: var(--text1) !important;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 18px;
            font-size: 0.85rem;
            transition: 0.2s;
        }

        .nav-btn:hover {
            background: var(--accent);
            color: var(--bg) !important;
            transform: scale(0.98);
        }

        /* Page */
        .page {
            padding: 28px 0 80px;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0 0 32px;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .breadcrumb a {
            color: var(--text3);
            transition: 0.2s;
        }

        .breadcrumb a:hover {
            color: var(--accent-light);
        }

        .breadcrumb-sep {
            color: var(--text3);
            font-size: 0.65rem;
        }

        .breadcrumb span {
            color: var(--text2);
        }

        /* Layout */
        .layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 48px;
            align-items: start;
        }

        @media (max-width: 1080px) {
            .layout {
                grid-template-columns: 1fr 360px;
                gap: 32px;
            }
        }

        @media (max-width: 992px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        /* LEFT */
        .cat-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 5px 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            border: 1px solid var(--border);
            color: var(--text2);
            background: var(--card);
        }

        /* Image Gallery */
        .gallery-container {
            margin-bottom: 24px;
        }

        .img-frame {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: var(--card);
            border: 1px solid var(--border);
            aspect-ratio: 16/10;
        }

        .img-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge-vip {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 2;
            background: var(--card2);
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--text1);
        }

        .badge-discount {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 2;
            background: var(--text1);
            color: var(--bg);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.65rem;
            font-weight: 700;
        }

        /* Thumbnails */
        .thumb-strip {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: none;
        }

        .thumb {
            width: 76px;
            height: 54px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            cursor: pointer;
            flex-shrink: 0;
            transition: 0.2s;
            background: var(--card);
        }

        .thumb:hover {
            border-color: var(--border2);
        }

        .thumb.active {
            border-color: var(--text1);
        }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Specs Grid */
        .sec-label {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text3);
            font-weight: 600;
            margin: 32px 0 16px;
        }

        .sec-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        @media (max-width: 600px) {
            .specs-grid {
                grid-template-columns: 1fr;
            }
        }

        .spec-chip {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .spec-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: var(--card2);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text2);
        }

        .spec-info {
            min-width: 0;
        }

        .spec-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text3);
            margin-bottom: 2px;
            font-weight: 500;
        }

        .spec-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text1);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Feature Row */
        .feat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        @media (max-width: 768px) {
            .feat-grid {
                grid-template-columns: 1fr;
            }
        }

        .feat-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 14px;
        }

        .feat-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: var(--card2);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text2);
        }

        .feat-item .feat-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text1);
        }

        .feat-item .feat-sub {
            font-size: 0.65rem;
            color: var(--text3);
        }

        /* Description */
        .desc-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
            line-height: 1.6;
        }

        .desc-text {
            font-size: 0.9rem;
            color: var(--text2);
        }

        .desc-text strong {
            color: var(--text1);
            font-weight: 600;
        }

        /* RIGHT Panel */
        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 32px;
            position: sticky;
            top: 76px;
        }

        .panel-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .panel-cat {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid var(--border);
            color: var(--text2);
            background: var(--bg);
        }

        .stock-pill {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.7rem;
            color: var(--text2);
            font-weight: 500;
        }

        .stock-pill::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
        }

        .panel-title {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 6px;
            color: var(--text1);
            letter-spacing: -0.02em;
        }

        .panel-id {
            font-size: 0.7rem;
            color: var(--text3);
            font-family: 'Geist Mono', monospace;
            letter-spacing: 0.5px;
            margin-bottom: 24px;
        }

        /* Price */
        .price-box {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
            position: relative;
        }

        .price-original {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .price-original-label {
            font-size: 0.72rem;
            color: var(--text3);
        }

        .price-original-val {
            font-size: 0.95rem;
            color: var(--text3);
            text-decoration: line-through;
        }

        .price-tag {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--green);
            line-height: 1;
            letter-spacing: -1px;
        }

        .price-savings {
            display: inline-block;
            margin-left: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(239, 68, 68, 0.12);
            color: var(--red);
            padding: 3px 9px;
            border-radius: 6px;
            vertical-align: middle;
        }

        .price-note {
            font-size: 0.72rem;
            color: var(--text3);
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .price-note i {
            color: var(--green);
        }

        /* Quantity */
        .qty-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .qty-label {
            font-size: 0.8rem;
            color: var(--text2);
            font-weight: 600;
        }

        .qty-control {
            display: flex;
            align-items: center;
            gap: 0;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            color: var(--text2);
            cursor: pointer;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .qty-btn:hover {
            background: var(--card2);
            color: var(--text1);
        }

        .qty-val {
            width: 40px;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text1);
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            line-height: 36px;
        }

        /* Buy */
        .btn-buy {
            width: 100%;
            background: var(--text1);
            color: var(--bg);
            border: none;
            border-radius: 6px;
            font-weight: 600;
            padding: 14px 24px;
            font-size: 0.9rem;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
            cursor: pointer;
        }

        .btn-buy:hover {
            transform: scale(0.98);
            opacity: 0.9;
        }

        .btn-buy i {
            font-size: 1rem;
        }

        .btn-cart {
            width: 100%;
            background: transparent;
            color: var(--text1);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-weight: 500;
            padding: 13px 24px;
            font-size: 0.9rem;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-cart:hover {
            background: var(--card2);
        }

        .login-note {
            margin-top: 14px;
            padding: 14px;
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 6px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text2);
        }

        .login-note a {
            color: var(--accent-light);
            font-weight: 600;
        }

        /* Details */
        .panel-div {
            height: 1px;
            background: var(--border);
            margin: 18px 0;
        }

        .detail-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text3);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.84rem;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-key {
            color: var(--text3);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .detail-key i {
            font-size: 0.7rem;
            color: var(--text3);
        }

        .detail-val {
            color: var(--text1);
            font-weight: 600;
        }

        /* Trust */
        .trust-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 18px;
        }

        .trust-cell {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            text-align: center;
            padding: 14px 8px;
            background: var(--bg2);
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .trust-cell i {
            color: var(--text1);
            font-size: 1.1rem;
        }

        .trust-cell span {
            font-size: 0.65rem;
            color: var(--text3);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Related */
        .related {
            margin-top: 60px;
        }

        .related-title {
            font-size: 1.15rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 24px;
            color: var(--text1);
        }

        .related-title i {
            color: var(--text2);
        }

        .rel-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        @media (max-width: 1200px) {
            .rel-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .rel-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .rel-grid {
                grid-template-columns: 1fr;
            }
        }

        .rel-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            display: block;
            transition: 0.3s;
        }

        .rel-card:hover {
            border-color: var(--border2);
            transform: translateY(-2px);
        }

        .rel-img {
            aspect-ratio: 16/10;
            overflow: hidden;
            background: var(--bg2);
        }

        .rel-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rel-body {
            padding: 14px;
        }

        .rel-cat {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: var(--text3);
            margin-bottom: 6px;
        }

        .rel-name {
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 8px;
            color: var(--text1);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rel-price {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text1);
        }

        /* Footer */
        .site-footer {
            margin-top: 80px;
            border-top: 1px solid var(--border);
            padding: 32px 0;
            background: var(--bg);
        }

        .footer-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-brand {
            font-weight: 700;
            color: var(--text1);
            font-size: 1.2rem;
            letter-spacing: -0.5px;
        }

        .footer-text {
            font-size: 0.8rem;
            color: var(--text3);
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-links a {
            font-size: 0.8rem;
            color: var(--text3);
            transition: 0.2s;
        }

        .footer-links a:hover {
            color: var(--accent-light);
        }

        /* Animations */
        @keyframes in {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .anim-in {
            animation: in 0.45s ease forwards;
            opacity: 0;
        }

        .anim-in:nth-child(1) {
            animation-delay: 0s;
            opacity: 0;
        }

        .anim-in:nth-child(2) {
            animation-delay: 0.07s;
            opacity: 0;
        }

        .anim-in:nth-child(3) {
            animation-delay: 0.14s;
            opacity: 0;
        }

        .anim-in:nth-child(4) {
            animation-delay: 0.21s;
            opacity: 0;
        }

        .fade-up {
            animation: fadeUp 0.4s ease forwards;
            opacity: 0;
        }

        .fade-up:nth-child(1) {
            animation-delay: 0.25s;
        }

        .fade-up:nth-child(2) {
            animation-delay: 0.32s;
        }

        .fade-up:nth-child(3) {
            animation-delay: 0.39s;
        }

        .fade-up:nth-child(4) {
            animation-delay: 0.46s;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .page {
                padding: 20px 0 60px;
            }

            .breadcrumb {
                margin-bottom: 20px;
            }

            .img-frame {
                border-radius: 16px;
                aspect-ratio: 16/11;
            }

            .panel {
                border-radius: 16px;
                padding: 20px;
                position: static;
            }

            .panel-title {
                font-size: 1.2rem;
            }

            .price-tag {
                font-size: 1.75rem;
            }

            .trust-strip {
                grid-template-columns: repeat(3, 1fr);
            }

            .related {
                margin-top: 40px;
            }

            .footer-inner {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Nav -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><i class="fa-solid fa-ghost me-2"></i>NEXUS STORE</a>
            <div class="d-flex align-items-center ms-auto gap-3">
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <a href="../admin" class="text-danger fw-bold" style="font-size:0.85rem;"><i class="fa-solid fa-shield-halved me-1"></i> Quản trị</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['username'])):
                    $balance = getBalance($_SESSION['username']);
                ?>
                    <div class="dropdown">
                        <button class="nav-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-sm" style="background:var(--card); border:1px solid var(--border);">
                            <li><a class="dropdown-item" href="#"><i class="fa-solid fa-wallet text-warning me-2"></i> Số dư: <?php echo number_format($balance, 0, ',', '.'); ?>đ</a></li>
                            <li>
                                <hr class="dropdown-divider" style="border-color:var(--border);">
                            </li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="../auth/login.php" class="nav-btn"><i class="fa-regular fa-user me-1"></i> Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page">
            <!-- Breadcrumb -->
            <div class="breadcrumb anim-in">
                <a href="../index.php"><i class="fa-solid fa-house me-1"></i> Trang chủ</a>
                <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                <a href="../index.php">Cửa hàng</a>
                <i class="fa-solid fa-chevron-right breadcrumb-sep"></i>
                <span><?php echo htmlspecialchars($product['title']); ?></span>
            </div>

            <div class="layout">
                <!-- LEFT -->
                <div>
                    <div class="cat-tag <?php echo htmlspecialchars($product['color_class']); ?> anim-in">
                        <i class="fa-brands <?php echo htmlspecialchars($product['icon_class']); ?>"></i>
                        <?php echo htmlspecialchars($product['category']); ?>
                        <?php if (!empty($product['type_name'])): ?>
                            <span class="badge" style="background:var(--border);color:var(--text1);font-size:0.65rem;padding:3px 8px;border-radius:4px;font-weight:600;border:1px solid var(--border);">
                                <i class="fa-solid <?php echo htmlspecialchars($product['type_icon'] ?? 'fa-tag'); ?> me-1"></i><?php echo htmlspecialchars($product['type_name']); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="gallery-container anim-in">
                        <div class="img-frame" id="mainImage">
                            <?php if ($badgeText): ?>
                                <span class="badge-vip"><i class="fa-solid fa-crown me-1"></i><?php echo $badgeText; ?></span>
                            <?php endif; ?>
                            <?php if ($discount > 0): ?>
                                <span class="badge-discount">-<?php echo $discount; ?>%</span>
                            <?php endif; ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" id="mainImg" />
                        </div>
                        <div class="thumb-strip">
                            <div class="thumb active" onclick="changeImage(this, '<?php echo htmlspecialchars($product['image_url']); ?>')">
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Thumb 1" />
                            </div>
                            <?php
                            $thumbs = [];
                            if (!empty($product['gallery'])) {
                                $g = json_decode($product['gallery'], true);
                                if (is_array($g)) $thumbs = $g;
                            }
                            foreach ($thumbs as $i => $t): ?>
                                <div class="thumb" onclick="changeImage(this, '<?php echo htmlspecialchars($t); ?>')">
                                    <img src="<?php echo htmlspecialchars($t); ?>" alt="Thumb <?php echo $i + 2; ?>" />
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (!empty($product['description'])): ?>
                        <div class="sec-label">Mô tả</div>
                        <div class="desc-card anim-in">
                            <p class="desc-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (count($detailsArr) > 0): ?>
                        <div class="sec-label">Thông số kỹ thuật</div>
                        <div class="specs-grid">
                            <?php
                            $specIcons = [
                                'cpu' => 'fa-microchip',
                                'ram' => 'fa-memory',
                                'storage' => 'fa-database',
                                'gpu' => 'fa-desktop',
                                'battery' => 'fa-battery-full',
                                'display' => 'fa-desktop',
                                'os' => 'fa-brands fa-windows',
                                'network' => 'fa-wifi',
                                'weight' => 'fa-scale',
                                'size' => 'fa-ruler',
                                'color' => 'fa-palette',
                                'warranty' => 'fa-shield-halved',
                                'port' => 'fa-plug',
                                'camera' => 'fa-camera',
                                'audio' => 'fa-volume-high',
                            ];
                            $iconIdx = 0;
                            $defaultIcons = ['fa-server', 'fa-gear', 'fa-box', 'fa-layer-group', 'fa-cube', 'fa-puzzle-piece', 'fa-bolt', 'fa-star'];
                            foreach ($detailsArr as $key => $val):
                                $slug = strtolower(str_replace([' ', '-'], '', $key));
                                $icon = isset($specIcons[$slug]) ? $specIcons[$slug] : $defaultIcons[$iconIdx % count($defaultIcons)];
                                $iconIdx++;
                            ?>
                                <div class="spec-chip fade-up">
                                    <div class="spec-icon"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                                    <div class="spec-info">
                                        <div class="spec-label"><?php echo htmlspecialchars($key); ?></div>
                                        <div class="spec-value"><?php echo htmlspecialchars($val); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="sec-label">Cam kết dịch vụ</div>
                    <div class="feat-grid">
                        <div class="feat-item anim-in">
                            <div class="feat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                            <div>
                                <div class="feat-title">Bảo hành 7 ngày</div>
                                <div class="feat-sub">Đổi trả nếu lỗi</div>
                            </div>
                        </div>
                        <div class="feat-item anim-in">
                            <div class="feat-icon"><i class="fa-solid fa-bolt"></i></div>
                            <div>
                                <div class="feat-title">Giao tức thì</div>
                                <div class="feat-sub">Nhận tài khoản ngay</div>
                            </div>
                        </div>
                        <div class="feat-item anim-in">
                            <div class="feat-icon"><i class="fa-solid fa-headset"></i></div>
                            <div>
                                <div class="feat-title">Hỗ trợ 24/7</div>
                                <div class="feat-sub">Luôn sẵn sàng</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT Panel -->
                <div class="panel anim-in" style="animation-delay:0.08s;">
                    <div class="panel-meta">
                        <span class="panel-cat <?php echo htmlspecialchars($product['color_class']); ?>">
                            <i class="fa-brands <?php echo htmlspecialchars($product['icon_class']); ?>"></i>
                            <?php echo htmlspecialchars($product['category']); ?>
                        </span>
                        <?php if (!empty($product['type_name'])): ?>
                            <span class="badge" style="background:var(--border);color:var(--text1);border:1px solid var(--border);padding:2px 8px;border-radius:4px;font-size:0.65rem;font-weight:600;">
                                <i class="fa-solid <?php echo htmlspecialchars($product['type_icon'] ?? 'fa-tag'); ?> me-1"></i><?php echo htmlspecialchars($product['type_name']); ?>
                            </span>
                        <?php endif; ?>
                        <span class="stock-pill">Còn hàng</span>
                    </div>

                    <h1 class="panel-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                    <div class="panel-id">ID #<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></div>

                    <div class="price-box">
                        <?php if ($oldPriceStr): ?>
                            <div class="price-original">
                                <span class="price-original-label">Giá gốc</span>
                                <span class="price-original-val"><?php echo $oldPriceStr; ?></span>
                                <?php if ($discount > 0): ?>
                                    <span style="background:var(--red); color:var(--bg); font-size:0.65rem; font-weight:700; padding:2px 8px; border-radius:4px;">-<?php echo $discount; ?>%</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <span class="price-tag"><?php echo $priceStr; ?></span>
                            <?php if ($discount > 0 && $product['old_price'] > 0): ?>
                                <span class="price-savings"><i class="fa-solid fa-tags me-1"></i>Tiết kiệm <?php echo number_format($product['old_price'] - $product['price'], 0, ',', '.'); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div class="price-note"><i class="fa-solid fa-check-circle"></i> Đã bao gồm phí chuyển giao</div>
                    </div>

                    <div class="qty-row">
                        <span class="qty-label">Số lượng</span>
                        <div class="qty-control">
                            <button class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                            <div class="qty-val" id="qtyVal">1</div>
                            <button class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>

                    <a href="#" class="btn-buy" id="buyBtn">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Mua ngay — <?php echo $priceStr; ?></span>
                    </a>
                    <a href="#" class="btn-cart">
                        <i class="fa-solid fa-cart-arrow-down"></i>
                        <span>Thêm vào giỏ hàng</span>
                    </a>

                    <?php if (!isset($_SESSION['username'])): ?>
                        <div class="login-note">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Cần <a href="../auth/login.php">đăng nhập</a> để mua hàng
                        </div>
                    <?php endif; ?>

                    <div class="panel-div"></div>

                    <div class="detail-label">Chi tiết sản phẩm</div>
                    <div class="detail-row">
                        <span class="detail-key"><i class="fa-solid fa-layer-group"></i> Mã sản phẩm</span>
                        <span class="detail-val">#<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-key"><i class="fa-solid fa-folder"></i> Danh mục</span>
                        <span class="detail-val"><?php echo htmlspecialchars($product['category']); ?></span>
                    </div>
                    <?php if (!empty($product['type_name'])): ?>
                        <div class="detail-row">
                            <span class="detail-key"><i class="fa-solid fa-tag"></i> Loại</span>
                            <span class="detail-val" style="color:var(--text1); font-weight: 500; font-size: 0.8rem; border: 1px solid var(--border); padding: 1px 6px; border-radius: 4px;"><?php echo htmlspecialchars($product['type_name']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="detail-key"><i class="fa-solid fa-circle-check"></i> Tình trạng</span>
                        <span class="detail-val" style="color:var(--green);">Còn hàng</span>
                    </div>
                    <?php foreach ($detailsArr as $key => $val): ?>
                        <div class="detail-row">
                            <span class="detail-key"><?php echo htmlspecialchars($key); ?></span>
                            <span class="detail-val"><?php echo htmlspecialchars($val); ?></span>
                        </div>
                    <?php endforeach; ?>

                    <div class="trust-strip">
                        <div class="trust-cell"><i class="fa-solid fa-lock"></i><span>Bảo mật</span></div>
                        <div class="trust-cell"><i class="fa-solid fa-rotate-left"></i><span>Đổi trả</span></div>
                        <div class="trust-cell"><i class="fa-solid fa-rocket"></i><span>Tức thì</span></div>
                    </div>
                </div>
            </div>

            <!-- Related -->
            <?php if (count($related) > 0): ?>
                <div class="related">
                    <h2 class="related-title">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        Có thể bạn cũng thích
                    </h2>
                    <div class="rel-grid">
                        <?php foreach ($related as $r): ?>
                            <a href="index.php?id=<?php echo $r['id']; ?>" class="rel-card">
                                <div class="rel-img">
                                    <img src="<?php echo htmlspecialchars($r['image_url']); ?>" alt="<?php echo htmlspecialchars($r['title']); ?>">
                                </div>
                                <div class="rel-body">
                                    <div class="rel-cat">
                                        <i class="fa-brands <?php echo htmlspecialchars($r['icon_class']); ?>"></i>
                                        <?php echo htmlspecialchars($r['category']); ?>
                                        <?php if (!empty($r['type_name'])): ?>
                                            <span class="badge ms-1" style="background:var(--border);color:var(--text1);font-size:0.6rem;font-weight:500;padding:2px 6px;border-radius:4px;border:1px solid var(--border);"><?php echo htmlspecialchars($r['type_name']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="rel-name"><?php echo htmlspecialchars($r['title']); ?></div>
                                    <div class="rel-price"><?php echo number_format($r['price'], 0, ',', '.'); ?>đ</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="container">
        <footer class="site-footer">
            <div class="footer-inner">
                <span class="footer-brand"><i class="fa-solid fa-ghost me-2"></i>NEXUS STORE</span>
                <span class="footer-text">© <?php echo date('Y'); ?> NEXUS STORE. Mọi quyền được bảo lưu.</span>
                <div class="footer-links">
                    <a href="#">Điều khoản</a>
                    <a href="#">Bảo mật</a>
                    <a href="#">Liên hệ</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let qty = 1;
        const price = <?php echo $product['price']; ?>;
        const priceFormatted = '<?php echo $priceStr; ?>';

        function changeQty(delta) {
            qty = Math.max(1, Math.min(99, qty + delta));
            document.getElementById('qtyVal').textContent = qty;
            const total = (price * qty).toLocaleString('vi-VN');
            document.getElementById('buyBtn').querySelector('span').textContent = 'Mua ngay — ' + total + 'đ';
        }

        function changeImage(el, src) {
            document.getElementById('mainImg').src = src;
            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }
    </script>
</body>

</html>