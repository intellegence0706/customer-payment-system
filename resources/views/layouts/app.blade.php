<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客管理システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
    <noscript>
        <style>
            [data-aos] { opacity: 1 !important; transform: none !important; }
        </style>
    </noscript>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --jp-primary: #1f4f8f; /* 落ち着いた和のブルー */
            --jp-accent: #e60012; /* 朱色 */
            --jp-bg: #0f1221; /* darker base for neon */
            --jp-surface: #121633;
            --jp-border: #1f2a44;
            --jp-text: #e7ecff;
            --neon-1: #00e5ff;
            --neon-2: #8a2be2;
            --neon-3: #ff0080;
            --glow: 0 0 18px rgba(0,229,255,.35), 0 0 32px rgba(138,43,226,.2);
        }

        [data-theme='light'] {
            --jp-primary: #1f4f8f;
            --jp-accent: #e60012;
            --jp-bg: #f7f7f7;
            --jp-surface: #ffffff;
            --jp-border: #e9ecef;
            --jp-text: #1f2d3d;
            --glow: 0 0 12px rgba(31,79,143,.15);
        }

        html, body {
            font-family: 'Noto Sans JP', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            color: var(--jp-text);
            background-color: var(--jp-bg);
        }

        /* Animated background */
        body.animated-bg {
            background-image: radial-gradient(1000px 600px at 10% 10%, rgba(0,229,255,.08), transparent 60%),
                              radial-gradient(800px 500px at 90% 20%, rgba(255,0,128,.07), transparent 60%),
                              radial-gradient(700px 400px at 50% 90%, rgba(138,43,226,.06), transparent 60%);
            background-attachment: fixed;
            animation: bg-move 18s ease-in-out infinite alternate;
        }
        @keyframes bg-move {
            0% { background-position: 0 0, 0 0, 0 0; }
            100% { background-position: 5% 2%, -3% 4%, 2% -3%; }
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--jp-surface), rgba(18,22,51,.8));
            border-right: 1px solid var(--jp-border);
        }

        .sidebar .nav-link {
            color: #556; 
            border-radius: .5rem;
            padding: .5rem .75rem;
        }

        .sidebar .nav-link:hover {
            background-color: #f2f4f8;
            color: var(--jp-primary);
        }

        .sidebar .nav-link.active {
            background-color: #eaeff7;
            color: var(--jp-primary);
            font-weight: 600;
        }

        .main-content {
            min-height: 100vh;
            background-color: var(--jp-bg);
        }

        .btn-primary {
            --bs-btn-bg: var(--jp-primary);
            --bs-btn-border-color: var(--jp-primary);
            --bs-btn-hover-bg: #194271;
            --bs-btn-hover-border-color: #173b65;
            --bs-btn-active-bg: #15365c;
            --bs-btn-active-border-color: #133053;
        }

        .btn-outline-secondary {
            --bs-btn-color: #5c677d;
            --bs-btn-border-color: #c7ccd6;
            --bs-btn-hover-bg: #eef1f5;
            --bs-btn-hover-border-color: #b8bfcc;
        }

        .card {
            border: 1px solid var(--jp-border);
            border-radius: .9rem;
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.04));
            box-shadow: var(--glow);
            backdrop-filter: blur(6px);
            transition: transform .25s ease, box-shadow .25s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,.2), 0 0 24px rgba(0,229,255,.12);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--jp-border);
            font-weight: 600;
        }

        .table {
            font-size: 0.95rem;
        }
        .table thead th {
            background-color: #f8f9fb;
            border-bottom: 1px solid var(--jp-border);
        }
        .table tbody tr:hover {
            background-color: #f8fbff;
        }

        .form-control, .form-select {
            border-radius: .5rem;
            border-color: #dfe3ea;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--neon-1);
            box-shadow: 0 0 0 .2rem rgba(0,229,255,.18);
        }

        .alert {
            border-radius: .75rem;
        }

        /* Neon button */
        .btn-neon {
            color: #0b1026;
            background: linear-gradient(135deg, var(--neon-1), var(--neon-3));
            border: none;
            box-shadow: 0 0 12px rgba(0,229,255,.35), 0 0 18px rgba(255,0,128,.18);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .btn-neon:hover { filter: brightness(1.05); transform: translateY(-1px); }
        .btn-neon:active { transform: translateY(0); }
        .neon-title { color: var(--jp-text); }
        @supports ((-webkit-background-clip: text) or (background-clip: text)) {
            .neon-title {
                background: linear-gradient(135deg, var(--neon-1), var(--neon-2), var(--neon-3));
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
                color: transparent;
            }
        }

        .text-gray-800, .text-dark { color: var(--jp-text) !important; }
        .theme-toggle {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 1050;
            border-radius: 999px;
            padding: .6rem .8rem;
            background: linear-gradient(135deg, var(--jp-surface), rgba(255,255,255,.06));
            border: 1px solid var(--jp-border);
            box-shadow: var(--glow);
            color: var(--jp-text);
        }
    </style>
</head>

<body class="animated-bg">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <h5 class="px-3 mb-3">顧客管理</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                ダッシュボード
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                                href="{{ route('customers.index') }}">
                                <i class="fas fa-users me-2"></i>
                                顧客
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}"
                                href="{{ route('payments.index') }}">
                                <i class="fas fa-credit-card me-2"></i>
                                入金
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payments.upload-form') }}">
                                <i class="fas fa-upload me-2"></i>
                                データ取込
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payments.postcard-form') }}">
                                <i class="fas fa-mail-bulk me-2"></i>
                                はがきデータ
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="pt-3">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Floating Theme Toggle -->
    <button id="themeToggle" class="theme-toggle" title="テーマ切替">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.min.js"></script>
    <script>
        // AOS animations with robust fallback if the library fails to load
        (function() {
            function revealIfAOSMissing() {
                // If AOS is not available but its CSS is hiding elements, force reveal
                document.querySelectorAll('[data-aos]').forEach(function(el) {
                    el.classList.add('aos-animate');
                });
            }

            function initAnimations() {
                if (window.AOS && typeof window.AOS.init === 'function') {
                    window.AOS.init({ duration: 800, easing: 'ease-out-cubic', once: true });
                } else {
                    revealIfAOSMissing();
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAnimations);
            } else {
                initAnimations();
            }
        })();

        // Theme handling
        (function() {
            const root = document.documentElement;
            const stored = localStorage.getItem('cms-theme');
            if (!stored) {
                // default to dark
                root.removeAttribute('data-theme');
            } else {
                root.setAttribute('data-theme', stored);
            }
            const toggleBtn = document.getElementById('themeToggle');
            const updateIcon = () => {
                const mode = root.getAttribute('data-theme');
                toggleBtn.innerHTML = mode === 'light' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
            };
            updateIcon();
            toggleBtn.addEventListener('click', () => {
                const mode = root.getAttribute('data-theme') === 'light' ? null : 'light';
                if (mode) root.setAttribute('data-theme', mode); else root.removeAttribute('data-theme');
                localStorage.setItem('cms-theme', mode || 'dark');
                updateIcon();
            });
        })();
    </script>
    @yield('scripts')
</body>

</html>
