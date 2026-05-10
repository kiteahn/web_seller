<?php
require_once __DIR__ . '/admin_sidebar.modules.php';

function admin_renderLayout($title, $currentPage, $content = null)
{
    $content = $content ?? ($GLOBALS['content'] ?? '');
    $sidebar = admin_renderSidebar($currentPage);
?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> — NEXUS Admin</title>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background-color: #f4f6f9;
            }

            ::selection {
                background: #6E56CF;
                color: #fff;
            }

            ::-webkit-scrollbar {
                width: 5px;
            }

            ::-webkit-scrollbar-track {
                background: transparent;
            }

            ::-webkit-scrollbar-thumb {
                background: #dee2e6;
                border-radius: 3px;
            }

            .d-flex {
                display: flex;
            }

            .flex-grow-1 {
                flex-grow: 1;
            }

            .sidebar {
                min-height: 100vh;
                background: #1a1d23;
                color: #fff;
                padding-top: 16px;
                position: fixed;
                width: 260px;
                left: 0;
                top: 0;
                bottom: 0;
                overflow-y: auto;
                z-index: 100;
                transition: transform 0.3s;
            }

            .sidebar-brand {
                font-size: 1.4rem;
                font-weight: 800;
                text-align: center;
                margin-bottom: 24px;
                padding: 0 20px 16px;
                background: linear-gradient(135deg, #6E56CF, #38BDF8);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                letter-spacing: -0.5px;
            }

            .sidebar-section-label {
                font-size: 0.6rem;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                color: #4b5563;
                font-weight: 700;
                padding: 0 28px;
                margin-bottom: 6px;
            }

            .sidebar a {
                color: #9ca3af;
                text-decoration: none;
                padding: 11px 20px;
                display: flex;
                align-items: center;
                border-radius: 8px;
                margin: 3px 12px;
                transition: 0.25s;
                font-size: 0.88rem;
                font-weight: 500;
            }

            .sidebar a:hover {
                background: rgba(255, 255, 255, 0.06);
                color: #fff;
            }

            .sidebar a.active {
                background: linear-gradient(135deg, #6E56CF, #4F46E5);
                color: #fff;
                font-weight: 700;
            }

            .sidebar hr {
                border-color: rgba(255, 255, 255, 0.06);
            }

            .main-content {
                margin-left: 260px;
                padding: 32px;
                min-height: 100vh;
            }

            .card {
                border: none;
                border-radius: 14px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                overflow: hidden;
            }

            .card-header {
                border-bottom: 1px solid rgba(0, 0, 0, 0.06);
                font-weight: 700;
                font-size: 0.95rem;
            }

            .stat-card {
                border-radius: 14px;
                padding: 22px;
                border: none;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
                transition: 0.3s;
                position: relative;
                overflow: hidden;
            }

            .stat-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
            }

            .stat-value {
                font-size: 1.8rem;
                font-weight: 800;
                line-height: 1.2;
            }

            .stat-label {
                font-size: 0.8rem;
                color: #6b7280;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 4px;
            }

            .badge-vip {
                background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
                color: #000;
                font-weight: 700;
            }

            .table img {
                border-radius: 8px;
                object-fit: cover;
            }

            .table-hover>tbody>tr:hover {
                background-color: rgba(110, 86, 207, 0.03) !important;
            }

            .page-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 28px;
            }

            .page-title {
                font-size: 1.5rem;
                font-weight: 800;
                color: #111;
                margin: 0;
            }

            @media (max-width: 992px) {
                .sidebar {
                    transform: translateX(-260px);
                    position: fixed;
                }

                .sidebar.open {
                    transform: translateX(0);
                }

                .main-content {
                    margin-left: 0;
                    padding: 20px;
                }

                .mobile-toggle {
                    display: flex !important;
                }
            }

            .mobile-toggle {
                display: none;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 99;
            }

            .sidebar-overlay.show {
                display: block;
            }
        </style>
    </head>

    <body>

        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <div class="d-flex">
            <?php echo $sidebar; ?>

            <div class="main-content flex-grow-1">
                <div class="d-flex align-items-center mb-4 d-lg-none">
                    <button class="btn btn-dark me-3 mobile-toggle" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <span class="fw-bold" style="font-size:1.1rem;">NEXUS Admin</span>
                </div>

                <?php echo $content; ?>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleSidebar() {
                document.querySelector('.sidebar').classList.toggle('open');
                document.getElementById('sidebarOverlay').classList.toggle('show');
            }
        </script>
    </body>

    </html>
<?php
}
