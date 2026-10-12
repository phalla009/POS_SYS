<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('pageTitle', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="/image/logokr.png" />
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    @yield('headerBlock')

    <style>
        /* Submenu styles */
        .submenu { max-height: 0; overflow: hidden; margin-top: 0; transition: max-height 0.3s ease, margin-top 0.3s ease; }
        li.menu-container.open .submenu { max-height: 500px; margin-top: 5px; }
        .submenu a { display: block; padding: 10px 15px 10px 55px; text-align: left; text-decoration: none; color: white; }
        .icon i { margin-right: 8px; font-size: 18px; }
        .dropdown-arrow { float: right; font-size: 15px; margin-top: 2px; margin-left: 80px; transition: transform 0.3s ease; }
        li.menu-container.open .dropdown-arrow i { transform: rotate(90deg); }
        .sidebar-menu :hover a { font-size: 17px; transition: all 0.2s ease;}
        .l1 { color: #ff4d4d; } .l2 { color: #ffa500; } .l3 { color: #ffffff; }
        .sidebar-menu li:last-child a:hover { color: red; }
        .sidebar-header .user-info { margin-top: 10px; display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: -10px; }
        .sidebar-header .user-info i { font-size: 18px; color: #ffffff; }

        /* Collapsed Sidebar Styles (Icon-Only Mode) */
        .sidebar {
            width: 300px;
            min-width: 300px;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.collapsed {
            width: 80px;
            min-width: 80px;
        }
        .sidebar.collapsed .sidebar-header #logo .l3,
        .sidebar.collapsed .sidebar-header .user-info,
        .sidebar.collapsed .sidebar-menu a span:not(.icon),
        .sidebar.collapsed .dropdown-arrow,
        .sidebar.collapsed .version-tag-container,
        .sidebar.collapsed .submenu {
            display: none !important;
        }
        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            padding: 1rem 0;
            position: relative;
        }
        .sidebar.collapsed .sidebar-menu a .icon {
            margin-right: 0;
            font-size: 1.4rem;
        }
        .sidebar.collapsed .sidebar-header {
            padding: 1.5rem 0.5rem;
            text-align: center;
        }
        .sidebar.collapsed .sidebar-header #logo img {
            height: 36px;
            width: 36px;
        }

        /* Sidebar Toggle Button */
        .sidebar-toggle-btn {
            background: transparent;
            border: none;
            color: #aaa;
            cursor: pointer;
            font-size: 1.1rem;
            margin-top: 10px;
            transition: color 0.2s;
        }
        .sidebar-toggle-btn:hover {
            color: #fff;
        }

        /* Loading overlay */
        #loading-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.85);
            display: none;
            justify-content: center; align-items: center; flex-direction: column;
            z-index: 99999;
        }
        .spinner {
            border: 6px solid #f3f3f3; border-top: 6px solid #3498db;
            border-radius: 50%; width: 60px; height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0%{ transform:rotate(0deg); } 100%{ transform:rotate(360deg); } }
        #loading-text { margin-top: 15px; font-size: 16px; color: #333; }

        /* Modal Overlay */
        #logout-confirm {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65); /* Semi-transparent dark overlay */
            backdrop-filter: blur(4px); /* Modern blur effect */
            display: none; /* Controlled via JS */
            align-items: center;
            justify-content: center;
            z-index: 9999;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Modal Box */
        .confirm-box {
            background: #ffffff;
            width: 100%;
            max-width: 380px;
            padding: 32px 24px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: modalPop 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* Main Icon Container */
        .icon-container {
            width: 64px;
            height: 64px;
            background-color: #fee2e2; /* Light red tint */
            color: #ef4444; /* Vibrant danger red */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 28px;
        }

        /* Text Styling */
        .confirm-box h3 {
            margin: 0 0 8px 0;
            color: #0f172a;
            font-size: 20px;
            font-weight: 600;
        }

        .confirm-box p {
            color: #64748b;
            font-size: 14px;
            margin: 0 0 24px 0;
            line-height: 1.5;
        }

        /* Button Group Layout */
        .button-group {
            display: flex;
            gap: 12px;
        }

        .btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Yes / Logout Button */
        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
        }

        /* No / Cancel Button */
        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
            color: #1e293b;
            transform: translateY(-1px);
        }

        /* Entrance Animation */
        @keyframes modalPop {
            0% {
                opacity: 0;
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>

<!-- Burger button for mobile -->
<button class="burger-btn" id="burgerBtn" aria-label="Toggle sidebar">
    <span></span><span></span><span></span>
</button>

<!-- Sidebar backdrop -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dashboard">

    <div class="sidebar" id="mainSidebar">
        <div class="sidebar-header">
            <div id="logo">
                <img src="/image/logokr.png" alt="Logo" style="height:50px; width:50px; border-radius:6px; object-fit:cover; vertical-align:middle;">
            </div>
            <p class="user-info">
                <i class="fas fa-circle-user"></i>
                <span>{{ Auth::user()->name }}</span>
            </p>
        </div>
        <ul class="sidebar-menu">

            @if(Auth::user()->hasPermission('dashboard'))
                <li><a href="{{ route('dashboard') }}"><span class="icon"><i class="fas fa-tachometer-alt"></i></span> <span>Dashboard</span></a></li>
            @endif

            @if(Auth::user()->hasPermission('pos'))
                <li><a href="{{ route('pos.index') }}"><span class="icon"><i class="fas fa-cash-register"></i></span> <span>POS</span></a></li>
            @endif

            @if(Auth::user()->hasPermission('products') || Auth::user()->hasPermission('categories'))
                <li class="menu-container">
                    <a href="javascript:void(0)" class="submenu-toggle">
                        <span class="icon"><i class="fas fa-box-open"></i></span> <span>Products</span>
                        <span class="dropdown-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="submenu">
                        @if(Auth::user()->hasPermission('products'))
                            <a href="{{ route('products.index') }}"><span class="icon"><i class="fas fa-box"></i></span> Products</a>
                        @endif
                        @if(Auth::user()->hasPermission('categories'))
                            <a href="{{ route('categories.index') }}"><span class="icon"><i class="fas fa-tags"></i></span> Category</a>
                        @endif
                    </div>
                </li>
            @endif

            @if(Auth::user()->hasPermission('customers'))
                <li><a href="{{ route('customers.index') }}"><span class="icon"><i class="fas fa-users"></i></span> <span>Customers</span></a></li>
            @endif

            @if(Auth::user()->hasPermission('orders'))
                <li><a href="{{ route('orders.index') }}"><span class="icon"><i class="fas fa-shopping-cart"></i></span> <span>Orders</span></a></li>
            @endif

            @if(Auth::user()->hasPermission('inventory'))
                <li><a href="{{ route('inventory-items.index') }}"><span class="icon"><i class="fas fa-boxes"></i></span> <span>Inventory</span></a></li>
            @endif

            @if(Auth::user()->hasPermission('reports'))
                <li><a href="{{ route('reports.index') }}"><span class="icon"><i class="fas fa-chart-line"></i></span> <span>Reports</span></a></li>
            @endif

            @if(Auth::user()->hasPermission('settings'))
                <li class="menu-container">
                    <a href="javascript:void(0)" class="submenu-toggle">
                        <span class="icon"><i class="fas fa-cog"></i></span> <span>Settings</span>
                        <span class="dropdown-arrow"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <div class="submenu">
                        @if(Auth::user()->hasPermission('usermanagers'))
                            <a href="{{ route('usermanagers.index') }}"><span class="icon"><i class="fas fa-user"></i></span> User Managements</a>
                        @endif
                        @if(Auth::user()->hasPermission('userroles'))
                            <a href="{{ route('userroles.index') }}"><span class="icon"><i class="fas fa-user-shield"></i></span> User Roles</a>
                        @endif
                        @if(Auth::user()->hasPermission('types'))
                            <a href="{{ route('types.index') }}"><span class="icon"><i class="fas fa-layer-group"></i></span> Setup Types</a>
                        @endif
                        @if(Auth::user()->hasPermission('units'))
                            <a href="{{ route('units.index') }}"><span class="icon"><i class="fas fa-ruler"></i></span> Setup Units</a>
                        @endif
                        @if(Auth::user()->hasPermission('barcodes'))
                            <a href="{{ route('barcodes.index') }}"><span class="icon"><i class="fas fa-barcode"></i></span> Setup Barcodes</a>
                        @endif
                    </div>
                </li>
            @endif

            <li>
                <a id="logout-link"><span class="icon"><i class="fas fa-sign-out-alt"></i></span> <span>Logout</span></a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
            </li>
        </ul>
        <div class="sidebar-header">
            <!-- Toggle button for collapsing/expanding on desktop -->
            <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Toggle Sidebar">
                <i class="fas fa-angles-left"></i>
            </button>
        </div>

        <div class="version-tag-container" style="margin-top: auto; padding: 16px 20px; text-align: center;">
            <span style="
                display: inline-block;
                background: rgba(255,255,255,0.08);
                color: #ff5a05;
                font-family: 'Inter', sans-serif;
                font-size: 12px;
                font-weight: 500;
                letter-spacing: 0.3px;
                padding: 4px 12px;
                border-radius: 999px;
                border: 1px solid rgba(255,255,255,0.15);
            ">V2.0.0</span>
        </div>

    </div>

    <div class="main-content">
        <div id="content">@yield('content')</div>
    </div>

</div>

<!-- Loading Overlay -->
<div id="loading-overlay">
    <div class="spinner"></div>
    <div id="loading-text">Loading...</div>
</div>

<!-- Logout Modal -->
<div id="logout-confirm">
    <div class="confirm-box">
        <div class="icon-container">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h3>Logging Out?</h3>
        <p>Are you sure you want to end your current session?</p>

        <div class="button-group">
            <button id="confirm-yes" class="btn btn-danger">
                <i class="fas fa-check"></i> Yes, Logout!
            </button>
            <button id="confirm-no" class="btn btn-secondary">
                <i class="fas fa-times"></i> No, Keep it!
            </button>
        </div>
    </div>
</div>

<script>
    const overlay        = document.getElementById('loading-overlay');
    const burgerBtn      = document.getElementById('burgerBtn');
    const mainSidebar    = document.getElementById('mainSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

    // Hide overlay on every page load
    window.addEventListener('load',     function() { overlay.style.display = 'none'; });
    window.addEventListener('pageshow', function() { overlay.style.display = 'none'; });

    // Sidebar open/close for mobile
    function openSidebar() {
        mainSidebar.classList.add('open');
        burgerBtn.classList.add('active');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        mainSidebar.classList.remove('open');
        burgerBtn.classList.remove('active');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    burgerBtn.addEventListener('click', function() {
        mainSidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
    sidebarOverlay.addEventListener('click', closeSidebar);
    window.addEventListener('resize', function() { if (window.innerWidth > 768) closeSidebar(); });

    // Desktop Collapse Toggle
    sidebarToggleBtn.addEventListener('click', function() {
        mainSidebar.classList.toggle('collapsed');
        const toggleIcon = sidebarToggleBtn.querySelector('i');
        if (mainSidebar.classList.contains('collapsed')) {
            toggleIcon.classList.remove('fa-angles-left');
            toggleIcon.classList.add('fa-angles-right');
        } else {
            toggleIcon.classList.remove('fa-angles-right');
            toggleIcon.classList.add('fa-angles-left');
        }
    });
    // Submenu toggle
    document.querySelectorAll('.submenu-toggle').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (mainSidebar.classList.contains('collapsed')) {
                e.preventDefault();
                return;
            }
            e.preventDefault();
            e.stopPropagation();

            const parent = this.closest('.menu-container');
            document.querySelectorAll('.menu-container').forEach(function(container) {
                if (container !== parent) {
                    container.classList.remove('open');
                }
            });
            if (parent) {
                parent.classList.toggle('open');
            }
        });
    });

    // Sidebar nav links → close sidebar + loading overlay
    document.querySelectorAll('.sidebar-menu a').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (!href || href === 'javascript:void(0)' || href === '#' ||
                this.id === 'logout-link' || this.classList.contains('submenu-toggle')) return;

            e.preventDefault();
            closeSidebar();
            document.querySelectorAll('.menu-container.open').forEach(function(container) {
                container.classList.remove('open');
            });
            overlay.style.display = 'flex';
            setTimeout(function() {
                window.location.href = href;
            }, 150);
        });
    });

    // Logout
    const logoutLink    = document.getElementById('logout-link');
    const logoutConfirm = document.getElementById('logout-confirm');
    const confirmYes    = document.getElementById('confirm-yes');
    const confirmNo     = document.getElementById('confirm-no');
    const logoutForm    = document.getElementById('logout-form');
    logoutLink.addEventListener('click', function(e) { e.preventDefault(); logoutConfirm.style.display = 'flex'; });
    confirmYes.addEventListener('click', function() { logoutForm.submit(); });
    confirmNo.addEventListener('click',  function() { logoutConfirm.style.display = 'none'; });

    // Auto-open active submenu
    const currentPath = window.location.pathname;
    document.querySelectorAll('.submenu a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && currentPath.startsWith(href.replace(window.location.origin, ''))) {
            const parent = link.closest('.menu-container');
            if (parent) parent.classList.add('open');
        }
    });
</script>

</body>
</html>
