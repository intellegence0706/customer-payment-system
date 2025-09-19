<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>顧客管理システム | Customer Management System</title>
    <meta name="description" content="Modern Customer Management System with advanced analytics and beautiful UI">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💼</text></svg>">
    
    <!-- Modern CSS Framework & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <noscript>
        <style>
            [data-aos] { opacity: 1 !important; transform: none !important; }
        </style>
    </noscript>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+JP:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            /* Revolutionary Color Palette */
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            
            /* Dark Theme */
            --bg-primary: #0a0e27;
            --bg-secondary: #1a1f3a;
            --bg-tertiary: #252a4a;
            --surface: rgba(255, 255, 255, 0.05);
            --surface-hover: rgba(255, 255, 255, 0.08);
            --border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-muted: rgba(255, 255, 255, 0.5);
            
            /* Neon Colors */
            --neon-blue: #00d4ff;
            --neon-purple: #a855f7;
            --neon-pink: #ec4899;
            --neon-green: #10b981;
            --neon-orange: #f59e0b;
            
            /* Shadows & Effects */
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 12px 32px rgba(0, 0, 0, 0.25);
            --glow-blue: 0 0 20px rgba(0, 212, 255, 0.3);
            --glow-purple: 0 0 20px rgba(168, 85, 247, 0.3);
            --glow-pink: 0 0 20px rgba(236, 72, 153, 0.3);
        }

        [data-theme='light'] {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-tertiary: #f1f5f9;
            --surface: rgba(255, 255, 255, 0.8);
            --surface-hover: rgba(255, 255, 255, 0.9);
            --border: rgba(0, 0, 0, 0.08);
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1);
            --glow-blue: 0 0 15px rgba(59, 130, 246, 0.2);
            --glow-purple: 0 0 15px rgba(147, 51, 234, 0.2);
            --glow-pink: 0 0 15px rgba(236, 72, 153, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Inter', 'Noto Sans JP', system-ui, -apple-system, sans-serif;
            color: var(--text-primary);
            background: var(--bg-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Revolutionary Animated Background */
        .app-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: var(--bg-primary);
        }

        .app-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(0, 212, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(168, 85, 247, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(236, 72, 153, 0.08) 0%, transparent 50%);
            animation: backgroundFloat 20s ease-in-out infinite;
        }

        .app-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                linear-gradient(45deg, transparent 30%, rgba(0, 212, 255, 0.02) 50%, transparent 70%),
                linear-gradient(-45deg, transparent 30%, rgba(168, 85, 247, 0.02) 50%, transparent 70%);
            animation: backgroundShift 15s ease-in-out infinite reverse;
        }

        @keyframes backgroundFloat {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(2%, 1%) rotate(1deg); }
            50% { transform: translate(-1%, 2%) rotate(-1deg); }
            75% { transform: translate(1%, -1%) rotate(0.5deg); }
        }

        @keyframes backgroundShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Revolutionary Sidebar */
        .modern-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            scrollbar-width: none;
        }

        .modern-sidebar::-webkit-scrollbar {
            display: none;
        }

        .sidebar-header {
            padding: 2rem 1.5rem 1rem;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: var(--glow-blue);
        }

        .brand-text {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section {
            margin-bottom: 2rem;
        }

        .nav-section-title {
            padding: 0 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
        }

        .nav-item {
            margin: 0.25rem 0.75rem;
        }

        .nav-link-modern {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--surface);
            opacity: 0;
            transition: opacity 0.2s ease;
            border-radius: 12px;
        }

        .nav-link-modern:hover::before {
            opacity: 1;
        }

        .nav-link-modern:hover {
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .nav-link-modern.active {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--glow-blue);
            transform: translateX(4px);
        }

        .nav-link-modern.active::before {
            display: none;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }

        .nav-text {
            position: relative;
            z-index: 1;
        }

        .nav-badge {
            background: var(--danger-gradient);
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            margin-left: auto;
            position: relative;
            z-index: 1;
        }

        /* Modern Main Content */
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            background: var(--bg-primary);
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .content-wrapper {
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Revolutionary Buttons */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-md), var(--glow-blue);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg), var(--glow-blue);
            color: white;
        }

        .btn-secondary-modern {
            background: var(--surface);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary-modern:hover {
            background: var(--surface-hover);
            transform: translateY(-2px);
            color: var(--text-primary);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
            box-shadow: var(--shadow-md), var(--glow-blue);
        }

        .btn-warning-modern {
            background: var(--warning-gradient);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-danger-modern {
            background: var(--danger-gradient);
            color: white;
            box-shadow: var(--shadow-md), var(--glow-pink);
        }

        /* Modern Cards */
        .card-modern {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }

        .card-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card-modern:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .card-modern:hover::before {
            opacity: 1;
        }

        .card-header-modern {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            background: transparent;
        }

        .card-body-modern {
            padding: 1.5rem;
        }

        .card-title-modern {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .card-subtitle-modern {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin: 0.25rem 0 0;
        }

        /* Modern Forms */
        .form-control-modern {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--neon-blue);
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
            background: var(--surface-hover);
        }

        .form-label-modern {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        /* Modern Alerts */
        .alert-modern {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--surface);
            margin-bottom: 1rem;
            animation: slideInRight 0.3s ease-out;
        }

        .alert-success-modern {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: var(--neon-green);
        }

        .alert-warning-modern {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.2);
            color: var(--neon-orange);
        }

        .alert-danger-modern {
            background: rgba(236, 72, 153, 0.1);
            border-color: rgba(236, 72, 153, 0.2);
            color: var(--neon-pink);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Modern Theme Toggle */
        .theme-toggle-modern {
            position: fixed;
            top: 2rem;
            right: 2rem;
            width: 48px;
            height: 48px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            cursor: pointer;
            z-index: 1100;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(20px);
        }

        .theme-toggle-modern:hover {
            transform: scale(1.05);
            background: var(--surface-hover);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            width: 48px;
            height: 48px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: none;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            cursor: pointer;
            z-index: 1200;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(20px);
        }

        .mobile-menu-toggle:hover {
            transform: scale(1.05);
            background: var(--surface-hover);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: flex;
            }

            .modern-sidebar {
                transform: translateX(-100%);
                box-shadow: var(--shadow-xl);
            }

            .modern-sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 1rem;
                padding-top: 5rem;
            }

            .theme-toggle-modern {
                top: 1rem;
                right: 1rem;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }
    </style>
</head>

<body>
    <!-- Revolutionary Animated Background -->
    <div class="app-background"></div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle d-md-none" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Modern Sidebar -->
    <nav class="modern-sidebar" id="modernSidebar">
        <div class="sidebar-header" data-aos="fade-down" data-aos-delay="100">
            <a href="{{ route('dashboard') }}" class="sidebar-brand">
                <div class="brand-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="brand-text">顧客管理システム</div>
            </a>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section" data-aos="fade-right" data-aos-delay="200">
                <div class="nav-section-title">メインメニュー</div>
                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link-modern {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="nav-text">ダッシュボード</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('customers.index') }}" class="nav-link-modern {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">顧客管理</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('payments.index') }}" class="nav-link-modern {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <span class="nav-text">請求管理</span>
                    </a>
                </div>
            </div>

            <div class="nav-section" data-aos="fade-right" data-aos-delay="300">
                <div class="nav-section-title">データ管理</div>
                <!-- <div class="nav-item">
                    <a href="{{ route('payments.upload-form') }}" class="nav-link-modern">
                        <div class="nav-icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <span class="nav-text">データ取込</span>
                    </a>
                </div> -->
                <div class="nav-item">
                    <a href="{{ route('customers.import') }}" class="nav-link-modern">
                        <div class="nav-icon">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <span class="nav-text">顧客データ取込</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('payments.postcard-form') }}" class="nav-link-modern">
                        <div class="nav-icon">
                            <i class="fas fa-mail-bulk"></i>
                        </div>
                        <span class="nav-text">はがきデータ</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('payments.detailed.import') }}" class="nav-link-modern {{ request()->routeIs('payments.detailed.*') ? 'active' : '' }}">
                        <div class="nav-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <span class="nav-text">請求情報キャプチャ</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-wrapper">
            <!-- Modern Alert Messages -->
            @if (session('success'))
                <div class="alert-modern alert-success-modern" data-aos="slide-left" data-aos-duration="500">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">成功</div>
                        <div class="alert-message">{{ session('success') }}</div>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert-modern alert-warning-modern" data-aos="slide-left" data-aos-duration="500" data-aos-delay="100">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">警告</div>
                        <div class="alert-message">{{ session('warning') }}</div>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                        </div>
                    @endif

                    @if (session('error'))
                <div class="alert-modern alert-danger-modern" data-aos="slide-left" data-aos-duration="500" data-aos-delay="200">
                    <div class="alert-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="alert-content">
                        <div class="alert-title">エラー</div>
                        <div class="alert-message">{{ session('error') }}</div>
                    </div>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>

    <!-- Modern Theme Toggle -->
    <button id="themeToggle" class="theme-toggle-modern" title="テーマ切替">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Modern Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.min.js"></script>
    <script>
        // Modern App Initialization
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });

        function initializeApp() {
            initAOS();
            initThemeToggle();
            initMobileMenu();
            initModernEffects();
        }

        // Enhanced AOS Animation System
        function initAOS() {
            if (window.AOS && typeof window.AOS.init === 'function') {
                window.AOS.init({
                    duration: 600,
                    easing: 'ease-out-cubic',
                    once: true,
                    offset: 50,
                    delay: 0,
                    anchorPlacement: 'top-bottom'
                });
            } else {
                // Fallback for missing AOS
                document.querySelectorAll('[data-aos]').forEach(function(el) {
                    el.classList.add('aos-animate');
                    el.style.opacity = '1';
                    el.style.transform = 'none';
                });
            }
        }

        // Enhanced Theme System
        function initThemeToggle() {
            const root = document.documentElement;
            const toggleBtn = document.getElementById('themeToggle');
            
            // Load saved theme or default to dark
            const savedTheme = localStorage.getItem('cms-theme') || 'dark';
            if (savedTheme === 'light') {
                root.setAttribute('data-theme', 'light');
            } else {
                root.removeAttribute('data-theme');
            }
            
            updateThemeIcon();
            
            toggleBtn.addEventListener('click', function() {
                const currentTheme = root.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                // Add transition effect
                document.body.style.transition = 'all 0.3s ease';
                
                if (newTheme === 'light') {
                    root.setAttribute('data-theme', 'light');
                } else {
                    root.removeAttribute('data-theme');
                }
                
                localStorage.setItem('cms-theme', newTheme);
                updateThemeIcon();
                
                // Remove transition after animation
                setTimeout(() => {
                    document.body.style.transition = '';
                }, 300);
            });
            
            function updateThemeIcon() {
                const currentTheme = root.getAttribute('data-theme');
                const icon = toggleBtn.querySelector('i');
                
                if (currentTheme === 'light') {
                    icon.className = 'fas fa-moon';
                    toggleBtn.title = 'ダークモードに切替';
                } else {
                    icon.className = 'fas fa-sun';
                    toggleBtn.title = 'ライトモードに切替';
                }
            }
        }

        // Mobile Menu System
        function initMobileMenu() {
            const mobileToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('modernSidebar');
            const overlay = document.createElement('div');
            
            overlay.className = 'mobile-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(overlay);
            
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    const isOpen = sidebar.classList.contains('show');
                    
                    if (isOpen) {
                        closeMobileMenu();
                    } else {
                        openMobileMenu();
                    }
                });
                
                overlay.addEventListener('click', closeMobileMenu);
                
                // Close on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                        closeMobileMenu();
                    }
                });
            }
            
            function openMobileMenu() {
                sidebar.classList.add('show');
                overlay.style.opacity = '1';
                overlay.style.visibility = 'visible';
                document.body.style.overflow = 'hidden';
                
                const icon = mobileToggle.querySelector('i');
                icon.className = 'fas fa-times';
            }
            
            function closeMobileMenu() {
                sidebar.classList.remove('show');
                overlay.style.opacity = '0';
                overlay.style.visibility = 'hidden';
                document.body.style.overflow = '';
                
                const icon = mobileToggle.querySelector('i');
                icon.className = 'fas fa-bars';
            }
        }

        // Modern Visual Effects
        function initModernEffects() {
            // Add hover effects to cards
            const cards = document.querySelectorAll('.card-modern');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert-modern');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.style.transform = 'translateX(100%)';
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            alert.remove();
                        }, 300);
                    }
                }, 5000);
            });
            
            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('.btn-modern, .nav-link-modern');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.3);
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add CSS for ripple animation
            if (!document.getElementById('ripple-styles')) {
                const style = document.createElement('style');
                style.id = 'ripple-styles';
                style.textContent = `
                    @keyframes ripple {
                        to {
                            transform: scale(2);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Smooth scrolling for anchor links
        document.addEventListener('click', function(e) {
            if (e.target.matches('a[href^="#"]')) {
                e.preventDefault();
                const target = document.querySelector(e.target.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    </script>
    @yield('scripts')
</body>

</html>
