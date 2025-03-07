<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

$admin_pages = ['/products', '/categories', '/product-variants/create/3', '/colors', '/sizes', '/orders'];

$current_page = $_SERVER['REQUEST_URI'];
if (in_array($current_page, $admin_pages) && !isAdmin()) {
    header('Location: /');
    exit();
}

$menu_items = [
    'public' => [
        '/' => ['name' => 'Trang chủ', 'icon' => 'bi-house-door'],
        '/product-list-user' => ['name' => 'Sản phẩm', 'icon' => 'bi-shop'],
        '/cart' => ['name' => 'Giỏ hàng', 'icon' => 'bi-cart'],
    ],
    'admin' => [
        '/orders/dashboard' => ['name' => 'Dashboard', 'icon' => 'bi-graph-up'],

        '/orders' => [
            'name' => 'Orders',
            'icon' => 'bi-list',
            'submenu' => [
                '/orders/dashboard' => 'Dashboard',
                '/orders' => 'List',
                '/orders/create' => 'Create',
            ]
        ],
        '/products' => [
            'name' => 'Products',
            'icon' => 'bi-box-seam',
            'submenu' => [
                '/products' => 'List',
                '/products/create' => 'Create',

            ]
        ],
        '/categories' => [
            'name' => 'Categories',
            'icon' => 'bi-list-nested',
            'submenu' => [
                '/categories' => 'List',
                '/categories/create' => 'Create',

            ]
        ],

        'variants' => [
            'name' => 'Variants',
            'icon' => 'bi-grid-3x3-gap',
            'submenu' => [
                '/product-variants/create/3' => 'Create',
                '/colors' => 'Colors',
                '/sizes' => 'Sizes'
            ]
        ],
        '/coupons' => [
            'name' => 'Coupons',
            'icon' => 'bi-percent',
            'submenu' => [
                '/coupons' => 'List',
                '/coupons/create' => 'Create',
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "My App" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        header {
            background: #1a1a1a;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* .sidebar {
            background: #2c2c2c;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 230px;
            z-index: 1000;
        } */

        .main-content {
            margin-left: 230px;
        }

        nav a {
            color: #aaa;
            text-decoration: none;
            font-weight: 500;
            text-transform: uppercase;
            padding: 10px 15px;
            transition: all 0.3s ease-in-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        nav a:hover {
            color: #ffcc00;
            background: rgba(255, 255, 255, 0.1);
        }

        nav a.active {
            color: #ffffff;
            background: #ffcc00;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-custom {
            padding: 8px 15px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .admin-nav {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 15px;
            padding-top: 15px;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }



        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffcc00;
            text-decoration: none;
        }

        .brand-logo i {
            font-size: 24px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 20px;
        }





        .nav-menu a {
            color: #aaa;
            text-decoration: none;
            font-weight: 500;
            text-transform: uppercase;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            color: #ffcc00;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-menu a.active {
            color: #ffffff;
            background: #ffcc00;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-greeting {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .user-greeting i {
            font-size: 16px;
        }

        .btn-custom {
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-dropdown {
            position: relative;
        }

        .user-greeting {
            color: #fff;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .user-greeting:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .user-greeting i {
            font-size: 16px;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            min-width: 210px;
            display: none;
            z-index: 1000;
        }

        .user-dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dropdown-menu .dropdown-item:hover {
            background: #f8f9fa;
            color: #ffcc00;
        }

        .dropdown-menu .dropdown-item i {
            font-size: 16px;
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background-color: rgb(19, 100, 125);
            /* Màu xanh Bootstrap primary */
            color: white;
        }

        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 5px 0;
        }

        .btn-cart {
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            background-color: #1a1a1a;
        }

        /* Sidebar styles */
        /* Sidebar styles with improved transitions */
        .sidebar {
            width: 230px;
            height: 100vh;
            background: #2c3e50;
            color: white;
            position: fixed;
            left: -250px;
            top: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding-top: 20px;
            z-index: 1000;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
        }

        /* When sidebar is open */
        .sidebar.open {
            left: 0;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.2);
        }

        /* Toggle button with smoother animation */
        #toggle-sidebar {
            position: fixed;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #2c3e50;
            border: none;
            padding: 10px;
            color: white;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        #toggle-sidebar:hover {
            background: rgb(255, 221, 0);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        /* Move toggle button when sidebar is open */
        .sidebar.open+#toggle-sidebar {
            left: 260px;
        }

        /* Rotate arrow when open with smooth rotation */
        #toggle-sidebar i {
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
        }

        #toggle-sidebar.open i {
            transform: rotate(180deg);
        }

        /* Main content area with improved transitions */
        .main-content {
            margin-left: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            min-height: 100vh;
            position: relative;
            background: #fff;
        }

        /* Adjust main content when sidebar is open */
        .sidebar.open~.main-content {
            margin-left: 230px;
            width: calc(100% - 230px);
        }

        /* Make header responsive with smoother transitions */
        .header-container {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .sidebar.open~.main-content .header-container {
            max-width: calc(100% - 30px);
        }

        /* Add smooth transitions for all interactive elements */
        .nav-menu a,
        .dropdown-menu,
        .user-greeting,
        .btn-custom {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Optional: Add subtle animation for sidebar content */
        .sidebar .brand-logo,
        .sidebar .user-info,
        .sidebar nav a {
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.open .brand-logo,
        .sidebar.open .user-info,
        .sidebar.open nav a {
            opacity: 1;
            transform: translateX(0);
        }

        /* Stagger the animations for sidebar items */
        .sidebar.open .brand-logo {
            transition-delay: 0.1s;
        }

        .sidebar.open .user-info {
            transition-delay: 0.2s;
        }

        .sidebar.open nav a {
            transition-delay: 0.3s;
        }
    </style>
</head>

<body class="<?= isAdmin() ? 'has-sidebar' : '' ?>">
    <?php if (isAdmin()): ?>


        <div class="sidebar" id="sidebar">
            <div class="px-3">
                <h1 class="h4 text-white mb-4 brand-logo">
                    <i class="bi bi-speedometer2"></i>
                    Admin
                </h1>
                <div class="user-info">
                    <p class="text-white mb-1">
                        <i class="bi bi-person-circle"></i>
                        Welcome,
                    </p>
                    <p class="text-white mb-0">
                        <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                    </p>
                </div>
            </div>
            <nav class="d-flex flex-column">
                <?php foreach ($menu_items['admin'] as $url => $item): ?>
                    <?php if (isset($item['submenu'])): ?>
                        <div class="nav-item dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi <?= $item['icon'] ?>"></i>
                                <?= $item['name'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach ($item['submenu'] as $subUrl => $subName): ?>
                                    <li>
                                        <a class="dropdown-item <?= ($current_page == $subUrl) ? 'active' : '' ?>"
                                            href="<?= $subUrl ?>">
                                            <?= $subName ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= $url ?>" class="<?= ($current_page == $url) ? 'active' : '' ?>">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <?= $item['name'] ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
        <button id="toggle-sidebar">
            <i class="bi bi-chevron-right"></i>
        </button>
    <?php endif; ?>


    <div class="<?= isAdmin() ? 'main-content' : '' ?>">
        <header>
            <div class="header-container">
                <a href="/" class="brand-logo">
                    <i class="bi bi-bag-heart"></i>
                    <h1 class="h3 mb-0"></h1>
                </a>

                <div class="nav-links">
                    <div class="nav-menu">
                        <?php foreach ($menu_items['public'] as $url => $item): ?>
                            <a href="<?= $url ?>" class="<?= ($current_page == $url) ? 'active' : '' ?>">
                                <i class="bi <?= $item['icon'] ?>"></i>
                                <?= $item['name'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="user-section">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="user-dropdown">
                                <div class="user-greeting">
                                    <i class="bi bi-person-circle"></i>
                                    <span>Xin chào, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                                    <i class="bi bi-chevron-down ms-2"></i>
                                </div>
                                <div class="dropdown-menu">
                                    <div class="dropdown-header p-3">
                                        <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a href="/account" class="dropdown-item">
                                        <i class="bi bi-person"></i>
                                        Thông tin tài khoản
                                    </a>
                                    <a href="/orders/user/<?= $_SESSION['user_id'] ?>" class="dropdown-item">
                                        <i class="bi bi-bag"></i>
                                        Đơn hàng của tôi
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a href="/logout" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Đăng xuất
                                    </a>
                                </div>
                            </div>

                        <?php else: ?>
                            <a href="/login" class="btn btn-primary btn-custom">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Đăng nhập
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="container my-4">
            <?= $content ?>
        </main>

        <footer class="bg-dark text-white py-3 mt-4">
            <div class="container text-center">
                <p class="mb-0">&copy; <?= date("Y") ?> My App. All Rights Reserved.</p>
            </div>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById("sidebar");
        const toggleBtn = document.getElementById("toggle-sidebar");
        const toggleIcon = toggleBtn.querySelector("i");
        const mainContent = document.querySelector(".main-content");

        // Add smooth scroll behavior to the whole page
        document.documentElement.style.scrollBehavior = 'smooth';

        toggleBtn.addEventListener("click", function() {
            // Add transition class before toggling
            sidebar.classList.add('transitioning');
            mainContent.classList.add('transitioning');

            // Toggle open classes
            sidebar.classList.toggle("open");
            toggleBtn.classList.toggle("open");

            // Handle icon rotation
            if (sidebar.classList.contains("open")) {
                toggleIcon.classList.replace("bi-chevron-right", "bi-chevron-left");
            } else {
                toggleIcon.classList.replace("bi-chevron-left", "bi-chevron-right");
            }

            // Remove transition class after animation completes
            setTimeout(() => {
                sidebar.classList.remove('transitioning');
                mainContent.classList.remove('transitioning');
            }, 400); // Match this with your CSS transition duration
        });

        // Optional: Add resize handler for responsive behavior
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    toggleBtn.classList.remove('open');
                    toggleIcon.classList.replace("bi-chevron-left", "bi-chevron-right");
                }
            }, 250);
        });
    });
</script>

</html>