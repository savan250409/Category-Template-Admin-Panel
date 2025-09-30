<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <!-- Favicons -->
    <link href="{{ asset('NiceAdmin/images/icon/logo-2023 copy.png') }}" rel="icon" />

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet" />
    <!-- Vendor CSS Files -->
    <link href="{{ asset('NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('NiceAdmin/assets/css/style.css') }}">

    <link href="{{ asset('NiceAdmin/images/icon/logo-2023 copy.png') }}" rel="icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #sidebar-nav .nav-link {
            transition: all 0.2s ease-in-out;
        }

        #sidebar-nav .nav-link:hover {
            background: #3b3b4d !important;
            color: #fff !important;
        }

        #sidebar-nav .nav-link.active {
            background: #007bff !important;
            color: #fff !important;
        }

        #sidebar-nav ul.collapse .nav-link:hover {
            background: #2a2a3b !important;
        }

        .sidebar-nav .nav-link i {
            margin-right: 5px !important;
        }
    </style>

</head>

<body>
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar">
        <div class="p-3 border-bottom">
            <a class="sidebar-brand d-flex align-items-center" href="{{ url('dashboard') }}">
                <span class="fw-bold fs-5 text-white">NGD Admin</span>
            </a>
        </div>

        <ul class="sidebar-nav flex-column p-2" id="sidebar-nav" style="background:#1e1e2d; min-height:100vh;">

            {{-- Dashboard --}}
            <li class="nav-item mb-1">
                <a class="nav-link d-flex align-items-center px-3 py-2 rounded-3
            {{ request()->is('/') || request()->is('dashboard') ? 'active bg-primary text-white' : 'text-light' }}"
                    href="{{ url('dashboard') }}">
                    <i class="bi bi-speedometer me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- Categories --}}
            @foreach ($categories as $cat)
                @php
                    $catId = 'cat-' . Str::slug($cat, '-');
                    $currentRoute = request()->route()->getName();
                    $currentSubId = request()->route('id');

                    // active check
                    $subActive = $currentRoute === 'subcategories.form' && request('category_name') === $cat;
                    $activeSub =
                        $currentRoute === 'subcategories.show'
                            ? collect($allSubs[$cat] ?? [])->first(fn($s) => $currentSubId == $s->id)
                            : null;

                    $isOpen = $subActive || $activeSub;
                @endphp

                <li class="nav-item mb-1">
                    {{-- Category --}}
                    <a class="nav-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 text-light
                {{ $isOpen ? 'bg-secondary' : '' }}"
                        data-bs-toggle="collapse" href="#{{ $catId }}"
                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="{{ $catId }}"
                        style="transition: all 0.2s;">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-folder-fill me-2 text-warning"></i>
                            <span class="fw-semibold">{{ $cat }}</span>
                        </div>
                        <i class="bi bi-chevron-down small"></i>
                    </a>

                    {{-- Subcategories --}}
                    <ul id="{{ $catId }}"
                        class="collapse nav flex-column ps-4 mt-2 {{ $isOpen ? 'show' : '' }}">

                        {{-- Add Subcategory --}}
                        <li class="mb-1">
                            <a href="{{ route('subcategories.form', ['category_name' => $cat]) }}"
                                class="nav-link d-flex align-items-center px-2 py-1 rounded-2
                       {{ $subActive ? 'active bg-primary text-white' : 'text-light' }}"
                                style="transition: all 0.2s;">
                                <i class="bi bi-plus-circle me-2"></i>
                                <span>Add Subcategory</span>
                            </a>
                        </li>

                        {{-- Existing Subcategories --}}
                        @foreach ($allSubs[$cat] ?? [] as $sub)
                            <li class="mb-1">
                                <a href="{{ route('subcategories.show', $sub->id) }}"
                                    class="nav-link d-flex align-items-center px-2 py-1 rounded-2
                           {{ $currentRoute === 'subcategories.show' && $currentSubId == $sub->id ? 'active bg-primary text-white' : 'text-light' }}"
                                    style="transition: all 0.2s;">
                                    <i class="bi bi-circle me-2"></i>
                                    <span>{{ $sub->title }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>

        <style>
            #sidebar-nav .nav-link {
                transition: all 0.2s ease-in-out;
                cursor: pointer;
            }

            #sidebar-nav .nav-link:hover {
                background: #3b3b4d !important;
                color: #fff !important;
            }

            #sidebar-nav .nav-link.active {
                background: #007bff !important;
                color: #fff !important;
            }

            #sidebar-nav ul.collapse .nav-link:hover {
                background: #2a2a3b !important;
            }

            #sidebar-nav ul.collapse {
                transition: height 0.3s ease;
            }
        </style>
    </aside>

    <!-- Header -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between toggle-wrapper">
            <div class="left">
                <a href="#" class="js-sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list" style="font-size: 2.5rem; color: black"></i>
                </a>
            </div>
        </div>

        <a href="{{ route('clear.cache') }}" title="Clear Cache">
            <i class="fas fa-broom" style="font-size: 24px; color: #9aa5df; margin-left:20px;"></i>
        </a>

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle" href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li>

                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="{{ asset('NiceAdmin/images/icon/logo-2023 copy.png') }}" alt="Profile"
                            class="rounded-circle" />
                        <span class="d-none d-md-block dropdown-toggle ps-2">NGD Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>NGD Admin</h6>
                            <span>Developer</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ url('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Log Out</span>
                            </a>
                            <form id="logout-form" action="{{ url('logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->

    <main class="main">
        @section('container')
        @show
    </main>


    <!-- Vendor JS Files -->
    <script src="{{ asset('NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('NiceAdmin/assets/js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- existing sidebar toggle state -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("sidebar");
            const header = document.getElementById("header");
            const main = document.querySelector(".main");
            const toggleBtn = document.getElementById("sidebarToggle");
            const isClosed = localStorage.getItem('sidebarClosed') === 'true';
            if (isClosed) {
                sidebar.classList.add("closed");
                main.classList.add("full");
            }

            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                sidebar.classList.toggle("closed");
                main.classList.toggle("full");
                localStorage.setItem('sidebarClosed', sidebar.classList.contains("closed"));
            });
        });
    </script>

    <script>
        (function() {
            function normalizePath(href) {
                try {
                    const url = new URL(href, location.origin);
                    return url.pathname + url.search;
                } catch (e) {
                    return href;
                }
            }

            function restoreSidebarState() {
                const openCat = localStorage.getItem('sidebar_open_cat');
                if (openCat) {
                    const collapseEl = document.getElementById(openCat);
                    if (collapseEl) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, {
                            toggle: false
                        });
                        bsCollapse.show();
                    }
                }

                const activePath = localStorage.getItem('sidebar_active_path');
                if (activePath) {
                    document.querySelectorAll('#sidebar-nav a.nav-link').forEach(a => {
                        a.classList.remove('active', 'bg-primary', 'text-white');
                    });

                    const links = document.querySelectorAll('#sidebar-nav a.nav-link');
                    links.forEach(a => {
                        const p = normalizePath(a.getAttribute('href') || '');
                        if (p === activePath) {
                            a.classList.add('active', 'bg-primary', 'text-white');
                            const parentCollapse = a.closest('.collapse');
                            if (parentCollapse) {
                                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(parentCollapse, {
                                    toggle: false
                                });
                                bsCollapse.show();
                            }
                        }
                    });
                } else {
                    document.querySelectorAll('#sidebar-nav a.nav-link').forEach(a => {
                        if (a.classList.contains('active')) {
                            const parentCollapse = a.closest('.collapse');
                            if (parentCollapse) {
                                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(parentCollapse, {
                                    toggle: false
                                });
                                bsCollapse.show();
                            }
                        }
                    });
                }
            }

            function saveActiveLinkByElement(el) {
                const href = el.getAttribute('href') || '';
                const path = normalizePath(href);
                localStorage.setItem('sidebar_active_path', path);
            }

            function saveOpenCategory(catId) {
                if (catId) localStorage.setItem('sidebar_open_cat', catId);
                else localStorage.removeItem('sidebar_open_cat');
            }

            document.addEventListener('DOMContentLoaded', function() {
                restoreSidebarState();

                document.querySelectorAll('#sidebar-nav a.nav-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        const isToggle = link.hasAttribute('data-bs-toggle'); // category header
                        const dataCat = link.getAttribute('data-sidebar-cat');

                        if (!isToggle) {
                            e.stopPropagation();
                        }

                        if (dataCat) {
                            saveOpenCategory(dataCat);
                        } else {
                            const collapseParent = link.closest('.collapse');
                            if (collapseParent) saveOpenCategory(collapseParent.id);
                        }

                        saveActiveLinkByElement(link);
                    });
                });

                document.querySelectorAll('#sidebar-nav .collapse').forEach(collapseEl => {
                    collapseEl.addEventListener('shown.bs.collapse', function() {
                        saveOpenCategory(this.id);
                    });
                    collapseEl.addEventListener('hidden.bs.collapse', function() {
                        const open = localStorage.getItem('sidebar_open_cat');
                        if (open === this.id) {
                            localStorage.removeItem('sidebar_open_cat');
                        }
                    });
                });
            });
        })();
    </script>

</body>

</html>
