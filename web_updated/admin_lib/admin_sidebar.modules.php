<?php
function admin_renderSidebar($currentPage = '')
{
    $navItems = [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fa-gauge',
            'href' => '/admin/dashboard.php',
        ],
        [
            'key' => 'products',
            'label' => 'Sản phẩm',
            'icon' => 'fa-box-open',
            'href' => '/admin/manage/products.php',
        ],
        [
            'key' => 'users',
            'label' => 'Người dùng',
            'icon' => 'fa-users',
            'href' => '/admin/manage/users.php',
        ],
        [
            'key' => 'categories',
            'label' => 'Danh mục & Loại',
            'icon' => 'fa-layer-group',
            'href' => '/admin/manage/categories.php',
        ],
        [
            'key' => 'orders',
            'label' => 'Đơn hàng',
            'icon' => 'fa-receipt',
            'href' => '/admin/manage/orders.php',
            'disabled' => true,
        ],
    ];

    $bottomItems = [
        [
            'key' => 'settings',
            'label' => 'Cài đặt',
            'icon' => 'fa-gear',
            'href' => '/admin/settings.php',
            'disabled' => true,
        ],
        [
            'key' => 'back_to_shop',
            'label' => 'Trở về Shop',
            'icon' => 'fa-store',
            'href' => '/index.php',
            'divider' => true,
        ],
        [
            'key' => 'logout',
            'label' => 'Đăng xuất',
            'icon' => 'fa-right-from-bracket',
            'href' => '/auth/logout.php',
            'class' => 'text-danger',
        ],
    ];

    ob_start();
?>
    <div class="sidebar" style="width: 260px; min-width: 260px;">
        <div class="sidebar-brand">
            <i class="fa-solid fa-ghost me-2"></i>NEXUS
        </div>

        <div class="sidebar-section-label">QUẢN LÝ</div>
        <?php foreach ($navItems as $item): ?>
            <?php if (!empty($item['divider'])): ?>
                <hr class="mx-3 border-secondary">
            <?php endif; ?>
            <a href="<?php echo $item['href']; ?>"
                class="<?php echo ($currentPage === $item['key']) ? 'active' : ''; ?>"
                <?php echo (!empty($item['disabled'])) ? 'onclick="return false;" style="opacity:0.4;cursor:not-allowed;"' : ''; ?>>
                <i class="fa-solid <?php echo $item['icon']; ?> me-2"></i>
                <?php echo htmlspecialchars($item['label']); ?>
                <?php if (!empty($item['disabled'])): ?>
                    <span class="badge bg-secondary ms-auto" style="font-size:0.6rem;">Sắp ra</span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>

        <hr class="mx-3 border-secondary">

        <?php foreach ($bottomItems as $item): ?>
            <?php if (!empty($item['divider'])): ?>
                <hr class="mx-3 border-secondary">
            <?php endif; ?>
            <a href="<?php echo $item['href']; ?>"
                class="<?php echo htmlspecialchars($item['class'] ?? ''); ?>">
                <i class="fa-solid <?php echo $item['icon']; ?> me-2"></i>
                <?php echo htmlspecialchars($item['label']); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
}
